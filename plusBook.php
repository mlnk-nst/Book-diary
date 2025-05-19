<?php
include 'server/database.php';
$message = isset($_GET['message']) ? $_GET['message'] : '';
$messageType = isset($_GET['message_type']) ? $_GET['message_type'] : '';

$bookId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$bookData = null;
$authorsString = '';
$selectedGenreId = null;
$selectedSubgenres = [];

if ($bookId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM books WHERE book_id = ?");
    $stmt->execute([$bookId]);
    $bookData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$bookData) {
        die("Книга не знайдена");
    }
    $stmt = $pdo->prepare("SELECT a.name FROM author a
        JOIN book_authors ba ON a.author_id = ba.author_id
        WHERE ba.book_id = ?");
    $stmt->execute([$bookId]);
    $authors = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $authorsString = implode(', ', $authors);

    $stmt = $pdo->prepare("SELECT g.genre_id FROM book_genre bg
        JOIN genre g ON bg.genre_id = g.genre_id
        WHERE bg.book_id = ? AND g.parent_genre_id IS NULL LIMIT 1");
    $stmt->execute([$bookId]);
    $selectedGenreId = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT g.genre_id FROM book_genre bg
        JOIN genre g ON bg.genre_id = g.genre_id
        WHERE bg.book_id = ? AND g.parent_genre_id IS NOT NULL");
    $stmt->execute([$bookId]);
    $selectedSubgenres = $stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $bookId ? "Редагувати книгу" : "Додати книгу"; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">
    <link rel="website icon" type="png" href="picture/logo-s.png">
    <link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="style/plus-book.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />

</head>
<body>
<?php if ($message): ?>
    <div class="message <?php echo $messageType; ?>">
    <p><?php echo htmlspecialchars($message); ?></p>
</div>
<?php endif; ?>


    <div class="form-b">
    <h2><?php echo $bookId ? "Редагувати книгу" : "Додати нову книгу"; ?></h2>
    <form  class="form-book"id="bookForm"  action="server/add-book/adding_books.php" method="post" enctype="multipart/form-data">
    <div class="left-column">
          <input type="hidden" name="book_id" id="book_id">
        <?php if ($bookId > 0): ?>
            <input type="hidden" name="book_id" value="<?php echo $bookId; ?>">
        <?php endif; ?>

        <label class="label" for="title">Назва книги:</label>
       <input type="text" id="title" name="title" class="input" required
               value="<?php echo htmlspecialchars($bookData['name'] ?? ''); ?>">
        
        <label class="label" for="author">Автор:</label>
         <input type="text" id="author" name="author" class="input" required
               pattern="^[a-zA-Zа-яА-ЯіІїЇєЄґҐ\s.,-']+$"
               value="<?php echo htmlspecialchars($authorsString); ?>">

        <label class="label" for="year">Рік видання:</label>
        <input type="number" id="year" name="year"class="input" required
               value="<?php echo htmlspecialchars($bookData['published_year'] ?? ''); ?>">

        <label class="label" for="genre">Жанр:</label>
        <select class="input" id="genre" name="genre" required>
            <option value="">Оберіть жанр</option>
            <?php
            $stmt = $pdo->query("SELECT * FROM genre WHERE parent_genre_id IS NULL");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $selected = $row['genre_id'] == $selectedGenreId ? 'selected' : '';
                echo "<option value='{$row['genre_id']}' $selected>{$row['name']}</option>";
            }
            ?>
        </select>

        <div class="checkbox-container">
        <input type="checkbox" id="new_genre_checkbox" name="new_genre_checkbox" >
        <label for="new_genre_checkbox">Немає потрібного жанру?</label>
    </div>

    <div id="new_genre_container" style="visibility:hidden;">
        <label class="label" for="new_genre">Додати новий жанр:</label>
        <input class="input i1" type="text" id="new_genre" name="new_genre" placeholder="Введіть новий жанр" >
    </div>
</div>

<div class="right-column">
    <label class="label" for="subgenre">Піджанр:</label>
     <select class="input" id="subgenre" name="subgenre[]" multiple>
            <?php
            if ($selectedGenreId) {
                $stmt = $pdo->prepare("SELECT * FROM genre WHERE parent_genre_id = ?");
                $stmt->execute([$selectedGenreId]);
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $selected = in_array($row['genre_id'], $selectedSubgenres) ? 'selected' : '';
                    echo "<option value='{$row['genre_id']}' $selected>{$row['name']}</option>";
                }
            }
            ?>
        </select>
        <label class="label" for="pages">Кількість сторінок:</label>
         <input type="number" id="pages" name="pages" class="input" min="1" required
               value="<?php echo htmlspecialchars($bookData['pages'] ?? ''); ?>">
        
        <label class="label" for="annotation">Анотація:</label>
        <textarea id="annotation"  class="annotation"name="annotation" required><?php echo htmlspecialchars($bookData['annotation'] ?? ''); ?></textarea>

        <label class="label">Обкладинка книги:</label>
        <input type="file" id="image" name="image" accept="image/*" class="file-input" >
         <div class="file-btn">
            <?php
            $imageLabelText = $bookId > 0 ? 'Оновити' : 'Обрати файл';
            ?>      
            <label for="image" class="btn-blue file-label"><?php echo $imageLabelText; ?></label>
            <span class="file-name">Файл не вибрано</span>
       </div>
       </div>
          <button type="submit" id="submitBtn" class="btn-blue btn"><?php echo $bookId ? "Зберегти зміни" : "Додати книгу"; ?></button>
    </form>
    </form></div>
     <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="js/plusBook.js"> </script>
</body>
</html>