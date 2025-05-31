<?php
session_start();
include 'db.php';

$message = '';
$showForm = false;

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    
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

                
                $stmtUpdate = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
                $stmtUpdate->bind_param("si", $hashedPassword, $user['id']);
                if ($stmtUpdate->execute()) {
                    $message = "Your password has been reset successfully. You can now <a href='login.php'>Login!</a>";
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
    <link rel="icon" href="images/logo.png" sizes="32x32" type="image/png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
    <title>Reset Password</title>
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
        .reset-container {
            background: rgba(255, 255, 255, 0.1);
            transition: transform 0.6s ease-in-out, background 0.6s ease-in-out, box-shadow 0.6s ease-in-out;
            padding: 40px;
            border-radius: 20px;
            max-width: 400px;
            width: 100%;
            text-align: center;
             backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.2); /* kufi i lehtë si te xhami */
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2); /* pak hije për thellësi */
}

        .reset-container:hover {
            background: linear-gradient(135deg, rgba(20, 0, 40, 0.95), rgba(0, 30, 100, 0.95));
            transform: scale(1.02);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.35);
}
        .reset-container h2 {
            margin-bottom: 20px;
            font-size: 28px;
            color: white;
            transition: all 0.4s ease-in-out;
            background: linear-gradient(135deg, #348aff, #ff5ef7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        input {
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
        input::placeholder{
            color: rgba(128, 128, 128, 0.7); 
        }
        
        button {
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
        }.button:hover {
            transform: scale(1.01);
            box-shadow: 0 2px 10px rgba(0, 180, 255, 0.7);
        }

        .message {
    margin-bottom: 20px;
    padding: 15px 20px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 1rem;
    color: #a0eaff;
    background: rgba(78, 46, 255, 0.1);
    box-shadow: 0 4px 12px rgba(78, 46, 255, 0.2);
    animation: fadeIn 0.4s ease forwards;
    backdrop-filter: blur(8px);
    transition: background-color 0.3s ease, color 0.3s ease;
}

.message.error {
    color: #ff6b6b;
    background: rgba(255, 107, 107, 0.1);
    box-shadow: 0 4px 12px rgba(255, 107, 107, 0.25);
}

.message a {
    padding-left:10px;
    position: relative;
    font-weight: 700;
    text-decoration: none;
    background: linear-gradient(135deg, #4e2eff, #ff6ec4);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    transition: transform 0.3s ease, filter 0.3s ease;
    cursor: pointer;
    display: inline-block;
}

.message a::after {
    content: '';
    position: absolute;
    left: 0;
    bottom: -4px;
    width: 100%;
    height: 3px;
    background: linear-gradient(135deg, #4e2eff, #ff6ec4);
    border-radius: 2px;
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.3s ease;
}

.message a:hover,
.message a:focus {
    transform: scale(1.05);
    filter: drop-shadow(0 0 6px rgba(78, 46, 255, 0.8));
    outline: none;
}

.message a:hover::after,
.message a:focus::after {
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

        @keyframes slideDownFadeIn {
            from {
                opacity: 0;
                transform: translateX(-50%) translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(-50%) translateY(0);
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


<div class="reset-container">
    <h2>Reset Password</h2>

    <?php if ($message): ?>
        <div class="message <?php echo strpos($message, 'successfully') ? '' : 'error'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <?php if ($showForm): ?>
    <form method="POST" action="">
        <input type="password" name="password" placeholder="New password" required minlength="8" />
        <input type="password" name="password_confirm" placeholder="Confirm new password" required minlength=8" />
        <button class="button" type="submit">Reset Password</button>
    </form>
    <?php endif; ?>
</div>
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
