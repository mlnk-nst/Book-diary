<?php
header('Content-Type: application/json');

include __DIR__ . '/../database.php';
$type = $_GET['type'] ?? '';

if ($type === 'new') {
    $stmt = $pdo->prepare("
        SELECT b.book_id, b.name AS title, b.cover_image, a.name AS author
        FROM books b
        JOIN book_authors ba ON b.book_id = ba.book_id
        JOIN author a ON ba.author_id = a.author_id
        ORDER BY b.book_id DESC
        LIMIT 24
    ");
}
elseif ($type === 'popular') {
 $stmtTop = $pdo->prepare("
        SELECT book_id, COUNT(*) AS cnt
        FROM diary
        GROUP BY book_id
        ORDER BY cnt DESC
        LIMIT 24
    ");
    $stmtTop->execute();
    $topBooks = $stmtTop->fetchAll(PDO::FETCH_ASSOC);

    if (empty($topBooks)) {
        echo json_encode([]);
        exit;
    }

    $ids = array_column($topBooks, 'book_id');
    $inQuery = implode(',', array_fill(0, count($ids), '?'));

    $stmtBooks = $pdo->prepare("
        SELECT b.book_id, b.name AS title, b.cover_image, a.name AS author
        FROM books b
        JOIN book_authors ba ON b.book_id = ba.book_id
        JOIN author a ON ba.author_id = a.author_id
        WHERE b.book_id IN ($inQuery)
        ORDER BY b.book_id
    ");
    $stmtBooks->execute($ids);

    $booksRaw = $stmtBooks->fetchAll(PDO::FETCH_ASSOC);

    $books = [];
    foreach ($booksRaw as $row) {
        $bookId = $row['book_id'];
        if (!isset($books[$bookId])) {
            $books[$bookId] = [
                'title' => $row['title'],
                'authors' => [],
                'cover' => 'data:image/jpeg;base64,' . base64_encode($row['cover_image'])
            ];
        }
        $books[$bookId]['authors'][] = $row['author'];
    }

    foreach ($books as &$book) {
        $book['author'] = implode(', ', $book['authors']);
        unset($book['authors']);
    }

    $books = array_values($books);

    echo json_encode($books);
    exit;
}


$stmt->execute();
$books = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $books[] = [
         'book_id' => $row['book_id'],
        'title' => $row['title'],
        'author' => $row['author'],
        'cover' => 'data:image/jpeg;base64,' . base64_encode($row['cover_image'])
    ];
}

echo json_encode($books);
