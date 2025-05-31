<?php
session_start();
require 'db.php'; 

$current_user_id = $_SESSION['user_id'];


$sql = "SELECT id, username FROM users WHERE id != ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$result = $stmt->get_result();

echo "<h2>Lista e përdoruesve</h2>";

while ($user = $result->fetch_assoc()) {
    $user_id = $user['id'];
    $username = htmlspecialchars($user['username']);
    
    
    $checkSql = "SELECT * FROM friendships WHERE 
        (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("iiii", $current_user_id, $user_id, $user_id, $current_user_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows == 0) {
        
        echo "<div>$username 
                <a href='send_request.php?friend_id=$user_id'>Shto si mik</a>
              </div>";
    } else {
        echo "<div>$username - (Miqtë ose në pritje)</div>";
    }
}
?>
