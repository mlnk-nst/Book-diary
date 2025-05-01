const PATHS = {
    auth: 'server/login/check_auth.php',
    modals: 'iteration/'
};

// перевірка статусу  ( помилки)
async function checkAuth() {
    try {
        const response = await fetch(PATHS.auth, {
            credentials: 'include'
        });
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        const data = await response.json();
        if (data.session_id) {
            localStorage.setItem('last_session_id', data.session_id);
        }
        return data;
    } catch (error) {
        console.error('Помилка перевірки авторизації:', error);
        return false;
    }
}



//  завантаження модалки 
async function loadModal(modalType, authData = null) {
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
        else if (modalType === 'profile') {
            if (!authData) authData = await checkAuth();
            initProfileModal(authData);
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

        let modalType;
        if (authData.isLoggedIn) {
            modalType = 'profile';
        } else {
            modalType = 'login';
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
                        if (data.session_id) {
                            localStorage.setItem('last_session_id', data.session_id);
                        }
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

// вікно профіля
async function initProfileModal(authData) {
    try {
        const profileContainer = document.querySelector('.container-profile');
        if (!profileContainer) throw new Error('Profile container not found');

        const userData = await fetchUserData(authData.userId);
        if (userData) {
            document.getElementById('profile-name').textContent = userData.username || 'Ім\'я не вказано';
            document.getElementById('profile-email').textContent = userData.email || 'Пошта не вказана';
            authData.user = {
                name: userData.username,
                email: userData.email,
                role: userData.role
            };
        }

        if (authData.userRole === 'user') {
            await loadUserProgress(authData.userId);
        }
        const addBookBtn = document.getElementById('add-book-btn');
        const userInfo = document.querySelector('.user-info');
        const logoutBtn = document.getElementById('logout-btn');

        const isAdmin = authData.userRole === 'admin';

        profileContainer.classList.remove('admin', 'user');
        profileContainer.classList.add(isAdmin ? 'admin' : 'user');

        // вийти з профіля
        if (logoutBtn) {
            logoutBtn.addEventListener('click', async (e) => {
                e.preventDefault();
                try {
                    const response = await fetch('server/login/logout.php', {
                        method: 'POST',
                        credentials: 'include'
                    });

                    if (!response.ok) throw new Error('Помилка виходу');

                    const data = await response.json();

                    if (data.success) {
                        localStorage.clear();
                        sessionStorage.clear();

                        document.cookie.split(';').forEach(cookie => {
                            const [name] = cookie.trim().split('=');
                            document.cookie = `${name}=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;`;
                        });
                        closeModal();
                        window.location.href = 'main.php';
                    } else {
                        showErrorToast(data.message || 'Помилка при виході');
                    }
                } catch (error) {
                    console.error('Помилка виходу:', error);
                    showErrorToast('Помилка при виході');
                }
            });
        }

    } catch (error) {
        console.error('Помилка ініціалізації профілю:', error);
        showErrorToast('Помилка завантаження профілю');
        window.location.href = 'login.php';
    }
}

async function loadUserProgress(userId) {
    try {
        const response = await fetch(`server/user/get-progress.php?user_id=${userId}`);
        if (!response.ok) throw new Error('Помилка завантаження прогресу');

        const progressData = await response.json();

        if (progressData.success) {
            document.getElementById('profile-level').textContent = `Рівень: ${progressData.level || 0}`;
            document.getElementById('profile-points').textContent = `Накопичено балів: ${progressData.experience_points || 0}`;
        } else {
            console.error('Помилка прогресу:', progressData.message);
        }
    } catch (error) {
        console.error('Помилка завантаження прогресу:', error);
    }
}
async function fetchUserData(userId) {
    try {
        const response = await fetch(`server/user/get-user.php?user_id=${userId}`);
        if (!response.ok) throw new Error('Помилка завантаження даних');

        const data = await response.json();

        if (data.success) {
            return data.user;
        } else {
            throw new Error(data.message || 'Помилка отримання даних');
        }
    } catch (error) {
        console.error('Помилка:', error);
        return {};
    }
}