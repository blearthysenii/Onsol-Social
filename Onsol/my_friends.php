<?php
session_start();
require 'db.php';

$current_user_id = $_SESSION['user_id'];

$sql = "SELECT u.id, u.username FROM users u 
        JOIN friendships f ON 
        ((f.user_id = ? AND f.friend_id = u.id) OR (f.friend_id = ? AND f.user_id = u.id)) 
        WHERE f.status = 'accepted'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $current_user_id, $current_user_id);
$stmt->execute();
$result = $stmt->get_result();

echo "<h2>My friends</h2>";

while ($friend = $result->fetch_assoc()) {
    echo "<div>" . htmlspecialchars($friend['username']) . "</div>";
}
?>
