<?php 
include 'database.php';
header('Content-Type: application/json');
// добавлення піджанру в базу
// сторінка для добавлення книг

if (isset($_POST['subgenre_name']) && isset($_POST['parent_genre_id'])) {
    $subgenre_name = trim($_POST['subgenre_name']);
    $parent_genre_id = (int) $_POST['parent_genre_id'];

    $stmt = $pdo->prepare("SELECT genre_id FROM genre WHERE name = ? AND parent_genre_id = ?");
    $stmt->execute([$subgenre_name, $parent_genre_id]);
    $subgenre = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($subgenre) {
       
        echo json_encode(["success" => true, "message" => "Піджанр вже існує"]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO genre (name, parent_genre_id) VALUES (?, ?)");
        $stmt->execute([$subgenre_name, $parent_genre_id]);
        echo json_encode(["success" => true, "message" => "Піджанр успішно додано"]);
    }
} else {
    echo json_encode(["error" => "Недостатньо даних для додавання піджанра"]);
}
?>