<?php
require "../config/config.php";
session_start();

if (!isset($_SESSION['username'], $_SESSION['email'])) {
    header("Location: ../home.html");
    exit();
}

$username = $_SESSION['username'];
$email = $_SESSION['email'];
$entered_pwd = $_POST['confirm_password'] ?? '';

if (empty($entered_pwd)) {
    echo "<script>
        alert('Please enter your current password.');
        window.location.href='current_password.php';
    </script>";
    exit();
}

// Step 1: Fetch user credentials from DB
$sql = "SELECT uid, pwd FROM login WHERE username='$username' AND email='$email'";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $user_id = $row['uid'];
    $stored_pwd = $row['pwd'];

    // Step 2: Match entered password with stored password
    if ($entered_pwd === $stored_pwd) {
        // Step 3: Delete tasks
        $deleteTasksQuery = "DELETE FROM task WHERE user_id='$user_id'";
        $deleteTasksResult = mysqli_query($conn, $deleteTasksQuery);

        // Step 4: Delete user account
        $deleteUserQuery = "DELETE FROM login WHERE uid='$user_id'";
        $deleteUserResult = mysqli_query($conn, $deleteUserQuery);

        if ($deleteUserResult) {
            session_unset();
            session_destroy();
          echo "<script>
    alert('Your account has been deleted.');
    window.location.href = '../home.html';
</script>";
exit();

        } else {
            echo "<script>
                alert('Failed to delete account!');
                window.location.href='Todo.php';
            </script>";
        }
    } else {
        echo "<script>
            alert('Incorrect password!');
            window.location.href='current_password.php';
        </script>";
    }
} else {
    echo "<script>
        alert('User not found!');
        window.location.href='current_password.php';
    </script>";
}
?>
