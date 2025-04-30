<?php
require_once __DIR__ . '/../../server/database.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Метод не підтримується',
        'errors' => ['Використовуйте метод POST для входу']
    ]);
    exit;
} 
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$errors = [];

if (empty($email)) {
    $errors['email'] = "Email обов'язковий для заповнення";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = "Некоректний формат email-адреси";
}
if (empty($password)) {
    $errors['password'] = "Пароль обов'язковий для заповнення";
}

if (!empty($errors)) {
    echo json_encode([
        'success' => false,
        'message' => 'Помилка входу',
        'errors' => $errors
    ]);
    exit;
}
try {
    $stmt = $pdo->prepare("SELECT user_id, username, email, password, role FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($password, $user['password'])) {
        ini_set('session.gc_maxlifetime', 1209600);
        session_set_cookie_params([
            'lifetime' => 1209600,
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        echo json_encode([
            'success' => true,
            'message' => 'Вхід успішний',
            'user' => [
                'id' => $user['user_id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role']
            ]
        ]);
    } else { echo json_encode([
        'success' => false,
        'message' => 'Невірний email або пароль'
    ]);
}
}
catch (PDOException $e) {
    error_log("Помилка входу: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Помилка при вході. Спробуйте пізніше.'
    ]);
}
?>