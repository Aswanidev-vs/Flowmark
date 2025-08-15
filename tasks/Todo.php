<?php
header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require "../config/config.php";
session_start();

// ✅ Ensure session is valid
if (!isset($_SESSION['username'], $_SESSION['email'], $_SESSION['pwd'])) {
    header("Location: ../auth/login.html");

}
// ✅ Get user data
$username = $_SESSION['username'];
$email = $_SESSION['email'];
$password = $_SESSION['pwd'];

// ✅ Fetch user and store user ID
$sql = "SELECT * FROM login WHERE email='" . mysqli_real_escape_string($conn, $email) . "' AND pwd='" . mysqli_real_escape_string($conn, $password) . "' AND username='" . mysqli_real_escape_string($conn, $username) . "'";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $_SESSION['user_id'] = $row['uid'];
    $user_id = $row['uid'];
} else {
    echo "Invalid session data.";
    exit();
}

// Filtering Logic
$tasknameFilter = '';
$statusFilter = '';
$priorityFilter = '';

if (isset($_GET['filter'])) {
    $tasknameFilter = trim($_GET['taskname'] ?? '');
    $statusFilter = trim($_GET['status'] ?? '');
    $priorityFilter = trim($_GET['priority'] ?? '');
}

// Build the SQL query for fetching tasks (include new fields)
$sqlTasks = "SELECT taskid, status, priority, taskname, due_date, created_at, updated_at FROM task WHERE user_id = '" . mysqli_real_escape_string($conn, $user_id) . "'";

if (!empty($tasknameFilter)) {
    $sqlTasks .= " AND taskname LIKE '%" . mysqli_real_escape_string($conn, $tasknameFilter) . "%'";
}

if (!empty($statusFilter)) {
    $sqlTasks .= " AND status = '" . mysqli_real_escape_string($conn, $statusFilter) . "'";
}

if (!empty($priorityFilter)) {
    $sqlTasks .= " AND priority = '" . mysqli_real_escape_string($conn, $priorityFilter) . "'";
}

$sqlTasks .= " ORDER BY ";
// sort: overdue first, then due_date asc, priority desc, created_at desc
$sqlTasks .= " (CASE WHEN due_date IS NULL THEN 1 ELSE 0 END), due_date ASC, FIELD(priority,'High','Medium','Low'), created_at DESC";

$resultTasks = mysqli_query($conn, $sqlTasks);

// Build counts for progress pie chart
$counts = [
    'Not Started' => 0,
    'In Progress' => 0,
    'Completed' => 0,
];
$countSql = "SELECT status, COUNT(*) AS c FROM task WHERE user_id='" . mysqli_real_escape_string($conn, $user_id) . "' GROUP BY status";
$resCount = mysqli_query($conn, $countSql);
if ($resCount) {
    while ($r = mysqli_fetch_assoc($resCount)) {
        $s = $r['status'];
        $counts[$s] = intval($r['c']);
    }
}

