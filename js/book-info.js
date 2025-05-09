
document.addEventListener("DOMContentLoaded", async () => {
    const urlParams = new URLSearchParams(window.location.search);
    const bookId = urlParams.get("id");


    if (!bookId) {
        alert("ID книги не вказано в URL");
        return;
    }
    const { status, rating, review } = await checkBookStatus(bookId);

    const statusText = document.getElementById("statusText");
    const statusContainer = document.getElementById("bookStatusContainer");
    const ratingSection = document.getElementById("ratingSection");
    const reviewSection = document.getElementById("reviewSection");
    const actionBtn = document.getElementById("saveBookBtn");

    switch (status) {
        case "Збережено":
            statusContainer.style.display = "block";
            statusText.textContent = "Збережено";
            ratingSection.style.display = "none";
            reviewSection.style.display = "none";
            actionBtn.textContent = "Читати";
            actionBtn.dataset.status = "Збережено";
            break;
        case "Читаю":
            statusContainer.style.display = "block";
            statusText.textContent = "Читаю";
            ratingSection.style.display = "none";
            reviewSection.style.display = "none";
            actionBtn.textContent = "Читати";
            break;
        case "Прочитано":
            statusContainer.style.display = "block";
            statusText.textContent = "Прочитано";
            ratingSection.style.display = "block";
            reviewSection.style.display = "block";
            break;
        default:
            statusText.textContent = " ";
            statusContainer.style.display = "none";
            ratingSection.style.display = "none";
            reviewSection.style.display = "none";
            break;
    }

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

    actionBtn.addEventListener("click", async () => {
        const currentStatus = actionBtn.dataset.status || "";
        const readingMode = actionBtn.dataset.readingMode || "idle";

        switch (currentStatus) {
            case "": // обробка кнока Зберегти
                await createStatus(bookId, "Збережено");
                break;

            case "Збережено": // обробка кнопка Читати
                await updateBookStatus(bookId, "Читаю")
                openReadingModal("start");
                break;

            case "Читаю": // Кнопка "Завершити"
                showFinishModal();
                break;
        }
    });
});

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
        const response = await fetch("server/book-info/create-status.php", {
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
            setTimeout(() => location.reload(), 1500);
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
// модальне вікно 
function closeModal() {
    const modal = document.getElementById("modal-reading");
    if (modal) modal.style.display = "none";
}

function openReadingModal(mode = "start") {
    const modal = document.getElementById("modal-reading");
    const title = document.getElementById("modal-title");
    const label = document.getElementById("modal-label");

    if (!modal || !title || !label) return;

    if (mode === "start") {
        title.textContent = "Початок читання";
        label.textContent = "З якої сторінки ви починаєте читати?";
    } else if (mode === "end") {
        title.textContent = "Завершення читання";
        label.textContent = "На якій сторінці ви закінчили читати?";
    }

    modal.style.display = "block";
}
