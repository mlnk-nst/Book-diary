<?php
require_once 'database.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $errors = [];

    if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
       echo "Усі обов’язкові поля повинні бути заповнені!";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Некоректна email-адреса!';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Паролі не співпадають!';
    }

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE email = :email');
    $stmt->execute(['email' => $email]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = 'Цей email вже зареєстрований!';
    }
    if (!empty($errors)) {
        $error_string = implode(',', $errors);
    
        exit;
    }
  
}
?>