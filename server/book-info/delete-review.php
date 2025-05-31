<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Користувач не авторизований']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$bookId = $data['bookId'] ?? null;

if (!$bookId) {
    http_response_code(400);
    echo json_encode(['error' => 'Не вказано ID книги']);
    exit;
}

include __DIR__ . '/../database.php';

try {
    $pdo->beginTransaction();

    // Видаляємо відгук
    $stmt = $pdo->prepare("
        DELETE FROM reviews 
        WHERE book_id = :book_id 
        AND user_id = :user_id
    ");

    $stmt->execute([
        ':book_id' => $bookId,
        ':user_id' => $_SESSION['user_id']
    ]);

    // Оновлюємо запис в diary
    $stmt = $pdo->prepare("
        UPDATE diary 
        SET reviews_id = NULL, 
            rating = NULL 
        WHERE book_id = :book_id 
        AND user_id = :user_id
    ");

    $stmt->execute([
        ':book_id' => $bookId,
        ':user_id' => $_SESSION['user_id']
    ]);

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Відгук успішно видалено']);

} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Помилка бази даних: ' . $e->getMessage()]);
    error_log('Помилка PDO: ' . $e->getMessage());
}
?> 