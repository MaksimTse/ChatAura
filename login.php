<?php
include 'config.php';
session_start();

// Перенаправление, если пользователь уже авторизован
if (isset($_SESSION['user_id'])) {
    header("Location: rooms.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Проверка данных пользователя в базе данных
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
            echo "<p>Неверный пароль!</p>";
        }
    } else {
        echo "<p>Пользователь не найден!</p>";
    }

    $stmt->close();
    $conn->close();
}
?>

<!-- HTML-код для формы входа (оставляем без изменений) -->


<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>Вход</h2>
    <form action="login.php" method="POST">
        <div class="form-group">
            <label for="username">Имя пользователя:</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="password">Пароль:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="btn">Войти</button>
    </form>
    <div class="link">
        Нет аккаунта? <a href="register.php">Зарегистрироваться</a>
    </div>
</div>
</body>
</html>
