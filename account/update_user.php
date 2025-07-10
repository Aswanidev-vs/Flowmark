<?php
require "../config/config.php"; // <--- Make sure this is added if missing
session_start();

// ✅ Ensure user is logged in and we have their user_id from session
if (!isset($_SESSION['user_id'])) {
    echo "Unauthorized access.";
    exit();
}

if (isset($_POST['save'])) {
    $user_id = $_SESSION['user_id'];
    $username = $_POST['username'];

    // ✅ Update user data in the database first
    $update_sql = "UPDATE login SET username='$username' WHERE uid='$user_id'";
    $result = mysqli_query($conn, $update_sql);

    if ($result) {
        // ✅ After successful update, update session values
        $_SESSION['username'] = $username;
 
        header("Location: ../tasks/Todo.php");
        exit();
    } else {
        echo "Update failed: " . mysqli_error($conn);
    }
}
?>
