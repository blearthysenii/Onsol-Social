<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user']['id'])) {
    echo "Nuk je i kyçur!";
    exit;
}

$current_user_id = $_SESSION['user']['id'];

if (!isset($_GET['request_id'], $_GET['action'])) {
    echo "Parametra të pavlefshëm!";
    exit;
}

$request_id = intval($_GET['request_id']);
$action = $_GET['action'];

if ($action === 'accept') {
   
    $sql = "UPDATE friend_requests SET status = 'accepted' WHERE id = ? AND receiver_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $request_id, $current_user_id);
    $stmt->execute();

   
    $sql1 = "SELECT sender_id FROM friend_requests WHERE id = ?";
    $stmt1 = $conn->prepare($sql1);
    $stmt1->bind_param("i", $request_id);
    $stmt1->execute();
    $res = $stmt1->get_result();
    $row = $res->fetch_assoc();
    $sender_id = $row['sender_id'];

    
    $sql2 = "INSERT INTO friendships (user_id, friend_id, status) VALUES (?, ?, 'accepted'), (?, ?, 'accepted')";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("iiii", $current_user_id, $sender_id, $sender_id, $current_user_id);
    $stmt2->execute();

} elseif ($action === 'reject') {
   
    $sql = "UPDATE friend_requests SET status = 'rejected' WHERE id = ? AND receiver_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $request_id, $current_user_id);
    $stmt->execute();
} else {
    echo "Veprim i papërcaktuar!";
    exit;
}


header("Location: friend_requests.php");
exit();
?>
