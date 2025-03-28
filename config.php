<?php
// Database connection
$host = 'fdb1029.awardspace.net';
$user = '4611173_wellnessapparel';
$pass = 'DWEB2025_wellness_apparel';
$db_name = '4611173_wellnessapparel';

$conn = new mysqli($host, $user, $pass, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
