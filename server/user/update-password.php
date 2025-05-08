<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../server/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Необхідно авторизуватися']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$requiredFields = ['userId', 'currentPassword', 'newPassword', 'confirmPassword'];
foreach ($requiredFields as $field) {
    if (empty($data[$field])) {
        echo json_encode(['success' => false, 'message' => 'Усі поля обов\'язкові для заповнення']);
        exit;
    }
}

$userId = (int)$data['userId'];
$currentPassword = $data['currentPassword'];
$newPassword = $data['newPassword'];
$confirmPassword = $data['confirmPassword'];

if ($_SESSION['user_id'] != $userId) {
    echo json_encode(['success' => false, 'message' => 'Можна змінювати тільки свій пароль']);
    exit;
}

try {
    
    $stmt = $pdo->prepare("SELECT password, email FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Користувача не знайдено']);
        exit;
    }

    if (!password_verify($currentPassword, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Поточний пароль введено невірно']);
        exit;
    }

    if ($newPassword !== $confirmPassword) {
        echo json_encode(['success' => false, 'message' => 'Новий пароль і підтвердження не збігаються']);
        exit;
    }

    if (strlen($newPassword) < 8) {
        echo json_encode(['success' => false, 'message' => 'Пароль повинен містити щонайменше 8 символів']);
        exit;
    }

    $oldPasswordHash = $user['password'];
    $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
    $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
    $updateStmt->execute([$newPasswordHash, $userId]);

    if ($_SESSION['role'] == 'admin') {
        $logStmt = $pdo->prepare("
            INSERT INTO action_logs 
            (admin_id, action, old_value, new_value, timestamp) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        $logStmt->execute([
            $_SESSION['user_id'],
            'Зміна пароля',
            $oldPasswordHash,  
            $newPasswordHash  
        ]);
    }

    echo json_encode(['success' => true, 'message' => 'Пароль успішно змінено']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'PDO помилка: ' . $e->getMessage()]);
    echo json_encode(['success' => false, 'message' => 'Внутрішня помилка сервера']);
}