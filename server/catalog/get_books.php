<?php
//вивід книг в каталозі
header('Content-Type: application/json');
try {
include 'server/database.php'; 

$stmt = $pdo->prepare("
SELECT 
    b.book_id,
    b.name as book_title,
    b.published_year,
    b.pages,
    b.annotation,
    b.cover_image,
    a.name as author_name,
    a.bio as author_bio,
    GROUP_CONCAT(DISTINCT g.name SEPARATOR ', ') as genres
FROM books b
JOIN author a ON b.author_id = a.author_id
LEFT JOIN book_genre bg ON b.book_id = bg.book_id
LEFT JOIN genre g ON bg.genre_id = g.genre_id
GROUP BY b.book_id
ORDER BY b.name
");
$stmt->execute();
$books = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    // Обробка зображення
    $row['cover_image'] = $row['cover_image'] ? 
        'data:image/jpeg;base64,' . base64_encode($row['cover_image']) : 
        null;
    
    // Обробка жанрів
    $row['genres'] = $row['genres'] ? explode(', ', $row['genres']) : [];
    
    $books[] = $row;
}

echo json_encode([
    'success' => true,
    'data' => $books,
    'count' => count($books)
]);

} catch (PDOException $e) {
http_response_code(500);
echo json_encode([
    'success' => false,
    'message' => 'Database error: ' . $e->getMessage()
]);
}
?>