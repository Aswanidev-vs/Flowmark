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
  .error-message {
      color: #e74c3c; /* A strong red color */
      font-size: 0.9em;
      display: block; /* Ensures it's on a new line */
      margin-top: 5px;
    }
    #password-feedback{
      position: relative;
      bottom: 15px;
    }
    .valid {
    color: green;
}

.invalid {
    color: red;
}
    </style>
</head>
<body>

<form method="POST" action="save_new_password.php" onsubmit="return validateForm()">
    <h2 style="text-align: center; margin-bottom: 1rem;">Update Password</h2>

    <label for="newpass">New Password:</label>
    <input type="password" name="new_password" id="newpass" required>
    
   <div id="password-feedback">
    <p id="length-check" class="invalid">Minimum 8 characters</p>
    <p id="upper-check" class="invalid">At least 1 uppercase letter</p>
    <p id="lower-check" class="invalid">At least 1 lowercase letter</p>
    <p id="digit-check" class="invalid">At least 1 number</p>
    <p id="special-check" class="invalid">At least 1 special character</p>
</div>
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
const pwdInput = document.getElementById("newpass");
const lengthCheck = document.getElementById("length-check");
const upperCheck = document.getElementById("upper-check");
const lowerCheck = document.getElementById("lower-check");
const digitCheck = document.getElementById("digit-check");
const specialCheck = document.getElementById("special-check");

pwdInput.addEventListener("input", () => {
    const value = pwdInput.value;

    lengthCheck.className = value.length >= 8 ? "valid" : "invalid";
    upperCheck.className = /[A-Z]/.test(value) ? "valid" : "invalid";
    lowerCheck.className = /[a-z]/.test(value) ? "valid" : "invalid";
    digitCheck.className = /\d/.test(value) ? "valid" : "invalid";
    specialCheck.className = /[^A-Za-z0-9]/.test(value) ? "valid" : "invalid";
});
function validateForm() {
    const pwd = document.getElementById("newpass").value;
    const confirmPwd = document.getElementById("cpass").value; // ✅ fixed ID

    // Clear previous errors
    const passwordError = document.getElementById('password-error');
    const confirmError = document.getElementById('confirm-error');
    if (passwordError) passwordError.textContent = "";
    if (confirmError) confirmError.textContent = "";

    // Required fields
    if (!pwd || !confirmPwd) {
        alert("Please fill in all fields.");
        return false;
    }

    // Password match check
    if (pwd !== confirmPwd) {
        if (confirmError) confirmError.textContent = "Passwords do not match.";
        return false;
    }

    // Password length check
    if (pwd.length < 8) {
        if (passwordError) passwordError.textContent = "Password must be at least 8 characters long.";
        return false;
    }

    // Password complexity check
    let hasUpper = /[A-Z]/.test(pwd);
    let hasLower = /[a-z]/.test(pwd);
    let hasDigit = /\d/.test(pwd);
    let hasSpecial = /[^A-Za-z0-9]/.test(pwd);

    if (!hasUpper || !hasLower || !hasDigit || !hasSpecial) {
        if (passwordError) passwordError.textContent =
            "Password must include uppercase, lowercase, number, and special character.";
        return false;
    }

    return true; // All checks passed
}


</script>

</body>
</html>


