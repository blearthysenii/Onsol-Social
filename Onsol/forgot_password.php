<?php
// forgot_password.php
session_start();
include 'db.php';

// Require Composer autoload për PHPMailer
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE LOWER(email) = LOWER(?)");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result === false) {
            die("Query failed: " . $conn->error);
        }

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            $token = bin2hex(random_bytes(16));
            $expires = date("Y-m-d H:i:s", strtotime('+1 hour'));

            $stmtUpdate = $conn->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
            if (!$stmtUpdate) {
                die("Prepare failed (update): " . $conn->error);
            }
            $stmtUpdate->bind_param("ssi", $token, $expires, $user['id']);
            $stmtUpdate->execute();

            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
            $host = $_SERVER['HTTP_HOST']; // ky merr domainin aktual nga kërkesa
            $resetLink = $protocol . "://" . $host . "/Onsol-Social/Onsol/reset_password.php?token=" . $token;
  


            // Sent email with PHPMailer
            $mail = new PHPMailer(true);

            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'blearthyseni834@gmail.com';  
                $mail->Password   = 'ykwh xshv qsyp nzaq'; 
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                // Recipients
                $mail->setFrom('no-reply@yourdomain.com', 'Onsol');
                $mail->addAddress($email);

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Password Reset Request';
                $mail->Body    = "
                    <p>Hello, from Onsol</p>
                    <p>Please click the link below to reset your password:</p>
                    <p><a href='$resetLink'>$resetLink</a></p>
                    <p>This link will expire in 1 hour.</p>
                    <p>If you did not request this, please ignore this email.</p>
                ";

                $mail->send();
                $message = "An email with password reset instructions has been sent to your email address.";
            } catch (Exception $e) {
                $message = "Failed to send reset email. Mailer Error: {$mail->ErrorInfo}";
            }

        } else {
            $message = "No user found with that email address.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <link rel="icon" href="images/logo.png" sizes="32x32" type="image/png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
    <title>Forgot Password</title>
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #000000, #0a1a3f);
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: #fff;
            overflow: hidden;
            position: relative;
        }

        .forgot-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            max-width: 400px;
            width: 100%;
            animation: fadeIn 1s ease-in-out;
            transition: all 0.9s ease-in-out;
            z-index: 10;
            position: relative;
             backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.2); /* kufi i lehtë si te xhami */
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2); /* pak hije për thellësi */
}


        .forgot-container:hover {
            background: linear-gradient(135deg, rgba(20, 0, 40, 0.95), rgba(0, 30, 100, 0.95));
            transform: scale(1.019);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.35);
        }

        .forgot-container h2 {
            margin-bottom: 20px;
            font-size: 28px;
            color: white;
            transition: all 0.4s ease-in-out;
            background: linear-gradient(135deg, #348aff, #ff5ef7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .forgot-container input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: none;
            border-radius: 12px;
            outline: none;
            background-color: rgba(255, 255, 255, 0.15);
            color: #fff;
            font-size: 16px;
        }

        .forgot-container input::placeholder {
            color: rgba(128, 128, 128, 0.7); 
        }

        .forgot-container button {
            width: 100%;
            padding: 15px 0;
            margin-top: 15px;
            border: none;
            border-radius: 25px;
            background-color: #3a2a6a;
            background-image: linear-gradient(45deg, #3a2a6a 0%, #0050ff 100%);
            color: #fff;
            font-size: 20px;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 100, 255, 0.4);
        }

        .forgot-container button:hover {
            transform: scale(1.01);
            box-shadow: 0 2px 10px rgba(0, 180, 255, 0.7);
        }

        .forgot-container p {
            margin-top: 20px;
            color: #ddd;
        }

        .forgot-container a {
            color: #fff;
            text-decoration: underline;
        }

        .message {
            background: linear-gradient(135deg, rgba(20, 10, 40, 0.9), rgba(0, 40, 120, 0.9));
            padding: 15px 25px;
            border-radius: 15px;
            color: #a0eaff;
            font-size: 18px;
            max-width: 500px;
            box-shadow: 0 4px 20px rgba(0, 100, 255, 0.4);
            text-align: center;
            margin: 0 0 20px 0;
            user-select: none;
            cursor: default;
            transition: all 0.3s ease;
        }

        .message.error {
            background: linear-gradient(135deg, #700000, #ff0000);
            color: #fff;
            box-shadow: 0 4px 20px rgba(255, 0, 0, 0.7);
        }.back-to-login-link {
    position: relative;
    font-weight: 700;
    text-decoration: none;
    background: linear-gradient(135deg, #4e2eff, #ff6ec4);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    transition: transform 0.3s ease;
    cursor: pointer;
    display: inline-block;
}

.back-to-login-link::after {
    content: '';
    position: absolute;
    left: 0;
    bottom: -2px;
    width: 100%;
    height: 2px;
    background: linear-gradient(135deg, #4e2eff, #ff6ec4);
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.3s ease;
}

.back-to-login-link:hover,
.back-to-login-link:focus {
    transform: scale(1.02);
    outline: none;
}

.back-to-login-link:hover::after {
    transform: scaleX(1);
}


        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        #backgroundCanvas {
    position: fixed;
    top: 0;
    left: 0;
    z-index: -1; 
    width: 100vw;
    height: 100vh;
    background: transparent;
}

    </style>
</head>
<body>
<canvas id="backgroundCanvas"></canvas>

<div class="forgot-container">
    <h2>Forgot Password</h2>

    <?php if ($message): ?>
        <div class="message <?php echo (strpos($message, 'Failed') !== false || strpos($message, 'No user') !== false) ? 'error' : ''; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <form action="forgot_password.php" method="POST" autocomplete="off">
        <input type="email" name="email" placeholder="Enter your email" required />
        <button type="submit">Send Reset Link</button>
    </form>

    <p class="signup-text">
    Remembered your password? 
    <a href="login.php" class="back-to-login-link">Back to login!</a>
</p>
<script>
const canvas = document.getElementById('backgroundCanvas');
const ctx = canvas.getContext('2d');
canvas.width = window.innerWidth;
canvas.height = window.innerHeight;

let stars = [];
for (let i = 0; i < 150; i++) {
    stars.push({
        x: Math.random() * canvas.width,
        y: Math.random() * canvas.height,
        r: Math.random() * 1.5,
        dx: (Math.random() - 0.5) * 0.5,
        dy: (Math.random() - 0.5) * 0.5
    });
}

function animate() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    for (let s of stars) {
        ctx.beginPath();
        ctx.arc(s.x, s.y, s.r, 0, Math.PI * 2);
        ctx.fillStyle = '#ffffff88';
        ctx.fill();
        s.x += s.dx;
        s.y += s.dy;
        if (s.x < 0 || s.x > canvas.width) s.dx *= -1;
        if (s.y < 0 || s.y > canvas.height) s.dy *= -1;
    }
    requestAnimationFrame(animate);
}
animate();

window.addEventListener('resize', () => {
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
});
</script>



</body>
</html>
