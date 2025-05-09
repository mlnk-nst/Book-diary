<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Користувач не авторизований']);
    exit;
}

$bookId = isset($_GET['book_id']) ? (int)$_GET['book_id'] : null;
if (!$bookId) {
    http_response_code(400);
    echo json_encode(['error' => 'Не вказано ID книги']);
    exit;
}

include __DIR__ . '/../database.php';
try {
    $stmt = $pdo->prepare("
        SELECT 
            status, 
            rating, 
            DATE_FORMAT(read_data, '%d.%m.%Y') as read_date,
            reviews_id
        FROM diary 
        WHERE user_id = :user_id AND book_id = :book_id
        LIMIT 1
    ");
    
    $stmt->execute([
        ':user_id' => $_SESSION['user_id'], 
        ':book_id' => $bookId
    ]);
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        echo json_encode([
            'status' => $result['status'],
            'rating' => $result['rating'],
            'read_date' => $result['read_date'],
            'reviews_id' => $result['reviews_id']
        ]);
    } else {
        echo json_encode([
            'status' => null,
            'rating' => null,
            'read_date' => null,
            'reviews_id' => null
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Помилка бази даних: ' . $e->getMessage()]);
    error_log('Помилка PDO: ' . $e->getMessage());
}

