<?php
session_start();
include 'db.php';
 

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    

    
    $stmt = $conn->prepare("UPDATE users SET username = ?, fullname = ?, email = ? WHERE id = ?");
    $stmt->bind_param("sssi", $username, $fullname, $email, $user_id);
    $stmt->execute();
    $stmt->close();
    
    
    $_SESSION['user']['username'] = $username;
}


$stmt = $conn->prepare("SELECT username, email, fullname FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo "User not found.";
    exit;
}

$user = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <link rel="icon" href="images/logo.png" sizes="32x32" type="image/png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
    <title>Profile - <?php echo htmlspecialchars($user['username']); ?></title>
    <style>
       html, body {
  margin: 0;
  padding: 0;
  width: 100%;
  overflow-x: hidden; 
}    
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #000000, #0a1a3f);
            color: #fff;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .profile-container {
            margin: auto;
            margin-top: 100px;
            background: rgba(255, 255, 255, 0.05);
            transition: transform 0.6s ease-in-out, background 0.6s ease-in-out, box-shadow 0.6s ease-in-out;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 40px 30px;
            width: 100%;
            max-width: 400px;
            backdrop-filter: blur(12px);
            box-shadow: 0 0 25px rgba(0, 0, 255, 0.2);
            animation: fadeIn 1s ease-in-out;
             backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.2); 
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2); 
}

        
        .profile-container:hover {
            background: linear-gradient(135deg, rgba(20, 0, 40, 0.95), rgba(0, 30, 100, 0.95));
            transform: scale(1.019);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.35);
        }

        .profile-container h2 {
            text-align: center;
            font-weight: 600;
            margin-bottom: 20px;
            font-size: 28px;
            transition: all 0.4s ease-in-out;
            background: linear-gradient(135deg, #348aff, #ff5ef7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .conte {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        label {
            font-weight: 600;
            margin-bottom: 5px;
            display: block;
            background: linear-gradient(135deg, #348aff, #ff5ef7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
}
        input[type="text"], input[type="email"] {
            padding: 10px 15px;
            border-radius: 10px;
            border: none;
            font-size: 16px;
            background: rgba(255, 255, 255, 0.15);
            outline: none;
            transition: background 0.3s ease, color 0.3s ease;
        }
        input[readonly] {
    color: lightgrey;
    background: rgba(255, 255, 255, 0.3);
    cursor: pointer;
    opacity: 0.3;
}
        input:not([readonly]) {
            color: white;
        }
        input[type="text"]:focus, input[type="email"]:focus {
            background: rgba(255, 255, 255, 0.3);
        }
        .save-btn {
            width: 100%;
            padding: 15px 0;
            margin-top: 15px;
            border: none;
            border-radius: 25px;
            background-color: #3a2a6a;
            background-image: linear-gradient(45deg, #3a2a6a 0%, #0050ff 100%);
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 100, 255, 0.4);
        }
        .save-btn:hover {
            transform: scale(1.01);
            box-shadow: 0 2px 10px rgba(0, 180, 255, 0.7);
        }
        .logout-btn {
            display: block;
            margin-top: 30px;
            text-align: center;
            padding: 10px 20px;
            background-color: red;
            border-radius: 10px;
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.3s ease;
        }
        .logout-btn:hover {
            background-color: #e63950;
        }
        .logout-btn i {
            margin-right: 8px;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
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

    <?php include 'header.php'; ?>

    <div class="profile-container">
        <h2><i class="fas fa-user-circle"></i> Profile</h2>

        <form class="conte" method="POST" action="">
            <label for="username"><i class="fas fa-user"></i> Username</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required readonly>

            <label for="fullname"><i class="fas fa-id-card"></i> Full Name</label>
            <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($user['fullname']); ?>" required readonly>

            <label for="email"><i class="fas fa-envelope"></i> Email</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required readonly>

            <button type="submit" class="save-btn">Save Changes</button>
        </form>

        <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <script>
        const inputs = document.querySelectorAll("input[type='text'], input[type='email']");
        inputs.forEach(input => {
            input.addEventListener("click", () => {
                if (input.hasAttribute("readonly")) {
                    input.removeAttribute("readonly");
                    input.focus();
                }
            });
        });
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
