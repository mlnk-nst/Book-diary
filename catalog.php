<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Каталог</title>
    <link rel="website icon" type="png" href="picture/logo-s.png">
    <link rel="stylesheet" href="style/style.css">
    <link rel="stylesheet" href="style/style-cataloge.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <?php include 'iteration/header.php'; ?>
        <div class="content">
            <?php include 'server/catalog/get_genres.php'; ?>
            <section class="menu-category">
                <h3>Категорії книг</h3>
                <?php foreach ($categories as $category): ?>
                    <div class="category">
                        <div class="category-header" onclick="toggleDropdown('<?php echo $category['genre_id']; ?>')">
                            <h4><?php echo $category['name']; ?></h4>
                            <img src="picture/arrow.png" alt="стрілка вниз" class="arrow-icon" id="<?php echo $category['genre_id']; ?>-arrow">
                        </div>

                        <?php
                        $subgenres = getSubgenres($category['genre_id']);
                        if ($subgenres): ?>
                            <div class="subcategories" id="<?php echo $category['genre_id']; ?>-dropdown">
                                <?php foreach ($subgenres as $subgenre): ?>
                                    <p class="subcategory"><?php echo $subgenre['name']; ?></p>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </section>

            <div class="search-container">
                <input type="text" id="search-input" placeholder="Пошук книг..." />
                <button id="search-button">
                    <img src="picture/search.png" alt="Пошук" class="search-icon" />
                </button>
            </div>
            <div id="info" class="txt" > </div>
            <section class="book-list" id="book-list"></section>
            <section class="pagination" id="pagination"></section>
        </div>  
        <div class="footer">
            <?php include 'iteration/footer.php'; ?>
        </div>  
        
    <script src="js/cataloge.js"></script>
</body>
</html>