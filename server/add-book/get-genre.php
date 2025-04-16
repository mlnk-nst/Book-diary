<?php
include __DIR__ . '/../database.php';
header('Content-Type: application/json');

$stmt = $pdo->prepare("SELECT genre_id, name FROM genre WHERE parent_genre_id IS NULL");
$stmt->execute();
$genres = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($genres) {
    echo json_encode($genres);
} else {
    echo json_encode(["error" => "Основних жанрів не знайдено"]);
}
?>