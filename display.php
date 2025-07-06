<?php
include "config.php";
$sql="select * from task";
$result=mysqli_query($conn,$sql);
if(!$result){
    echo mysqli_error($conn);
}
else{
    echo "<h2> task details</h2>
    <table border='1'>
    <tr> <th>tid</th>
    <th> tname </th>
        <th> description</th>
            <th> status </th>
            <th> created_at</th>
              <th> updated_at</th>
                 <th> delete</th>
                    <th> update</th>
            </tr>
            ";
            while($row=mysqli_fetch_assoc($result)){
                echo "<tr>
                <td>".$row['taskid']."</td>
      <td>".$row['taskname']."</td>
          <td>".$row['description']."</td>
                 <td>".$row['status']."</td>
                  <td>".$row['created_at']."</td>
                   <td>".$row['updated_at']."</td>
                  <td> <a href='delete.php?taskid=".$row['taskid']." '>delete</a></td>
                     <td> <a href='update.php?taskid=".$row['taskid']." '>update</a></td>
                  </tr>";}
                  echo "</table>";
            }
            

?>