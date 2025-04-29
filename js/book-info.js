document.addEventListener("DOMContentLoaded", () => {
    const urlParams = new URLSearchParams(window.location.search);
    const bookId = urlParams.get("id");

    if (!bookId) {
        alert("ID книги не вказано в URL");
        return;
    }
    fetch(`server/book-info/get-book-info.php?id=${bookId}`)

        .then(response => {
            if (!response.ok) throw new Error("Помилка при завантаженні даних");
            return response.json();
        })
        .then(data => {
            console.log("Отримані дані:", data);

            const book = data.book;
            document.getElementById("bookTitle").textContent = book.book_title || "—";
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
});
