<?php
$servername = "localhost";
$database = "class_schedule_db";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


    $stmt = $conn->query("SHOW DATABASES LIKE '$database'");
    $db_exists = $stmt->fetch();

    if (!$db_exists) {
        
        include 'create_database.php';
    }

    $conn->exec("USE $database");



} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
