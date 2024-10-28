<?php
$servername = "localhost";
$username = "darja";
$password = "2203";
$dbname = "chat_app";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

