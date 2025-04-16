<?php
include 'database.php'; 

$message = ''; 
$messageType = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageData = file_get_contents($_FILES['image']['tmp_name']);  }
        else {  $imageData = ''; }

        $title = trim($_POST['title']);
        $author = trim($_POST['author']);
        $year = trim($_POST['year']);
        $pages = trim($_POST['pages']);
        $annotation = trim($_POST['annotation']);

    /*жанр*/
        if (isset($_POST['new_genre_checkbox']) && !empty($_POST['new_genre'])) {
            $new_genre = trim($_POST['new_genre']);
            $stmt = $pdo->prepare("SELECT genre_id FROM genre WHERE name = ?");
            $stmt->execute([$new_genre]);
            $genre = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($genre) {
                    $genre_id = $genre['genre_id']; }
                else {
                    $stmt = $pdo->prepare("INSERT INTO genre (name, parent_genre_id) VALUES (?, ?)");
                    $stmt->execute([$new_genre, NULL]);
                    $genre_id = $pdo->lastInsertId(); }
                } 
        else { $genre_id = $_POST['genre'];  }

        /*піджанр */
        $subgenre_ids = [];
        if (isset($_POST['subgenre'])) {
            foreach ($_POST['subgenre'] as $subgenre_id) {
                if (!empty($subgenre_id)) {
                    if (is_numeric($subgenre_id)) {
                        $subgenre_ids[] = (int)$subgenre_id;
                    } else { 
                        $subgenre_name = trim($subgenre_id);
                        if (!empty($subgenre_name)) { 
                            $stmt = $pdo->prepare("SELECT genre_id FROM genre WHERE name = ? AND parent_genre_id = ?");
                            $stmt->execute([$subgenre_name, $genre_id]);
                            $subgenre = $stmt->fetch(PDO::FETCH_ASSOC);
                            
                            if ($subgenre) {
                                $subgenre_ids[] = $subgenre['genre_id'];
                            } else {
                                $stmt = $pdo->prepare("INSERT INTO genre (name, parent_genre_id) VALUES (?, ?)");
                                $stmt->execute([$subgenre_name, $genre_id]);
                                $subgenre_ids[] = $pdo->lastInsertId();
                            }
                        } else {
                            echo "Назва піджанру не може бути порожньою!";
                        }
                    }
                }
            }
        }
        
        /*автор*/
        $author_name = $_POST['author'];
        $stmt = $pdo->prepare("SELECT author_id FROM author WHERE name = ?");
        $stmt->execute([$author_name]);
        $author = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($author) { $author_id = $author['author_id'];  }
            else {  $stmt = $pdo->prepare("INSERT INTO author (name) VALUES (?)");
                    $stmt->execute([$author_name]);
                    $author_id = $pdo->lastInsertId();}
        $currentYear = date("Y");
        if (isset($_POST['year'])) {
            $year = (int)$_POST['year'];
            if ($year < 1800 || $year > $currentYear) {
               $message = "Рік видання повинен бути між 1800 і поточним роком ($currentYear).";
               $messageType = 'error';
               exit; }}

/*перевірка в базі*/
$stmt = $pdo->prepare("SELECT b.book_id FROM books b WHERE b.name = ? AND b.author_id = ? AND b.published_year = ?");
$stmt->execute([$title, $author_id, $year]);
$existingBook = $stmt->fetch(PDO::FETCH_ASSOC);

var_dump($existingBook);
 if ($existingBook) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM book_genre WHERE book_id = ? AND genre_id IN (" .implode(',', array_map('intval', $subgenre_ids)) . ")");
    $stmt->execute([$existingBook['book_id']]);
    $commonGenresCount = $stmt->fetchColumn();

    if ($commonGenresCount > 0) {
     $message = "Книга з такою назвою, автором та іншими параметрами вже існує в базі.";
     $messageType = 'error';
     header("Location:/курсовий 3 курс/plusBook.php?message=" . urlencode($message) . "&message_type=" . urlencode($messageType));
 exit;
 }}
    try {
        $stmt = $pdo->prepare("INSERT INTO books (name, author_id, published_year, pages, annotation, cover_image) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $author_id, $year, $pages, $annotation, $imageData]);
        $book_id = $pdo->lastInsertId();

        $genre_ids = array_merge([$genre_id], $subgenre_ids);
        foreach ($genre_ids as $g_id) {
            $stmt = $pdo->prepare("INSERT INTO book_genre (book_id, genre_id) VALUES (?, ?)");
            $stmt->execute([$book_id, $g_id]);
        }
        $message =  "Книга успішно додана!";
        $messageType = 'success';
    } 
    catch (PDOException $e) {
        $message =  "Помилка при додаванні книги: " . $e->getMessage();
        $messageType = 'error';
    }
}
header("Location:/курсовий 3 курс/plusBook.php?message=" . urlencode($message). "&message_type=" . urlencode($messageType));
exit;
?>