<?php
session_start();
header('Content-Type: application/json');

include __DIR__ . '/../database.php';
include 'level_system.php';

$userId = $_SESSION['user_id'] ?? null;
$input = json_decode(file_get_contents('php://input'), true);
$bookId = $input['bookId'] ?? null;

if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'Користувач не авторизований']);
    exit;
}

if (!$bookId) {
    echo json_encode(['success' => false, 'message' => 'Не передано ID книги']);
    exit;
}

try {
    $stmtCheck = $pdo->prepare("SELECT id FROM user_xp_log WHERE user_id = ? AND action_type = ? AND action_reference_id = ?");
    $stmtCheck->execute([$userId, 'finished', $bookId]);
    if ($stmtCheck->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Бали за статус "Прочитано" вже нараховані для цієї книги']);
        exit;
    }

    $stmtProgress = $pdo->prepare("SELECT experience_points, level FROM user_progress WHERE user_id = ?");
    $stmtProgress->execute([$userId]);
    $progress = $stmtProgress->fetch(PDO::FETCH_ASSOC);

    if (!$progress) {
        $stmtInsert = $pdo->prepare("INSERT INTO user_progress (user_id, experience_points, level) VALUES (?, 0, 1)");
        $stmtInsert->execute([$userId]);
        $progress = ['experience_points' => 0, 'level' => 1];
    }

    $xpToAdd = 40; // Бали за статус "Прочитано"
    $newExp = $progress['experience_points'] + $xpToAdd;
    $stmtUpdate = $pdo->prepare("UPDATE user_progress SET experience_points = ? WHERE user_id = ?");
    $stmtUpdate->execute([$newExp, $userId]);

    $stmtLog = $pdo->prepare("INSERT INTO user_xp_log (user_id, action_type, action_reference_id, experience_points) VALUES (?, ?, ?, ?)");
    $stmtLog->execute([$userId, 'finished', $bookId, $xpToAdd]);

    checkAndUpdateLevel($userId, $pdo);

    echo json_encode(['success' => true, 'message' => 'Бали успішно нараховані']);
} catch (PDOException $e) {
    error_log('Database error in status-finished.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Помилка бази даних']);
} 