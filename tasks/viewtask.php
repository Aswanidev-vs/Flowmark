<?php
header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
require "../config/config.php";
session_start();

if (!isset($_GET['taskid'])) {
    echo "No task selected.";
    exit();
}else{
    $taskid=$_GET['taskid'];
$sql = "SELECT * FROM task WHERE taskid = $taskid";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $task = mysqli_fetch_assoc($result);
} else {
    echo "Task not found.";
    exit();
}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
      <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
    <title>View Task</title>
     <link rel="icon"  type="image/png" href="../public/assets/images/checked.png">
        <style>
      * {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Segoe UI', sans-serif;
  background-color: #212121;
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  padding: 2rem;
}

.task-container {
  background: #2a2a2a;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
  max-width: 520px;
  width: 100%;
}

.task-container h2 {
  color: #ffffff;
  margin-bottom: 1.5rem;
  text-align: center;
}

.task-detail {
  margin-bottom: 1.4rem;
}

.task-detail label {
  display: block;
  font-weight: 600;
  color: #bdbdbd;
  margin-bottom: 0.4rem;
}

.task-detail p {
  font-size: 1rem;
  color: #e0e0e0;
  background: #333;
  padding: 0.9rem 1rem;
  border-radius: 8px;
  border: 1px solid #444;
  white-space: pre-wrap;
  line-height: 1.5;
}

.back-btn {
  display: inline-block;
  margin-top: 1rem;
  text-decoration: none;
  color: #ffffff;
  background-color: #424242;
  padding: 0.6rem 1.2rem;
  border-radius: 8px;
  font-weight: bold;
  transition: background-color 0.2s ease;
}

.back-btn:hover {
  background-color: #616161;
}

    </style>
</head>
<body>

<div class="task-container">
    <h2>Task Details</h2>

    <div class="task-detail">
        <label>Task Name:</label>
        <p><?php echo $task['taskname']; ?></p>
    </div>

    <div class="task-detail">
        <label>Description:</label>
        <p><?php echo $task['description']; ?></p>
    </div>

    <div class="task-detail">
        <label>Status:</label>
        <p><?php echo $task['status']; ?></p>
    </div>

    <a href="Todo.php" class="back-btn">← Back to Tasks</a>
</div>

</body>
</html>
