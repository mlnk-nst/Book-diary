<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
include __DIR__ . '/../database.php';
try {
$genre_id = isset($_GET['genre_id']) ? intval($_GET['genre_id']) : 0;

if (!isset($_GET['genre_id']) || !is_numeric($_GET['genre_id'])) {
    echo json_encode([]);
    exit;
}
$stmt = $pdo->prepare("SELECT 
        b.book_id,
        b.name AS book_title,
        GROUP_CONCAT(DISTINCT a.name SEPARATOR ', ') AS author_name,
        b.cover_image
    FROM books b
    JOIN book_authors ba ON b.book_id = ba.book_id
    JOIN author a ON ba.author_id = a.author_id
    JOIN book_genre bg ON b.book_id = bg.book_id
    WHERE bg.genre_id = :genre_id
    GROUP BY b.book_id
    ORDER BY b.name
    LIMIT 100
");$stmt->bindParam(':genre_id', $genre_id, PDO::PARAM_INT);
$stmt->execute();

$books = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $row['cover_image'] = $row['cover_image'] ?
        'data:image/jpeg;base64,' . base64_encode($row['cover_image']) :
        null;
    $books[] = $row;
}

echo json_encode([
    'success' => true,
    'data' => $books
]);

} catch (PDOException $e) {
http_response_code(500);
echo json_encode([
    'success' => false,
    'message' => 'Database error: ' . $e->getMessage()
]);
}
?>