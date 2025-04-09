<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мої книги</title>
    <link rel="website icon" type="png" href="picture/logo-s.png">
    <link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="style/style-mybook.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">

</head>

<body>
<div class="header">
        <?php include 'iteration/header.php'; ?>
    </div>

 <section class="book"> 
    <div class="h-2">
        <h3 class="font_h">Продовжити читати</h3>
        <button id="b-continue" class="show-all" onclick="toggleView('continue-reading', this)">Показати всі</button>
    </div>
    <div class="book-row-container">
        <button class="scroll-btn left">
            <img src="picture/arrow-l.png" alt="Попередні книги">
         </button>
        <div id="continue-reading" class="book-row"></div>
        <button class="scroll-btn right">
            <img src="picture/arrow-r.png" alt="Наступні книги">
        </button>
         <div id="continue-empty" class="empty-message">У вас поки немає книг для продовження</div>
    </div>
    <div class="h-2">
        <h3 class="font_h">Збережені книги</h3>
        <button id="b-saved"class="show-all" onclick="toggleView('saved-books', this)">Показати всі</button>
    </div>
    <div class="book-row-container">
        <button class="scroll-btn left">
            <img src="picture/arrow-l.png" alt="Попередні книги">
         </button>
        <div class="book-row" id="saved-books"></div>
        <button class="scroll-btn right">
            <img src="picture/arrow-r.png" alt="Наступні книги">
        </button>
          <div  id="saved-empty" class="empty-message">Тут з’являться книги, які ви зберегли на потім </div>
    </div>
    <div class="h-2">
        <h3 class="font_h">Прочитані</h3>
        <button id="b-read"class="show-all" onclick="toggleView('read-books', this)">Показати всі</button>
    </div>
    <div class="book-row-container">
        <button class="scroll-btn left">
            <img src="picture/arrow-l.png" alt="Попередні книги">
         </button>
    <div class="book-row" id="read-books"> </div>
    <button class="scroll-btn right">
            <img src="picture/arrow-r.png" alt="Наступні книги">
        </button>
        <div  id="read-empty" class="empty-message">Ви ще не прочитали жодної книги </div>
    </div>
 
</section>
<script src="js/myBooks.js"></script>
    <div class="footer">
        <?php include 'iteration/footer.php'; ?>
    </div>
</body>

</html>