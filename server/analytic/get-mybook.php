<?php
session_start();

// Перевірка наявності user_id в сесії
if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

include __DIR__ . '/../database.php'; 

$user_id = $_SESSION['user_id'];
$status = $_GET['status'];

$query = "SELECT 
        b.book_id,
        b.name AS title,
        a.name AS author,
        d.status,
        d.rating,
        d.read_data,
        b.cover_image 
    FROM books b
    JOIN diary d ON b.book_id = d.book_id
    JOIN book_authors ba ON b.book_id = ba.book_id
    JOIN author a ON ba.author_id = a.author_id
    WHERE d.status = :status AND d.user_id = :user_id
";

$stmt = $pdo->prepare($query);
$stmt->bindParam(':status', $status, PDO::PARAM_STR);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();

$books = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($books as &$book) {
    if ($book['cover_image']) {
        $book['cover_image'] = base64_encode($book['cover_image']);
    }
}

echo json_encode($books);
?>
