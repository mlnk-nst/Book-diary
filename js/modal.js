const PATHS = {
    auth: 'server/login/check_auth.php',
    modals: 'iteration/'
};

// перевірка статусу  ( помилки)
async function checkAuth() {
    try {
        const response = await fetch(PATHS.auth);
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        const data = await response.json();
        return data.isLoggedIn;
    } catch (error) {
        console.error('Помилка перевірки авторизації:', error);
        return false;
    }
}



//  завантаження модалки 
async function loadModal(modalType) {
    try {
        const container = document.getElementById('modal-container');
        if (!container) throw new Error('Modal container not found');

        const response = await fetch(`${PATHS.modals}${modalType}.html`);
        if (!response.ok) throw new Error(`Failed to load ${modalType} modal`);

        const html = await response.text();
        container.innerHTML = html;
        container.style.display = 'flex';

        if (modalType === 'registration') {
            initRegistrationForm();
        }
        else if (modalType === 'login') {
            initLoginForm();
        }

        const closeBtn = container.querySelector('.close-modal');
        if (closeBtn) closeBtn.addEventListener('click', closeModal);

    } catch (error) {
        console.error('Помилка завантаження модалки:', error);
    }
}


// обробник кнопки профілю
async function handleProfileButton() {
    try {
        const authData = await checkAuth();
        let modalType = 'login';
        if (authData.isLoggedIn) {
            modalType = authData.userRole === 'admin' ? 'admin-profile' : 'user-profile';
        }
        await loadModal(modalType, authData);
    } catch (error) {
        console.error('Profile button error:', error);
        showErrorToast('Не вдалося перевірити статус авторизації');
    }
}

// закриття модалки
function closeModal() {
    const container = document.getElementById('modal-container');
    if (container) container.style.display = 'none';

}


// ініціалізація 
document.addEventListener('DOMContentLoaded', () => {
    const profileBtn = document.getElementById('profile-btn');
    if (profileBtn) {
        profileBtn.addEventListener('click', handleProfileButton);
    } else {
        console.warn('Кнопка профілю не знайдена (id="profile-btn")');
    }

    const modalContainer = document.getElementById('modal-container');
    if (modalContainer) {
        modalContainer.addEventListener('click', (e) => {
            if (e.target === e.currentTarget) closeModal();
        });
    }
});

// обробка реєстрації 
function initRegistrationForm() {
    const registerForm = document.getElementById('registerForm');
    const registerMessages = document.querySelector('.register-messages');

    if (registerForm) {
        registerForm.addEventListener('submit', function (e) {
            console.log("Форма відправлена!")
            e.preventDefault();

            if (registerMessages) {
                registerMessages.innerHTML = '';
            }

            const password = document.getElementById('regPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            if (password !== confirmPassword) {
                if (registerMessages) {
                    console.error("Паролі не збігаються");
                    registerMessages.innerHTML = '<div class="error-message">Паролі не збігаються</div>';
                }
                return;
            }
            const formData = new FormData(registerForm);
            console.log("Відправляємо запит на сервер...");
            fetch('server/login/registration.php', {
                method: 'POST',
                body: formData
            })

                .then(response => {
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        if (registerMessages) {
                            registerMessages.innerHTML = `
                            <div class="success-message">
                                ${data.message}
                            </div>
                        `;
                        }
                        registerForm.reset();
                        setTimeout(() => {
                            loadModal('login');
                        }, 2000);

                    } else {
                        let errorHtml = `<div class="error-message">${data.message}</div>`;
                        if (data.errors) {
                            errorHtml += '<ul class="error-list">';
                            for (const field in data.errors) {
                                errorHtml += `<li>${data.errors[field]}</li>`;
                                const inputField = document.getElementById(field);
                                if (inputField) {
                                    inputField.classList.add('error-input');
                                    inputField.addEventListener('focus', function () {
                                        this.classList.remove('error-input');
                                    }, { once: true });
                                }
                            }
                            errorHtml += '</ul>';
                        }

                        if (registerMessages) {
                            registerMessages.innerHTML = errorHtml;
                        }
                    }
                })
                .catch(error => {
                    if (registerMessages) {
                        registerMessages.innerHTML = `
                        <div class="error-message">
                            Помилка при відправці форми: ${error.message}
                        </div>
                    `;
                    }
                });
        });
    }
}

// обробка форми входу
function initLoginForm() {
    const loginFormForm = document.getElementById('loginForm');
    const loginMessages = document.querySelector('.login-messages');

    if (loginForm) {
        loginForm.addEventListener('submit', function (e) {
            console.log("Форма відправлена!")
            e.preventDefault();

            if (loginMessages) {
                loginMessages.innerHTML = '';
            }

            const formData = new FormData(loginForm);
            console.log("Відправляємо запит на сервер...");
            fetch('server/login/log.php', {
                method: 'POST',
                body: formData
            })

                .then(response => {
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        if (loginMessages) {
                            loginMessages.innerHTML = `
                            <div class="success-message">
                                ${data.message}
                            </div>
                        `;
                        }

                        setTimeout(() => {
                            closeModal();
                            window.location.reload();
                        }, 2000);
                    } else {
                        let errorHtml = `<div class="error-message">${data.message}</div>`;

                        if (data.errors) {
                            errorHtml += '<ul class="error-list">';
                            for (const field in data.errors) {
                                errorHtml += `<li>${data.errors[field]}</li>`;
                            }
                            errorHtml += '</ul>';
                        }

                        if (loginMessages) {
                            loginMessages.innerHTML = errorHtml;
                        }
                    }
                })
                .catch(error => {
                    if (loginMessages) {
                        loginMessages.innerHTML = `
                        <div class="error-message">
                            Помилка при відправці форми: ${error.message}
                        </div>
                    `;
                    }
                });
        });
    }
}
