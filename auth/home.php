<?php
include "../config/config.php";
session_start();
if(!(isset($_SESSION['email']))){
    header("location:../auth/login.html");
}
else{
    echo"<br> welcome,".$_SESSION['email']."<br>";
    echo"<a href='logout.php'>logout</a>";
}
?>