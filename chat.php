<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['room_id'])) {
    header("Location: login.php");
    exit;
}

$room_id = $_GET['room_id'];
$user_id = $_SESSION['user_id'];

// Получаем название текущей комнаты
$stmt = $conn->prepare("SELECT name FROM rooms WHERE id = ?");
$stmt->bind_param("i", $room_id);
$stmt->execute();
$stmt->bind_result($room_name);
$stmt->fetch();
$stmt->close();

if (!$room_name) {
    echo "Комната не найдена!";
    exit;
}

// Получение комнат, в которых состоит пользователь
$my_rooms = $conn->query("
    SELECT rooms.id, rooms.name 
    FROM rooms 
    JOIN user_rooms ON rooms.id = user_rooms.room_id 
    WHERE user_rooms.user_id = $user_id
");

// Обработка отправки сообщения
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message'])) {
    $message = $_POST['message'];
    $stmt = $conn->prepare("INSERT INTO messages (room_id, user_id, content) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $room_id, $user_id, $message);
    $stmt->execute();
    $stmt->close();
}

// Получение сообщений из текущей комнаты
$messages = $conn->query("SELECT users.username, messages.content, messages.sent_at FROM messages JOIN users ON messages.user_id = users.id WHERE room_id = $room_id ORDER BY messages.sent_at ASC");

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="chat-style.css">
    <title>Чат - <?php echo htmlspecialchars($room_name); ?></title>
</head>
<body>
<div class="container">
    <div class="sidebar">
        <h3>Ваши чаты</h3>
        <ul class="room-list">
            <?php while ($room = $my_rooms->fetch_assoc()): ?>
                <li>
                    <a href="chat.php?room_id=<?php echo $room['id']; ?>"
                       class="<?php echo $room['id'] == $room_id ? 'active-room' : ''; ?>">
                        <?php echo htmlspecialchars($room['name']); ?>
                    </a>
                </li>
            <?php endwhile; ?>
        </ul>
    </div>

    <div class="chat-container">
        <div class="chat-header">
            <h2><?php echo htmlspecialchars($room_name); ?></h2>
        </div>

        <div class="chat-messages" id="message-box">
            <?php while ($message = $messages->fetch_assoc()): ?>
                <div class="message">
                    <strong><?php echo htmlspecialchars($message['username']); ?>:</strong>
                    <p><?php echo htmlspecialchars($message['content']); ?></p>
                    <span class="timestamp"><?php echo $message['sent_at']; ?></span>
                </div>
            <?php endwhile; ?>
        </div>

        <form action="chat.php?room_id=<?php echo $room_id; ?>" method="POST" class="chat-input">
            <input type="text" name="message" placeholder="Введите сообщение" required>
            <button type="submit">Отправить</button>
        </form>

        <div class="back-link">
            <a href="rooms.php">Назад к комнатам</a>
        </div>
    </div>
</div>
</body>
</html>
