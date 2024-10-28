<?php
include 'config.php';
session_start();

// Перенаправление, если пользователь уже авторизован
if (isset($_SESSION['user_id'])) {
    header("Location: rooms.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Чат Приложение</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Добро пожаловать в Чат</h1>
    <a href="login.php" class="btn">Войти</a>
    <a href="register.php" class="btn">Регистрация</a>
</div>
</body>
</html>
