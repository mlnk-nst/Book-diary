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
                    window.loadModal("login");
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
        if (saveBookBtn) saveBookBtn.style.display = "none";
        if (readBookBtn) readBookBtn.style.display = "none";
        if (endReadBtn) endReadBtn.style.display = "none";
        if (statusContainer) statusContainer.style.display = "none";
        if (ratingSection) ratingSection.style.display = "none";
        if (reviewSection) reviewSection.style.display = "none";
        if (readSection) readSection.style.display = "none";

        if (status) {
            switch (status) {
                case "Збережено":
                    if (statusContainer) statusContainer.style.display = "block";
                    if (statusText) statusText.textContent = "Збережено";
                    if (readBookBtn) readBookBtn.style.display = "inline-block";
                    break;
                case "Читаю":
                    if (statusContainer) statusContainer.style.display = "block";
                    if (statusText) statusText.textContent = "Читаю";
                    if (endReadBtn) endReadBtn.style.display = "inline-block";
                    if (readBookBtn) readBookBtn.style.display = "inline-block";
                    if (readSection) readSection.style.display = "block";
                    break;
                case "Прочитано":
                    if (statusContainer) statusContainer.style.display = "block";
                    if (statusText) statusText.textContent = "Прочитано";
                    if (ratingSection) ratingSection.style.display = "block";
                    if (reviewSection) reviewSection.style.display = "block";
                    if (readSection) readSection.style.display = "block";
                    break;
            }
        } else {
            if (saveBookBtn) saveBookBtn.style.display = "inline-block";
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
    checkActiveSession();

    const storedStartTime = localStorage.getItem(`readingSessionStart_${bookId}`);
    if (storedStartTime) {
        startTime = new Date(storedStartTime);
        startTimer(bookId);
    }

    if (status === "Читаю" || status === "Прочитано") {
        const sessions = await getReadingHistory(bookId);
        displayReadingHistory(sessions);
    }
});

// Модальні вікна
async function loadReadingModal() {
    const response = await fetch('iteration/read.html');
    const html = await response.text();
    const parser = new DOMParser();
    const doc = parser.parseFromString(html, 'text/html');
    const modal = doc.getElementById('modal-reading');
    document.body.appendChild(modal);
    return modal;
}

async function showReadingModal() {
    const modal = await loadReadingModal();
    modal.style.display = 'flex';
    setTimeout(() => {
        modal.classList.add('active');
    }, 10);

    const form = document.getElementById('reading-form');
    form.onsubmit = async (e) => {
        e.preventDefault();
        const startPage = document.getElementById('reading-input').value;
        await startReadingSession(startPage);
        closeReadingModal();
    };
}

function closeReadingModal() {
    const modal = document.getElementById('modal-reading');
    if (modal) {
        modal.classList.remove('active');
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }
}

