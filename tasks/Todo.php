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
    exit();
}

// ✅ Get user data
$username = $_SESSION['username'];
$email = $_SESSION['email'];
$password = $_SESSION['pwd'];

// ✅ Fetch user and store user ID
$sql = "SELECT * FROM login WHERE email='$email' AND pwd='$password' AND username='$username'";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $_SESSION['user_id'] = $row['uid'];
    $user_id = $row['uid'];
} else {
    echo "Invalid session data.";
    exit();
}

// ---------------------------------
// ✅ FIX: Fetch Metrics from the database
// ---------------------------------
$metrics = [
    'completed' => 0,
    'highPriority' => 0,
];

// Query for completed tasks
$sqlCompleted = "SELECT COUNT(*) AS completed_count FROM task WHERE user_id = '$user_id' AND status = 'Completed'";
$resultCompleted = mysqli_query($conn, $sqlCompleted);
if ($resultCompleted) {
    $completedRow = mysqli_fetch_assoc($resultCompleted);
    $metrics['completed'] = $completedRow['completed_count'];
}

// Query for high priority tasks
$sqlHighPriority = "SELECT COUNT(*) AS high_priority_count FROM task WHERE user_id = '$user_id' AND priority = 'High'";
$resultHighPriority = mysqli_query($conn, $sqlHighPriority);
if ($resultHighPriority) {
    $highPriorityRow = mysqli_fetch_assoc($resultHighPriority);
    $metrics['highPriority'] = $highPriorityRow['high_priority_count'];
}


// Filtering Logic
$tasknameFilter = '';
$statusFilter = '';

if (isset($_GET['filter'])) {
    $tasknameFilter = $_GET['taskname'] ?? '';
    $statusFilter = $_GET['status'] ?? '';
}

// ---------------------------------
// ✅ FIX: Added priority and due_date to the SQL query
// ---------------------------------
$sqlTasks = "SELECT taskid, status, taskname, created_at, updated_at, priority, due_date FROM task WHERE user_id = '$user_id'";

if (!empty($tasknameFilter)) {
    $sqlTasks .= " AND taskname LIKE '%" . mysqli_real_escape_string($conn, $tasknameFilter) . "%'";
}

if (!empty($statusFilter)) {
    $sqlTasks .= " AND status = '" . mysqli_real_escape_string($conn, $statusFilter) . "'";
}

$sqlTasks .= " ORDER BY taskid DESC";

$resultTasks = mysqli_query($conn, $sqlTasks);

// Functions for rendering badges
function renderStatusBadge($status) {
    $class = match(strtolower(trim($status))) {
        'not started' => 'not-started',
        'in progress' => 'in-progress',
        'completed'   => 'completed',
        default       => ''
    };
    return "<div class='status $class'>" . htmlspecialchars($status) . "</div>";
}

function renderPriorityBadge($priority) {
    return "<span class='priority-badge " . htmlspecialchars($priority) . "'>" . htmlspecialchars($priority) . "</span>";
}

function renderDeadline($due_date) {
    if (empty($due_date) || $due_date === '0000-00-00') return '';
    $due = new DateTime($due_date);
    $today = new DateTime();
    $interval = (int)$today->diff($due)->format('%r%a');
    if ($interval < 0) return '<span class="deadline overdue">Overdue</span>';
    if ($interval <= 2) return '<span class="deadline soon">Due ' . abs($interval) . 'd</span>';
    return '<span class="deadline">Due ' . htmlspecialchars($due_date) . '</span>';
}

// ---------------------------------
// ✅ NEW: Fetch tasks for the calendar
// ---------------------------------
$calendar_tasks = [];
$sql_calendar = "SELECT taskname, due_date, status, taskid FROM task WHERE user_id = '$user_id' AND due_date IS NOT NULL";
$result_calendar = mysqli_query($conn, $sql_calendar);

if ($result_calendar && mysqli_num_rows($result_calendar) > 0) {
    while ($task = mysqli_fetch_assoc($result_calendar)) {
        $color = '#2ecc71'; // Completed
        if (strtolower($task['status']) === 'not started') {
            $color = '#e74c3c';
        } elseif (strtolower($task['status']) === 'in progress') {
            $color = '#f1c40f';
        }

        $calendar_tasks[] = [
            'title' => htmlspecialchars($task['taskname']),
            'start' => $task['due_date'],
            'color' => $color,
            'url' => 'viewtask.php?taskid=' . htmlspecialchars($task['taskid'])
        ];
    }
}
$json_calendar_tasks = json_encode($calendar_tasks);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Flowmark</title>
    <link rel="stylesheet" href="../public/assets/css/todo.css">
    <link rel="icon" type="image/png" href="../public/assets/images/checked.png">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

