<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

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

// Shto koment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['photo_id']) && isset($_POST['comment_text'])) {
        $photo_id = intval($_POST['photo_id']);
        $comment_text = trim($_POST['comment_text']);
        $user_id = $_SESSION['user']['id'];

        if ($comment_text !== '') {
            $stmt = $conn->prepare("INSERT INTO comments (photo_id, user_id, comment_text, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("iis", $photo_id, $user_id, $comment_text);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Shto Like
    if (isset($_POST['like_photo_id'])) {
        $photo_id = intval($_POST['like_photo_id']);
        $user_id = $_SESSION['user']['id'];

        $check = $conn->prepare("SELECT id FROM likes WHERE user_id = ? AND photo_id = ?");
        $check->bind_param("ii", $user_id, $photo_id);
        $check->execute();
        $res = $check->get_result();
        if ($res->num_rows === 0) {
            $like = $conn->prepare("INSERT INTO likes (user_id, photo_id) VALUES (?, ?)");
            $like->bind_param("ii", $user_id, $photo_id);
            $like->execute();
            $like->close();
        }
    }

    // Shto Save
    if (isset($_POST['save_photo_id'])) {
        $photo_id = intval($_POST['save_photo_id']);
        $user_id = $_SESSION['user']['id'];

        $check = $conn->prepare("SELECT id FROM saved_photos WHERE user_id = ? AND photo_id = ?");
        $check->bind_param("ii", $user_id, $photo_id);
        $check->execute();
        $res = $check->get_result();
        if ($res->num_rows === 0) {
            $save = $conn->prepare("INSERT INTO saved_photos (user_id, photo_id) VALUES (?, ?)");
            $save->bind_param("ii", $user_id, $photo_id);
            $save->execute();
            $save->close();
        }
    }

    header("Location: index.php");
    exit;
}

function getComments($conn, $photo_id) {
    $stmt = $conn->prepare("SELECT * FROM comments WHERE photo_id = ? ORDER BY created_at ASC");
    $stmt->bind_param("i", $photo_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $comments = [];
    while ($row = $res->fetch_assoc()) {
        $comments[] = $row;
    }
    $stmt->close();
    return $comments;
}

function countLikes($conn, $photo_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) as like_count FROM likes WHERE photo_id = ?");
    $stmt->bind_param("i", $photo_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    return $row['like_count'];
}

$photos = [];
$result = $conn->query("SELECT * FROM photos ORDER BY created_at DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $photos[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Home</title>
    <link rel="icon" href="images/logo.png" sizes="32x32" type="image/png" />
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #000000, #0a1a3f);
            margin: 0; padding: 0;
        }
        header {
            background: #0050ff;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        header h1 {
            margin: 0;
        }
        header nav a {
            color: white;
            margin-left: 20px;
            text-decoration: none;
            font-weight: 600;
        }
        main {
            max-width: 800px;
            margin: 30px auto;
            padding: 0 15px;
        }
        .photo-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 8px rgb(0 0 0 / 0.1);
            margin-bottom: 30px;
            overflow: hidden;
            padding-bottom: 15px;
        }
        .photo-card img {
            width: 100%;
            display: block;
        }
        .photo-info {
            padding: 15px 20px;
        }
        .photo-info .caption {
            font-size: 16px;
            margin: 5px 0;
        }
        .photo-info .author {
            font-weight: 700;
            color: #0050ff;
        }
        .photo-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        .photo-actions form {
            display: inline;
        }
        .photo-actions button {
            padding: 6px 14px;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: background 0.3s ease, transform 0.2s ease;
        }
        .like-btn { background-color: #ff4d4d; color: white; }
        .like-btn:hover { background-color: #cc0000; transform: scale(1.05); }
        .save-btn { background-color: #009688; color: white; }
        .save-btn:hover { background-color: #00675b; transform: scale(1.05); }
        .share-btn { background-color: #3f51b5; color: white; }
        .share-btn:hover { background-color: #283593; transform: scale(1.05); }
        .comments {
            padding: 0 20px;
            margin-top: 10px;
        }
        .comment {
            border-top: 1px solid #eee;
            padding: 10px 0;
            font-size: 14px;
        }
        .comment strong {
            color: #0050ff;
        }
        form.comment-form {
            padding: 10px 20px;
            border-top: 1px solid #ccc;
            display: flex;
            gap: 10px;
        }
        form.comment-form input[type="text"] {
            flex-grow: 1;
            padding: 8px 12px;
            border-radius: 20px;
            border: 1px solid #ccc;
            outline: none;
        }
        form.comment-form button {
            background: #0050ff;
            border: none;
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            cursor: pointer;
            font-weight: 700;
            transition: background-color 0.3s ease;
        }
        form.comment-form button:hover {
            background: #003dcc;
        }
    </style>
</head>
<body>

<header>
    <h1>Onsol</h1>
    <nav>
        <a href="upload.php">Upload Photo</a>
        <a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['user']['username']); ?>)</a>
    </nav>
</header>

<main>
    <?php if (count($photos) === 0): ?>
        <p>No photos yet. Be the first to <a href="upload.php">upload</a>!</p>
    <?php endif; ?>

    <?php foreach ($photos as $photo): ?>
        <div class="photo-card">
            <img src="uploads/<?php echo htmlspecialchars($photo['image_path']); ?>" alt="Photo" />
            <div class="photo-info">
                <div class="author"><?php echo htmlspecialchars(getUsername($conn, $photo['user_id'])); ?></div>
                <div class="caption"><?php echo htmlspecialchars($photo['caption']); ?></div>
                <div class="photo-actions">
                    <form method="POST"><input type="hidden" name="like_photo_id" value="<?php echo $photo['id']; ?>"><button type="submit" class="like-btn">❤️ Like (<?php echo countLikes($conn, $photo['id']); ?>)</button></form>
                    <form method="POST"><input type="hidden" name="save_photo_id" value="<?php echo $photo['id']; ?>"><button type="submit" class="save-btn">💾 Save</button></form>
                    <button class="share-btn" onclick="navigator.clipboard.writeText('<?php echo 'uploads/' . $photo['image_path']; ?>')">🔗 Share</button>
                </div>
                <div class="date"><?php echo htmlspecialchars($photo['created_at']); ?></div>
            </div>

            <div class="comments">
                <?php $comments = getComments($conn, $photo['id']); ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="comment"><strong><?php echo htmlspecialchars(getUsername($conn, $comment['user_id'])); ?>:</strong> <?php echo htmlspecialchars($comment['comment_text']); ?></div>
                <?php endforeach; ?>
            </div>

            <form class="comment-form" method="POST">
                <input type="hidden" name="photo_id" value="<?php echo $photo['id']; ?>" />
                <input type="text" name="comment_text" placeholder="Add a comment..." required />
                <button type="submit">Send</button>
            </form>
        </div>
    <?php endforeach; ?>
</main>

</body>
</html>
