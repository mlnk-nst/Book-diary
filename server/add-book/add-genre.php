<?php
include __DIR__ . '/../database.php';
header('Content-Type: application/json');

if (isset($_POST['genre_name'])) {
    $genre_name = trim($_POST['genre_name']);

    $stmt = $pdo->prepare("SELECT genre_id FROM genre WHERE name = ? AND parent_genre_id IS NULL");
    $stmt->execute([$genre_name]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        echo json_encode(["success" => true, "genre_id" => $existing['genre_id'], "message" => "Жанр вже існує"]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO genre (name, parent_genre_id) VALUES (?, NULL)");
        $stmt->execute([$genre_name]);
        $newId = $pdo->lastInsertId();
        echo json_encode(["success" => true, "genre_id" => $newId, "message" => "Жанр створено"]);
    }
} else {
    echo json_encode(["error" => "Не вказано назву жанру"]);
}
?>
