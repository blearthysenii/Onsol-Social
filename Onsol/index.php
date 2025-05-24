<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Welcome</title>
</head>
<body>
    <h1>Hello, <?= htmlspecialchars($user['username']) ?>! Welcome to Onsol 🚀</h1>
    <p><a href="logout.php">Logout</a></p>
</body>
</html>