<style>
    /* New CSS for a more compact chart */
    
    /* Fixed CSS for tasks list and status */
    .task-name-list {
        list-style: none;
        padding: 0;
    }
    
    .task-name-list li {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background-color: #2c2c2c;
        color: #fff;
        margin-bottom: 10px;
        padding: 15px;
        border-radius: 8px;
        flex-wrap: wrap;
        gap: 10px;
    }
    .task-text {
        flex: 1;
        font-size: 18px;
        text-decoration: none;
        color: #fff;
        word-break: break-word;
    }
    .status {
        padding: 5px 10px;
        border-radius: 5px;
        font-weight: bold;
        white-space: nowrap;
        margin-right: 100px;
    }
    .not-started {
      background-color: #e74c3c;
    }
    .in-progress {
      background-color: #f1c40f;
    }
    .completed {
      background-color: #2ecc71;
    }
    .time {
        text-align: right;
        font-size: 14px;
        color: #ccc;
        min-width: 150px;
    }
    .action-buttons {
      display: flex;
      gap: 10px;
    }
    .edit-page, .delete-page {
      font-size: 18px;
      color: #fff;
      text-decoration: none;
      background-color: #444;
      border-radius: 50%;
      padding: 10px;
      transition: background 0.3s;
    }
    .edit-page:hover {
      background-color: #2980b9;
    }
    .delete-page:hover {
      background-color: #c0392b;
    }
    .filter-form {
        margin-bottom: 20px;
        display: flex;
        gap: 10px;
        align-items: center;
    }
    .filter-form form {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
    }
    .filter-form input, .filter-form select, .filter-form button {
        padding: 8px;
        border: 1px solid #555;
        background-color: #333;
        color: #fff;
        border-radius: 5px;
    }
    .daccount{
      background-color: #d32f2f;
      border-radius:10px;
      border:none;
      color:white;
      padding:5px 100px;
    }
    .daccount:hover{
      transform: translateY(-1px);
    }
    .metric-container {
        display: flex;
        justify-content: space-between;
        color: #fff;
        position: absolute;
        top: 150px;
        left: 90pt;
    }
  
      .metric-item {
        text-align: center;
        display: flex;
        justify-content: space-between;
        gap: 43px;
        
      }
      .metric-item h2 {
        margin: 0;
        font-size: 2em;
        position: relative;
        left: 90px;
        bottom: 25px;

        
      }
    .priority-badge {
        padding: 3px 8px;
        border-radius: 5px;
        font-size: 0.8em;
        font-weight: bold;
        color: #fff;
        white-space: nowrap;
    }
    .High { background-color: #e74c3c; } /* Red */
    .Medium { background-color: #f1c40f; } /* Yellow */
    .Low { background-color: #2ecc71; } /* Green */
    .deadline {
        padding: 3px 8px;
        border-radius: 5px;
        font-size: 0.8em;
        font-weight: bold;
        color: #fff;
    }
    .overdue { background-color: #c0392b; }
    .soon { background-color: #f39c12; }
@media(max-width:600px){
    .status{
    display:flex;
    justify-content: center;
    position: relative;
    left: 45px;
    }
}
.card-widget {
    position: relative;
    left: 50%;
    top:39%;
    transform: translateX(-50%);
}
.card-wrapper{
    position: absolute;
    border-radius: 7px;
    padding-top: 0px;
    padding-left:10px;
    padding-right: 10px;
    /* padding: 20px;  */
    /* border: 2px solid #00e1ffff; */
    background-color: #424040ff;
left: 8%;
top: 48%;
transform:translate(-50%,-50%);
margin-top: 16px;
}
/* FullCalendar specific styles */
#calendar {
    max-width: 600px;
    max-height: 300px;
    background-color: #2c2c2c;
    color: #fff;
    padding: 10px 15px;
    border-radius: 8px;
    position: absolute;
    top: 39%;
    right: 10%;
    /* bottom: 10%; */
    transform: translateY(-50%);
}
.fc-toolbar-title {
    color: #fff !important;
    
}
.fc-daygrid-day-number {
    color: #fff !important;
}
.fc-col-header-cell-cushion {
    color: #fff !important;
}
.fc-button {
    background-color: #444 !important;
    border: 1px solid #555 !important;
    color: #fff !important;
}
.fc-event {
    border: none !important;
    color: #fff !important;
    font-size: 0.8em;
    padding: 2px;
}

</style>
</head>
<body>
    <header>
    <div class="logo">
      <img src="../public/assets/images/profile.png" alt="Logo" />
    </div>
    <h1>FlowMark</h1>
    <div class="ui_switch">
      <a href="../../ui2.php">
        <h2>UI-2</h2>
      </a>
    </div>
    <style>
      /* Container for the UI switch link */
.ui_switch {
  display: inline-block; /* Allows the container to size to its content */
  margin: 20px; /* Adds space around the container */
}

/* Styling for the link itself to make it look like a button */
.ui_switch a {
  background-color: #555; /* A dark gray background */
  color: white; /* White text color */
  text-decoration: none; /* Removes the default underline from the link */
  padding: 10px 20px; /* Adds padding inside the button */
  border-radius: 5px; /* Rounds the corners */
  transition: background-color 0.3s ease; /* Smooth transition for hover effect */
  display: block; /* Makes the entire link area clickable */
}

/* Hover effect */
.ui_switch a:hover {
  background-color: #777; /* Lightens the background on hover */
}

/* Styling for the text inside the link */
.ui_switch h2 {
  margin: 0; /* Removes default margin from the heading */
  font-size: 1.2em; /* Sets a reasonable font size */
  font-weight: normal; /* Makes the text not bold */
}
    </style>
    <div class="setting">
      <button class="setting-btn" onclick="toggleSetting()">
        <img id="settingIconImg" src="../public/assets/images/settings.png" alt="Settings" />
      </button>
      <div class="settings-card" id="settings-card">
        <form action="../account/update_user.php" method="POST" enctype="multipart/form-data">
          <div class="setting-item">
            <label for="username">Name:</label>
            <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($row['username']); ?>" />
          </div>
          <div class="password">
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" value="<?php echo htmlspecialchars($row['pwd']); ?>" readonly />
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
      </div>
    </div>
  </header>

  <div class="add-task">
    <div class="icon-container">
      <button class="transparent-btn" onclick="window.location.href='task.php'">&#43;</button>
    </div>
  </div>
  
  <div class="chart-container">
    <canvas id="statusPieChart"></canvas>
  </div>

  <div class="metric-container">
    <div class="metric-item">
        <h2><?php echo $metrics['completed']; ?></h2>
        <p>Completed</p>
    </div>
    <div class="metric-item">
        <h2><?php echo $metrics['highPriority']; ?></h2>
        <p>High priority</p>
    </div>
  </div>
          

  <div class="full-line"></div>

  <section>
    <div id="calendar"></div>

    <div class="filter-form">
      <form action="Todo.php" method="GET">
        <input type="text" name="taskname" placeholder="Filter by Task Name" value="<?php echo htmlspecialchars($tasknameFilter); ?>">
        <select name="status">
               <option value="" <?php if (empty($statusFilter)) echo 'selected'; ?>>All Statuses</option>
               <option value="Not Started" <?php if ($statusFilter === 'Not Started') echo 'selected'; ?>>Not Started</option>
               <option value="In Progress" <?php if ($statusFilter === 'In Progress') echo 'selected'; ?>>In Progress</option>
               <option value="Completed" <?php if ($statusFilter === 'Completed') echo 'selected'; ?>>Completed</option>
        </select>
        <button type="submit" name="filter">Apply Filter</button>
        <button type="button" onclick="window.location.href='Todo.php'">Clear Filter</button>
      </form>
    </div>

    <?php
    if ($resultTasks && mysqli_num_rows($resultTasks) > 0) {
        echo '<ul class="task-name-list">';
        while ($task = mysqli_fetch_assoc($resultTasks)) {
            $statusText = htmlspecialchars($task['status']);
            $statusClass = '';
            $priorityText = htmlspecialchars($task['priority'] ?? 'Low'); // Default to 'Low' if not set
            $due_date = htmlspecialchars($task['due_date'] ?? '');

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
            echo '</div>';
            echo '
            <li>
                <a class="task-text" href="viewtask.php?taskid=' . htmlspecialchars($task['taskid']) . '">' . htmlspecialchars($task['taskname']) . '</a>
                <div>' . renderPriorityBadge($priorityText) . '</div>
                <div class="time">
                    Created: ' . htmlspecialchars($task['created_at']) . '<br>
                    Updated: ' . htmlspecialchars($task['updated_at']) . '<br>' . renderDeadline($due_date) . '
                </div>
                <div class="status ' . htmlspecialchars($statusClass) . '">' . htmlspecialchars($statusText) . '</div>
                <span class="action-buttons">
                    <a class="edit-page" href="update.php?taskid=' . htmlspecialchars($task['taskid']) . '">✏️</a>
                    <a class="delete-page" href="delete.php?taskid=' . htmlspecialchars($task['taskid']) . '">🗑️</a>
                </span>
            </li>';
        }
        echo '</ul>';
    } else {
        echo '<p style="text-align:center;">No tasks found.</p>';
    }
    ?>
  </section>
  <div class="card-wrapper">
  <div class="card-widget">
            <div id="upcomingList">
          <h3>Upcoming (next 7 days)</h3>
          <?php
          // fetch upcoming tasks
          $upSql = "SELECT taskid, taskname, due_date, priority, status FROM task WHERE user_id='" . mysqli_real_escape_string($conn, $user_id) . "' AND due_date IS NOT NULL AND due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) ORDER BY due_date ASC";
          $ru = mysqli_query($conn, $upSql);
          if ($ru && mysqli_num_rows($ru) > 0) {
              echo '<ul style="list-style:none;padding:0;margin:0;">';
              while ($u = mysqli_fetch_assoc($ru)) {
                  $d = htmlspecialchars($u['due_date']);
                  $n = htmlspecialchars($u['taskname']);
                  echo "<li style='padding:8px 6px; border-bottom:1px solid rgba(255,255,255,0.03)'>\n <div style=\"font-weight:600\">{$n}</div>\n<div style=\"font-size:12px;color:var(--muted)\">Due: {$d} • {$u['priority']} • {$u['status']}</div>\n                        </li>";
              }
              echo '</ul>';
          } else {
              echo '<div style="color:var(--muted)">No upcoming tasks.</div>';
          }
          ?>
            </div>
          </div>
</div>
  <script src="../public/assets/js/todo.js"></script>
  
<script>
    // PHP to get status counts
    <?php
      function getStatusCounts($user_id, $conn) {
          $counts = ['Not Started' => 0, 'In Progress' => 0, 'Completed' => 0];
          $sql = "SELECT status, COUNT(*) AS c FROM task WHERE user_id='" . mysqli_real_escape_string($conn, $user_id) . "' GROUP BY status";
          $res = mysqli_query($conn, $sql);
          if ($res) {
              while ($r = mysqli_fetch_assoc($res)) {
                  if (isset($counts[$r['status']])) {
                      $counts[$r['status']] = intval($r['c']);
                  }
              }
          }
          return $counts;
      }
      $statusCounts = getStatusCounts($user_id, $conn);
    ?>
    const notStartedCount = <?php echo $statusCounts['Not Started']; ?>;
    const inProgressCount = <?php echo $statusCounts['In Progress']; ?>;
    const completedCount = <?php echo $statusCounts['Completed']; ?>;

    const ctx = document.getElementById('statusPieChart');
    
    new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['Not Started', 'In Progress', 'Completed'],
        datasets: [{
          label: 'Task Status',
          data: [notStartedCount, inProgressCount, completedCount],
          backgroundColor: [
            '#e74c3c', // Not Started
            '#f1c40f', // In Progress
            '#2ecc71'  // Completed
          ],
          hoverOffset: 4
        }]
      },
      options: {
    responsive: true,
    maintainAspectRatio: true, 
    width:180,
    height:100,
    plugins: {
        legend: {
            position: 'bottom',  // Moves the legend to the right
            labels: {
                color: '#fff',
                font: {
                    size: 10
                }
            }
        },
        title: {
            display: false, // Hides the title
          }
        }
        
      }
    });

    // ✅ NEW: FullCalendar Initialization
    document.addEventListener('DOMContentLoaded', function() {
        const calendarEl = document.getElementById('calendar');
        const tasks = <?php echo $json_calendar_tasks; ?>;
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            events: tasks,
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            themeSystem: 'standard',
            eventClick: function(info) {
                if (info.event.url) {
                    info.jsEvent.preventDefault();
                    window.location.href = info.event.url;
                }
            }
        });
        calendar.render();
    });
</script>
<style>
    canvas{
      position: relative;
      top:3px;
      /* border: 2px solid tomato; */
    }
    .chart-container {
      
        max-width: 150px; /* Reduced the maximum width for a smaller chart */
      
      position: relative;
        bottom: 70pt;
    }
    .full-line{
    position: relative;
    bottom: 80pt;
    }
    .filter-form{
      position: relative;
      bottom:140pt;
      right: 230pt;
      width: 0px;
      height: 0px;
      background: transparent;
      min-width: fit-content;
      border: none;
      box-shadow: none;
    }
 
</style>
</body>
</html>