let readingSessionInterval;
let readingSessionSeconds = 0;
let readingSessionStartTime = null;
const READING_SESSION_KEY = 'currentReadingSession';

let userRole = '';

let activeSession = null;
let timerInterval = null;
let startTime = null;

document.addEventListener("DOMContentLoaded", async () => {
    const urlParams = new URLSearchParams(window.location.search);
    const bookId = urlParams.get("id");
    if (!bookId) {
        alert("ID книги не вказано в URL");
        return;
    }

    const saveBookBtn = document.getElementById("saveBookBtn");
    const readBookBtn = document.getElementById("readBookBtn");
    const endReadBtn = document.getElementById("endReadBtn");

    saveBookBtn.style.display = "none";
    readBookBtn.style.display = "none";
    endReadBtn.style.display = "none";

    //перевірка ролі
    fetch('server/login/check_auth.php')
        .then(response => response.json())
        .then(data => {
            const isLoggedIn = data.isLoggedIn;
            userRole = data.userRole || '';
            const adminControls = document.getElementById('adminControls');
            const readingHistorySection = document.getElementById("readingHistorySection");

            if (userRole !== 'admin') {
                if (adminControls) adminControls.style.display = 'none';
                if (saveBookBtn) saveBookBtn.style.display = 'block';
            } else {
                if (saveBookBtn) saveBookBtn.style.display = 'none';
                if (readBookBtn) readBookBtn.style.display = 'none';
                if (endReadBtn) endReadBtn.style.display = 'none';
                if (readingHistorySection) readingHistorySection.style.display = 'none';
                if (adminControls) adminControls.style.display = 'block';
            }

            if (!isLoggedIn && saveBookBtn) {
                saveBookBtn.addEventListener("click", () => {
                    loadModal("login");
                });
            }
        })
        .catch(error => {
            console.error('Помилка перевірки ролі користувача:', error);
        });

    const { status, rating, review } = await checkBookStatus(bookId);

    const statusText = document.getElementById("statusText");
    const statusContainer = document.getElementById("bookStatusContainer");
    const ratingSection = document.getElementById("ratingSection");
    const reviewSection = document.getElementById("reviewSection");
    const readSection = document.getElementById("readingHistorySection");

    if (userRole !== 'admin') {
        switch (status) {
            case "Збережено":
                statusContainer.style.display = "block";
                statusText.textContent = "Збережено";
                ratingSection.style.display = "none";
                reviewSection.style.display = "none";
                readBookBtn.style.display = "inline-block";
                saveBookBtn.style.display = "none";
                readSection.style.display = "none";
                break;
            case "Читаю":
                statusContainer.style.display = "block";
                statusText.textContent = "Читаю";
                ratingSection.style.display = "none";
                reviewSection.style.display = "none";
                endReadBtn.style.display = "inline-block";
                saveBookBtn.style.display = "none";
                readSection.style.display = "block";
                break;
            case "Прочитано":
                statusContainer.style.display = "block";
                statusText.textContent = "Прочитано";
                ratingSection.style.display = "block";
                reviewSection.style.display = "block";
                saveBookBtn.style.display = "none";
                readSection.style.display = "block";
                break;
            default:
                statusContainer.style.display = "none";
                ratingSection.style.display = "none";
                reviewSection.style.display = "none";
                saveBookBtn.style.display = "inline-block";
                readSection.style.display = "none";
                break;
        }
    }

    //вивід детальної іфнормації
    fetch(`server/book-info/get-book-info.php?id=${bookId}`)
        .then(response => {
            if (!response.ok) throw new Error("Помилка при завантаженні даних");
            return response.json();
        })
        .then(data => {
            const book = data.book;
            document.getElementById("bookTitle").textContent = book.book_title || "—";
            document.title = book.book_title || "Книга";
            document.getElementById("bookAuthor").textContent = book.author || "—";
            document.getElementById("bookGenres").textContent = book.genres || "—";
            document.getElementById("bookYear").textContent = book.published_year || "—";
            document.getElementById("bookPages").textContent = book.pages || "—";
            document.getElementById("bookAnnotation").textContent = book.annotation || "—";

            if (book.cover_image) {
                document.getElementById("bookCover").src = book.cover_image;
            }
        })
        .catch(error => {
            console.error("Помилка:", error);
            alert("Не вдалося завантажити дані про книгу");
        });

    //видалення книги адмін
    const deleteBookBtn = document.getElementById("deleteBookBtn");
    if (deleteBookBtn) {
        deleteBookBtn.addEventListener("click", async () => {
            const confirmDelete = confirm("Ви впевнені, що хочете видалити цю книгу?");
            if (confirmDelete) {
                try {
                    const response = await fetch("server/book-info/delete-book.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({ bookId: bookId })
                    });

                    const result = await response.json();

                    if (result.success) {
                        showMessage("Книгу успішно видалено!", true);
                        setTimeout(() => {
                            window.location.href = "catalog.php";
                        }, 1500);
                    } else {
                        showMessage("Помилка: " + result.message, false);
                    }
                } catch (error) {
                    console.error("Помилка видалення:", error);
                    showMessage("Не вдалося видалити книгу. Спробуйте пізніше.", false);
                }
            }
        });
    }

    //редагування книги
    const editBookBtn = document.getElementById("editBookBtn");
    if (editBookBtn) {
        editBookBtn.onclick = () => {
            function getQueryParam(param) {
                const urlParams = new URLSearchParams(window.location.search);
                return urlParams.get(param);
            }

            const bookId = getQueryParam('id');

            if (!bookId) {
                alert('ID книги в URL не знайдено!');
                return;
            }
            window.location.href = `plusBook.php?id=${bookId}`;
        };
    }

    // обробка кнопки зберегти
    saveBookBtn.addEventListener("click", async () => {
        await createStatus(bookId, "Збережено");
        saveBookBtn.style.display = "none";
    });

    // обробка кнопки читати
    readBookBtn.addEventListener("click", async () => {
        await showReadingModal();
    });

    // обробка кнопки завершити читання
    if (endReadBtn) {
        endReadBtn.addEventListener("click", async () => {
            await showEndReadingModal();
        });
    }

    // Check for active session on page load
    checkActiveSession();

    // Check for stored session on page load
    const storedStartTime = localStorage.getItem(`readingSessionStart_${bookId}`);
    if (storedStartTime) {
        startTime = new Date(storedStartTime);
        startTimer(bookId);
    }
});

