<?php
include 'db.php';

$message = "";  // Variable to hold messages

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string(trim($_POST['username']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $password = $_POST['password'];

    if (empty($username) || empty($email) || empty($password)) {
        $message = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$password_hash')";

        if ($conn->query($sql) === TRUE) {
            $message = "Registration successful!";
        } else {
            if ($conn->errno == 1062) {
                $message = "Username or email already exists.";
            } else {
                $message = "Error: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Register</title>
</head>
<body>
    <?php if ($message) : ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form action="register.php" method="POST">
        <input type="text" name="username" placeholder="Username" required /><br />
        <input type="email" name="email" placeholder="Email" required /><br />
        <input type="password" name="password" placeholder="Password" required /><br />
        <button type="submit">Register</button>
    </form>
</body>
</html>
