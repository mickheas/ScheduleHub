<?php
session_start();
include '../database/connection.php'; // Include your database connection file

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //$email = trim($_POST['email']);
    //$password = trim($_POST['password']);
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';


    // Debugging: Check if form data is received
    file_put_contents('debug_log.txt', "Email: $email, Password: $password\n", FILE_APPEND);

    // Check if all fields are filled
    if (empty($email) || empty($password)) {
        die("Error: Please fill all the fields.");
    }

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Error: Invalid email address.");
    }

    // Prepare the SQL statement
    $sql = "SELECT * FROM users WHERE email = :email";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Debugging: Check the values retrieved from the database
    file_put_contents('debug_log.txt', "User: " . print_r($user, true) . "\n", FILE_APPEND);

    if ($user && password_verify($password, $user['password'])) {
        //if ($user && $password === $user['password']) {
        // User found, create session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['name'];
        $_SESSION['role_id'] = $user['role_id'];

        setcookie('user', $user['name'], time() + (30 * 24 * 60 * 60), "/");

        header('Location: ../public/Home.html'); // Redirect to dashboard
        exit();
    } else {
        // User not found or password incorrect
        die("Error: Invalid email or password.");
    }
} else {
    header('Location: ../views/auth/login.php');
    exit();
}
?>