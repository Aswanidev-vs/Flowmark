<?php

header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");



session_start();

// Save email to use in localStorage cleanup
$email = isset($_SESSION['email']) ? $_SESSION['email'] : null;

// Destroy session completely
$_SESSION = array();
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Logging Out...</title>
    <link rel="icon"  type="image/png" href="../public/assets/images/checked.png">
  <script>
    const email = "<?php echo $email; ?>";
    if (email) {
      localStorage.removeItem(`profilePic_${email}`);
    }

    // Prevent back button caching by navigating to home
    window.location.replace("../home.html");

  </script>
</head>
<body>
  <p>Logging you out...</p>
</body>
</html>
