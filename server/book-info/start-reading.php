<?php
header('Content-Type: application/json');

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

include __DIR__ . '/../database.php';
$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['book_id']) || !isset($data['start_page'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Необхідно вказати book_id та start_page']);
    exit;
}

$bookId = (int)$data['book_id'];
$startPage = (int)$data['start_page'];
$userId = $auth['userId'];
$conn = $pdo;
$startTime = date('Y-m-d H:i:s');
try {
    $conn->beginTransaction();
    $stmt = $conn->prepare("
        INSERT INTO reading_sessions 
        (user_id, book_id, start_time, start_page, is_active) 
        VALUES (:user_id, :book_id, NOW(), :start_page, 1)
    ");
    $stmt->execute([
        ':user_id' => $userId,
        ':book_id' => $bookId,
        ':start_page' => $startPage
    ]);
    $sessionId = $conn->lastInsertId();
       $conn->commit();
    
    echo json_encode([
        'success' => true,
        'sessionId' => $sessionId,
        'start_time' => $startTime,
        'message' => 'Сесія читання успішно розпочата'
    ]);
    
} catch (PDOException $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
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
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Внутрішня помилка сервера']);
}
?>