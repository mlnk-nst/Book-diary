<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['isActive' => false]);
    exit;
}
$book_id = filter_input(INPUT_GET, 'book_id', FILTER_VALIDATE_INT);
if (!$book_id) {
    echo json_encode(['isActive' => false]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT id as session_id, start_time, start_page
        FROM reading_sessions 
        WHERE user_id = ? AND book_id = ? AND is_active = 1
        LIMIT 1
    ");
    $stmt->execute([$_SESSION['user_id'], $book_id]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($session) {
        $start = new DateTime($session['start_time']);
        $now = new DateTime();
        $diff = $start->diff($now);
        
        echo json_encode([
            'isActive' => true,
            'sessionId' => $session['session_id'],
            'start_time' => $session['start_time'],
            'hours' => $diff->h,
            'minutes' => $diff->i,
            'seconds' => $diff->s,
            'startPage' => $session['start_page']
        ]);
    } else {
        echo json_encode(['isActive' => false]);
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['isActive' => false]);
}
