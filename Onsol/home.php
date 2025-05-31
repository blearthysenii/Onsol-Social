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
    return ($res && $res->num_rows === 1) ? $res->fetch_assoc()['username'] : "Unknown";
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
    return $res->fetch_assoc()['like_count'];
}

function userLiked($conn, $user_id, $photo_id) {
    $stmt = $conn->prepare("SELECT id FROM likes WHERE user_id = ? AND photo_id = ?");
    $stmt->bind_param("ii", $user_id, $photo_id);
    $stmt->execute();
    $res = $stmt->get_result();
    return $res->num_rows > 0;
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

    // Like/Unlike (toggle)
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
        } else {
            $unlike = $conn->prepare("DELETE FROM likes WHERE user_id = ? AND photo_id = ?");
            $unlike->bind_param("ii", $user_id, $photo_id);
            $unlike->execute();
            $unlike->close();
        }
        $check->close();
    }

    // Save photo
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

    // Delete comment
    if (isset($_POST['delete_comment_id'])) {
        $comment_id = intval($_POST['delete_comment_id']);
        $stmt = $conn->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $comment_id, $user_id);
        $stmt->execute();
        $stmt->close();
    }

    // Delete photo
    if (isset($_POST['delete_photo_id'])) {
        $photo_id = intval($_POST['delete_photo_id']);
        $stmt = $conn->prepare("SELECT image_path FROM photos WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $photo_id, $user_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 1) {
            $row = $res->fetch_assoc();
            $imagePath = 'upload/' . $row['image_path'];
            if (file_exists($imagePath)) unlink($imagePath);
            $del = $conn->prepare("DELETE FROM photos WHERE id = ?");
            $del->bind_param("i", $photo_id);
            $del->execute();
            $del->close();
        }
        $stmt->close();
    }

    header("Location: home.php");
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
    <link rel="icon" href="images/logo.png" sizes="32x32" type="image/png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
    <title>Home - Photo Gallery</title>
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
        align-items: center;
    }
    main {
        max-width: 800px;
        width: 100%;
        margin: 40px 15px 60px;
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
         backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.2); /* kufi i lehtë si te xhami */
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2); /* pak hije për thellësi */


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
        cursor: pointer;
        border-radius: 0 0 10px 10px;
        user-select: none;
        transition: filter 0.3s ease;
    }
    .photo-card img:hover {
        filter: brightness(1.1);
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
        gap: 15px;
        margin-top: 10px;
    }
    .photo-actions button {
        display: flex;
        align-items: center;
        gap: 6px;
        background-color: #f0f0f0;
        border: none;
        padding: 8px 14px !important;
        font-size: 14px;
        border-radius: 8px;
        font-weight: 600;
        color: #333;
        cursor: pointer;
        transition: background-color 0.3s ease, color 0.3s ease;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        box-shadow: 0 2px 5px rgb(0 0 0 / 0.1);
    }
    .photo-actions button:hover {
        background-color: #0050ff;
        color: #fff;
        box-shadow: 0 0 10px #0050ff;
    }
    .photo-actions button.like-liked {
        color: #e60023;
        background: #fff0f3;
        box-shadow: 0 0 5px #e60023;
    }
    .comments-section {
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px solid rgba(255,255,255,0.2);
        max-height: 160px;
        overflow-y: auto;
    }
    .comment {
        margin-bottom: 8px;
        font-size: 13px;
        line-height: 1.3;
        color: #ccc;
        position: relative;
        padding-right: 24px;
    }
    .comment .author {
        font-weight: 700;
        color: #99cfff;
    }
    .comment .delete-comment {
        position: absolute;
        right: 0;
        top: 1px;
        background: none;
        border: none;
        color: #ff4c4c;
        cursor: pointer;
        font-size: 14px;
        padding: 0 4px;
        user-select: none;
    }
    .comment .delete-comment:hover {
        color: #ff0000;
    }
    .add-comment {
        display: flex;
        margin-top: 10px;
    }
    .add-comment textarea {
        flex-grow: 1;
        resize: none;
        border-radius: 6px;
        border: 1px solid #ccc;
        padding: 6px 8px;
        font-size: 14px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        margin-right: 8px;
    }
    .add-comment button {
        padding: 8px 15px;
        font-weight: 700;
        background-color: #0050ff;
        color: white;
        border-radius: 6px;
        border: none;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }
    .add-comment button:hover {
        background-color: #003bb3;
    }
    form {
        margin: 0;
    }
    /* Modal styles */
    #modal {
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.8);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }
    #modal img {
        max-width: 90vw;
        max-height: 90vh;
        border-radius: 12px;
        box-shadow: 0 0 30px rgba(0, 80, 255, 0.7);
    }
    #modal .close-modal {
        position: absolute;
        top: 20px;
        right: 20px;
        background: #0050ff;
        border: none;
        color: white;
        font-size: 24px;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        cursor: pointer;
        user-select: none;
        box-shadow: 0 0 10px #0050ff;
        transition: background-color 0.3s ease;
    }
    #modal .close-modal:hover {
        background: #003bb3;
    }
    /* Scrollbar for comments */
    .comments-section::-webkit-scrollbar {
        width: 6px;
    }
    .comments-section::-webkit-scrollbar-thumb {
        background-color: #0050ff;
        border-radius: 3px;
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
<main>

<h1 style="text-align:center; margin-bottom: 25px;"></h1>

<?php if (empty($photos)): ?>
    <p style="text-align:center;">No photos available.</p>
<?php else: ?>
    <?php foreach ($photos as $photo): ?>
        <?php 
            $photo_id = $photo['id']; 
            $photo_user_id = $photo['user_id']; 
            $username = getUsername($conn, $photo_user_id);
            $comments = getComments($conn, $photo_id);
            $likes_count = countLikes($conn, $photo_id);
            $liked = userLiked($conn, $user_id, $photo_id);
            $isOwner = ($photo_user_id === $user_id);
        ?>
        <div class="photo-card">
            <img src="upload/<?= htmlspecialchars($photo['image_path']) ?>" alt="<?= htmlspecialchars($photo['caption']) ?>" class="photo-thumb" data-img="<?= htmlspecialchars($photo['image_path']) ?>" />
            <div class="photo-info">
                <p class="caption"><?= htmlspecialchars($photo['caption']) ?></p>
                <p class="author">By: <?= htmlspecialchars($username) ?></p>

                <div class="photo-actions">
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="like_photo_id" value="<?= $photo_id ?>" />
                        <button type="submit" class="<?= $liked ? 'like-liked' : '' ?>">
                            <i class="fa-solid fa-heart"></i> <?= $likes_count ?>
                        </button>
                    </form>

                    <form method="post" style="display:inline;">
                        <input type="hidden" name="save_photo_id" value="<?= $photo_id ?>" />
                        <button type="submit">
                            <i class="fa-solid fa-bookmark"></i> Save
                        </button>
                    </form>

                    <?php if ($isOwner): ?>
                    <form method="post" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this photo?');">
                        <input type="hidden" name="delete_photo_id" value="<?= $photo_id ?>" />
                        <button type="submit" style="color: #ff4c4c;">
                            <i class="fa-solid fa-trash"></i> Delete
                        </button>
                    </form>
                    <?php endif; ?>
                </div>

                <div class="comments-section">
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment">
                            <span class="author"><?= htmlspecialchars(getUsername($conn, $comment['user_id'])) ?>:</span> 
                            <?= htmlspecialchars($comment['comment_text']) ?>
                            <?php if ($comment['user_id'] === $user_id): ?>
                            <form method="post" style="display:inline;" onsubmit="return confirm('Do you want to delete this comment?');">
                                <input type="hidden" name="delete_comment_id" value="<?= $comment['id'] ?>" />
                                <button type="submit" class="delete-comment" title="Delete comment"><i class="fa-solid fa-xmark"></i></button>
                            </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <form class="add-comment" method="post">
                    <input type="hidden" name="photo_id" value="<?= $photo_id ?>" />
                    <textarea name="comment_text" rows="1" placeholder="Add a comment..."></textarea>
                    <button type="submit">Add</button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

</main>

<!-- Modal for full image -->
<div id="modal">
    <button class="close-modal" aria-label="Close modal">&times;</button>
    <img src="" alt="Full view of photo" />
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modal');
    const modalImg = modal.querySelector('img');
    const closeModalBtn = modal.querySelector('.close-modal');

    document.querySelectorAll('.photo-thumb').forEach(img => {
        img.addEventListener('click', () => {
            modalImg.src = 'upload/' + img.dataset.img;
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden'; // prevent scroll while modal open
        });
    });

    closeModalBtn.addEventListener('click', () => {
        modal.style.display = 'none';
        modalImg.src = '';
        document.body.style.overflow = ''; // restore scroll
    });

    // Close modal on clicking outside the image
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeModalBtn.click();
        }
    });

    // Close modal on ESC key
    document.addEventListener('keydown', e => {
        if (e.key === "Escape" && modal.style.display === 'flex') {
            closeModalBtn.click();
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
