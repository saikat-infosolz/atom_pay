
<?php

$servername = "localhost";
$username   = "usr_root";
$password   = "123@abc@321";
$dbname     = "tgusrjdb";


// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
  echo "error";
  die("Connection failed: " . $conn->connect_error);
}
/*else{
  echo "Database Connected successfully.";
}*/


?>