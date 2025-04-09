document.getElementById('image').addEventListener('change', function () {
    const fileName = this.files.length > 0 ? this.files[0].name : 'Файл не вибрано';
    document.querySelector('.file-name').textContent = fileName;
});


document.getElementById('genre').addEventListener('change', function () {
    var genreId = this.value;
    var subgenreSelect = document.getElementById('subgenre');
    var newSubgenreContainer = document.getElementById('new_subgenre_container');

    subgenreSelect.innerHTML = '<option value="">Оберіть піджанр</option>';
    subgenreSelect.disabled = true;

    if (genreId) {
        fetch('server/genre.php?genre_id=' + genreId)
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    data.forEach(subgenre => {
                        var option = document.createElement('option');
                        option.value = subgenre.genre_id;
                        option.textContent = subgenre.name;
                        subgenreSelect.appendChild(option);
                    });
                    subgenreSelect.disabled = false;
                } else {
                    subgenreSelect.disabled = true;
                }
            })
            .catch(error => console.error('Помилка при отриманні піджанрів:', error));
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
/*піджанр*/
$(document).ready(function () {
    $('#subgenre').select2({
        tags: true,
        placeholder: "Оберіть піджанри або введіть новий",
        ajax: {
            url: 'server/genre.php',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                var genre_id = $('#genre').val();
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
    if (selected.id === '') {
        $.ajax({
            url: 'server/add_subgenre.php',
            method: 'POST',
            data: {
                subgenre_name: newSubgenre,
                parent_genre_id: $('#genre').val()
            },
            success: function (response) {
                if (response.success) {
                    $('#subgenre').append(new Option(newSubgenre, newSubgenre, true, true)).trigger('change');
                }
            }
        });
    }
});