// Load modal content
async function loadModal() {
    const response = await fetch('iteration/read.html');
    const html = await response.text();
    const parser = new DOMParser();
    const doc = parser.parseFromString(html, 'text/html');
    const modal = doc.getElementById('modal-reading');
    document.body.appendChild(modal);
    return modal;
}

// Show modal
async function showReadingModal() {
    const modal = await loadModal();
    modal.style.display = 'block';

    const form = document.getElementById('reading-form');
    form.onsubmit = async (e) => {
        e.preventDefault();
        const startPage = document.getElementById('reading-input').value;
        await startReadingSession(startPage);
        modal.style.display = 'none';
    };
}

// Close modal
function closeModal() {
    const modal = document.getElementById('modal-reading');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Start reading session
async function startReadingSession(startPage) {
    const bookId = getBookId();

    try {
        // First check current status
        const statusCheck = await checkBookStatus(bookId);
        if (statusCheck.status === "Читаю") {
            showMessage("Ви вже читаєте цю книгу!", false);
            return;
        }

        const response = await fetch('server/book-info/start-reading.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                book_id: bookId,
                start_page: startPage
            })
        });
        const data = await response.json();

        if (data.success) {
            activeSession = {
                session_id: data.sessionId,
                start_time: new Date(data.start_time),
                start_page: startPage,
                book_id: bookId
            };
            startTimer(bookId);
            updateUIForActiveSession();
            
            // Only update status if it's "Збережено"
            if (statusCheck.status === "Збережено") {
                await createStatus(bookId, "Читаю");
            }
            
            showMessage("Сесію читання розпочато!", true);
        } else {
            showMessage("Помилка: " + (data.message || "Не вдалося почати сесію читання"), false);
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage("Помилка при початку сесії читання", false);
    }
}

// Show end reading modal
async function showEndReadingModal() {
    const modal = await loadEndReadingModal();
    modal.style.display = 'block';

    const form = document.getElementById('end-reading-form');
    form.onsubmit = async (e) => {
        e.preventDefault();
        const endPage = document.getElementById('end-page-input').value;
        await endReadingSession(endPage);
        modal.style.display = 'none';
    };
}

