<?php
file_put_contents('debug_log.txt', print_r($_POST, true), FILE_APPEND);

include '../database/connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve and sanitize input values
    $user = trim($_POST['name']);
    $email = trim($_POST['email']);
    $pass = trim($_POST['password']);
    $role = trim($_POST['role']);


    if (empty($user) || empty($email) || empty($pass) || empty($role)) {
        die("Error: Please fill all the fields.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Error: Invalid email address.");
    }

    $hashedPassword = password_hash($pass, PASSWORD_DEFAULT);

    try {
        $sql = "INSERT INTO users (name, email, password, role_id) VALUES (:name, :email, :password, :role_id)";
        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':name', $user);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':role_id', $role);

        if ($stmt->execute()) {
            header('Location: /ScheduleHub/views/auth/login.php?signup=success');
            exit();
        } else {
            die("Error: Could not save the user.");
        }
    } catch (PDOException $e) {
        // Check if the error is due to duplicate email
        if ($e->getCode() == 23000) {
            die("Error: The email address is already in use.");
        } else {
            die("Error: " . $e->getMessage());
        }
    }
} else {
    die("Error: Invalid request method.");
}
