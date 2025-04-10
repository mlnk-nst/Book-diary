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

    fetch('server/catalog/get_books.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.count > 0) {
                renderBooks(data.data);
            } else {
                booksContainer.innerHTML = `
                    <div class="no-books">
                        <p>Книги не знайдено</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            booksContainer.innerHTML = `
                <div class="error">
                    <p>Помилка завантаження даних</p>
                </div>
            `;
        });

    function renderBooks(books) {
        booksContainer.innerHTML = books.map(book => `
            <div class="book-card" data-id="${book.book_id}">
                <div class="book-cover">
                    ${book.cover_image ?
                `<img src="${book.cover_image}" alt="${book.book_title}">` :
                '<div class="no-cover">Обкладинка відсутня</div>'}
                </div>
                <div class="book-info">
                    <h3>${book.book_title}</h3>
                    <p class="author">${book.author_name}</p>
                    <div class="meta">
                        <span>${book.published_year}</span>
                        <span>${book.pages} стор.</span>
                    </div>
                    ${book.genres.length > 0 ? `
                        <div class="genres">
                            ${book.genres.map(genre => `<span class="genre-tag">${genre}</span>`).join('')}
                        </div>
                    ` : ''}
                    <div class="annotation">${book.annotation || 'Опис відсутній'}</div>
                </div>
            </div>
        `).join('');
    }
});