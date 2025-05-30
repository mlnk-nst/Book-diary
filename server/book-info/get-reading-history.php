<?php
include __DIR__ . '/../database.php';
require_once '../login/check_auth.php';

// Перевіряємо авторизацію
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$bookId = $_GET['book_id'] ?? null;

if (!$bookId) {
    http_response_code(400);
    echo json_encode(['error' => 'Book ID is required']);
    exit;
}

try {
    // Перевіряємо підключення до бази даних
    if (!isset($pdo)) {
        throw new PDOException('Database connection not established');
    }

    $stmt = $pdo->prepare("
        SELECT 
            rs.session_id,
            rs.start_time,
            rs.end_time,
            rs.start_page,
            rs.end_page,
            rs.pages_read,
            TIMESTAMPDIFF(SECOND, rs.start_time, COALESCE(rs.end_time, NOW())) as duration
        FROM reading_sessions rs
        WHERE rs.book_id = ? AND rs.user_id = ?
        ORDER BY rs.start_time DESC
    ");
    
    $stmt->execute([$bookId, $_SESSION['user_id']]);
    $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Форматуємо тривалість для кожного сеансу
    foreach ($sessions as &$session) {
        if ($session['duration']) {
            $hours = floor($session['duration'] / 3600);
            $minutes = floor(($session['duration'] % 3600) / 60);
            $seconds = $session['duration'] % 60;
            $session['duration'] = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }
    }

    header('Content-Type: application/json');
    echo json_encode(['sessions' => $sessions]);
} catch (PDOException $e) {
    error_log('Database error in get-reading-history.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log('General error in get-reading-history.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
} 