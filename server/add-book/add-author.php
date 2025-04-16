<?php
include __DIR__ . '/../database.php';
header('Content-Type: application/json');

if (isset($_POST['author_name']) && !empty($_POST['author_name'])) {
    $author_name = trim($_POST['author_name']);

    $stmt = $pdo->prepare("SELECT author_id FROM authors WHERE name = ?");
    $stmt->execute([$author_name]);
    $existingAuthor = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingAuthor) {
        echo json_encode([
            'success' => true,
            'author_id' => $existingAuthor['author_id'],
            'author_name' => $author_name
        ]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO authors (name) VALUES (?)");
        $stmt->execute([$author_name]);

        $authorId = $pdo->lastInsertId();

        echo json_encode([
            'success' => true,
            'author_id' => $authorId,
            'author_name' => $author_name
        ]);
    }
} else {
    echo json_encode(['error' => 'Не вказано ім\'я автора']);
}
?>
