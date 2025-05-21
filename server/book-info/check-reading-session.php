<?php
ob_start();

session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../database.php';

error_log("Checking reading session - User ID: " . ($_SESSION['user_id'] ?? 'not set'));

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
        SELECT session_id, start_time, start_page
        FROM reading_sessions 
        WHERE user_id = ? AND book_id = ? AND is_active = 1
        LIMIT 1
    ");
    
    $stmt->execute([$_SESSION['user_id'], $book_id]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);
    
    error_log("Session query result: " . ($session ? "found" : "not found"));

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
        error_log("No active session found for user " . $_SESSION['user_id'] . " and book " . $book_id);
        echo json_encode(['isActive' => false]);
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['isActive' => false]);
}

ob_end_flush();
