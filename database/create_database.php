<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "class_schedule_db";

try {
    $conn = new PDO("mysql:host=$servername", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create database
    $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
    $conn->exec($sql);
    echo "Database created successfully<br>";

    $conn->exec("USE $dbname");

    $sql = "CREATE TABLE IF NOT EXISTS sessions (
        id VARCHAR(255) PRIMARY KEY,
        data TEXT NOT NULL,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    echo "Table 'sessions' created successfully<br>";

    // Create users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(30) NOT NULL,
        email VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role_id INT(1) NOT NULL,
        reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    echo "Table 'users' created successfully<br>";

    // Create sections table
    $sql = "CREATE TABLE IF NOT EXISTS sections (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        section_name VARCHAR(50) NOT NULL
    )";
    $conn->exec($sql);
    echo "Table 'sections' created successfully<br>";

    // Create schedules table
    $sql = "CREATE TABLE IF NOT EXISTS schedules (
        schedule_id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT(6) UNSIGNED NOT NULL,
        section_id INT(6) UNSIGNED NOT NULL,
        day VARCHAR(10) NOT NULL,
        period VARCHAR(10) NOT NULL,
        subject VARCHAR(50) NOT NULL,
        classroom VARCHAR(50) NOT NULL,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (section_id) REFERENCES sections(id)
    )";
    $conn->exec($sql);
    echo "Table 'schedules' created successfully<br>";

    // Create rooms table
    $sql = "CREATE TABLE IF NOT EXISTS rooms (
        room_id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        room_no VARCHAR(10) NOT NULL
    )";
    $conn->exec($sql);
    echo "Table 'rooms' created successfully<br>";

    // Create roles table
    $sql = "CREATE TABLE IF NOT EXISTS roles (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        role_name VARCHAR(50) NOT NULL
    )";
    $conn->exec($sql);
    echo "Table 'roles' created successfully<br>";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

$conn = null;
?>