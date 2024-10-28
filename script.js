// Функция для отображения уведомления
function showNotification(message, type = 'success') {
    const container = document.getElementById('notification-container');
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <span>${message}</span>
        <button onclick="this.parentElement.remove()">✕</button>
    `;

    container.appendChild(notification);

    // Удаление уведомления через 5 секунд
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

let isSignIn = true;

// Переключение между формами
function toggleForm() {
    isSignIn = !isSignIn;

    document.querySelector('.sign-in-container').style.display = isSignIn ? 'block' : 'none';
    document.querySelector('.sign-up-container').style.display = isSignIn ? 'none' : 'block';

    document.getElementById('overlay-text').textContent = isSignIn ? 'Добро пожаловать!' : 'Рады видеть вас!';
    document.getElementById('overlay-paragraph').textContent = isSignIn
        ? 'Пожалуйста, войдите, чтобы продолжить.'
        : 'Создайте учетную запись для входа.';
    document.getElementById('toggle-button').textContent = isSignIn ? 'Зарегистрироваться' : 'Войти';
}

// Обработка входа
function handleSignIn(event) {
    event.preventDefault(); // Предотвращаем отправку формы

    const username = document.getElementById('login-username').value;
    const password = document.getElementById('login-password').value;

    // Пример проверки. В реальном приложении проверка происходила бы на сервере
    if (username === "testuser" && password === "testpass") {
        showNotification("Вход выполнен успешно!", 'success');
        window.location.href = "rooms.php"; // Переход на страницу rooms.php
    } else {
        showNotification("Неверное имя пользователя или пароль!", 'error');
    }
}

// Обработка регистрации
function handleSignUp(event) {
    event.preventDefault(); // Предотвращаем отправку формы

    const username = document.getElementById('register-username').value;
    const email = document.getElementById('register-email').value;
    const password = document.getElementById('register-password').value;

    // Простая проверка на заполненность полей
    if (username && email && password) {
        showNotification("Регистрация прошла успешно! Теперь войдите в систему.", 'success');
        toggleForm(); // Переключаемся на форму входа после успешной регистрации
    } else {
        showNotification("Пожалуйста, заполните все поля для регистрации.", 'error');
    }
}
