<?php
session_start();
require 'db.php';


if (!isset($_SESSION['user'])) {
    http_response_code(403);
    echo json_encode(["error" => "Nuk je i kyçur."]);
    exit;
}

$my_id = $_SESSION['user']['id'];


if (!isset($_POST['friend_id']) || empty($_POST['friend_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "ID e përdoruesit që do ta ftuar mungon."]);
    exit;
}

$friend_id = intval($_POST['friend_id']);


if ($friend_id == $my_id) {
    http_response_code(400);
    echo json_encode(["error" => "Nuk mund të dërgosh kërkesë vetes."]);
    exit;
}


$conn = new mysqli("localhost", "root", "", "onsol_db1");
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Lidhja me DB dështoi."]);
    exit;
}


$stmt_check = $conn->prepare("SELECT * FROM friend_requests WHERE sender_id = ? AND receiver_id = ?");
$stmt_check->bind_param("ii", $my_id, $friend_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    echo json_encode(["message" => "Kërkesa për miqësi tashmë është dërguar më parë."]);
    exit;
}
$stmt_check->close();


$stmt = $conn->prepare("INSERT INTO friend_requests (sender_id, receiver_id, status) VALUES (?, ?, 'pending')");
$stmt->bind_param("ii", $my_id, $friend_id);

if ($stmt->execute()) {
    echo json_encode(["message" => "Kërkesa për miqësi është dërguar me sukses!"]);
} else {
    echo json_encode(["error" => "Gabim gjatë dërgimit të kërkesës: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>