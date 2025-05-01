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
    $stmt = $pdo->prepare("SELECT level, experience_points FROM user_progress WHERE user_id = ?");
    $stmt->execute([$userId]);
    $progress = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($progress) {
        echo json_encode([
            'success' => true,
            'level' => $progress['level'],
            'experience_points' => $progress['experience_points']
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'level' => 0,
            'experience_points' => 0
        ]);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Помилка бази даних: ' . $e->getMessage()]);
}
?>