// Priority distribution
$prioCounts = ['Low'=>0,'Medium'=>0,'High'=>0];
$pcsql = "SELECT priority, COUNT(*) AS c FROM task WHERE user_id='" . mysqli_real_escape_string($conn, $user_id) . "' GROUP BY priority";
$rp = mysqli_query($conn, $pcsql);
if ($rp) {
    while ($p = mysqli_fetch_assoc($rp)) {
        $prioCounts[$p['priority']] = intval($p['c']);
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <title>Flowmark - To-do List (Enhanced)</title>
  <link rel="stylesheet" href="../public/assets/css/todo.css">
  <link rel="icon"  type="image/png" href="../public/assets/images/checked.png">

  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

  <style>
    :root{ --bg:#121212; --card:#1f1f1f; --muted:#9aa6b2; --accent:#6f42c1; }
    body{ background: linear-gradient(180deg,#071021 0%, #051022 60%); color:#e6eef8; font-family: Inter, system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial; }
    header{ display:flex; align-items:center; gap:16px; padding:18px; flex-wrap: wrap; }
    header h1{ margin:0; font-size:18px; letter-spacing:0.6px; }
    .card { background:var(--card); border-radius:12px; padding:12px; }

    /* Task list */
    .task-name-list { list-style:none; padding:0; margin:18px 0; }
    .task-name-list li {
      display:flex; align-items:center; gap:12px;
      background:#161616; color:#fff; margin-bottom:10px;
      padding:12px; border-radius:8px; box-shadow: 0 4px 12px rgba(0,0,0,0.4);
      flex-wrap: wrap;
      justify-content: space-between;
    }
    .task-text { flex: 1 1 100%; font-size:16px; color:#f1f5f9; text-decoration:none; }

    .meta { display:flex; flex-direction:column; gap:8px; align-items:flex-end; min-width:170px; }
    .badge-container { display:flex; align-items:center; gap:8px; flex-wrap: wrap; }
    .status { padding:6px 10px; border-radius:8px; font-weight:700; font-size:13px; color:#fff; }
    .not-started { background:#e74c3c; }
    .in-progress { background:#f1c40f; color:#111; }
    .completed { background:#2ecc71; color:#042; }

    .priority-badge { padding:4px 8px; border-radius:8px; font-size:12px; }
    .priority-badge.Low { background:#6c757d; }
    .priority-badge.Medium { background:#0d6efd; }
    .priority-badge.High { background:#dc3545; }

    .deadline { font-size:12px; padding:4px 8px; border-radius:6px; }
    .deadline.overdue { background:#7f1d1d; color:#ffdede; }
    .deadline.soon { background:#ffd966; color:#2b2b2b; }
    .timestamps { font-size:12px; color:var(--muted); }

    .action-buttons { display:flex; gap:8px; }
    .action-buttons a { text-decoration:none; color:#fff; background:#2b2b2b; padding:8px 10px; border-radius:8px; }

    /* Filters & panels */
    .layout { display:grid; grid-template-columns: 1fr 360px; gap:18px; padding:18px; }

    .filters { display:flex; gap:10px; align-items:center; }
    .filters form { display:flex; gap:8px; width:100%; align-items:center; flex-wrap: wrap; }
    .filters input, .filters select { padding:8px 10px; border-radius:8px; border: none; background:#0f1720; color:#fff; }
    .filters button { padding:8px 12px; border-radius:8px; background:var(--accent); color:#fff; border:none; }
    .filters button[type="button"] { background: #333; }

    /* Small widgets */
    .widget { margin-bottom:12px; }
    #calendar { 
      background:#081026; 
      padding:8px; 
      border-radius:10px; 
      /* Fix for calendar not showing */
      display: block; 
      height: 400px;
      width: 100%;
    }

    /* Responsive adjustments */
    @media (max-width:1100px) {
        .layout { grid-template-columns: 1fr; }
        .meta { align-items: flex-start; min-width: unset; }
    }

    @media (max-width: 768px) {
        header { flex-direction: column; align-items: flex-start; gap: 8px; }
        header div[style*="margin-left:auto"] { margin-left: 0 !important; width: 100%; justify-content: space-between; }
        .filters form { flex-direction: column; align-items: stretch; }
        .filters input, .filters select, .filters button { width: 100%; margin-bottom: 8px; }
    }
    
    @media (max-width: 600px) {
        .task-name-list li { 
            flex-direction: column; 
            align-items: flex-start;
        }
        .task-text { 
            flex: 1 1 100%; 
        }
        .meta {
            order: 2; 
            width: 100%;
            align-items: flex-start;
            margin-top: 8px;
            min-width: unset;
        }
        .action-buttons { 
            order: 3; 
            margin-top: 8px;
        }
    }
  </style>
</head>
<body>
  <header>
    <div class="logo"><img src="../public/assets/images/profile.png" alt="Logo" style="width:44px;border-radius:8px"></div>
    <div>
      <h1>FLOWMARK • To‑do</h1>
      <div style="font-size:12px; color:var(--muted)">Welcome, <?php echo htmlspecialchars($row['username']); ?></div>
    </div>
    <div style="margin-left:auto; display:flex; gap:8px; align-items:center; flex-wrap: wrap;">
      <a href="task.php" class="card" style="padding:8px 12px; text-decoration:none; color:#fff">+ New Task</a>

  <div class="setting">
      <button class="setting-btn" onclick="toggleSetting()">
<img id="settingIconImg" src="../public/assets/images/settings.png" alt="Settings" />
      </button>
      <div class="settings-card" id="settings-card">
<form action="../account/update_user.php" method="POST" enctype="multipart/form-data">
          <div class="setting-item">
            <label for="username">Name:</label>
            <input type="text" name="username" id="username" value="<?php echo $row['username']; ?>" />
          </div>
          <div class="password">
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" value="<?php echo $row['pwd']; ?>" readonly />
          </div>
         <div style="margin-top: 0.3rem;">
            <input type="checkbox" id="showPwd" onclick="togglePasswordVisibility()">
            <label for="showPwd">Show Password</label>
          </div>
<a href="../password/change_password.php" style="text-decoration: none; color:white; margin-top:10px; gap:10px">change password</a>
        <script>
            function togglePasswordVisibility() {
                var pass = document.getElementById("password");
                if (pass.type === "password") {
                    pass.type = "text";
                } else {
                    pass.type = "password";
                }
            }
        </script>
          <button type="submit" name="save" class="save-btn">Save</button>
        </form>
<form action="../auth/logout.php" method="POST" style="margin-top: 10px;">
          <button type="submit" class="save-btn" style="background-color:rgb(112, 112, 112);">Logout</button>
        </form>
        <div class="delete-btn">
<form action="../account/current_password.php" method="POST" style="margin-top: 10px;">
        <button type="submit" class="daccount">Delete My Account</button>
    </form>
</div>

<style>

.setting {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: flex-end;
}

.setting-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 45px;
  width: 45px;
  border: none;
  background: transparent;
  cursor: pointer;
  border-radius: 50%;
  padding: 0;
  overflow: hidden;
}

#settingIconImg {
  height: 100%;
  width: 100%;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid transparent;
  transition: border-color 0.3s ease;
}

.setting-btn:hover #settingIconImg {
  border-color: #7c4dff;
}

.settings-card {
  position: absolute;
  top: 60px;
  right: 0;
  width: 280px;
  background: #333;
  color: #f0f0f0;
  border-radius: 10px;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
  padding: 25px;
  display: none;
  z-index: 100;
  opacity: 0;
  transform: translateY(-10px);
  transition: opacity 0.3s ease, transform 0.3s ease;
}

.settings-card.show {
  display: block;
  opacity: 1;
  transform: translateY(0);
}

.settings-card h3 {
  font-size: clamp(1rem, 3vw, 1.4rem);
  color: #ffffff;
  text-align: center;
  margin: 0 0 20px 0;
}

.daccount{
   background-color: #ff0000ff;
  color: white;
  padding: 12px 20px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  width: 100%;
  font-size: clamp(1rem, 2vw, 1.1rem);
  font-weight: 600;
  margin-top: 10px;
  transition: background-color 0.3s ease, transform 0.2s ease;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}
</style>

    </div>
  </header>
  <div class="layout">
    <div>
      <div class="card filters" style="align-items:center; margin-bottom:12px;">
        <form method="GET" action="Todo.php">
            <input type="text" name="taskname" placeholder="Filter by Task Name" value="<?php echo htmlspecialchars($tasknameFilter); ?>">
            <select name="status">
                <option value="">All Statuses</option>
                <option value="Not Started" <?php if ($statusFilter === 'Not Started') echo 'selected'; ?>>Not Started</option>
                <option value="In Progress" <?php if ($statusFilter === 'In Progress') echo 'selected'; ?>>In Progress</option>
                <option value="Completed" <?php if ($statusFilter === 'Completed') echo 'selected'; ?>>Completed</option>
            </select>
            <select name="priority">
                <option value="">All Priorities</option>
                <option value="Low" <?php if ($priorityFilter === 'Low') echo 'selected'; ?>>Low</option>
                <option value="Medium" <?php if ($priorityFilter === 'Medium') echo 'selected'; ?>>Medium</option>
                <option value="High" <?php if ($priorityFilter === 'High') echo 'selected'; ?>>High</option>
            </select>
            <button type="submit" name="filter">Apply Filter</button>
            <button type="button" onclick="window.location.href='Todo.php'">Clear</button>
        </form>
      </div>

      <div class="card" style="display:flex; gap:12px; align-items:center; margin-bottom:12px; flex-wrap: wrap;">
        <div style="width:160px; max-width: 100%;">
          <canvas id="progressChart"></canvas>
        </div>
        <div style="flex:1;">
          <div style="display:flex; gap:12px; align-items:center; flex-wrap: wrap;">
            <div>
              <strong><?php echo array_sum($counts); ?></strong><div class="tiny">Total tasks</div>
            </div>
            <div>
              <strong><?php echo $counts['Completed']; ?></strong><div class="tiny">Completed</div>
            </div>
            <div>
              <strong><?php echo $prioCounts['High']; ?></strong><div class="tiny">High priority</div>
            </div>
          </div>
          <div style="margin-top:10px; color:var(--muted); font-size:13px">Pro tip: set due dates and priorities when creating tasks to get deadline warnings and calendar events.</div>
        </div>
      </div>

      <?php
      if ($resultTasks && mysqli_num_rows($resultTasks) > 0) {
          echo '<ul class="task-name-list">';
          while ($task = mysqli_fetch_assoc($resultTasks)) {
              $statusText = $task['status'];
              $statusClass = '';

              switch (strtolower(trim($statusText))) {
                  case 'not started':
                      $statusClass = 'not-started';
                      break;
                  case 'in progress':
                      $statusClass = 'in-progress';
                      break;
                  case 'completed':
                      $statusClass = 'completed';
                      break;
                  default:
                      $statusClass = '';
              }

              // Priority badge
              $priority = $task['priority'] ?: 'Medium';
              $priorityBadge = '<span class="priority-badge ' . htmlspecialchars($priority) . '">' . htmlspecialchars($priority) . '</span>';

              // Deadline warnings
              $deadlineWarning = '';
              if (!empty($task['due_date']) && $task['due_date'] !== '0000-00-00') {
                  $dueDate = new DateTime($task['due_date']);
                  $today = new DateTime();
                  $interval = (int)$today->diff($dueDate)->format('%r%a'); // days difference
                  if ($interval < 0) {
                      $deadlineWarning = '<span class="deadline overdue">Overdue</span>';
                  } elseif ($interval <= 2) {
                      $deadlineWarning = '<span class="deadline soon">Due ' . abs($interval) . 'd</span>';
                  } else {
                      $deadlineWarning = '<span class="deadline">Due ' . htmlspecialchars($task['due_date']) . '</span>';
                  }
              }

              echo "\n        <li>\n            <a class=\"task-text\" href=\"viewtask.php?taskid={$task['taskid']}\">" . htmlspecialchars($task['taskname']) . "</a>\n            <div class=\"meta\">\n              <div class=\"badge-container\">\n                <div class=\"status $statusClass\">" . htmlspecialchars($statusText) . "</div>\n                $priorityBadge\n                $deadlineWarning\n              </div>\n              <div class=\"timestamps\">Created: {$task['created_at']}<br>Updated: {$task['updated_at']}</div>\n            </div>\n            <span class=\"action-buttons\">\n                <a class=\"edit-page\" href=\"update.php?taskid={$task['taskid']}\">✏</a>\n                <a class=\"delete-page\" href=\"delete.php?taskid={$task['taskid']}\">🗑</a>\n            </span>\n        </li>";
          }
          echo '</ul>';
      } else {
          echo '<p style="text-align:center; color:var(--muted)">No tasks found.</p>';
      }
      ?>
    </div>

    <aside>
      <div class="card widget">
        <h4 style="margin:0 0 8px 0">Upcoming (next 7 days)</h4>
        <div id="upcomingList">
          <?php
          // fetch upcoming tasks
          $upSql = "SELECT taskid, taskname, due_date, priority, status FROM task WHERE user_id='" . mysqli_real_escape_string($conn, $user_id) . "' AND due_date IS NOT NULL AND due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) ORDER BY due_date ASC";
          $ru = mysqli_query($conn, $upSql);
          if ($ru && mysqli_num_rows($ru) > 0) {
              echo '<ul style="list-style:none;padding:0;margin:0;">';
              while ($u = mysqli_fetch_assoc($ru)) {
                  $d = htmlspecialchars($u['due_date']);
                  $n = htmlspecialchars($u['taskname']);
                  echo "<li style='padding:8px 6px; border-bottom:1px solid rgba(255,255,255,0.03)'>\n                          <div style=\"font-weight:600\">{$n}</div>\n                          <div style=\"font-size:12px;color:var(--muted)\">Due: {$d} • {$u['priority']} • {$u['status']}</div>\n                        </li>";
              }
              echo '</ul>';
          } else {
              echo '<div style="color:var(--muted)">No upcoming tasks.</div>';
          }
          ?>
        </div>
      </div>

      <div class="card widget">
        <h4 style="margin:0 0 8px 0">Calendar</h4>
        <div id="calendar"></div>
      </div>

      <div class="card widget">
        <h4 style="margin:0 0 8px 0">Priority distribution</h4>
        <div style="display:flex; gap:8px; align-items:center; flex-wrap: wrap;">
          <div style="flex:1">High: <?php echo $prioCounts['High']; ?></div>
          <div style="flex:1">Medium: <?php echo $prioCounts['Medium']; ?></div>
          <div style="flex:1">Low: <?php echo $prioCounts['Low']; ?></div>
        </div>
      </div>

    </aside>
  </div>

  <script>
    // Chart.js pie
    const ctx = document.getElementById('progressChart');
    if (ctx) {
      new Chart(ctx, {
        type: 'pie',
        data: {
          labels: ['Not Started','In Progress','Completed'],
          datasets: [{
            data: [<?php echo intval($counts['Not Started']); ?>, <?php echo intval($counts['In Progress']); ?>, <?php echo intval($counts['Completed']); ?>],
            backgroundColor: ['#e74c3c','#f1c40f','#2ecc71']
          }]
        },
        options: { plugins: { legend: { position: 'bottom' } } }
      });
    }

    // FullCalendar events from PHP
    document.addEventListener('DOMContentLoaded', function() {
      var calendarEl = document.getElementById('calendar');
      if (!calendarEl) return;
      var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 400,
        headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,listWeek' },
        events: [
          <?php
            $eventSql = "SELECT taskid, taskname, due_date, priority FROM task WHERE user_id='" . mysqli_real_escape_string($conn, $user_id) . "' AND due_date IS NOT NULL";
            $eventResult = mysqli_query($conn, $eventSql);
            $evItems = [];
            while ($ev = mysqli_fetch_assoc($eventResult)) {
                $title = addslashes($ev['taskname'] . ' • ' . $ev['priority']);
                $color = '';
                switch ($ev['priority']) {
                    case 'High':
                        $color = '#dc3545';
                        break;
                    case 'Medium':
                        $color = '#0d6efd';
                        break;
                    case 'Low':
                        $color = '#6c757d';
                        break;
                    default:
                        $color = '#6c757d'; // Default color for tasks without a priority
                }
                $evItems[] = "{ id: '{$ev['taskid']}', title: '{$title}', start: '{$ev['due_date']}', color: '{$color}' }";
            }
            echo implode(",\n", $evItems);
          ?>
        ],
        eventClick: function(info) {
          window.location.href = 'viewtask.php?taskid=' + info.event.id;
        }
      });
      calendar.render();
    });
  </script>

  <script src="../public/assets/js/todo.js"></script>
</body>
</html>