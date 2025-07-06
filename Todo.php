<?php
session_start();
require "config.php";

// ✅ Ensure session is valid
if (!isset($_SESSION['username'], $_SESSION['email'], $_SESSION['pwd'])) {
    header("Location: login.php");
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

// Filtering Logic
$tasknameFilter = '';
$statusFilter = '';

if (isset($_GET['filter'])) {
    $tasknameFilter = $_GET['taskname'] ?? '';
    $statusFilter = $_GET['status'] ?? '';
}

// Build the SQL query for fetching tasks
$sqlTasks = "SELECT taskid, status, taskname, created_at, updated_at FROM task WHERE user_id = '$user_id'";

if (!empty($tasknameFilter)) {
    $sqlTasks .= " AND taskname LIKE '%$tasknameFilter%'";
}

if (!empty($statusFilter)) {
    $sqlTasks .= " AND status = '$statusFilter'";
}

$sqlTasks .= " ORDER BY taskid DESC";

$resultTasks = mysqli_query($conn, $sqlTasks);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Flowmark - To-do List</title>
  <link rel="stylesheet" href="todo.css">
  <style>
    .task-name-list li {
      display: flex;
      align-items: center;
      justify-content: space-between;
      background-color: #2c2c2c;
      color: #fff;
      margin-bottom: 10px;
      padding: 15px;
      border-radius: 8px;
    }

    .task-text {
      flex: 1;
      font-size: 18px;
      text-decoration: none;
      color: #fff;
    }

    .status {
      padding: 5px 10px;
      border-radius: 5px;
      font-weight: bold;
      display: flex;
      justify-content: center;
      position: relative;
      right: 550px;
    }


@media (max-width:755px){
  .status{
    display:flex;
    justify-content:center;
    position:relative;
    right:10px;
}
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
     display: flex;
      text-align: center;
      font-size: 14px;
      color: #ccc;
      justify-content:end;
      margin-right: 10px;
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
  </style>
</head>
<body>
  <header>
    <div class="logo">
      <img src="logo.jpg" alt="Logo" />
    </div>
    <h1>TO-DO LIST</h1>
    <div class="setting">
      <button class="setting-btn" onclick="toggleSetting()">
        <img id="settingIconImg" src="settings.png" alt="Settings" />
      </button>
      <div class="settings-card" id="settings-card">
        <form action="update_user.php" method="POST" enctype="multipart/form-data">
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
          <a href="change_password.php" style="text-decoration: none; color:white; margin-top:10px; gap:10px">change password</a>
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
        <form action="logout.php" method="POST" style="margin-top: 10px;">
          <button type="submit" class="save-btn" style="background-color: #d32f2f;">Logout</button>
        </form>
      </div>
    </div>
  </header>

  <div class="add-task">
    <div class="icon-container">
      <button class="transparent-btn" onclick="window.location.href='task.php'">&#43;</button>
    </div>
  </div>

  <div class="full-line"></div>

  <section>
    <div class="filter-form">
        <form action="Todo.php" method="GET">
            <input type="text" name="taskname" placeholder="Filter by Task Name" value="<?php echo $tasknameFilter; ?>">
            <select name="status">
                <option value="">All Statuses</option>
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

        echo '
        <li>
            <a class="task-text" href="viewtask.php?taskid=' . $task['taskid'] . '">' . $task['taskname'] . '</a>
            <div class="time">
              Created: ' . $task['created_at'] . '<br>
              Updated: ' . $task['updated_at'] . '
            </div>
            <div class="status ' . $statusClass . '">' . $statusText . '</div>
            <span class="action-buttons">
                <a class="edit-page" href="update.php?taskid=' . $task['taskid'] . '">✏️</a>
                <a class="delete-page" href="delete.php?taskid=' . $task['taskid'] . '">🗑️</a>
            </span>
        </li>';
    }
    echo '</ul>';
} else {
    echo '<p style="text-align:center;">No tasks found.</p>';
}
?>
  </section>

  <script src="todo.js"></script>
</body>
</html>
