document.getElementById('image').addEventListener('change', function () {
    const fileName = this.files.length > 0 ? this.files[0].name : 'Файл не вибрано';
    document.querySelector('.file-name').textContent = fileName;
});


// вікно з помилками
document.addEventListener('DOMContentLoaded', function () {
    var message = document.querySelector('.message');
    if (message) {
        message.style.display = 'block';
        setTimeout(function () {
            message.classList.add('fade-out');
        }, 3000);
        setTimeout(function () {
            message.style.display = 'none';
        }, 3500);
    }
});


document.addEventListener('DOMContentLoaded', function () {
    new TomSelect('#author', {
        plugins: ['remove_button'], // Додає кнопки для видалення вибраних елементів
        persist: false, // Не зберігати вибране між сеансами
        create: true,   // Дозволити створювати нові теги
        maxItems: null, // Не обмежувати кількість вибраних елементів
        valueField: 'id', // Поле з значенням (для форми)
        labelField: 'name', // Поле, яке відображається
        searchField: 'name', // Поле для пошуку
        load: function (query, callback) {
            // AJAX-запит для пошуку авторів
            fetch(`../server/add-book/get-author?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(json => {
                    callback(json.data); // Масив авторів у форматі [{id: 1, name: "Шевченко"}, ...]
                })
                .catch(() => callback([]));
        },
        onCreate: function (item, callback) {
            // Відправка нового автора на сервер
            fetch('/api/authors', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ name: item })
            })
                .then(response => response.json())
                .then(data => {
                    callback({ id: data.id, name: data.name }); // Повертаємо новий елемент з ID з БД
                })
                .catch(() => callback(null));
        }
    });
});