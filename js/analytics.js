let currentDate = new Date();

const mockReadingData = {
    '2025-03-01': 45,
    '2025-03-05': 60,
    '2025-03-10': 30,
    '2025-03-15': 10,
    '2025-03-28': 1,

};
const mockBooks = [
    { title: "Назва книги 1", author: "Автор 1", status: "Прочитано", rating: "4/5", date: "2025-03-01" },
    { title: "Назва книги 2", author: "Автор 2", status: "Прочитано", rating: "5/5", date: "2025-03-05" },
    { title: "Назва книги 3", author: "Автор 3", status: "Прочитано", rating: "3/5", date: "2025-03-10" },
    { title: "Назва книги 4", author: "Автор 4", status: "Прочитано", rating: "4/5", date: "2025-02-15" },
];

function updateCalendar() {
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();
    generateCalendar(year, month);

    const monthNames = ["Січень", "Лютий", "Березень", "Квітень", "Травень", "Червень",
        "Липень", "Серпень", "Вересень", "Жовтень", "Листопад", "Грудень"];
    document.getElementById('current-month').textContent = `${monthNames[month]} ${year}`;

    renderBooksForCurrentMonth(year, month + 1);
}

function generateCalendar(year, month) {
    const daysContainer = document.getElementById('days');
    daysContainer.innerHTML = '';

    const date = new Date(year, month, 1);
    const daysInMonth = new Date(year, month + 1, 0).getDate();

    for (let day = 1; day <= daysInMonth; day++) {
        const dayElement = document.createElement('div');
        dayElement.textContent = day;

        const currentDateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const minutesRead = mockReadingData[currentDateStr];

        if (minutesRead !== undefined) {
            dayElement.style.backgroundColor = getColorForMinutes(minutesRead);
        }

        daysContainer.appendChild(dayElement);
    }
}

// Зміна кольору комірок
function getColorForMinutes(minutes) {
    if (minutes >= 60) return '#AFE6FF';
    if (minutes >= 30) return '#C0F8B6';
    if (minutes >= 10) return '#FFEBAF';
    if (minutes > 0) return '#FFC1AF';
    return 'white';
}

document.getElementById('prev-month').addEventListener('click', () => {
    currentDate.setMonth(currentDate.getMonth() - 1);
    updateCalendar();
});

document.getElementById('next-month').addEventListener('click', () => {
    currentDate.setMonth(currentDate.getMonth() + 1);
    updateCalendar();
});

function renderBooksForCurrentMonth(year, month) {
    const readBooksContainer = document.getElementById('read-books-container');
    const emptyMessage = document.getElementById('read-books-empty');
    readBooksContainer.innerHTML = '';

    // Фільтрація книг за статусом
    const filteredBooks = mockBooks.filter(book => {
        const bookDate = new Date(book.date);
        return book.status === "Прочитано" &&
            bookDate.getFullYear() === year &&
            bookDate.getMonth() + 1 === month;
    });

    // Якщо книг немає
    if (filteredBooks.length === 0) {
        emptyMessage.style.display = 'block';
        readBooksContainer.style.display = 'none';
    } else {
        emptyMessage.style.display = 'none';
        readBooksContainer.style.display = 'flex';
    }

    // Додавання книг до контейнера
    filteredBooks.forEach(book => {
        const bookElement = document.createElement('div');
        bookElement.className = 'my-book';
        bookElement.innerHTML = `
            <img src="picture/приклад_обкладинки.jpg" alt="book">
            <div class="book-info">
                <div class="left-side">
                    <h4>${book.title}</h4>
                    <p class="author">${book.author}</p>
                </div>
                <div class="right-side">
                    <div class="rating">
                        <img src="picture/star.png" alt="star">
                        <p class="r">${book.rating}</p>
                    </div>
                    <h4 class="data">${book.date}</h4>
                </div>
            </div>
        `;
        readBooksContainer.appendChild(bookElement);
    });
}

updateCalendar();