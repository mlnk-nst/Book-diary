<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="style/style-book-info.css">
    <link rel="website icon" type="png" href="picture/logo-s.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">

</head>
<body>
    <div class="header">
         <?php include 'iteration/header.php'; ?>
    </div>
    <div class="wrapper" data-book-id="<?php echo $_GET['id'] ?? ''; ?>">
        <div id="messageBox" class="message book-info-message" style="display: none;"></div>
        <div class="container-left">
        <img src="" alt="Обкладинка книги" class="book-cover" id="bookCover">
        <button class="btn-blue" id="saveBookBtn">Зберегти</button>
        <button class="btn-blue" id="readBookBtn">Читати</button>
        <p class="btn-block" id="read-book">Прочитано</p>
        <p class="read-data"id="read-data">Дата завершення:</p>
        
        <div class="admin-controls" id="adminControls">
            <button class="btn-blue" id="editBookBtn">Редагувати книгу</button>
            <button class="btn-red" id="deleteBookBtn">Видалити книгу</button>
        </div>
         <div class="reading-timer" id="readingTimer" style="display: none;">
                <div class="timer-display">
                    <span id="hours">00</span>:<span id="minutes">00</span>:<span id="seconds">00</span>
                </div>
            </div>
            <button class="btn-red" id="endReadBtn" style="display: none;">Завершити читання</button>
    </div>
    <div class="info">
    <h1 id="bookTitle"></h1>
        <p><strong>Автор:</strong> <span id="bookAuthor"></span></p>
        <p><strong>Жанри:</strong> <span id="bookGenres"></span></p>
        <p><strong>Рік випуску:</strong> <span id="bookYear"></span></p>
        <p><strong>Кількість сторінок:</strong> <span id="bookPages"></span></p>

        <div class="book-status" id="bookStatusContainer" style="display: none;">
        <strong>Статус:</strong>
        <span id="statusText"></span>
    </div>
        <div class="rating-section" id="ratingSection" style="display: none;">
            <strong>Оцінка:</strong>
            <div class="stars">
                <img src="picture/star.png" alt="1 зірка" data-rating="1">
                <img src="picture/star.png" alt="2 зірки" data-rating="2">
                <img src="picture/star.png" alt="3 зірки" data-rating="3">
                <img src="picture/star.png" alt="4 зірки" data-rating="4">
                <img src="picture/star.png" alt="5 зірок" data-rating="5">
            </div>
        </div>
        <h3>Анотація:</h3>
        <p id="bookAnnotation"></p>
        <h3>Щоденник читання</h3>
         <div class="reading-history" id="readingHistorySection">
                <div class="reading-sessions" id="readingSessions">
                    <p class="no-sessions">Інформація про сесії читання відсутня</p>
                </div>
            </div>

        <div class="review-section" id="reviewSection" style="display: none;">
              <h3>Ваш відгук:</h3>
                <div id="userReviewContainer">
                </div>
            <button id="writeReviewBtn" class="btn-blue">Написати відгук</button>
            <div id="reviewForm" style="display: none;">
                <textarea id="reviewText" placeholder="Ваш відгук..."></textarea>
                <button id="submitReviewBtn" class="btn-blue">Надіслати відгук</button>
            </div>
        </div>
        
       
    <section class="reviews">
    <h3>Відгуки</h3>
    <div class="reviews-section" id="reviewsContainer">
        <?php if (empty($reviews)): ?>
            <p class="no-reviews">Відгуки на цю книгу відсутні</p>
        <?php endif; ?>
    </div>
</section>
    
    </div>
    </div>
    </div>
    
    <div class=" footer">
            <?php include 'iteration/footer.php'; ?>
    </div>
    <script src="js/book-info.js"></script>
</body>
</html>