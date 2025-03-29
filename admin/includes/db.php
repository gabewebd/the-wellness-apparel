<?php
$host = 'localhost'; // Your database host
$user = 'root';      // Your database username
$pass = '';          // Your database password
$db = '4611173_wellnessapparel'; // Your database name

$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

