<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['room_id'])) {
    header("Location: login.php");
    exit;
}

$room_id = $_GET['room_id'];
$user_id = $_SESSION['user_id'];

// Проверка, существует ли комната
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

// Обработка отправки сообщения
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message'])) {
    $message = $_POST['message'];

    $stmt = $conn->prepare("INSERT INTO messages (room_id, user_id, content) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $room_id, $user_id, $message);
    $stmt->execute();
    $stmt->close();
}

// Получение сообщений из комнаты
$messages = $conn->query("SELECT users.username, messages.content, messages.sent_at FROM messages JOIN users ON messages.user_id = users.id WHERE room_id = $room_id ORDER BY messages.sent_at ASC");

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Чат - <?php echo htmlspecialchars($room_name); ?></title>
</head>
<body>
<h2>Комната: <?php echo htmlspecialchars($room_name); ?></h2>

<div style="border: 1px solid #ddd; padding: 10px; max-height: 300px; overflow-y: scroll;">
    <?php while ($message = $messages->fetch_assoc()): ?>
        <p><strong><?php echo htmlspecialchars($message['username']); ?>:</strong> <?php echo htmlspecialchars($message['content']); ?> <small>(<?php echo $message['sent_at']; ?>)</small></p>
    <?php endwhile; ?>
</div>

<form action="chat.php?room_id=<?php echo $room_id; ?>" method="POST">
    <input type="text" name="message" placeholder="Введите сообщение" required>
    <button type="submit">Отправить</button>
</form>

<a href="rooms.php">Назад к комнатам</a>
</body>
</html>
