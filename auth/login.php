<?php
include "../config/config.php";
session_start();
if(isset($_POST['submit']))
{
    $email=$_POST['email'];
    $pwd=$_POST['pwd'];
    $sql="select * from login where email='$email' and pwd='$pwd'";
    $result=mysqli_query($conn,$sql);
    if(!$result){
      echo  mysqli_error($conn);
    }
    else{
        if( mysqli_num_rows($result)==1){
            $row=mysqli_fetch_assoc($result);

        $_SESSION['email']=$row['email'];
        $_SESSION['username']=$row['username'];
        $_SESSION['pwd']=$row['pwd'];
            header("location:../tasks/Todo.php");
    }
    else{
       echo "<script>
        alert('Email or password is invalid. try again');
        window.location.href = 'login.html';
    </script>";
    }

    }
}
?>