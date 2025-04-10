<?php
//меню в каталозі 

include 'server/database.php'; 
$stmt = $pdo->prepare("SELECT genre_id, name FROM genre WHERE parent_genre_id IS NULL");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

function getSubgenres($parent_genre_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT genre_id, name FROM genre WHERE parent_genre_id = ?");
    $stmt->execute([$parent_genre_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
