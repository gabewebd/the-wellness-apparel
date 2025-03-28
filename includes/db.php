<?php
$host = 'fdb1029.awardspace.net';
$user = '4611173_wellnessapparel';
$pass = 'DWEB2025_wellness_apparel';
$db = '4611173_wellnessapparel';
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
