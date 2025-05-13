function fetchBooksByStatus(status) {
    return fetch(`server/analytic/get-mybook.php?status=${status}`)
        .then(response => response.json())
        .then(data => data);
}

function toggleView(containerId, button) {
    const container = document.getElementById(containerId);
    container.classList.toggle('table-view');
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


function renderBooks(books) {
    const continueBooks = books.filter(book => book.status === "Читаю");
    const savedBooks = books.filter(book => book.status === "Збережено");
    const readBooks = books.filter(book => book.status === "Прочитано");

    const sections = [
        { id: "continue-reading", books: continueBooks, emptyId: "continue-empty" },
        { id: "saved-books", books: savedBooks, emptyId: "saved-empty" },
        { id: "read-books", books: readBooks, emptyId: "read-empty" }
    ];

    sections.forEach(section => {
        const container = document.getElementById(section.id);
        const emptyMsg = document.getElementById(section.emptyId);

        if (!container || !emptyMsg) return;

        if (section.books.length === 0) {
            emptyMsg.style.display = "block";
            container.style.display = "none";
        } else {
            emptyMsg.style.display = "none";
            container.style.display = "flex";

            container.innerHTML = section.books.map(book => `
                <div class="my-book">
                    <img src="data:image/jpeg;base64,${book.cover_image}" alt="${book.title}>
                    <div class="book-info">
                        <div class="left-side">
                               <h4 class="h4">
                            <a href="book-info.php?id=${book.book_id}" class="book-link">${book.title}</a>
                            </h4>
                        <p class="author">${book.author}</p>
                        </div>
                        ${book.status === "Прочитано" ? `
                        <div class="right-side">
                            <div class="rating">
                                <img src="picture/star.png" alt="star">
                                <p class="r">${book.rating}</p>
                            </div>
                            <h4 class="data">${book.date}</h4>
                        </div>
                        ` : ''}
                    </div>
                </div>
            `).join('');
            initScrollButtons();
            Show(section.id, section.books);
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    Promise.all([
        fetchBooksByStatus("Читаю"),
        fetchBooksByStatus("Збережено"),
        fetchBooksByStatus("Прочитано")
    ])
        .then(([continueBooks, savedBooks, readBooks]) => {
            const books = [...continueBooks, ...savedBooks, ...readBooks];
            renderBooks(books);
        })
        .catch(error => {
            console.error("Помилка при отриманні книг:", error);
        });
    initScrollButtons();
});


