<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Обработка создания новой комнаты
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['room_name'])) {
    $room_name = $_POST['room_name'];
    $stmt = $conn->prepare("INSERT INTO rooms (name, created_by) VALUES (?, ?)");
    $stmt->bind_param("si", $room_name, $user_id);
    $stmt->execute();
    $stmt->close();
}

// Присоединение к комнате
if (isset($_GET['join_room'])) {
    $room_id = $_GET['join_room'];
    $stmt = $conn->prepare("INSERT INTO user_rooms (user_id, room_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $room_id);
    $stmt->execute();
    $stmt->close();
    header("Location: rooms.php");
    exit;
}

// Выход из комнаты
if (isset($_GET['leave_room'])) {
    $room_id = $_GET['leave_room'];
    $stmt = $conn->prepare("DELETE FROM user_rooms WHERE user_id = ? AND room_id = ?");
    $stmt->bind_param("ii", $user_id, $room_id);
    $stmt->execute();
    $stmt->close();
    header("Location: rooms.php");
    exit;
}

// Получение списка комнат, в которых состоит пользователь
$my_rooms = $conn->query("SELECT rooms.id, rooms.name FROM rooms 
                          JOIN user_rooms ON rooms.id = user_rooms.room_id 
                          WHERE user_rooms.user_id = $user_id");

// Получение списка доступных комнат, к которым пользователь еще не присоединился
$available_rooms = $conn->query("SELECT id, name FROM rooms 
                                 WHERE id NOT IN (SELECT room_id FROM user_rooms WHERE user_id = $user_id)");
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Комнаты</title>
    <link rel="stylesheet" href="style.css">
    <script>
        function showTab(tabId) {
            document.getElementById('myRooms').style.display = tabId === 'myRooms' ? 'block' : 'none';
            document.getElementById('availableRooms').style.display = tabId === 'availableRooms' ? 'block' : 'none';
            document.getElementById('tabMyRooms').classList.toggle('active', tabId === 'myRooms');
            document.getElementById('tabAvailableRooms').classList.toggle('active', tabId === 'availableRooms');
        }
    </script>
</head>
<body>
<div class="container">
    <h2>Добро пожаловать, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>

    <div style="text-align: center; margin-bottom: 20px;">
        <a href="profile.php" class="btn">Профиль</a>
        <form action="logout.php" method="POST" style="display: inline;">
            <button type="submit" class="btn">Выйти</button>
        </form>
    </div>
    <hr>
    <h3>Создать комнату</h3>
    <form action="rooms.php" method="POST">
        <input type="text" name="room_name" placeholder="Название комнаты" required>
        <button type="submit" class="btn margin">Создать</button>
    </form>
    <br>
    <hr>
    <br>
    <div class="tabs">
        <div id="tabMyRooms" class="tab active" onclick="showTab('myRooms')">Мои комнаты</div>
        <div id="tabAvailableRooms" class="tab" onclick="showTab('availableRooms')">Доступные комнаты</div>
    </div>

    <div id="myRooms">
        <h3>Мои комнаты</h3>
        <ul class="room-list">
            <?php while ($room = $my_rooms->fetch_assoc()): ?>
                <li>
                    <?php echo htmlspecialchars($room['name']); ?>
                    <a href="chat.php?room_id=<?php echo $room['id']; ?>" class="btn">Войти</a>
                    <a href="rooms.php?leave_room=<?php echo $room['id']; ?>" class="btn">Выйти</a> <!-- Кнопка выхода из комнаты -->
                </li>
            <?php endwhile; ?>
        </ul>
    </div>

    <div id="availableRooms" style="display: none;">
        <h3>Доступные комнаты</h3>
        <ul class="room-list">
            <?php while ($room = $available_rooms->fetch_assoc()): ?>
                <li>
                    <?php echo htmlspecialchars($room['name']); ?>
                    <a href="rooms.php?join_room=<?php echo $room['id']; ?>" class="btn">Присоединиться</a>
                </li>
            <?php endwhile; ?>
        </ul>
    </div>
</div>
</body>
</html>
