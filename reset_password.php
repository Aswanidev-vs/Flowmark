<?php
require "config.php";
session_start();

if (isset($_POST['submit'])) {
    $email = $_POST['email'];

    // Check if email exists
    $sql = "SELECT * FROM login WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        // Redirect to actual password reset form
        $_SESSION['reset_email'] = $email;
        header("Location: update_password.php");
        exit();
    } else {
        echo "Email not found.";
    }
}
?>
