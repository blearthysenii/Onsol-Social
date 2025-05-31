<?php
session_start();
require 'db.php'; // corrected DB connection file

$from = $_SESSION['user']['id'] ?? 0;
$to = $_POST['to'] ?? 0;
$message = $_POST['message'] ?? '';

if ($from && $to && !empty($message)) {
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $from, $to, $message);
    $stmt->execute();
}

header("Location: chat.php?with=$to");
exit;
