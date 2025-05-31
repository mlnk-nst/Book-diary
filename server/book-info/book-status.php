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
            d.status, 
            d.rating, 
            DATE_FORMAT(d.read_data, '%d.%m.%Y') as read_date,
            d.reviews_id,
            r.review_text,
            r.is_public
        FROM diary d
        LEFT JOIN reviews r ON d.reviews_id = r.reviews_id
        WHERE d.user_id = :user_id AND d.book_id = :book_id
        LIMIT 1
    ");
    
    $stmt->execute([
        ':user_id' => $_SESSION['user_id'], 
        ':book_id' => $bookId
    ]);
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        echo json_encode([
            'success' => true,
            'status' => $result['status'],
            'rating' => $result['rating'],
            'read_date' => $result['read_date'],
            'reviews_id' => $result['reviews_id'],
            'review' => $result['review_text'],
            'is_public' => (bool)$result['is_public']
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'status' => null,
            'rating' => null,
            'read_date' => null,
            'reviews_id' => null,
            'review' => null,
            'is_public' => false
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Помилка бази даних: ' . $e->getMessage()]);
    error_log('Помилка PDO: ' . $e->getMessage());
}

