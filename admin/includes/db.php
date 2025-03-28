<?php
$host = "fdb1029.awardspace.net";
$dbname = "4611173_wellnessapparel";
$username = "4611173_wellnessapparel";
$password = "DWEB2025_wellness_apparel";

// Create MySQLi connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("❌ Database connection failed: " . $conn->connect_error);
}   
?>