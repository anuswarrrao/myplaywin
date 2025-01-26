<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'playwin'; 

// Create connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . htmlspecialchars($conn->connect_error));  
}

// Set character encoding
$conn->set_charset("utf8mb4");
?>
