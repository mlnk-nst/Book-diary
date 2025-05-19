<?php
session_start();
include __DIR__ . '/../database.php';

$message = ''; 
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    $adminId = $isAdmin ? $_SESSION['user_id'] : null;

    $book_id = isset($_POST['book_id']) ? (int)$_POST['book_id'] : 0;
    if ($book_id <= 0) {
        $message = "Невірний ID книги для редагування.";
        $messageType = 'error';
        header("Location:/book-diary/plusBook.php?book_id=$book_id&message=" . urlencode($message) . "&message_type=" . urlencode($messageType));
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM books WHERE book_id = ?");
    $stmt->execute([$book_id]);
    $oldBook = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$oldBook) {
        $message = "Книга не знайдена.";
        $messageType = 'error';
        header("Location:/book-diary/plusBook.php?book_id=$book_id&message=" . urlencode($message) . "&message_type=" . urlencode($messageType));
        exit;
    }
    $title = trim($_POST['title'] ?? '');
    $year = isset($_POST['year']) ? (int)$_POST['year'] : null;
    $pages = trim($_POST['pages'] ?? '');
    $annotation = trim($_POST['annotation'] ?? '');

    // Обробка жанру
    if (isset($_POST['new_genre_checkbox']) && !empty($_POST['new_genre'])) {
        $new_genre = trim($_POST['new_genre']);
        $stmt = $pdo->prepare("SELECT genre_id FROM genre WHERE name = ?");
        $stmt->execute([$new_genre]);
        $genre = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($genre) {
            $genre_id = $genre['genre_id'];
        } else {
            $stmt = $pdo->prepare("INSERT INTO genre (name, parent_genre_id) VALUES (?, ?)");
            $stmt->execute([$new_genre, NULL]);
            $genre_id = $pdo->lastInsertId();
        }
    } else {
        $genre_id = $_POST['genre'] ?? null;
    }

    // Піджанри
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
                    }
                }
            }
        }
    }

    // Обробка авторів — так само, як у додаванні
    $author_names = explode(',', $_POST['author'] ?? '');
    $author_ids = [];
    foreach ($author_names as $author_name) {
        $author_name = trim($author_name);
        if (!empty($author_name)) {
            $stmt = $pdo->prepare("SELECT author_id FROM author WHERE name = ?");
            $stmt->execute([$author_name]);
            $author = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($author) {
                $author_ids[] = $author['author_id'];
            } else {
                $stmt = $pdo->prepare("INSERT INTO author (name) VALUES (?)");
                $stmt->execute([$author_name]);
                $author_ids[] = $pdo->lastInsertId();
            }
        }
    }

    if (empty($author_ids)) {
        $message = "Будь ласка, введіть хоча б одного автора.";
        $messageType = 'error';
        header("Location:/book-diary/plusBook.php?book_id=$book_id&message=" . urlencode($message) . "&message_type=" . urlencode($messageType));
        exit;
    }

    $currentYear = date("Y");
    if ($year !== null && ($year < 1800 || $year > $currentYear)) {
        $message = "Рік видання повинен бути між 1800 і поточним роком ($currentYear).";
        $messageType = 'error';
        header("Location:/book-diary/plusBook.php?book_id=$book_id&message=" . urlencode($message) . "&message_type=" . urlencode($messageType));
        exit;
    }


    $fieldsToUpdate = [];
    $params = [];

    if ($title !== '' && $title !== $oldBook['name']) {
        $fieldsToUpdate[] = "name = ?";
        $params[] = $title;
    }
    if ($year !== null && $year != $oldBook['published_year']) {
        $fieldsToUpdate[] = "published_year = ?";
        $params[] = $year;
    }
    if ($pages !== '' && $pages != $oldBook['pages']) {
        $fieldsToUpdate[] = "pages = ?";
        $params[] = $pages;
    }
    if ($annotation !== '' && $annotation != $oldBook['annotation']) {
        $fieldsToUpdate[] = "annotation = ?";
        $params[] = $annotation;
    }

    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imageData = file_get_contents($_FILES['image']['tmp_name']);
            
            $fieldsToUpdate[] = "cover_image = ?";
            $params[] = $imageData;
        } else {
            $message = "Помилка завантаження обкладинки.";
            $messageType = 'error';
            header("Location:/book-diary/plusBook.php?book_id=$book_id&message=" . urlencode($message) . "&message_type=" . urlencode($messageType));
            exit;
        }
    }

    if (!empty($fieldsToUpdate)) {
        $params[] = $book_id;
        $sql = "UPDATE books SET " . implode(", ", $fieldsToUpdate) . " WHERE book_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    }
    $stmt = $pdo->prepare("DELETE FROM book_authors WHERE book_id = ?");
    $stmt->execute([$book_id]);

    foreach ($author_ids as $author_id) {
        $stmt = $pdo->prepare("INSERT INTO book_authors (book_id, author_id) VALUES (?, ?)");
        $stmt->execute([$book_id, $author_id]);
    }

    $stmt = $pdo->prepare("DELETE FROM book_genre WHERE book_id = ?");
    $stmt->execute([$book_id]);

    $genre_ids = array_merge([$genre_id], $subgenre_ids);
    foreach ($genre_ids as $g_id) {
        $stmt = $pdo->prepare("INSERT INTO book_genre (book_id, genre_id) VALUES (?, ?)");
        $stmt->execute([$book_id, $g_id]);
    }

    // --- Логування ---
    if ($isAdmin) {
        $oldValue = json_encode([
            'title' => $oldBook['name'],
            'year' => $oldBook['published_year'],
            'pages' => $oldBook['pages'],
            'annotation' => $oldBook['annotation'],
        ], JSON_UNESCAPED_UNICODE);

        $newValue = json_encode([
            'title' => $title,
            'year' => $year,
            'pages' => $pages,
            'annotation' => $annotation,
        ], JSON_UNESCAPED_UNICODE);

        $stmt = $pdo->prepare("INSERT INTO action_logs (admin_id, action, old_value, new_value, timestamp) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([
            $adminId,
            'Редагування книги #' . $book_id,
            $oldValue,
            $newValue
        ]);
    }

    $message = "Книгу успішно оновлено!";
    $messageType = 'success';

    header("Location:/book-diary/plusBook.php?book_id=$book_id&message=" . urlencode($message) . "&message_type=" . urlencode($messageType));
    exit;
}
?>
