<?php
header('Content-Type: application/json');
include __DIR__ . '/../database.php';


session_start();

$auth = [
    'isLoggedIn' => isset($_SESSION['user_id']),
    'userId' => $_SESSION['user_id'] ?? null,
];

if (!$auth['isLoggedIn']) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Необхідна авторизація']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$requiredFields = ['session_id', 'book_id', 'end_page', 'hours', 'minutes', 'seconds'];
foreach ($requiredFields as $field) {
    if (!isset($data[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Необхідно вказати $field"]);
        exit;
    }
}

$sessionId = (int)$data['session_id'];
$bookId = (int)$data['book_id'];
$endPage = (int)$data['end_page'];
$duration = sprintf('%02d:%02d:%02d', $data['hours'], $data['minutes'], $data['seconds']);
$userId = $auth['userId'];

try {
    $pdo->beginTransaction();
    
    $stmt = $pdo->prepare("
        SELECT * FROM reading_sessions 
        WHERE session_id = ? 
        AND user_id = ? 
        AND book_id = ? 
        AND is_active = 1
    ");
    $stmt->execute([$sessionId, $userId, $bookId]);
    $session = $stmt->fetch();

    if (!$session) {
        throw new Exception("Сесія читання не знайдена або вже завершена");
    }

    $stmt = $pdo->prepare("SELECT pages FROM books WHERE book_id = ?");
    $stmt->execute([$bookId]);
    $book = $stmt->fetch();
    $total_pages = $book['pages'];

    if ($endPage > $total_pages) {
        throw new Exception("Номер сторінки не може бути більше загальної кількості сторінок ($total_pages)");
    }

    $startTime = new DateTime($session['start_time']);
    $endTime = clone $startTime;
    $endTime->add(new DateInterval('PT' . $data['hours'] . 'H' . $data['minutes'] . 'M' . $data['seconds'] . 'S'));

    $stmt = $pdo->prepare("
        UPDATE reading_sessions 
        SET end_time = ?, 
            end_page = ?,
            is_active = 0
        WHERE session_id = ? 
        AND user_id = ? 
        AND is_active = 1
    ");
    
    $stmt->execute([
        $endTime->format('Y-m-d H:i:s'),
        $endPage,
        $sessionId,
        $userId
    ]);

    $is_near_end = ($total_pages - $endPage) <= 15;

    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Сесію читання успішно завершено',
        'is_near_end' => $is_near_end,
        'total_pages' => $total_pages
    ]);
    
} catch (PDOException $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    if ($e->getCode() == '45000') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    } else {
        error_log("Database error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Помилка бази даних']);
    }
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>