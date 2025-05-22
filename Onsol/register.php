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
        header("Location: login.php");
        exit();
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
    <style>

    body {
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;  
    align-items: center;      
    height: 100vh;            
    background-color: #f0f0f0; 

}

    .registerForm {
    text-align: center;
    padding: 100px;
    background-color: white;
    border: 1px solid #ccc;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.20);

}
input{    
    padding-top:20px;
    padding-right:100px ;
    padding-bottom:20px;
    padding-left:7px;
    font-size: 16px;
    text-align: left;
}
.usernameClass{
    margin-bottom:10px;
}
.emailClass{
    margin-bottom:10px;
}
.passwordClass{
    margin-bottom:20px;
}

        </style>

     
</head>
<body>
    <?php if ($message) : ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form action="register.php" method="POST" class="registerForm">
        <input class ="usernameClass" type="text" name="username" placeholder="Username" required /><br />
        <input class = "emailClass" type="email" name="email" placeholder="Email" required /><br />
        <input class = "passwordClass" type="password" name="password" placeholder="Password" required /><br />
        <button type="submit">Register</button>
    </form>
</body>
</html>
