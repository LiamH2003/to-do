<?php
$servername = "localhost:3306";
$username = "root";  // Replace with your database username
$password = "track22";      // Replace with your database password
$dbname = "to-do";   // The name of your database

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>