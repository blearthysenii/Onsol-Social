<?php
if (!isset($_GET['user_id'])) {
    echo "User ID missing.";
    exit;
}

$userId = intval($_GET['user_id']);

$conn = new mysqli("localhost", "root", "", "onsol_db1");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$stmt = $conn->prepare("SELECT id, username, email, fullname FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$resultUser = $stmt->get_result();

if ($resultUser->num_rows === 0) {
    echo "User not found.";
    exit;
}

$user = $resultUser->fetch_assoc();
$stmt->close();

$stmt = $conn->prepare("SELECT image_path, caption, created_at FROM photos WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$resultPhotos = $stmt->get_result();

$photos = [];
while ($row = $resultPhotos->fetch_assoc()) {
    $photos[] = $row;
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <link rel="icon" href="images/logo.png" sizes="32x32" type="image/png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
    <title><?= htmlspecialchars($user['username']) ?>'s Profile</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #000000, #0a1a3f);
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 800px;
            margin: 40px auto;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            backdrop-filter: blur(20px);
            animation: fadeIn 1s ease-in-out;
            transition: all 0.9s ease-in-out;
            z-index: 10;
        }
         .container:hover{
            background: linear-gradient(135deg, rgba(20, 0, 40, 0.95), rgba(0, 30, 100, 0.95));
            transform: scale(1.019);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.35);
        }


        .profile-header {
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        h1 {
            font-size: 32px;
            color: white;
            background: linear-gradient(135deg, #348aff, #ff5ef7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        p {
            color: white;
            background: linear-gradient(135deg, #348aff, #ff5ef7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .photos-section h2 {
            font-size: 24px;
            margin-bottom: 20px;
            color: white;
            background: linear-gradient(135deg, #348aff, #ff5ef7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .photo-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        .photo-card {
            background: #fafafa;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 3px 6px rgba(0,0,0,0.05);
            transition: 0.3s ease;
        }

        .photo-card:hover {
            transform: translateY(-5px);
        }

        .photo-card img {
            width: 100%;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .photo-card p {
            font-size: 14px;
            color: #333;
        }

        .photo-card small {
            font-size: 12px;
            color: #999;
        }
        a.back-link {
            margin-top: 25px;
            position: relative;
            font-weight: 700;
            text-decoration: none;
            background: linear-gradient(135deg, #4e2eff, #ff6ec4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            transition: transform 0.3s ease;
            cursor: pointer;
            display: inline-block;
            font-size:20px;
        }

        a.back-link::after {
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

        a.back-linkhover,
        a.back-link:focus {
            transform: scale(1.02);
            outline: none;
        }

        a.back-link:hover::after {
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
        

        @media (max-width: 600px) {
            .container {
                padding: 15px;
            }

            h1 {
                font-size: 26px;
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

    <div class="container">
        <a href="home.php" class="back-link">
  <i class="fas fa-arrow-left"></i> Back to Home!
</a>
        <div class="profile-header">
            <h1><?= htmlspecialchars($user['fullname']) ?> (<?= htmlspecialchars($user['username']) ?>)</h1>
            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
        </div>

        <div class="photos-section">
            <h2>Photos</h2>
            <?php if (count($photos) > 0): ?>
                <div class="photo-list">
                    <?php foreach ($photos as $photo): ?>
                        <div class="photo-card">
                            <?php if (!empty($photo['image_path'])): ?>
                                <img src="/Onsol-Social/Onsol/upload/<?= htmlspecialchars($photo['image_path']) ?>" alt="Photo" />
                            <?php endif; ?>
                            <p><?= nl2br(htmlspecialchars($photo['caption'])) ?></p>
                            <small>Posted on <?= htmlspecialchars($photo['created_at']) ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>This user has no photos yet.</p>
            <?php endif; ?>
        </div>
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
