<?php
session_start();
if (!isset($_SESSION['reset_email'])) {
    echo "Unauthorized access.";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Password</title>
     <link rel="icon"  type="image/png" href="../public/assets/images/checked.png">
   
    <style>
     * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: Arial, sans-serif;
      background: #f2f2f2;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    form {
      background: white;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 360px;
    }

    h2 {
      text-align: center;
      margin-bottom: 1.5rem;
      color: #333;
    }

    label {
      display: block;
      margin-bottom: 0.4rem;
      font-weight: 600;
      color: #444;
    }

    input[type="password"],
input[type="text"] {
  width: 100%;
  padding: 0.6rem;
  margin-bottom: 1rem;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 1rem;
}
    .checkbox-container {
      display: flex;
      align-items: center;
      margin-bottom: 1rem;
    }

    .checkbox-container input[type="checkbox"] {
      margin-right: 0.4rem;
    }

  input[type="submit"] {
  width: 100%;
  padding: 0.75rem;
  background-color: #111;
  color: #fff;
  font-size: 1rem;
  font-weight: bold;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: background-color 0.2s;
  margin-top: 10px;
}



input[type="submit"]:hover {
  background-color: #222;
}

    </style>
</head>
<body>

<form method="POST" action="save_new_password.php">
    <h2 style="text-align: center; margin-bottom: 1rem;">Update Password</h2>

    <label for="newpass">New Password:</label>
    <input type="password" name="new_password" id="newpass" required>

    <label for="cpass">Confirm Password:</label>
    <input type="password" name="confirm_password" id="cpass" required>

    <input type="checkbox" id="showPwd" onclick="togglePasswordVisibility()">
    <label for="showPwd" style="display: inline;">Show Password</label>
        <input type="submit" value="update password" name="submit">

</form>

<script>
function togglePasswordVisibility() {
    const pwdField = document.getElementById("newpass");
    const confirmPwdField = document.getElementById("cpass");

    if (pwdField.type === "password") {
        pwdField.type = "text";
        confirmPwdField.type = "text";
    } else {
        pwdField.type = "password";
        confirmPwdField.type = "password";
    }
}
</script>

</body>
</html>
