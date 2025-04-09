<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Аналітика</title>
    <link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="style/style-analytics.css">
    <link rel="website icon" type="png" href="picture/logo-s.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">

</head>

<body>
    <div class="header">
        <?php include 'iteration/header.php'; ?>
    </div>
    <section class="month">
        <button id="prev-month">
            <img src="picture/arrow-l.png" alt="Попередній місяць" class="arrow">
        </button>
        <span id="current-month" class="current-month"></span>
        <button id="next-month">
            <img src="picture/arrow-r.png" alt="Наступний місяць" class="arrow">
        </button>
    </section>
    <div class="main-container">
        <div id="calendar" class="calendar">
            <div id="days" class="days-grid"></div>
        </div>
        <section class="container">
            <div class="circle-item">
                <div class="circle circle-1"></div>
                <h4 class="text-circle">1 сек+</h4>
            </div>
            <div class="circle-item">
                <div class="circle circle-2"></div>
                <h4 class="text-circle">10 хв+</h4>
            </div>
            <div class="circle-item">
                <div class="circle circle-3"></div>
                <h4 class="text-circle">30 хв+</h4>
            </div>
            <div class="circle-item">
                <div class="circle circle-4"></div>
                <h4 class="text-circle">1 год+</h4>
            </div>
        </section>
    </div>
    <section class="book-read">
        <h4 class="current-month"> Прочитані книги за місяць</h4>
        <div id="read-books-container" class="my-book book-row"></div>
        <div id="read-books-empty" class="empty-message" ">Немає прочитаних книг за цей місяць </div>
    </section>
    <div class=" footer">
            <?php include 'iteration/footer.php'; ?>
        </div>
        <script src="js/analytics.js"></script>
</body>

</html>