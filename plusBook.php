<?php include 'server/database.php'; 
$message = isset($_GET['message']) ? $_GET['message'] : '';
$messageType = isset($_GET['message_type']) ? $_GET['message_type'] : '';?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Додавання книги</title>
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
    <h2>Додати нову книгу</h2> 
    <form  class="form-book" action="server/adding_books.php" method="post" enctype="multipart/form-data">
    <div class="left-column">
        <label class="label" for="title">Назва книги:</label>
        <input class="input" type="text" id="title" name="title" required>
        
        <label class="label" for="author">Автор:</label>
        <input class="input" type="text" id="author" name="author" required  pattern="^[a-zA-Zа-яА-ЯіІїЇєЄґҐ\s.]+$">

        <label class="label" for="year">Рік видання:</label>
        <input class="input" type="number" id="year" name="year" required>
        <label class="label" for="genre">Жанр:</label>
        <select class="input" id="genre" name="genre" required>
        <option value="">Оберіть жанр</option>   
        <?php
            $stmt = $pdo->query("SELECT * FROM genre WHERE parent_genre_id IS NULL");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<option value='{$row['genre_id']}'>{$row['name']}</option>";}
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
        <option value="">Оберіть піджанр</option> </select>

        <label class="label" for="pages">Кількість сторінок:</label>
        <input class="input" type="number" id="pages" name="pages" min="1" required>
        
        <label class="label" for="annotation">Анотація:</label>
        <textarea class="annotation" id="annotation" name="annotation" required></textarea>

        <label class="label">Обкладинка книги:</label>
        <input type="file" id="image" name="image" accept="image/*" class="file-input" >
         <div class="file-btn">
            <label for="image" class="btn-blue file-label">Обрати файл</label>
            <span class="file-name">Файл не вибрано</span>
       </div>
       </div>
        <button type="submit" class="btn-blue btn">Додати книгу</button>
    </form></div>
     <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="js/plusBook.js"> </script>
</body>
</html>