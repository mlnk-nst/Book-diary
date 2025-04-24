<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
include __DIR__ . '/../database.php';

if (!isset($_GET['genre_id']) || !is_numeric($_GET['genre_id'])) {
    echo json_encode([]);
    exit;
}
$genre_id = intval($_GET['genre_id']);
$stmt = $pdo->prepare("
    SELECT 
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
");
$stmt->bindParam(':genre_id', $genre_id, PDO::PARAM_INT);
$stmt->execute();
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

$response = [
    'success' => true,
    'data' => $books
];

echo json_encode($response);
?>