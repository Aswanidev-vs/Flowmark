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
    <title>View Task</title>
    <style>
        * {
          margin: 0;
          padding: 0;
          box-sizing: border-box;
        }

        body {
          font-family: 'Segoe UI', sans-serif;
          background-color: #f0f2f5;
          display: flex;
          justify-content: center;
          align-items: center;
          min-height: 100vh;
          padding: 2rem;
        }

        .task-container {
          background: #fff;
          padding: 2rem;
          border-radius: 12px;
          box-shadow: 0 4px 16px rgba(0,0,0,0.1);
          max-width: 500px;
          width: 100%;
        }

        .task-container h2 {
          color: #333;
          margin-bottom: 1rem;
        }

        .task-detail {
          margin-bottom: 1.2rem;
        }

        .task-detail label {
          display: block;
          font-weight: 600;
          color: #555;
          margin-bottom: 0.3rem;
        }

        .task-detail p {
          font-size: 1rem;
          color: #333;
          background: #f9f9f9;
          padding: 0.75rem;
          border-radius: 8px;
          border: 1px solid #ddd;
          white-space: pre-wrap; /* preserves line breaks without nl2br */
        }

        .back-btn {
          display: inline-block;
          margin-top: 1rem;
          text-decoration: none;
          color: #fff;
          background: #111;
          padding: 0.6rem 1.2rem;
          border-radius: 8px;
          transition: background 0.2s;
        }

        .back-btn:hover {
          background: #333;
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
