document.addEventListener('DOMContentLoaded', function () {
    loadBooks('new', 'new-books', 'new-empty');
    loadBooks('popular', 'popular-books', 'popular-empty');
    initScrollButtons();
});

function loadBooks(type, containerId, emptyId) {
    fetch(`server/main/get_books.php?type=${type}`)
        .then(response => response.json())
        .then(books => {
            console.log(type, books);
            renderBookSection(books, containerId, emptyId);
            initScrollButtons();
        })
        .catch(error => {
            console.error(`Error loading ${type} books:`, error);
        });
}

function renderBookSection(books, containerId, emptyId) {
    const container = document.getElementById(containerId);
    if (!container) return;
    const bookRow = container.querySelector('.book-row');
    if (!bookRow) return;

    if (books.length === 0) {
        container.style.display = "none";
        const type = containerId.split('-')[0];
        const button = document.getElementById(`b-${type}`);
        if (button) button.style.display = "none";
        return;
    }

    container.style.display = "flex";

    bookRow.innerHTML = books.map(book => `
        <div class="my-book">
            <img src="${book.cover}" alt="${book.title}">
            <div class="book-info">
                <div class="left-side">
                    <h4 class="h4">
                        <a href="book-info.php?id=${book.book_id}" class="book-link">${book.title}</a>
                    </h4>
                    <p class="author">${book.author}</p>
                </div>
            </div>
        </div>
    `).join('');
    initScrollButtons();
    Show(containerId, books);
}

function initScrollButtons() {
    document.querySelectorAll('.book-row-container').forEach(container => {
        const scrollElement = container.querySelector('.book-row');
        const leftBtn = container.querySelector('.scroll-btn.left');
        const rightBtn = container.querySelector('.scroll-btn.right');

        if (!scrollElement || !leftBtn || !rightBtn) return;

        const booksCount = scrollElement.querySelectorAll('.my-book').length;
        const shouldShowButtons = booksCount > 4 && !scrollElement.classList.contains('table-view');


        leftBtn.style.display = shouldShowButtons ? 'flex' : 'none';
        rightBtn.style.display = shouldShowButtons ? 'flex' : 'none';

        leftBtn.onclick = () => scrollElement.scrollBy({ left: -300, behavior: 'smooth' });
        rightBtn.onclick = () => scrollElement.scrollBy({ left: 300, behavior: 'smooth' });
    });
}
function toggleView(containerId, button) {
    const container = document.getElementById(containerId);
    const bookRow = container.querySelector('.book-row');

    if (bookRow) {
        bookRow.classList.toggle('table-view');
        console.log('table-view class toggled:', bookRow.classList.contains('table-view'));
    }

    if (button.textContent === "Показати всі") {
        button.textContent = "Сховати всі";
    } else {
        button.textContent = "Показати всі";
    }

    initScrollButtons();
}
function Show(containerId, bookList) {
    const container = document.getElementById(containerId);
    const button = document.querySelector(`.h-2 #b-${containerId.split('-')[0]}`);

    if (bookList.length === 0) {
        if (button) button.style.display = "none";
    } else {
        if (button) button.style.display = bookList.length > 4 ? "block" : "none";
    }

}

