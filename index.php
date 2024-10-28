<?php
include 'config.php';
session_start();

// Перенаправление, если пользователь уже авторизован
if (isset($_SESSION['user_id'])) {
    header("Location: rooms.php");
    exit;
}

// Логика входа
$loginError = $registerError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $hashed_password);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                $_SESSION['user_id'] = $id;
                $_SESSION['username'] = $username;
                header("Location: rooms.php");
                exit;
            } else {
                $loginError = "Неверный пароль!";
            }
        } else {
            $loginError = "Пользователь не найден!";
        }

        $stmt->close();
    }

    // Логика регистрации
    if (isset($_POST['register'])) {
        $username = $_POST['reg_username'];
        $email = $_POST['reg_email'];
        $password = password_hash($_POST['reg_password'], PASSWORD_BCRYPT);

        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $password);

        if ($stmt->execute()) {
            echo "<p class='success-message'>Регистрация успешна!</p>";
        } else {
            $registerError = "Ошибка регистрации: " . $stmt->error;
        }

        $stmt->close();
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Авторизация и Регистрация</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div id="notification-container"></div>
<div class="container">
    <!-- Форма авторизации -->
    <div id="form-section">
        <div class="form-container sign-in-container">
            <h1>Вход</h1>
            <?php if ($loginError): ?>
                <p class="error-message"><?= $loginError ?></p>
            <?php endif; ?>
            <form method="POST" action="">
                <input type="text" name="username" placeholder="Имя пользователя" required>
                <input type="password" name="password" placeholder="Пароль" required>
                <button type="submit" name="login">Войти</button>
            </form>
        </div>

        <!-- Форма регистрации -->
        <div class="form-container sign-up-container">
            <h1>Регистрация</h1>
            <?php if ($registerError): ?>
                <p class="error-message"><?= $registerError ?></p>
            <?php endif; ?>
            <form method="POST" action="">
                <input type="text" name="reg_username" placeholder="Имя пользователя" required>
                <input type="email" name="reg_email" placeholder="Email" required>
                <input type="password" name="reg_password" placeholder="Пароль" required>
                <button type="submit" name="register">Зарегистрироваться</button>
            </form>
        </div>
    </div>

    <!-- Текстовая секция с кнопками -->
    <div id="text-section">
        <div class="overlay-panel">
            <h1 id="overlay-text">Добро пожаловать!</h1>
            <p id="overlay-paragraph">Пожалуйста, войдите или зарегистрируйтесь.</p>
            <button id="toggle-button" onclick="toggleForm()">Зарегистрироваться</button>
        </div>
    </div>
</div>

<script src="script.js"></script>
</body>
</html>