// Load end reading modal
async function loadEndReadingModal() {
    const response = await fetch('iteration/end-reading.html');
    const html = await response.text();
    const parser = new DOMParser();
    const doc = parser.parseFromString(html, 'text/html');
    const modal = doc.getElementById('modal-end-reading');
    document.body.appendChild(modal);
    return modal;
}

// End reading session
async function endReadingSession(endPage) {
    if (!activeSession) return;

    try {
        // Get book info to check total pages
        const bookInfo = await getBookInfo(getBookId());
        const totalPages = bookInfo.pages || 0;
        
        // Check if end page is valid
        if (endPage > totalPages) {
            showMessage(`Помилка: Номер сторінки не може бути більше загальної кількості сторінок (${totalPages})`, false);
            return;
        }

        // Check if user is near the end of the book
        if (totalPages - endPage <= 15) {
            const confirmFinish = await showFinishBookConfirmation();
            if (!confirmFinish) {
                return;
            }
        }

        const now = new Date();
        const diff = now - startTime;
        const hours = Math.floor(diff / 3600000);
        const minutes = Math.floor((diff % 3600000) / 60000);
        const seconds = Math.floor((diff % 60000) / 1000);

        const response = await fetch('server/book-info/end-reading.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                session_id: activeSession.session_id,
                book_id: getBookId(),
                end_page: endPage,
                hours: hours,
                minutes: minutes,
                seconds: seconds
            })
        });
        const data = await response.json();

        if (data.success) {
            stopTimer(getBookId());
            activeSession = null;
            updateUIForInactiveSession();
            await createStatus(getBookId(), "Прочитано");
            showMessage("Сесію читання завершено!", true);
        } else {
            showMessage("Помилка: " + (data.message || "Не вдалося завершити сесію читання"), false);
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage("Помилка при завершенні сесії читання", false);
    }
}

