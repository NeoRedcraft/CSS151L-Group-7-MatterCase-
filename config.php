<?php

$hostname     = "localhost"; 
$username     = "root";  
$password     = "";   
$databasename = "test";  
// Create connection 
$conn = mysqli_connect($hostname, $username, $password,$databasename);
 // Check connection 
if ($conn->connect_error) { 
die("Unable to Connect database: " . $conn->connect_error);
 }
?>