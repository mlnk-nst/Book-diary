let isLoggedIn = false;


document.addEventListener('DOMContentLoaded', () => {
    fetch('server/login/check_auth.php')
        .then(res => res.json())
        .then(data => {
            isLoggedIn = data.isLoggedIn;
            if (data.userRole === 'admin') {
                document.querySelectorAll('.protected-link').forEach(link => {
                    link.style.display = 'none';
                });
            }

            document.querySelectorAll('.protected-link').forEach(link => {
                link.addEventListener('click', (e) => {
                    if (!isLoggedIn) {
                        e.preventDefault();
                        loadModal('login');
                    }
                });
            });
        })
        .catch(err => {
            console.error('Помилка перевірки авторизації:', err);
        });
    document.querySelectorAll(".close-modal").forEach(span => {
        span.addEventListener("click", closeModal);
    });
});

