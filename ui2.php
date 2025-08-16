<?php
session_start();
require "ui2/config/config.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.html");
    exit();
}
header("Location: ui2/tasks/Todo.php");
exit();
?>
