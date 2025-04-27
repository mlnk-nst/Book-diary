<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Доступ запрещен']);
    exit;
}

include __DIR__ . '/../database.php';
header('Content-Type: application/json');


    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $errors = [];

    if (empty($name)) {
        $errors['name'] = "Введіть ім'я";
    } elseif (strlen($name) < 2 || strlen($name) > 50) {
        $errors['name'] = "Ім'я має містити від 2 до 50 символів";
    } elseif (!preg_match("/^[a-zA-ZА-Яа-яЁёҐґІіЇїЄє'\-\s]+$/u", $name)) {
        $errors['name'] = "Допустимі лише літери, апостроф та дефіс";
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
    http_response_code(422); // Unprocessable Entity
    echo json_encode([
        'success' => false,
        'message' => 'Виправте помилки у формі',
        'errors' => $errors
    ]);
    exit;
}

try {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$name, $email, $hashedPassword]);
    
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