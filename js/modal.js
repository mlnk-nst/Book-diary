const PATHS = {
    auth: 'server/check_auth.php',
    modals: 'iteration/'
};

// перевірка статусу 
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

        const closeBtn = container.querySelector('.close-modal');
        if (closeBtn) closeBtn.addEventListener('click', closeModal);

    } catch (error) {
        console.error('Помилка завантаження модалки:', error);
        alert('Не вдалося завантажити вікно. Спробуйте оновити сторінку.');
    }
}

// обробник кнопки профілю
async function handleProfileButton() {
    try {
        const isLoggedIn = await checkAuth();
        await loadModal(isLoggedIn ? 'profile' : 'login');
    } catch (error) {
        console.error('Profile button error:', error);
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