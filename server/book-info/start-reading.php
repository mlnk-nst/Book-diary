<?php
header('Content-Type: application/json');

session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Необхідна авторизація']);
    exit;
}

include __DIR__ . '/../database.php';
$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['book_id']) || !isset($data['start_page'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Необхідно вказати book_id та start_page']);
    exit;
}

$bookId = (int)$data['book_id'];
$startPage = (int)$data['start_page'];
$userId = $_SESSION['user_id'];
$startTime = date('Y-m-d H:i:s');

try {
    $pdo->beginTransaction();
    
    $checkStmt = $pdo->prepare("
        SELECT session_id FROM reading_sessions 
        WHERE user_id = ? AND book_id = ? AND is_active = 1
    ");
    $checkStmt->execute([$userId, $bookId]);
    if ($checkStmt->fetch()) {
        throw new Exception('У вас вже є активна сесія читання для цієї книги');
    }
    
     $lastSessionStmt = $pdo->prepare("
        SELECT end_page FROM reading_sessions
        WHERE user_id = ? AND book_id = ? AND is_active = 0 AND end_page IS NOT NULL
        ORDER BY end_time DESC
        LIMIT 1
    ");
    $lastSessionStmt->execute([$userId, $bookId]);
    $lastSession = $lastSessionStmt->fetch(PDO::FETCH_ASSOC);

    if ($lastSession && isset($lastSession['end_page'])) {
        $lastEndPage = (int)$lastSession['end_page'];
        if ($startPage < $lastEndPage - 2) {
            throw new Exception("Ви вже прочитали до сторінки {$lastEndPage}. Ви можете почати не більше ніж на 2 сторінки раніше.");
        }
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO reading_sessions 
        (user_id, book_id, start_time, start_page, is_active) 
        VALUES (?, ?, NOW(), ?, 1)
    ");
    $stmt->execute([$userId, $bookId, $startPage]);
    $sessionId = $pdo->lastInsertId();
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'sessionId' => $sessionId,
        'start_time' => $startTime,
        'message' => 'Сесія читання успішно розпочата'
    ]);
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Error in start-reading.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>