// Початок сеансу читання
async function startReadingSession(startPage) {
    const bookId = getBookId();
    console.log('Starting reading session for book:', bookId, 'page:', startPage);

    try {
        const statusCheck = await checkBookStatus(bookId);
        console.log('Current book status:', statusCheck);

        // Перевіряємо наявність активної сесії
        const sessionCheck = await fetch(`server/book-info/check-reading-session.php?book_id=${bookId}`);
        const sessionData = await sessionCheck.json();

        if (sessionData.isActive) {
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
        console.log('Start reading response:', data);

        if (data.success) {
            activeSession = {
                session_id: data.sessionId,
                start_time: new Date(data.start_time),
                start_page: startPage,
                book_id: bookId
            };
            console.log('Created active session:', activeSession);
            startTime = new Date(data.start_time);
            startTimer(bookId);
            updateUIForActiveSession();

            if (statusCheck.status === "Збережено") {
                await createStatus(bookId, "Читаю");
            }
            showMessage("Сесію читання розпочато!", true);
            await checkActiveSession();
        } else {
            showMessage("Помилка: " + (data.message || "Не вдалося почати сесію читання"), false);
        }
    } catch (error) {
        console.error('Error starting reading session:', error);
        showMessage("Помилка при початку сесії читання", false);
    }
}

// модальне вікно завершення читання 
async function showEndReadingModal() {
    const modal = await loadEndReadingModal();
    modal.style.display = 'flex';
    setTimeout(() => {
        modal.classList.add('active');
    }, 10);

    updateModalTimer();

    const form = document.getElementById('end-reading-form');
    form.onsubmit = async (e) => {
        e.preventDefault();
        const endPage = document.getElementById('end-page-input').value;
        await endReadingSession(endPage);
        closeEndReadingModal();
    };
}

async function loadEndReadingModal() {
    const response = await fetch('iteration/end-reading.html');
    const html = await response.text();
    const parser = new DOMParser();
    const doc = parser.parseFromString(html, 'text/html');
    const modal = doc.getElementById('modal-end-reading');
    document.body.appendChild(modal);
    return modal;
}

// видалення модальних
function closeEndReadingModal() {
    const modal = document.getElementById('modal-end-reading');
    if (modal) {
        modal.classList.remove('active');
        setTimeout(() => {
            modal.style.display = 'none';
            modal.remove();
        }, 300);
    }
}


function updateModalTimer() {
    if (!startTime) return;

    const now = new Date();
    const diff = now - startTime;

    const hours = Math.floor(diff / 3600000);
    const minutes = Math.floor((diff % 3600000) / 60000);
    const seconds = Math.floor((diff % 60000) / 1000);

    const modalHours = document.getElementById('modal-hours');
    const modalMinutes = document.getElementById('modal-minutes');
    const modalSeconds = document.getElementById('modal-seconds');

    if (modalHours) modalHours.textContent = hours.toString().padStart(2, '0');
    if (modalMinutes) modalMinutes.textContent = minutes.toString().padStart(2, '0');
    if (modalSeconds) modalSeconds.textContent = seconds.toString().padStart(2, '0');
}

// Закінчення сеансу читання
async function endReadingSession(endPage) {
    if (!activeSession) {
        showMessage("Немає активної сесії читання", false);
        return;
    }

    try {
        const bookInfo = await getBookInfo(getBookId());
        const totalPages = parseInt(bookInfo.pages) || 0;
        const endPageNum = parseInt(endPage);

        if (endPageNum > totalPages) {
            showMessage(`Помилка: Номер сторінки не може бути більше загальної кількості сторінок (${totalPages})`, false);
            return;
        }

        const isNearEnd = (totalPages - endPageNum) <= 15;

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
                end_page: endPageNum,
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

            // Закриваємо модальне вікно введення кінцевої сторінки
            const endReadingModal = document.getElementById('modal-end-reading');
            if (endReadingModal) {
                endReadingModal.classList.remove('active');
                setTimeout(() => {
                    endReadingModal.style.display = 'none';
                    endReadingModal.remove();
                }, 300);
            }

            if (isNearEnd) {
                setTimeout(async () => {
                    const confirmFinish = await showFinishBookConfirmation();
                    if (confirmFinish) {
                        await createStatus(getBookId(), "Прочитано");
                        // Оновлюємо інтерфейс після зміни статусу
                        location.reload(); // або інший спосіб оновлення
                    }
                }, 500);
            }

            showMessage("Сесію читання завершено!", true);
        } else {
            showMessage("Помилка: " + (data.message || "Не вдалося завершити сесію читання"), false);
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage("Помилка при завершенні сесії читання", false);
    }
}

async function showFinishBookConfirmation() {
    return new Promise((resolve) => {
        // Завантажуємо HTML модального вікна
        fetch('iteration/finish-book.html')
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const modal = doc.getElementById('modal-finish-book');
                document.body.appendChild(modal);

                // Показуємо модальне вікно
                modal.style.display = 'flex';
                setTimeout(() => {
                    modal.classList.add('active');
                }, 10);

                // Додаємо обробники подій для кнопок
                document.getElementById('confirmFinish').onclick = () => {
                    modal.classList.remove('active');
                    setTimeout(() => {
                        modal.remove();
                        resolve(true);
                    }, 300);
                };

                document.getElementById('cancelFinish').onclick = () => {
                    modal.classList.remove('active');
                    setTimeout(() => {
                        modal.remove();
                        resolve(false);
                    }, 300);
                };

                // Додаємо обробник для закриття по кліку поза модальним вікном
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) {
                        modal.classList.remove('active');
                        setTimeout(() => {
                            modal.remove();
                            resolve(false);
                        }, 300);
                    }
                });
            });
    });
}

