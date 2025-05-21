<?php
include 'db.php';  

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $username = $conn->real_escape_string(trim($_POST['username']));
    $password = $_POST['password'];

    
    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

      
        if (password_verify($password, $user['password'])) {
            echo "Login successful! Welcome, " . htmlspecialchars($user['username']);
            // Here you can set session variables if needed
        } else {
            echo "Incorrect password!";
        }
    } else {
        echo "User not found!";
    }
} else {
    echo "Invalid request method.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>
    <form action="login.php" method="POST">
        <input type="text" name="username" placeholder="Username" required /><br />
        <input type="password" name="password" placeholder="Password" required /><br />
        <button type="submit">Login</button>
    </form>
</body>
</html>

