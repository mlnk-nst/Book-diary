<?php
session_start();
header('Content-Type: application/json');

include __DIR__ . '/../database.php';
include __DIR__ . '/../xp/level_system.php';

$userId = $_SESSION['user_id'] ?? null;
$input = json_decode(file_get_contents('php://input'), true);

if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'Користувач не авторизований']);
    exit;
}

$bookId = $input['bookId'] ?? null;
$rating = $input['rating'] ?? null;
$reviewText = $input['reviewText'] ?? null;
$isPublic = $input['is_public'] ?? false;

if (!$bookId || !$rating) {
    echo json_encode(['success' => false, 'message' => 'Відсутні необхідні дані']);
    exit;
}

try {
    $pdo->beginTransaction();

    if (!empty(trim($reviewText))) {
        $stmtReview = $pdo->prepare("INSERT INTO reviews (user_id, book_id, review_text, is_public, created_at) 
            VALUES (?, ?, ?, ?, NOW())");
        $stmtReview->execute([$userId, $bookId, $reviewText, $isPublic]);
        $reviewId = $pdo->lastInsertId();

        $stmtProgress = $pdo->prepare("SELECT experience_points FROM user_progress WHERE user_id = ?");
        $stmtProgress->execute([$userId]);
        $progress = $stmtProgress->fetch(PDO::FETCH_ASSOC);

        if (!$progress) {
            $stmtInsert = $pdo->prepare("INSERT INTO user_progress (user_id, experience_points, level) VALUES (?, 15, 1)");
            $stmtInsert->execute([$userId]);
        } else {
            $newExp = $progress['experience_points'] + 15;
            $stmtUpdate = $pdo->prepare("UPDATE user_progress SET experience_points = ? WHERE user_id = ?");
            $stmtUpdate->execute([$newExp, $userId]);
        }

        $stmtLog = $pdo->prepare("INSERT INTO user_xp_log (user_id, action_type, action_reference_id, experience_points) VALUES (?, ?, ?, ?)");
        $stmtLog->execute([$userId, 'review', $bookId, 15]);

        checkAndUpdateLevel($userId, $pdo);
    } else {
        $reviewId = null;
    }

    $stmtDiary = $pdo->prepare("UPDATE diary SET reviews_id = ?, rating = ? WHERE user_id = ? AND book_id = ?");
    $stmtDiary->execute([$reviewId, $rating, $userId, $bookId]);

    $pdo->commit();
    echo json_encode([
        'success' => true, 
        'message' => !empty(trim($reviewText)) ? 
            'Відгук успішно збережено та нараховано 15 балів!' : 
            'Оцінку успішно збережено'
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    error_log('Error in submit-review.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Помилка при збереженні відгуку']);
}
?> 