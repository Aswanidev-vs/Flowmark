<?php
include "../config/config.php";

if (isset($_POST['submit'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $pwd = $_POST['pwd'];
    $confirm_pwd = $_POST['confirm_pwd'];

    // ✅ Basic validations
    if (empty($username) || empty($email) || empty($pwd) || empty($confirm_pwd)) {
        echo "Please fill in all fields.";
        exit();
    }

    if ($pwd !== $confirm_pwd) {
        echo "Passwords do not match.";
        exit();
    }

    // ✅ Check if email already exists
    $check = mysqli_query($conn, "SELECT * FROM login WHERE email = '$email'");
  if (mysqli_num_rows($check) > 0) {
    echo "<script>
        alert('Email already registered. Please use a different one.');
        window.location.href = 'signup.html';
    </script>";
    exit();
}


    // ✅ Insert user (you can hash password if needed)
    $sql = "INSERT INTO login (username, email, pwd) VALUES ('$username', '$email', '$pwd')";
    if (mysqli_query($conn, $sql)) {
        header("Location: ../auth/login.html");
        exit();
    } else {
        echo "Signup failed: " . mysqli_error($conn);
    }
}
?>