// функції для таймера
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

function updateUIForActiveSession() {
    const readBookBtn = document.getElementById('readBookBtn');
    const endReadBtn = document.getElementById('endReadBtn');
    const readingTimer = document.getElementById('readingTimer');

    if (readBookBtn) readBookBtn.style.display = 'none';
    if (endReadBtn) endReadBtn.style.display = 'inline-block';
    if (readingTimer) readingTimer.style.display = 'block';
}

function updateUIForInactiveSession() {
    const readBookBtn = document.getElementById('readBookBtn');
    const endReadBtn = document.getElementById('endReadBtn');
    const readingTimer = document.getElementById('readingTimer');

    if (readBookBtn) readBookBtn.style.display = 'inline-block';
    if (endReadBtn) endReadBtn.style.display = 'none';
    if (readingTimer) readingTimer.style.display = 'none';
}

// Перевірка активного сеансу під час завантаження сторінки
async function checkActiveSession() {
    const bookId = getBookId();
    console.log('Checking active session for book:', bookId);

    try {
        const statusCheck = await checkBookStatus(bookId);
        const bookStatus = statusCheck.status;

        const response = await fetch(`server/book-info/check-reading-session.php?book_id=${bookId}`);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();
        console.log('Active session check response:', data);
        const readBookBtn = document.getElementById('readBookBtn');
        const endReadBtn = document.getElementById('endReadBtn');
        const readingTimer = document.getElementById('readingTimer');

        if (data.isActive) {
            console.log('Active session found:', data);
            activeSession = {
                session_id: data.sessionId,
                start_time: new Date(data.start_time),
                start_page: data.startPage
            };
            startTime = new Date(data.start_time);
            startTimer(bookId);

            if (readBookBtn) readBookBtn.style.display = 'none';
            if (endReadBtn) endReadBtn.style.display = 'inline-block';
            if (readingTimer) readingTimer.style.display = 'block';
        } else {
            console.log('No active session found');
            activeSession = null;
            if (timerInterval) {
                clearInterval(timerInterval);
                timerInterval = null;
            }
            if (readBookBtn && (bookStatus === "Збережено" || bookStatus === "Читаю")) {
                readBookBtn.style.display = 'inline-block';
            }
            if (endReadBtn) endReadBtn.style.display = 'none';
            if (readingTimer) readingTimer.style.display = 'none';
        }
    } catch (error) {
        console.error('Error checking active session:', error);
        const readBookBtn = document.getElementById('readBookBtn');
        const endReadBtn = document.getElementById('endReadBtn');
        const readingTimer = document.getElementById('readingTimer');

        if (readBookBtn) readBookBtn.style.display = 'none';
        if (endReadBtn) endReadBtn.style.display = 'none';
        if (readingTimer) readingTimer.style.display = 'none';
    }
}

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
        const readBookBtn = document.getElementById("readBookBtn");
        const statusText = document.getElementById("statusText");
        const statusContainer = document.getElementById("bookStatusContainer");
        const ratingSection = document.getElementById("ratingSection");
        const reviewSection = document.getElementById("reviewSection");
        const readSection = document.getElementById("readingHistorySection");

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
            // Нараховуємо бали в залежності від статусу
            if (status === "Читаю") {
                await fetch('server/xp/status-read.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ bookId: bookId })
                });
            } else if (status === "Прочитано") {
                await fetch('server/xp/status-finished.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ bookId: bookId })
                });
            }

            showMessage("Статус книги успішно оновлено!", true);

            // Оновлюємо UI елементи
            if (statusText) statusText.textContent = status;
            if (statusContainer) statusContainer.style.display = "block";

            switch (status) {
                case "Збережено":
                    if (saveBookBtn) saveBookBtn.style.display = "none";
                    if (readBookBtn) readBookBtn.style.display = "inline-block";
                    if (ratingSection) ratingSection.style.display = "none";
                    if (reviewSection) reviewSection.style.display = "none";
                    if (readSection) readSection.style.display = "none";
                    break;
                case "Читаю":
                    if (saveBookBtn) saveBookBtn.style.display = "none";
                    if (readBookBtn) readBookBtn.style.display = "inline-block";
                    if (ratingSection) ratingSection.style.display = "none";
                    if (reviewSection) reviewSection.style.display = "none";
                    if (readSection) readSection.style.display = "block";
                    if (endReadBtn) endReadBtn.style.display = "none";
                    break;
                case "Прочитано":
                    if (saveBookBtn) saveBookBtn.style.display = "none";
                    if (readBookBtn) readBookBtn.style.display = "none";
                    if (ratingSection) ratingSection.style.display = "block";
                    if (reviewSection) reviewSection.style.display = "block";
                    if (readSection) readSection.style.display = "block";
                    if (endReadBtn) endReadBtn.style.display = "none";
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

// Функція для отримання історії читання
async function getReadingHistory(bookId) {
    try {
        const response = await fetch(`server/book-info/get-reading-history.php?book_id=${bookId}`);
        if (!response.ok) {
            throw new Error('Помилка запиту до сервера');
        }
        const data = await response.json();
        return data.sessions || [];
    } catch (error) {
        console.error('Помилка при отриманні історії читання:', error);
        return [];
    }
}

function formatDuration(timeString) {
    const [hoursStr, minutesStr, secondsStr] = timeString.split(':');
    const hours = parseInt(hoursStr, 10);
    const minutes = parseInt(minutesStr, 10);

    let parts = [];
    if (hours > 0) parts.push(`${hours} година${hours === 1 ? '' : (hours < 5 ? 'и' : '')}`);
    if (minutes > 0) parts.push(`${minutes} хвилин${minutes === 1 ? 'а' : (minutes < 5 ? 'и' : '')}`);

    return parts.length ? parts.join(' ') : 'менше хвилини';
}

// Функція для відображення історії читання
function displayReadingHistory(sessions) {
    const sessionsContainer = document.getElementById('readingSessions');
    if (!sessionsContainer) return;

    if (!sessions || sessions.length === 0) {
        sessionsContainer.innerHTML = '<p class="no-sessions">Інформація про сесії читання відсутня</p>';
        return;
    }

    const sessionsHTML = sessions.map(session => {
        const startDate = new Date(session.start_time);
        const endDate = session.end_time ? new Date(session.end_time) : null;
        const duration = session.duration || 'Триває';

        return `
            <div class="reading-sessions">
            <div class="reading-session">
            <img src="picture/ar-history.png" alt="стрілка" class="session-image">
                <div class="session-info">
                 <div class="left-info">
                    <p class="session-date">${startDate.toLocaleDateString()}</p>
                    <p class="session-duration"> ${formatDuration(session.duration)}</p>
                    </div>
                    <div class="right-info">
                    
                     <p class="session-pages"> ${session.pages_read != null && session.pages_read !== 0 ? session.pages_read + ' сторінок' : 'Читається'}</p>
                    <p class="session-pages">${session.start_page} - ${session.end_page || '...'}</p>
                     </div>
                     </div>
                </div>
            </div>
        `;
    }).join('');

    sessionsContainer.innerHTML = sessionsHTML;
}

