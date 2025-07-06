<?php
include "config.php";
session_start();
if(!(isset($_SESSION['email']))){
    header("location:login.html");
}
else{
    echo"<br> welcome,".$_SESSION['email']."<br>";
    echo"<a href='logout.php'>logout</a>";
}
?>