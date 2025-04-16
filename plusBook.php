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
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">

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
        <select class="input"  id="author" name="author[]"required multiple pattern="^[a-zA-Zа-яА-ЯіІїЇєЄґҐ\s.]+$"> </select>


        <label class="label" for="year">Рік видання:</label>
        <input class="input" type="number" id="year" name="year" required>

        <label class="label" for="genre">Жанр:</label>
             <select class="input" id="genre" name="genre" required>
            <option value="">Оберіть жанр</option>
        </select>   
</div>

<div class="right-column">
    <label class="label" for="subgenre">Піджанр:</label>
    <select class="input" id="subgenre" name="subgenre[]" multiple required pattern="^[a-zA-Zа-яА-ЯіІїЇєЄґҐ\s.]+$">
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
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script src="js/plusBook.js"> </script>
</body>
</html>