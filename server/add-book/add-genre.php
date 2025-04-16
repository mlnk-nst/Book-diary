<?php
include __DIR__ . '/../database.php';
header('Content-Type: application/json');

if (isset($_POST['genre_name']) && !empty($_POST['genre_name'])) {
    $genre_name = trim($_POST['genre_name']);

    $stmt = $pdo->prepare("SELECT genre_id FROM genre WHERE name = ? AND parent_genre_id IS NULL");
    $stmt->execute([$genre_name]);
    $existing_genre = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing_genre) {
        echo json_encode(["error" => "Жанр з такою назвою вже існує."]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO genre (name, parent_genre_id) VALUES (?, NULL)");
        $stmt->execute([$genre_name]);
        
        $new_genre_id = $pdo->lastInsertId();
        
        echo json_encode(["success" => true, "genre_id" => $new_genre_id, "name" => $genre_name]);
    }
} else {
    echo json_encode(["error" => "Необхідно вказати назву жанру"]);
}
?>
