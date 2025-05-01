<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Доступ заборонено']);
    exit;
}

include __DIR__ . '/../database.php';

    $username = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $errors = [];

    if (empty($username)) {
        $errors['name'] = "Введіть ім'я";
    } elseif (strlen($username) < 2 || strlen($username) > 50) {
        $errors['name'] = "Ім'я має містити від 2 до 50 символів";
    } elseif (!preg_match("/^[a-zA-ZА-Яа-яЁёҐґІіЇїЄє'\-\s]+$/u", $username)) {
        $errors['name'] = "Допустимі лише літери, апостроф та дефіс";
    }
    if (preg_match('/[<>{}\/\\\\]/', $username)) {
        $errors['name'] = "Ім'я містить заборонені символи";
    }
    if (preg_match('/[<>{}]/', $email)) {
        $errors['email'] = "Email містить заборонені символи";
    }
    if (empty($email)) {
        $errors['email'] = "Введіть email";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Невірний формат email";
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $errors['email'] = "Цей email вже зареєстрований";
        }
    }
    
   if (empty($password)) {
    $errors['password'] = "Введіть пароль";
} elseif (strlen($password) < 8) {
    $errors['password'] = "Пароль має містити ≥8 символів";
} elseif (!preg_match("/^(?=.*[a-zA-ZА-Яа-я])(?=.*\d).{8,}$/u", $password)) {
    $errors['password'] = "Пароль має містити літери та цифри";
}

if ($password !== $confirmPassword) {
    $errors['confirmPassword'] = "Паролі не збігаються";
}
if (!empty($errors)) {
    http_response_code(422); 
    echo json_encode([
        'success' => false,
        'message' => 'Виправте помилки у формі',
        'errors' => $errors
    ]);
    exit;
}

try {
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
    
    if (strlen($hashedPassword) > 100) {
        throw new Exception("Помилка: хеш пароля занадто довгий");
    }
    $pdo->beginTransaction();
    
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, created_at) VALUES (?, ?, ?, 'user', NOW())");
    $stmt->execute([$username, $email, $hashedPassword]);
    $userId = $pdo->lastInsertId(); 

    $stmt = $pdo->prepare("INSERT INTO user_progress (user_id, experience_points, level) VALUES (?, 0, 1)");
    $stmt->execute([$userId]);
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Реєстрація успішна!'
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Помилка при реєстрації'
    ]);
}
?>