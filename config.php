<?php
$conn=mysqli_connect("localhost","root","");
mysqli_select_db($conn,"todo");
if(!$conn){
    die("connection Failed");
    
}
?>