<?php
// Create database if it doesn't exist
$conn = new mysqli('127.0.0.1', 'root', '', '', 3306);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "CREATE DATABASE IF NOT EXISTS pjbl_elearning CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";

if ($conn->query($sql) === TRUE) {
    echo "Database 'pjbl_elearning' created successfully or already exists\n";
} else {
    echo "Error creating database: " . $conn->error . "\n";
}

$conn->close();
?>
