<?php
include "../config/config.php";
if(isset($_GET['taskid'])){
    $taskid=$_GET['taskid'];
    $sql="delete from task where taskid='$taskid'";
    $result=mysqli_query($conn,$sql);
   if($result) {

   header("location:Todo.php");
   exit();
    }
    else{
        echo mysqli_error($conn);
    }
}
?>
