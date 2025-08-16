<?php
require "../config/config.php";
session_start();

if (!isset($_SESSION['reset_email'])) {
    echo "Session expired.";
    exit();
}

if (isset($_POST['submit'])) {
    $email = $_SESSION['reset_email'];
    $newPwd = $_POST['new_password'];
    $confirmPwd = $_POST['confirm_password'];

    if ($newPwd !== $confirmPwd) {
        echo "Passwords do not match.";
        exit();
    }
    $update = "UPDATE login SET pwd = '$newPwd' WHERE email = '$email'";
    if (mysqli_query($conn, $update)) {
        unset($_SESSION['reset_email']);
        echo "<script>
        alert('password updated successfully.');
        window.location.href = '../auth/login.html';
        </script>";
    } else {
        echo "Update failed.";
    }
}
?>
