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
$rating = $data['rating'] ?? null;
$reviewText = $data['reviewText'] ?? '';
$isPublic = $data['is_public'] ?? false;
$isEdit = $data['is_edit'] ?? false;

if (!$bookId || !$rating) {
    http_response_code(400);
    echo json_encode(['error' => 'Необхідні поля відсутні']);
    exit;
}

include __DIR__ . '/../database.php';

try {
    $pdo->beginTransaction();

    if ($isEdit) {
        // Оновлення існуючого відгуку
        $stmt = $pdo->prepare("
            UPDATE reviews 
            SET review_text = :review_text, 
                is_public = :is_public 
            WHERE book_id = :book_id 
            AND user_id = :user_id
        ");

        $stmt->execute([
            ':review_text' => $reviewText,
            ':is_public' => $isPublic,
            ':book_id' => $bookId,
            ':user_id' => $_SESSION['user_id']
        ]);

        // Оновлюємо рейтинг в таблиці diary
        $stmt = $pdo->prepare("
            UPDATE diary 
            SET rating = :rating
            WHERE book_id = :book_id 
            AND user_id = :user_id
        ");

        $stmt->execute([
            ':rating' => $rating,
            ':book_id' => $bookId,
            ':user_id' => $_SESSION['user_id']
        ]);

        $message = 'Відгук успішно оновлено';
    } else {
        // Створення нового відгуку
        $stmt = $pdo->prepare("
            INSERT INTO reviews (book_id, user_id, review_text, is_public) 
            VALUES (:book_id, :user_id, :review_text, :is_public)
        ");

        $stmt->execute([
            ':book_id' => $bookId,
            ':user_id' => $_SESSION['user_id'],
            ':review_text' => $reviewText,
            ':is_public' => $isPublic
        ]);

        $reviewsId = $pdo->lastInsertId();

        // Оновлюємо reviews_id та рейтинг в таблиці diary
        $stmt = $pdo->prepare("
            UPDATE diary 
            SET reviews_id = :reviews_id,
                rating = :rating
            WHERE book_id = :book_id 
            AND user_id = :user_id
        ");

        $stmt->execute([
            ':reviews_id' => $reviewsId,
            ':rating' => $rating,
            ':book_id' => $bookId,
            ':user_id' => $_SESSION['user_id']
        ]);

        // Нараховуємо XP тільки за новий відгук
        if (!empty($reviewText)) {
            // Перевіряємо, чи вже є запис про нарахування XP за цей відгук
            $stmt = $pdo->prepare("
                SELECT COUNT(*) 
                FROM user_xp_log 
                WHERE user_id = :user_id 
                AND action_type = 'review' 
                AND action_reference_id = :book_id
            ");
            $stmt->execute([
                ':user_id' => $_SESSION['user_id'],
                ':book_id' => $bookId
            ]);
            
            if ($stmt->fetchColumn() == 0) {
                // Якщо запису немає, нараховуємо XP
                $stmt = $pdo->prepare("
                    UPDATE user_progress 
                    SET experience_points = experience_points + 10 
                    WHERE user_id = :user_id
                ");
                $stmt->execute([':user_id' => $_SESSION['user_id']]);

                // Логуємо нарахування XP
                $stmt = $pdo->prepare("
                    INSERT INTO user_xp_log (user_id, action_type, action_reference_id, experience_points) 
                    VALUES (:user_id, 'review', :book_id, 10)
                ");
                $stmt->execute([
                    ':user_id' => $_SESSION['user_id'],
                    ':book_id' => $bookId
                ]);
            }
        }

        $message = 'Відгук успішно додано';
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => $message]);

} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Помилка бази даних: ' . $e->getMessage()]);
    error_log('Помилка PDO: ' . $e->getMessage());
}
?> 