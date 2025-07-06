<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Forgot Password</title>
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

    input[type="email"] {
      width: 100%;
      padding: 0.6rem;
      margin-bottom: 1.2rem;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 1rem;
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
    }

    input[type="submit"]:hover {
      background-color: #222;
    }
  </style>
</head>
<body>

  <form action="reset_password.php" method="POST">
    <h2>Reset Password</h2>
    <label for="email">Enter your email:</label>
    <input type="email" name="email" id="email" required>
    <input type="submit" name="submit" value="Reset Password">
  </form>

</body>
</html>
