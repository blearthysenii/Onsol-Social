<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];


function getUsername($conn, $user_id) {
    $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows === 1) {
        return $res->fetch_assoc()['username'];
    }
    return "Unknown";
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unsave_photo_id'])) {
    $unsave_photo_id = intval($_POST['unsave_photo_id']);
    $stmt = $conn->prepare("DELETE FROM saved_photos WHERE user_id = ? AND photo_id = ?");
    $stmt->bind_param("ii", $user_id, $unsave_photo_id);
    $stmt->execute();
    $stmt->close();

   
    header("Location: saved.php");
    exit;
}


$stmt = $conn->prepare("
    SELECT photos.* FROM photos
    INNER JOIN saved_photos ON photos.id = saved_photos.photo_id
    WHERE saved_photos.user_id = ?
    ORDER BY photos.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$saved_photos = [];
while ($row = $result->fetch_assoc()) {
    $saved_photos[] = $row;
}
$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <link rel="icon" href="images/logo.png" sizes="32x32" type="image/png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
    <title>Saved Photos</title>
    <style>
        #backgroundCanvas {
    position: fixed;
    top: 0;
    left: 0;
    z-index: -1; 
    width: 100vw;
    height: 100vh;
    background: transparent;
}
        body {
    margin: 0;
    padding: 0 20px 40px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #000000, #0a1a3f);
    min-height: 100vh;
    color: #fff;
    display: flex;
    justify-content: center;
}

.container {
    margin-top:75px;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(20px);
    border-radius: 40px;
    padding: 40px;
    max-width: 700px;
    width: 100%;
    animation: fadeIn 1s ease-in-out;
    transition: all 0.9s ease-in-out;
    position: relative;
    z-index: 10;
     backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.2); 
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2); 
}

.container:hover {
    background: linear-gradient(135deg, rgba(20, 0, 40, 0.95), rgba(0, 30, 100, 0.95));
    transform: scale(1.019);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.35);
}

h1 {
    text-align: center;
    margin-bottom: 30px;
    font-weight: 700;
    text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
}

nav {
    text-align: center;
    margin-bottom: 20px;
}

nav a {
    color: #fff;
    text-decoration: none;
    font-weight: 600;
    font-size: 1.1rem;
    border: 2px solid #fff;
    padding: 8px 15px;
    border-radius: 8px;
    transition: background-color 0.3s, color 0.3s;
}

nav a:hover {
    background-color: #fff;
    color: #764ba2;
}

.photo-card {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    margin-bottom: 25px;
    padding: 15px;
    overflow: hidden;
    transition: transform 0.3s ease;
    position: relative;
}

.photo-card:hover {
    transform: scale(1.03);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
}

.photo-card img {
    width: 100%;
    border-radius: 12px;
    object-fit: cover;
    max-height: 350px;
}

.author {
    font-weight: 700;
    margin-top: 10px;
    color: #ffd700;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
}

.caption {
    margin-top: 6px;
    font-style: italic;
    color: #eee;
}

.date {
    margin-top: 4px;
    font-size: 0.9rem;
    color: #ccc;
}

form.unsave-form {
    position: absolute;
    top: 18px;
    right: 18px;
    z-index: 20;
}

form.unsave-form button {
    background: linear-gradient(45deg, #ff5858, #ff0000);
    border: none;
    padding: 9px 18px;
    border-radius: 10px;
    color: #fff;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 4px 15px rgba(255, 0, 0, 0.6);
    transition: background 0.3s ease, box-shadow 0.3s ease;
    font-size: 0.9rem;
    user-select: none;
}

form.unsave-form button:hover {
    background: linear-gradient(45deg, #ff0000, #cc0000);
    box-shadow: 0 6px 22px rgba(204, 0, 0, 0.9);
}

/* Animations */
@keyframes fadeSlideIn {
    0% {
        opacity: 0;
        transform: translateY(10px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeSlideUp {
    0% {
        opacity: 0;
        transform: translateY(15px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}


</style>
</head>
<body>
    <canvas id="backgroundCanvas"></canvas>


<div class="container">
    <nav>
        <a href="home.php" aria-label="Return to Home">Return to Home</a>
    </nav>

    <h1>Saved Photos</h1>

    <?php if (empty($saved_photos)): ?>
        <p style="text-align:center; font-size: 1.3rem; color: #bbb;">You have not saved any photos yet.</p>
    <?php else: ?>
        <?php foreach ($saved_photos as $photo): ?>
            <div class="photo-card" tabindex="0" aria-label="Saved photo by <?php echo htmlspecialchars(getUsername($conn, $photo['user_id'])); ?>">
                <img src="upload/<?php echo htmlspecialchars($photo['image_path']); ?>" alt="Saved Photo" loading="lazy" />
                <div class="author">By: <?php echo htmlspecialchars(getUsername($conn, $photo['user_id'])); ?></div>
                <div class="caption"><?php echo htmlspecialchars($photo['caption']); ?></div>
                <div class="date"><?php echo htmlspecialchars($photo['created_at']); ?></div>

                <form class="unsave-form" method="post" onsubmit="return confirm('Are you sure you want to remove this photo from your saved list?');">
                    <input type="hidden" name="unsave_photo_id" value="<?php echo $photo['id']; ?>" />
                    <button type="submit" aria-label="Unsave photo">Unsave</button>
                </form>
            </div>
        <?php endforeach; ?>
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