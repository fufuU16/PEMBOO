<?php
// Database configuration
$servername = "pembodatabase.mysql.database.azure.com";
$username = "pemboweb";
$password = 'Pa$$wordDINS';
$dbname = "pembodb";


// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
