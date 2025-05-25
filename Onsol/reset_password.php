<?php
session_start();
include 'db.php';

$message = '';
$showForm = false;

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Kontrollo në DB nëse token ekziston dhe nuk ka skaduar
    $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expires > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $showForm = true;
        $user = $result->fetch_assoc();

        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $password = $_POST['password'];
            $passwordConfirm = $_POST['password_confirm'];

            if (strlen($password) < 6) {
                $message = "Password must be at least 6 characters.";
            } elseif ($password !== $passwordConfirm) {
                $message = "Passwords do not match.";
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Update fjalëkalimin dhe fshij token-in
                $stmtUpdate = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
                $stmtUpdate->bind_param("si", $hashedPassword, $user['id']);
                if ($stmtUpdate->execute()) {
                    $message = "Your password has been reset successfully. You can now <a href='login.php'>login</a>.";
                    $showForm = false;
                } else {
                    $message = "Something went wrong. Please try again.";
                }
            }
        }
    } else {
        $message = "Invalid or expired token.";
    }
} else {
    $message = "No token provided.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Reset Password</title>
    <style>
        /* Ngjashëm me stilin që ke tek forgot_password.php */
        body {
            background: linear-gradient(135deg, #000000, #0a1a3f);
            color: white;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .reset-container {
            background: rgba(255, 255, 255, 0.1);
            padding: 40px;
            border-radius: 20px;
            max-width: 400px;
            width: 100%;
            text-align: center;
        }
        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 12px;
            border: none;
            outline: none;
            background: rgba(255, 255, 255, 0.15);
            color: white;
            font-size: 16px;
        }
        button {
            width: 100%;
            padding: 15px 0;
            border: none;
            border-radius: 25px;
            background-image: linear-gradient(45deg, #3a2a6a 0%, #0050ff 100%);
            color: white;
            font-size: 20px;
            cursor: pointer;
            margin-top: 15px;
        }
        .message {
            margin-bottom: 20px;
            color: #a0eaff;
        }
        .message.error {
            color: #ff6b6b;
        }
    </style>
</head>
<body>

<div class="reset-container">
    <h2>Reset Password</h2>

    <?php if ($message): ?>
        <div class="message <?php echo strpos($message, 'successfully') ? '' : 'error'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <?php if ($showForm): ?>
    <form method="POST" action="">
        <input type="password" name="password" placeholder="New password" required minlength="6" />
        <input type="password" name="password_confirm" placeholder="Confirm new password" required minlength="6" />
        <button type="submit">Reset Password</button>
    </form>
    <?php endif; ?>
</div>

</body>
</html>
