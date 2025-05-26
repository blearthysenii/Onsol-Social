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

$user_id = $_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add comment
    if (isset($_POST['photo_id'], $_POST['comment_text'])) {
        $photo_id = intval($_POST['photo_id']);
        $comment_text = trim($_POST['comment_text']);
        if ($comment_text !== '') {
            $stmt = $conn->prepare("INSERT INTO comments (photo_id, user_id, comment_text, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("iis", $photo_id, $user_id, $comment_text);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Like
    if (isset($_POST['like_photo_id'])) {
        $photo_id = intval($_POST['like_photo_id']);
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
        $check->close();
    }

    // Save
    if (isset($_POST['save_photo_id'])) {
    $photo_id = intval($_POST['save_photo_id']);
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
    $check->close();
}

    header("Location: index.php");
    exit;
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
        color: #eee;
    }
    header {
        background: linear-gradient(90deg, #0050ff, #003dcc);
        color: white;
        padding: 15px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        position: sticky;
        top: 0;
        z-index: 100;
    }
    header h1 {
        margin: 0;
        font-weight: 700;
        letter-spacing: 1.5px;
        text-shadow: 0 1px 3px rgba(0,0,0,0.6);
    }
    header nav a {
        color: #cce0ff;
        margin-left: 20px;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.3s ease;
    }
    header nav a:hover {
        color: white;
        text-shadow: 0 0 6px #003dcc;
    }
    main {
        max-width: 800px;
        margin: 30px auto;
        padding: 0 15px 40px;
    }
    .photo-card {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        box-shadow: 0 0 12px rgba(0, 0, 0, 0.4);
        margin-bottom: 30px;
        overflow: hidden;
        padding-bottom: 15px;
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.15);
        color: #fff;
        transition: transform 0.3s ease;
    }
    .photo-card:hover {
        background: linear-gradient(135deg, rgba(20, 0, 40, 0.95), rgba(0, 30, 100, 0.95));
        transform: scale(1.02);
        box-shadow: 0 0 20px #0050ff;
    }
    .photo-card img {
        width: 100%;
        display: block;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    }
    .photo-info {
        padding: 15px 20px;
    }
    .photo-info .caption {
        font-size: 16px;
        margin: 5px 0;
        color: #ddd;
    }
    .photo-info .author {
        font-weight: 700;
        color: #80b3ff;
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
        border-top: 1px solid rgba(255,255,255,0.2);
        padding: 10px 0;
        font-size: 14px;
        color: #ccc;
    }
    .comment strong {
        color: #80b3ff;
    }
    form.comment-form {
        padding: 10px 20px;
        border-top: 1px solid rgba(255,255,255,0.3);
        display: flex;
        gap: 10px;
    }
    form.comment-form input[type="text"] {
        flex-grow: 1;
        padding: 8px 12px;
        border-radius: 20px;
        border: 1px solid #555;
        outline: none;
        background-color: rgba(255,255,255,0.15);
        color: white;
        transition: border-color 0.3s ease;
    }
    form.comment-form input[type="text"]:focus {
        border-color: #80b3ff;
        background-color: rgba(255,255,255,0.25);
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
    .date {
        font-size: 12px;
        color: #a0a0a0;
        margin-top: 8px;
        text-align: right;
    }
    .photo-actions {
  display: flex;
  gap: 15px;
  margin-top: 10px;
}

.photo-actions button {
  display: flex;
  align-items: center;
  gap: 6px;
  background-color: #f0f0f0;
  border: none;
  padding: 8px 14px;
  border-radius: 8px;
  font-weight: 600;
  color: #333;
  cursor: pointer;
  transition: background-color 0.3s ease, color 0.3s ease;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  box-shadow: 0 2px 5px rgb(0 0 0 / 0.1);
}

.photo-actions button:hover {
  background-color: #6c63ff;
  color: white;
  box-shadow: 0 4px 12px rgb(108 99 255 / 0.5);
}

.photo-actions button svg {
  width: 18px;
  height: 18px;
  fill: currentColor;
}

</style>
</head>
<body>

<header>
    <h1>Onsol</h1>
    <nav>
    <a href="upload.php">Upload Photo</a>
    <a href="saved.php">Saved Photos</a>
    <a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['user']['username']); ?>)</a>
</nav>
</header>

<main>
    <?php if (count($photos) === 0): ?>
        <p>No photos yet. Be the first to <a href="upload.php">upload</a>!</p>
    <?php endif; ?>

    <?php foreach ($photos as $photo): ?>
    <div class="photo-card">
        <img src="upload/<?php echo htmlspecialchars($photo['image_path']); ?>" alt="Photo" />
        <div class="photo-info">
            <div class="author"><?php echo htmlspecialchars(getUsername($conn, $photo['user_id'])); ?></div>
            <div class="caption"><?php echo htmlspecialchars($photo['caption']); ?></div>
            <div class="photo-actions">
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="like_photo_id" value="<?php echo (int)$photo['id']; ?>">
                    <button type="submit" class="like-btn" aria-label="Like photo">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="red">
                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 
                            2 5.42 4.42 3 7.5 3c1.74 0 3.41 0.81 4.5 2.09C13.09 3.81 
                            14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 
                            11.54L12 21.35z"/>
                        </svg>
                        Like (<?php echo countLikes($conn, $photo['id']); ?>)
                    </button>
                </form>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="save_photo_id" value="<?php echo (int)$photo['id']; ?>">
                    <button type="submit" class="save-btn" aria-label="Save photo">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="blue">
                            <path d="M17 3H7a2 2 0 0 0-2 2v16l7-3 7 3V5a2 2 0 0 0-2-2z"/>
                        </svg>
                        Save
                    </button>
                </form>
                <button class="share-btn" 
                        onclick="navigator.clipboard.writeText('<?php echo 'https://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/upload/' . rawurlencode($photo['image_path']); ?>').then(() => alert('Link copied!'));" 
                        aria-label="Share photo">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="green">
                        <path d="M18 8a3 3 0 1 0-2.83-4H9a3 3 0 0 0 0 6h6.17A3 3 0 1 0 18 8zm-7 
                        9a3 3 0 1 0 0-6h6.17A3 3 0 1 0 18 17h-7zM7 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
                    </svg>
                    Share
                </button>
            </div>
            <div class="date"><?php echo htmlspecialchars($photo['created_at']); ?></div>
        </div>

        <div class="comments">
            <?php $comments = getComments($conn, $photo['id']); ?>
            <?php foreach ($comments as $comment): ?>
                <div class="comment">
                    <strong><?php echo htmlspecialchars(getUsername($conn, $comment['user_id'])); ?>:</strong>
                    <?php echo htmlspecialchars($comment['comment_text']); ?>
                </div>
            <?php endforeach; ?>
        </div>

        <form class="comment-form" method="POST" autocomplete="off">
            <input type="hidden" name="photo_id" value="<?php echo (int)$photo['id']; ?>" />
            <input type="text" name="comment_text" placeholder="Write a comment..." required />
            <button type="submit">Comment</button>
        </form>
    </div>
<?php endforeach; ?>

</main>

</body>
</html>
