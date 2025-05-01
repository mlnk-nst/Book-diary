<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../server/database.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Не авторизовано']);
    exit;
}

$userId = $_GET['user_id'] ?? $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT username, email, role FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo json_encode([
            'success' => true,
            'user' => [
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role']
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Користувача не знайдено']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Помилка бази даних: ' . $e->getMessage()]);
}
?>