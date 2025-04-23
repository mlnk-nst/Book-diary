function toggleDropdown(category) {
    const dropdown = document.getElementById(`${category}-dropdown`);
    const arrow = document.getElementById(`${category}-arrow`);

    if (dropdown.style.display === "block") {
        dropdown.style.display = "none";
        arrow.classList.remove("rotate");
    } else {
        dropdown.style.display = "block";
        arrow.classList.add("rotate");
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const booksContainer = document.getElementById('book-list');
    const paginationContainer = document.getElementById('pagination');

    let currentPage = 1;

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
                <h4 class="h4"><a href="book_details.php?id=${book.book_id}">${book.book_title}</a></h4>
                <p class="p">${book.author_name}</p>
            </div>
        `).join('');
    }

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
});

