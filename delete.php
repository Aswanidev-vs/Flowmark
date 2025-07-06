<?php
include "config.php";
if(isset($_GET['taskid'])){
    $taskid=$_GET['taskid'];
    $sql="delete from task where taskid='$taskid'";
    $result=mysqli_query($conn,$sql);
   if($result) {

   header("location:Todo.php");
    }
    else{
        echo mysqli_error($conn);
    }
}
?>
