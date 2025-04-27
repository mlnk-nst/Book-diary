document.addEventListener('DOMContentLoaded', function () {
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const errorDiv = document.getElementById('generalError');

            // Очистити попередні повідомлення
            errorDiv.innerHTML = '';
            errorDiv.className = 'message';

            fetch(form.action, {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        errorDiv.classList.add('success');
                        errorDiv.textContent = data.message;

                        // Якщо є дія для клієнта (наприклад, відкрити модалку входу)
                        if (data.action === 'loadLoginModal') {
                            setTimeout(() => {
                                loadModal('login');
                            }, 1500);
                        }
                    } else {
                        errorDiv.classList.add('error');
                        errorDiv.textContent = data.message;
                    }
                })
                .catch(error => {
                    errorDiv.classList.add('error');
                    errorDiv.textContent = 'Сталася помилка при відправці форми';
                });
        });
    } else {
        console.warn('Форма реєстрації не знайдена');
    }
});