// Show finish book confirmation modal
function showFinishBookConfirmation() {
    return new Promise((resolve) => {
        const modal = document.createElement('div');
        modal.className = 'modal';
        modal.innerHTML = `
            <div class="modal-content">
                <h2>Завершити читання?</h2>
                <p>Ви майже дочитали книгу. Бажаєте завершити читання?</p>
                <div class="modal-buttons">
                    <button class="btn btn-primary" id="confirmFinish">Так</button>
                    <button class="btn btn-secondary" id="cancelFinish">Ні</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);

        document.getElementById('confirmFinish').onclick = () => {
            modal.remove();
            resolve(true);
        };

        document.getElementById('cancelFinish').onclick = () => {
            modal.remove();
            resolve(false);
        };
    });
}

// Timer functions
function startTimer(bookId) {
    if (timerInterval) clearInterval(timerInterval);

    if (!startTime) {
        startTime = new Date();
    }
    
    updateTimerDisplay();
    timerInterval = setInterval(updateTimerDisplay, 1000);
    localStorage.setItem(`readingSessionStart_${bookId}`, startTime.toISOString());
}

function stopTimer(bookId) {
    if (timerInterval) {
        clearInterval(timerInterval);
        timerInterval = null;
    }
    localStorage.removeItem(`readingSessionStart_${bookId}`);
    document.getElementById('readingTimer').style.display = 'none';
}

function updateTimerDisplay() {
    if (!startTime) return;
    
    const now = new Date();
    const diff = now - startTime;

    const hours = Math.floor(diff / 3600000);
    const minutes = Math.floor((diff % 3600000) / 60000);
    const seconds = Math.floor((diff % 60000) / 1000);

    const hoursElement = document.getElementById('hours');
    const minutesElement = document.getElementById('minutes');
    const secondsElement = document.getElementById('seconds');
    const timerElement = document.getElementById('readingTimer');

    if (hoursElement) hoursElement.textContent = hours.toString().padStart(2, '0');
    if (minutesElement) minutesElement.textContent = minutes.toString().padStart(2, '0');
    if (secondsElement) secondsElement.textContent = seconds.toString().padStart(2, '0');
    if (timerElement) timerElement.style.display = 'block';
}

// UI update functions
function updateUIForActiveSession() {
    document.getElementById('readBookBtn').style.display = 'none';
    document.getElementById('endReadBtn').style.display = 'block';
    document.getElementById('readingTimer').style.display = 'block';
}

function updateUIForInactiveSession() {
    document.getElementById('readBookBtn').style.display = 'block';
    document.getElementById('endReadBtn').style.display = 'none';
    document.getElementById('readingTimer').style.display = 'none';
}

// Check for active session on page load
async function checkActiveSession() {
    const bookId = getBookId();

    try {
        const response = await fetch(`server/book-info/check-reading-session.php?book_id=${bookId}`);
        const data = await response.json();

        if (data.isActive) {
            activeSession = {
                session_id: data.sessionId,
                start_time: new Date(data.start_time),
                start_page: data.startPage
            };
            startTime = new Date(data.start_time);
            startTimer(bookId);
            updateUIForActiveSession();
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// Helper function to get book ID
function getBookId() {
    return document.querySelector('[data-book-id]').dataset.bookId;
}

// Додаткова функція для отримання інформації про книгу
async function getBookInfo(bookId) {
    try {
        const response = await fetch(`server/book-info/get-book-info.php?id=${bookId}`);
        const data = await response.json();
        return data.book;
    } catch (error) {
        console.error("Помилка отримання інформації про книгу:", error);
        return { pages: 0 };
    }
}

// перегляд статусу книги 
async function checkBookStatus(bookId) {
    try {
        const response = await fetch(`server/book-info/book-status.php?book_id=${bookId}`);

        if (!response.ok) {
            throw new Error('Помилка запиту до сервера');
        }

        const data = await response.json();

        return {
            status: data.status || null,
            rating: data.rating || null,
            review: data.review || null
        };
    } catch (error) {
        console.error('Помилка при перевірці статусу книги:', error);
        return {
            status: null,
            rating: null,
            review: null
        };
    }
}

// оновлення статусу 
async function updateBookStatus(bookId, newStatus) {
    const response = await fetch("server/book-info/update-status.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify({
            book_id: bookId,
            status: newStatus
        })
    });
    return await response.json();
}

// створення статусу в бд , обробка для кнопки Зберегти
async function createStatus(bookId, status) {
    try {
        const saveBookBtn = document.getElementById("saveBookBtn");
        if (saveBookBtn) {
            saveBookBtn.style.display = "none";
        }

        // First check if the book exists in the diary
        const statusCheck = await checkBookStatus(bookId);
        const endpoint = statusCheck.status ? "server/book-info/update-status.php" : "server/book-info/create-status.php";

        const response = await fetch(endpoint, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                book_id: bookId,
                status: status
            })
        });

        const result = await response.json();

        if (result.success) {
            showMessage("Статус книги успішно оновлено!", true);
            // Update UI elements instead of reloading
            const statusText = document.getElementById("statusText");
            const statusContainer = document.getElementById("bookStatusContainer");
            const ratingSection = document.getElementById("ratingSection");
            const reviewSection = document.getElementById("reviewSection");
            const readSection = document.getElementById("readingHistorySection");

            if (statusText) statusText.textContent = status;
            if (statusContainer) statusContainer.style.display = "block";

            switch (status) {
                case "Збережено":
                    if (ratingSection) ratingSection.style.display = "none";
                    if (reviewSection) reviewSection.style.display = "none";
                    if (readSection) readSection.style.display = "none";
                    break;
                case "Читаю":
                    if (ratingSection) ratingSection.style.display = "none";
                    if (reviewSection) reviewSection.style.display = "none";
                    if (readSection) readSection.style.display = "block";
                    break;
                case "Прочитано":
                    if (ratingSection) ratingSection.style.display = "block";
                    if (reviewSection) reviewSection.style.display = "block";
                    if (readSection) readSection.style.display = "block";
                    break;
            }
        } else {
            showMessage("Помилка: " + result.message, false);
        }
    } catch (error) {
        console.error("Помилка:", error);
        showMessage("Щось пішло не так при оновленні статусу.", false);
    }
}

// показ повідомлення
function showMessage(text, isSuccess = true) {
    const messageBox = document.getElementById("messageBox");
    messageBox.textContent = text;
    messageBox.className = `message ${isSuccess ? "success-message" : "error-message"}`;
    messageBox.style.display = "block";
    messageBox.style.opacity = "1";

    setTimeout(() => {
        messageBox.style.opacity = "0";
        setTimeout(() => {
            messageBox.style.display = "none";
        }, 500);
    }, 3000);
}

