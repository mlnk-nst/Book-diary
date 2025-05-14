// меню 
function toggleDropdown(category) {
    const dropdown = document.getElementById(`${category}-dropdown`);
    const arrow = document.getElementById(`${category}-arrow`);
    dropdown.style.display = "block";
    arrow.classList.add("rotate");

    dropdown.addEventListener("mouseleave", function () {
        dropdown.style.display = "none";
        arrow.classList.remove("rotate");
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const booksContainer = document.getElementById('book-list');
    const paginationContainer = document.getElementById('pagination');
    const searchButton = document.getElementById('search-button');
    const searchInput = document.getElementById('search-input');
    const infoContainer = document.getElementById('info');
    const resetFilterBtn = document.getElementById('resetFilterBtn');

    let currentPage = 1;
    // вивід книг
    function loadBooks(page) {
        fetch(`server/catalog/get_books.php?page=${page}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.count > 0) {
                    renderBooks(data.data);
                    if (data.total_pages > 1) {
                        renderPagination(data.total_pages, data.current_page);
                    } else {
                        paginationContainer.innerHTML = '';
                    }
                } else {
                    booksContainer.innerHTML = `<div class="txt"><p>Книги не знайдено</p></div>`;
                    paginationContainer.innerHTML = '';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                booksContainer.innerHTML = `<div class="txt"><p>Помилка завантаження даних</p></div>`;

            });
    }

    function renderBooks(books) {
        booksContainer.innerHTML = books.map(book => `
            <div class="book_dynamic">
                    <img class="img-book" src="${book.cover_image || 'picture/приклад_обкладинки.jpg'}" alt="${book.book_title}">
                <h4 class="h4"><a href=" book-info.php?id=${book.book_id}">${book.book_title}</a></h4>
                <p class="p">${book.author_name}</p>
            </div>
        `).join('');
    }
    // пошук
    function Search() {
        const query = searchInput.value.trim();
        if (!query) {
            loadBooks(1);
            infoContainer.innerHTML = '';
            resetFilterBtn.style.display = 'none';
            return;
        }

        fetch(`server/catalog/search-book.php?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                booksContainer.innerHTML = '';
                if (data.success && data.data.length > 0) {
                    renderBooks(data.data);
                    paginationContainer.innerHTML = '';
                    infoContainer.innerHTML = `Пошук по запиту: <strong>«${query}»</strong>`;
                    resetFilterBtn.style.display = 'block';
                } else {
                    paginationContainer.innerHTML = '';
                    infoContainer.innerHTML = `Книги не знайдено`;
                    resetFilterBtn.style.display = 'none';
                }
                searchInput.value = '';
            })
            .catch(error => {
                console.error('Помилка при пошуку:', error);
                infoContainer.innerHTML = `Помилка пошуку`;
                searchInput.value = '';
            });
    }

    searchButton.addEventListener('click', Search);

    searchInput.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            Search();
        }
    });
    searchInput.addEventListener('input', () => {
        if (searchInput.value.trim() === '') {
            loadBooks(1);
            infoContainer.innerHTML = '';
        }
    });
    resetFilterBtn.addEventListener('click', function () {
        loadBooks(1);
        resetFilterBtn.style.display = 'none';
        searchInput.value = '';
        infoContainer.innerHTML = '';
    });


    function loadBooksByGenre(genreId, genreName) {
        const existingMessages = document.querySelectorAll('.message');
        existingMessages.forEach(function (msg) {
            msg.remove();
        });
        booksContainer.innerHTML = '';
        infoContainer.innerHTML = `Вибраний жанр: <strong>${genreName}</strong>`;
        resetFilterBtn.style.display = 'inline-block';
        fetch(`server/catalog/get-book-genre.php?genre_id=${genreId}`)
            .then(response => response.json())
            .then(data => {
                try {
                    if (data.success && data.data.length > 0) {
                        renderBooks(data.data);
                        paginationContainer.innerHTML = '';
                    } else {
                        infoContainer.innerHTML += `<br><span>Немає книг у цьому жанрі.</span>`;
                        paginationContainer.innerHTML = '';
                    }
                } catch (error) {
                    console.error("Помилка при парсингу JSON:", error);
                    infoContainer.innerHTML = `<br><span >Помилка завантаження даних.</span>`;
                }
            })
            .catch(error => {
                console.error('Помилка завантаження книг за жанром:', error);
                infoContainer.innerHTML = `<br><span>Помилка завантаження книг</span>`;
            });
    }
    // пагінація
    function renderPagination(totalPages, currentPage) {
        let paginationHTML = '';

        paginationHTML += `
            <a href="#" class="pagination-arrow" id="prev-page" ${currentPage === 1 ? 'disabled' : ''}>
                <img src="picture/arrow-l.png" alt="Previous">
            </a>
        `;
        for (let i = 1; i <= totalPages; i++) {
            paginationHTML += `
                <a href="#" class="pagination-link" data-page="${i}" ${i === currentPage ? 'class="active"' : ''}>
                    ${i}
                </a>
            `;
        }
        paginationHTML += `
            <a href="#" class="pagination-arrow" id="next-page" ${currentPage === totalPages ? 'disabled' : ''}>
                <img src="picture/arrow-r.png" alt="Next">
            </a>
        `;

        paginationContainer.innerHTML = paginationHTML;

        document.querySelectorAll('.pagination-link').forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                currentPage = parseInt(e.target.getAttribute('data-page'));
                loadBooks(currentPage);
            });
        });

        document.getElementById('prev-page').addEventListener('click', function () {
            if (currentPage > 1) {
                currentPage--;
                loadBooks(currentPage);
            }
        });

        document.getElementById('next-page').addEventListener('click', function () {
            if (currentPage < totalPages) {
                currentPage++;
                loadBooks(currentPage);
            }
        });
    }
    loadBooks(currentPage);
    window.loadBooksByGenre = loadBooksByGenre;
    const menuToggle = document.getElementById('menu-toggle');
    menuToggle.classList.add('visible');
    const menuCategory = document.querySelector('.menu-category');

    menuToggle.addEventListener('click', () => {
        menuCategory.classList.toggle('active');
        if (menuCategory.classList.contains('active')) {
            menuToggle.style.display = 'none';
        } else {
            setTimeout(() => {
                menuToggle.style.display = 'block';
                menuToggle.classList.add('visible');
            }, 500);
        }
    });
    const categoryHeaders = document.querySelectorAll('.category-header');
    categoryHeaders.forEach(categoryHeader => {
        categoryHeader.addEventListener('click', (event) => {
            menuCategory.classList.remove('active');
            setTimeout(() => {
                menuToggle.style.display = 'block';
                menuToggle.classList.add('visible');
            }, 500);
            event.stopPropagation();
        });
    });

    const subcategories = document.querySelectorAll('.subcategory');
    subcategories.forEach(subcategory => {
        subcategory.addEventListener('click', (event) => {
            menuCategory.classList.remove('active');
            setTimeout(() => {
                menuToggle.style.display = 'block';
                menuToggle.classList.add('visible');
            }, 500);
            event.stopPropagation();
        });
    });
    document.addEventListener('click', function (event) {
        if (!menuCategory.contains(event.target) && !menuToggle.contains(event.target)) {
            menuCategory.classList.remove('active');
            setTimeout(() => {
                menuToggle.style.display = 'block';
                menuToggle.classList.add('visible');
            }, 500);
        }
    });
});



