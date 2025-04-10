<?php
//вивід книг в каталозі
header('Content-Type: application/json');
include __DIR__ . '/../database.php';
try {
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $booksPerPage = 30;
    $offset = ($page - 1) * $booksPerPage;

    $stmt = $pdo->prepare("
    SELECT 
        b.book_id,
        b.name as book_title,
        a.name as author_name,
        b.cover_image
    FROM books b
    JOIN author a ON b.author_id = a.author_id
    ORDER BY b.name
    LIMIT :offset, :limit
    ");
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $booksPerPage, PDO::PARAM_INT);
    $stmt->execute();

    $books = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $row['cover_image'] = $row['cover_image'] ? 
            'data:image/jpeg;base64,' . base64_encode($row['cover_image']) : 
            null;
        
        $books[] = $row;
    }

    $stmt = $pdo->query("SELECT COUNT(*) FROM books");
    $totalBooks = $stmt->fetchColumn();
    $totalPages = ceil($totalBooks / $booksPerPage);


    echo json_encode([
        'success' => true,
        'data' => $books,
        'count' => count($books),
        'total_pages' => $totalPages,
        'current_page' => $page
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
