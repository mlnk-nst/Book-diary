<?php
header('Content-Type: application/json');
session_start();
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $bookId = $input['bookId'] ?? null;

    if ($_SESSION['role'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Доступ заборонено.']);
        exit;
    }

    $adminId = $_SESSION['user_id'];

    if (!$bookId) {
        echo json_encode(['success' => false, 'message' => 'ID книги не вказано.']);
        exit;
    }

    include __DIR__ . '/../database.php';

    $stmt = $pdo->prepare("
        SELECT b.book_id, b.name AS title, b.published_year AS year
        FROM books b
        WHERE b.book_id = ?
    ");
    $stmt->execute([$bookId]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$book) {
        echo json_encode(['success' => false, 'message' => 'Книга не знайдена.']);
        exit;
    }

    $authorStmt = $pdo->prepare("
        SELECT a.name
        FROM author a
        INNER JOIN book_authors ba ON ba.author_id = a.author_id
        WHERE ba.book_id = ?
        LIMIT 1
    ");
    $authorStmt->execute([$bookId]);
    $author = $authorStmt->fetchColumn() ?? '(автор не знайдений)';

    $genreStmt = $pdo->prepare("
        SELECT g.name
        FROM genre g
        INNER JOIN book_genre bg ON bg.genre_id = g.genre_id
        WHERE bg.book_id = ?
        LIMIT 1
    ");
    $genreStmt->execute([$bookId]);
    $genre = $genreStmt->fetchColumn() ?? '(жанр не знайдений)';

    $deleteStmt = $pdo->prepare("DELETE FROM books WHERE book_id = ?");
    $result = $deleteStmt->execute([$bookId]);

    if ($result) {
        $action = 'Видалено книгу';
        $oldBookData = [
            'book_id' => $book['book_id'],
            'title' => $book['title'] ?? '(немає назви)',
            'author' => $author,
            'genre' => $genre,
            'year' => $book['year'] ?? '(рік не вказано)',
        ];

        $oldValue = json_encode($oldBookData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $newValue = null;
        $timestamp = date('Y-m-d H:i:s');

        $logStmt = $pdo->prepare("
            INSERT INTO action_logs (admin_id, action, old_value, new_value, timestamp)
            VALUES (?, ?, ?, ?, ?)
        ");
        $logStmt->execute([$adminId, $action, $oldValue, $newValue, $timestamp]);

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Помилка при видаленні.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Невірний метод запиту.']);
}
?>
