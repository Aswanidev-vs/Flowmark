<?php
session_start();
if (!isset($_SESSION['username'], $_SESSION['email'])) {
    header("Location: login.html");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Confirm Password</title>
    <style>
        body {
            background-color: #1f1f1f;
            color: white;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .confirm-box {
            background-color: #2c2c2c;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.1);
        }

        .confirm-box h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        input[type="password"] {
            background-color: #3a3a3a;
            color: white;
            padding: 10px 15px;
            border-radius: 8px;
            border: none;
            width: 100%;
            box-sizing: border-box;
            font-size: 16px;
            margin-bottom: 10px;
        }

        input[type="checkbox"] {
            margin-right: 5px;
        }

        label {
            font-size: 14px;
        }

        button[type="submit"] {
            background-color: #d32f2f;
            color: white;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 8px;
            font-size: 16px;
            margin-top: 15px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
.pwd-field {
    background-color: #3a3a3a;
    color: white;
    padding: 10px 15px;
    border-radius: 8px;
    border: none;
    width: 100%;
    box-sizing: border-box;
    font-size: 16px;
    margin-bottom: 10px;
}

        button[type="submit"]:hover {
            background-color: #b71c1c;
        }
    </style>
</head>
<body>
    <div class="confirm-box">
        <h2>Confirm Your Password</h2>
<form action="../account/delete_account.php" method="POST">
            <input type="password" name="confirm_password" id="pwd" class="pwd-field" placeholder="Enter current password" required />

            <div style="margin-top: 8px; margin-bottom: 15px;">
                <input type="checkbox" id="showPwd" onclick="togglePasswordVisibility()">
                <label for="showPwd">Show Password</label>
            </div>
            <button type="submit">Delete My Account</button>
        </form>
    </div>

    <script>
        function togglePasswordVisibility() {
            var pwd = document.getElementById("pwd");
            if (pwd.type === "password") {
                pwd.type = "text";
            } else {
                pwd.type = "password";
            }
        }
    </script>
</body>
</html>
