<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Обновление профиля
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $new_username = $_POST['username'];
    $stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
    $stmt->bind_param("si", $new_username, $user_id);
    $stmt->execute();
    $_SESSION['username'] = $new_username;
    $stmt->close();

    // Перезагрузка страницы после обновления профиля
    echo "<script>alert('Профиль обновлен!'); window.location.href='profile.php';</script>";
    exit;
}

// Поиск пользователей (исключаем уже добавленных в друзья или отправленные запросы)
$search_results = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search_user'])) {
    $search_name = $_POST['search_name'];
    $stmt = $conn->prepare("
        SELECT id, username 
        FROM users 
        WHERE username LIKE ? 
        AND id != ? 
        AND id NOT IN (
            SELECT friend_id FROM friends WHERE user_id = ? 
            UNION 
            SELECT user_id FROM friends WHERE friend_id = ? 
        )
    ");
    $search_query = "%$search_name%";
    $stmt->bind_param("siii", $search_query, $user_id, $user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $search_results = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Обработка добавления в друзья
if (isset($_GET['add_friend'])) {
    $friend_id = $_GET['add_friend'];
    $stmt = $conn->prepare("INSERT INTO friends (user_id, friend_id, status) VALUES (?, ?, 'pending')");
    $stmt->bind_param("ii", $user_id, $friend_id);
    $stmt->execute();
    header("Location: profile.php");
    exit;
}

// Принятие или отклонение дружбы
if (isset($_GET['accept_friend'])) {
    $friend_id = $_GET['accept_friend'];
    $stmt = $conn->prepare("UPDATE friends SET status = 'accepted' WHERE friend_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $user_id, $friend_id);
    $stmt->execute();
    header("Location: profile.php");
    exit;
}

if (isset($_GET['decline_friend'])) {
    $friend_id = $_GET['decline_friend'];
    $stmt = $conn->prepare("DELETE FROM friends WHERE friend_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $user_id, $friend_id);
    $stmt->execute();
    header("Location: profile.php");
    exit;
}

// Получение списка запросов в друзья
$friend_requests = $conn->query("
    SELECT u.id, u.username 
    FROM users u 
    JOIN friends f ON u.id = f.user_id 
    WHERE f.friend_id = $user_id AND f.status = 'pending'
");

// Получение списка друзей
$friends = $conn->query("
    SELECT u.id, u.username 
    FROM users u 
    JOIN friends f ON (u.id = f.friend_id AND f.user_id = $user_id OR u.id = f.user_id AND f.friend_id = $user_id)
    WHERE f.status = 'accepted'
");

// Получение комнат друзей
$friends_rooms = [];
while ($friend = $friends->fetch_assoc()) {
    $friend_id = $friend['id'];
    $stmt = $conn->prepare("
        SELECT r.name AS room_name 
        FROM rooms r 
        JOIN user_rooms ur ON r.id = ur.room_id 
        WHERE ur.user_id = ?
    ");
    $stmt->bind_param("i", $friend_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $friend_rooms = $result->fetch_all(MYSQLI_ASSOC);
    $friends_rooms[$friend['username']] = $friend_rooms;
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактировать профиль</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div id="profilePage" class="container">
    <h2>Редактировать профиль</h2>

    <div id="updateProfile" class="section">
        <h3>Ваше имя пользователя: <?php echo htmlspecialchars($username); ?></h3>
        <form action="profile.php" method="POST">
            <input type="text" name="username" placeholder="Новое имя пользователя" required>
            <button type="submit" name="update_profile" class="btn">Обновить профиль</button>
        </form>
    </div>

    <div id="searchUsers" class="section">
        <h3>Поиск пользователей</h3>
        <form action="profile.php" method="POST">
            <input type="text" name="search_name" placeholder="Имя пользователя для поиска">
            <button type="submit" name="search_user" class="btn">Найти</button>
        </form>
    </div>

    <div id="friendRequests" class="section">
        <h3>Запросы в друзья</h3>
        <ul class="user-list">
            <?php while ($request = $friend_requests->fetch_assoc()): ?>
                <li>
                    <?php echo htmlspecialchars($request['username']); ?>
                    <a href="profile.php?accept_friend=<?php echo $request['id']; ?>" class="btn">Принять</a>
                    <a href="profile.php?decline_friend=<?php echo $request['id']; ?>" class="btn">Отклонить</a>
                </li>
            <?php endwhile; ?>
        </ul>
    </div>
</div>

    <div class="section">
        <h3>Ваши друзья и их комнаты</h3>
        <ul class="user-list">
            <?php foreach ($friends_rooms as $friend_name => $rooms): ?>
                <li><?php echo htmlspecialchars($friend_name); ?>
                    <?php if (!empty($rooms)): ?>
                        <ul class="room-list">
                            <?php foreach ($rooms as $room): ?>
                                <li><?php echo htmlspecialchars($room['room_name']); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div style="text-align: center; margin-top: 20px;">
        <a href="rooms.php" class="btn">Вернуться к комнатам</a>
        <form action="logout.php" method="POST" style="display: inline;">
            <button type="submit" class="btn">Выйти</button>
        </form>
    </div>
</div>
</body>
</html>
