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
    <title>Saved Photos</title>
    <link rel="icon" href="images/logo.png" sizes="32x32" type="image/png" />
    <style>
        
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #000000, #0a1a3f);
            min-height: 100vh;
            color: #fff;
            display: flex;
            justify-content: center;
            padding: 40px 20px;
        }

        .container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 40px;
            padding: 40px;
            max-width: 700px;
            width: 100%;
            animation: fadeIn 1s ease-in-out;
            transition: all 0.9s ease-in-out;
            z-index: 10;
            position: relative;
        }

        .container:hover{
            background: linear-gradient(135deg, rgba(20, 0, 40, 0.95), rgba(0, 30, 100, 0.95));
            transform: scale(1.019);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.35);
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
            font-weight: 700;
            text-shadow: 0 1px 3px rgba(0,0,0,0.3);
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
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            margin-bottom: 25px;
            padding: 15px;
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        .photo-card:hover {
            transform: scale(1.03);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
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
            text-shadow: 0 1px 2px rgba(0,0,0,0.5);
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
    </style>
</head>
<body>

<div class="container">
    <nav>
        <a href="index.php">Return to Home</a>
    </nav>

    <h1>Saved Photos</h1>

    <?php if (empty($saved_photos)): ?>
        <p style="text-align:center;">You have not saved any photos yet.</p>
    <?php else: ?>
        <?php foreach ($saved_photos as $photo): ?>
            <div class="photo-card">
                <img src="upload/<?php echo htmlspecialchars($photo['image_path']); ?>" alt="Saved Photo" />
                <div class="author">By: <?php echo htmlspecialchars(getUsername($conn, $photo['user_id'])); ?></div>
                <div class="caption"><?php echo htmlspecialchars($photo['caption']); ?></div>
                <div class="date"><?php echo htmlspecialchars($photo['created_at']); ?></div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>
