 <?php
include 'database.php';
header('Content-Type: application/json');
//для добавлення книг 
/*вивід пджанра в фоорму*/
if (isset($_GET['genre_id']) && is_numeric($_GET['genre_id'])) {
    $genre_id = (int) $_GET['genre_id']; 
    $stmt = $pdo->prepare("SELECT genre_id, name FROM genre WHERE parent_genre_id = ?");
    $stmt->execute([$genre_id]);
    $subgenres = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($subgenres) {
        echo json_encode($subgenres);
    } else {
        echo json_encode(["error" => "Піджанрів не знайдено"]);
    }
} else {
    echo json_encode(["error" => "Виберіть основний жанр"]);
}
?>


