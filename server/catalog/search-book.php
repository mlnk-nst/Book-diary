<?php
header('Content-Type: application/json');
include __DIR__ . '/../database.php';

try {
    $query = isset($_GET['q']) ? trim($_GET['q']) : '';

    if (empty($query)) {
        echo json_encode(['success' => false, 'message' => 'Порожній запит']);
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT 
            b.book_id,
            b.name AS book_title,
            GROUP_CONCAT(a.name SEPARATOR ', ') AS author_name,
            b.cover_image
        FROM books b
        JOIN book_authors ba ON b.book_id = ba.book_id
        JOIN author a ON ba.author_id = a.author_id
        WHERE LOWER(b.name) LIKE LOWER(:query)
           OR LOWER(a.name) LIKE LOWER(:query)
        GROUP BY b.book_id
        ORDER BY b.name
        LIMIT 100
    ");

    $likeQuery = '%' . $query . '%';
    $stmt->bindParam(':query', $likeQuery, PDO::PARAM_STR);
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