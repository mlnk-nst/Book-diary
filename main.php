<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Читацький щоденник</title>
    <link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="style/style-main.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">
    <link rel="website icon" type="png" href="picture/logo-s.png">
</head>

<body>
    <div class="header">
        <?php include 'iteration/header.php'; ?>
</div>
    <img src="picture/book.png" alt="book" class="book">
    <div class="container1">
        <h2>Читацький щоденник</h2>
        <p class="font1"> — це ваш особистий простір, де кожна книга оживає, а враження залишається назавжди.</p>
        <p class="f">Зберігайте враження від кожної книги, слідкуйте за своїм прогресом, плануйте нові літературні
            пригоди.
            Читайте відгуки інших користувачів і відкривайте собі нові книги, які можуть стати вашими улюбленими.
            Читацький щоденник допоможе вам зберегти важливі моменти та підтримати інтерес до читання, адже кожна
            прочитана книга — це новий крок на вашому шляху.</p>
    </div>
    <div class="wrapper">
        <div class="container2">
            <img src="picture/coin.png" alt="coin">
            <div class="text-content">
                <h3>Букси</h3>
                <p class="font_text">За кожну прочитану книгу ви отримуєте бали, які допомагають підвищити ваш рівень
                </p>
            </div>
        </div>
        <div class="container3">
            <img src="picture/response.png" alt="response">
            <div class="text-content">
                <h3>Обговорення</h3>
                <p class="font_text">Діліться враженнями від прочитаних книг з іншими користувачами!</p>
            </div>
        </div>
    </div>
    <section>

        <div class="h-2">
            <h3 id ="new-book" class="font_h">Новинки</h3>
             <button id="b-new" class="show-all" onclick="toggleView('new-books', this)">Показати всі</button>
         </div>
        <div id="new-books" class="book-row-container">
             <button class="scroll-btn left">
            <img src="picture/arrow-l.png" alt="Попередні книги">
         </button>
        <div id="new-books-list" class="book-row"></div>
        <button class="scroll-btn right">
            <img src="picture/arrow-r.png" alt="Наступні книги">
        </button>
     
        </div>

        <div class="h-2">
            <h3 class="font_h">Популярні</h3>
             <button id="b-popular" class="show-all" onclick="toggleView('popular-books', this)">Показати всі</button>
         </div>
        <div id="popular-books" class="book-row-container">
             <button class="scroll-btn left">
            <img src="picture/arrow-l.png" alt="Попередні книги">
         </button>
        <div id="popular-books-list" class="book-row"></div>
        <button class="scroll-btn right">
            <img src="picture/arrow-r.png" alt="Наступні книги">
        </button>
     
        </div>

    </section>
    <div class=" footer">
            <?php include 'iteration/footer.php'; ?>
        </div>
        <script src="js/main.js"></script>
</body>

</html>