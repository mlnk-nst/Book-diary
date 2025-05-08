<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../server/database.php';

$data = json_decode(file_get_contents('php://input'), true);

$userId = $_SESSION['user_id'] ?? null;
$newEmail = trim($data['newEmail'] ?? '');
$password = $data['password'] ?? '';

if (!$userId || !$newEmail || !$password) {
    echo json_encode(['success' => false, 'message' => 'Заповніть усі поля']);
    exit;
}

if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Некоректний формат email']);
    exit;
}
$stmt = $pdo->prepare('SELECT user_id, email, password FROM users WHERE user_id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'Користувача не знайдено']);
    exit;
}

if (!password_verify($password, $user['password'])) {
    echo json_encode(['success' => false, 'message' => 'Неправильний пароль']);
    exit;
}
$oldEmail = $user['email'];

$stmt = $pdo->prepare('UPDATE users SET email = ? WHERE user_id = ?');
$success = $stmt->execute([$newEmail, $userId]);

if ($success) {
    if ($_SESSION['role'] == 'admin') {
        $logStmt = $pdo->prepare("INSERT INTO action_logs 
            (admin_id, action, old_value, new_value, timestamp) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        $logStmt->execute([
            $userId,
            'Зміна email',
            $oldEmail,  
            $newEmail    
        ]);
    }
    echo json_encode(['success' => true, 'message' => 'Email успішно змінено']);
} else {
    echo json_encode(['success' => false, 'message' => 'Не вдалося оновити email']);
}

