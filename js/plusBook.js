document.getElementById('image').addEventListener('change', function () {
    const fileName = this.files.length > 0 ? this.files[0].name : 'Файл не вибрано';
    document.querySelector('.file-name').textContent = fileName;
});

document.addEventListener('DOMContentLoaded', function () {
    const urlParams = new URLSearchParams(window.location.search);
    const bookId = urlParams.get('id');

    const form = document.getElementById('bookForm');
    const submitBtn = document.getElementById('submitBtn');
    const bookIdField = document.getElementById('book_id');

    if (bookId) {
        form.action = 'server/add-book/edit-book.php';
        submitBtn.textContent = 'Зберегти зміни';
        bookIdField.value = bookId;
    } else {
        form.action = 'server/add-book/adding_books.php';
        submitBtn.textContent = 'Додати книгу';
        bookIdField.value = '';
    }
});

document.getElementById('genre').addEventListener('change', function () {
    var genreId = this.value;
    var subgenreSelect = document.getElementById('subgenre');
    var newSubgenreContainer = document.getElementById('new_subgenre_container');

    subgenreSelect.innerHTML = '<option value="">Оберіть піджанр</option>';
    subgenreSelect.disabled = true;

    if (genreId) {
        console.log("Обраний жанр ID:", genreId);
        fetch('server/add-book/get-genre.php?genre_id=' + genreId)
            .then(response => response.json())
            .then(data => {
                console.log("Отримані піджанри:", data);
                if (data.length > 0) {
                    data.forEach(subgenre => {
                        var option = document.createElement('option');
                        option.value = subgenre.genre_id;
                        option.textContent = subgenre.name;
                        subgenreSelect.appendChild(option);
                    });
                    subgenreSelect.disabled = false;
                }
            })
            .catch(error => console.error('Помилка при отриманні піджанрів:', error));
        subgenreSelect.disabled = false;
    }
});


// Обробник для нового жанру
document.getElementById('new_genre_checkbox').addEventListener('change', function () {
    var newGenreContainer = document.getElementById('new_genre_container');


    if (this.checked) {
        newGenreContainer.style.visibility = 'visible';
        document.getElementById('genre').disabled = true;
        document.getElementById('genre').value = '';
    } else {
        newGenreContainer.style.visibility = 'hidden';
        document.getElementById('genre').disabled = false;
    }
});

// вікно з помилками
document.addEventListener('DOMContentLoaded', function () {
    const urlParams = new URLSearchParams(window.location.search);
    const message = urlParams.get('message');
    const messageType = urlParams.get('message_type');

    const existingMessages = document.querySelectorAll('.message');
    existingMessages.forEach(function (msg) {
        msg.remove();
    });

    if (message) {
        const messageBox = document.createElement('div');
        messageBox.classList.add('message');

        if (messageType === 'success') {
            messageBox.classList.add('success-message');
        } else {
            messageBox.classList.add('error-message');
        }

        messageBox.textContent = decodeURIComponent(message);
        document.body.appendChild(messageBox);

        setTimeout(function () {
            messageBox.classList.add('fade-out');
        }, 3000);
        setTimeout(function () {
            messageBox.remove();
        }, 3500);
    }
});

document.getElementById('new_genre').addEventListener('blur', function () {
    let genreName = this.value.trim();

    if (genreName !== '') {
        fetch('server/add-book/add-genre.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'genre_name=' + encodeURIComponent(genreName)
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    console.log('Жанр додано з ID:', data.genre_id);

                    document.getElementById('genre').setAttribute('data-new-genre-id', data.genre_id);

                    $('#subgenre').select2().val(null).trigger('change');
                } else {
                    console.error('Помилка при додаванні жанру:', data.error);
                }
            })
            .catch(error => console.error('Помилка AJAX:', error));
    }
});
/*піджанр*/
$(document).ready(function () {
    $('#subgenre').select2({
        tags: true,
        placeholder: "Оберіть піджанри або введіть новий",
        ajax: {
            url: 'server/add-book/get-genre.php',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                var genre_id = $('#genre').is(':disabled')
                    ? $('#genre').data('new-genre-id') : $('#genre').val();
                console.log('genre_id:', $('#genre').val());
                return {
                    genre_id: genre_id,
                    q: params.term
                };
            },
            processResults: function (data) {
                var results = [];
                if (data && Array.isArray(data)) {
                    results = data.map(function (item) {
                        return { id: item.genre_id, text: item.name };
                    });
                } else if (data && data.error) {
                    results.push({ id: 0, text: 'Помилка: ' + data.error });
                }
                return {
                    results: results
                };
            },
            cache: true
        }
    });
});

$('#subgenre').on('select2:select', function (e) {
    var selected = e.params.data;
    if (selected.id === selected.text) {
        var parent_genre_id = $('#genre').is(':disabled')
            ? $('#genre').data('new-genre-id') : $('#genre').val();

        $.ajax({
            url: 'server/add-book/add_subgenre.php',
            method: 'POST',
            dataType: 'json',
            data: {
                subgenre_name: selected.text,
                parent_genre_id: parent_genre_id
            },
            success: function (response) {
                if (response.success && response.genre_id) {
                    var newOption = new Option(response.name, response.genre_id, true, true);
                    $('#subgenre').append(newOption).trigger('change');
                } else {
                    console.error('Помилка при додаванні піджанру:', response.message);
                }
            },
            error: function (xhr) {
                console.error('Ajax помилка:', xhr.responseText);
            }
        });
    }
});
