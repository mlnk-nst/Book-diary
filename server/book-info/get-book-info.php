<?php
header('Content-Type: application/json');
include __DIR__ . '/../database.php';

$bookId = $_GET['id'] ?? null;


if (!$bookId) {
    error_log("Помилка: ID книги не передано. Усі GET-параметри: " . print_r($_GET, true));
    echo json_encode(['success' => false, 'error' => 'No book ID']);
    exit;
}

$stmt = $pdo->prepare("
    SELECT 
        b.book_id,
        b.name AS book_title,
        b.published_year,
        b.pages,
        b.annotation,
        b.cover_image,
        GROUP_CONCAT(DISTINCT a.name SEPARATOR ', ') AS author,
        GROUP_CONCAT(DISTINCT g.name SEPARATOR ', ') AS genres
    FROM books b
    LEFT JOIN book_authors ba ON b.book_id = ba.book_id
    LEFT JOIN author a ON ba.author_id = a.author_id
    LEFT JOIN book_genre bg ON b.book_id = bg.book_id
    LEFT JOIN genre g ON bg.genre_id = g.genre_id
    WHERE b.book_id = ?
    GROUP BY b.book_id
");
$stmt->execute([$bookId]);
$book = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$book) {
    echo json_encode(['success' => false, 'error' => 'Book not found']);
    exit;
}

if ($book['cover_image']) {
    $book['cover_image'] = 'data:image/jpeg;base64,' . base64_encode($book['cover_image']);
} else {
    $book['cover_image'] = null;
}

echo json_encode([
    'success' => true,
    'book' => [
        'book_title' => $book['book_title'],
        'author' => $book['author'],
        'genres' => $book['genres'],
        'published_year' => $book['published_year'],
        'pages' => $book['pages'],
        'annotation' => $book['annotation'],
        'cover_image' => $book['cover_image']
    ]
]);