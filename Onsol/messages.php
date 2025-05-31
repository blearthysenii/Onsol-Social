<?php
session_start();
require 'db.php';

class Chat {
    private $conn;
    private $userId;

    public function __construct($conn, $userId) {
        $this->conn = $conn;
        $this->userId = $userId;
    }

    // Merr miqtë e përdoruesit
    public function getFriends() {
        $sql = "SELECT DISTINCT u.id, u.username 
                FROM users u
                JOIN friendships f 
                  ON ( (f.user_id = ? AND u.id = f.friend_id) OR (f.friend_id = ? AND u.id = f.user_id) )
                WHERE f.status = 'accepted'";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $this->userId, $this->userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $friends = [];
        while ($row = $result->fetch_assoc()) {
            $friends[] = $row;
        }
        return $friends;
    }

    // Merr mesazhet mes përdoruesit aktual dhe një mikut të caktuar
    public function getMessagesWith($friendId) {
        $sql = "SELECT * FROM messages 
                WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
                ORDER BY created_at ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiii", $this->userId, $friendId, $friendId, $this->userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
        return $messages;
    }

    // Dërgon një mesazh
    public function sendMessage($to, $message) {
        $insertStmt = $this->conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        $insertStmt->bind_param("iis", $this->userId, $to, $message);
        return $insertStmt->execute();
    }
}

// Kontrollo nëse user është i loguar
if (!isset($_SESSION['user'])) {
    echo "You are not logged in.";
    exit;
}

$currentUserId = $_SESSION['user']['id'];

// Krijo objektin e chat-it
$chat = new Chat($conn, $currentUserId);

// Merr miqtë
$friends = $chat->getFriends();

// Proces POST për mesazhe
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $to = intval($_POST['to']);
    $message = trim($_POST['message']);

    if ($to && !empty($message)) {
        $chat->sendMessage($to, $message);
        header("Location: " . $_SERVER['PHP_SELF'] . "?with=" . $to);
        exit;
    }
}

// Merr ID-në e mikut për chat
$with = isset($_GET['with']) ? intval($_GET['with']) : 0;

// Merr mesazhet nëse kemi mik të zgjedhur
$messages = [];
if ($with) {
    $messages = $chat->getMessagesWith($with);
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="icon" href="images/logo.png" sizes="32x32" type="image/png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
    <title>My Messages</title>
    <style>
    body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #000000, #0a1a3f);
    color: #eee;
    margin: 0;
    padding: 20px;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
}
.chatapp__container {
    margin-top: 200px;
    max-width: 700px;
    width: 100%;
    background: rgba(255, 255, 255, 0.1); /* ngjyrë shumë transparente */
    border-radius: 12px;
    padding: 25px;
    display: flex;
    gap: 30px;
    flex-wrap: wrap;
    animation: fadeIn 1s ease-in-out;
    transition: all 0.9s ease-in-out;
    position: relative;
    z-index: 10;
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.2); /* kufi i lehtë si te xhami */
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2); /* pak hije për thellësi */
}


.chatapp__container:hover {
    background: linear-gradient(135deg, rgba(20, 0, 40, 0.95), rgba(0, 30, 100, 0.95));
    transform: scale(1.019);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.35);
}

.chatapp__friends-list {
    list-style: none;
    padding: 0;
    margin: 0;
    width: 250px;
    max-height: 400px;
    overflow-y: auto;
    background: rgba(46, 34, 80, 0.3);
    backdrop-filter: blur(5px);
    border-radius: 10px;
    padding-right: 10px;

    /* Scrollbar styling */
    scrollbar-width: thin;
    scrollbar-color: #222222 #000000;
}

.chatapp__friends-list::-webkit-scrollbar {
    width: 8px;
}

.chatapp__friends-list::-webkit-scrollbar-track {
    background: #000000;
}

.chatapp__friends-list::-webkit-scrollbar-thumb {
    background-color: #222222;
    border-radius: 10px;
    border: 2px solid #000000;
}

.chatapp__friend-item {
    margin: 10px 5px;
    border-radius: 12px;
    background: transparent; /* Transparent fill */
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
    transition: all 0.3s ease-in-out;
    overflow: hidden;
    position: relative;
}

.chatapp__friend-item:hover {
    transform: translateY(-2px);
    background: rgba(128, 128, 128, 0.15); /* Gri i lehtë me transparencë */
    box-shadow: 0 8px 18px rgba(0, 0, 0, 0.3);
}


.chatapp__friend-link {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 18px;
    color: #fff;
    text-decoration: none;
    font-weight: 600;
    font-size: 1rem;
    position: relative;
    z-index: 1;
    transition: color 0.2s;
}

.chatapp__friend-link i {
    font-size: 1.2rem;
    color: #d1c3ff;
    transition: transform 0.3s ease;
}

.chatapp__friend-link:hover i {
    transform: scale(1.15);
}

.chatapp__friend-link:hover {
    color: #eee5ff;
}

.chatapp__friend-item--active {
    border: 2px solid #bfaaff;
    background: linear-gradient(145deg, #876aff, #5d47d5);
    box-shadow: 0 0 10px rgba(180, 160, 255, 0.8);
}

.chatapp__chat-container {
    flex-grow: 1;
    background: #2f2f2f;
    border-radius: 12px;
    padding: 20px;
    height: 400px;
    overflow-y: auto;
    box-shadow: inset 0 0 15px rgba(100, 100, 100, 0.4);

    /* Scrollbar styling */
    scrollbar-width: thin;
    scrollbar-color: #222222 #000000;
}

.chatapp__chat-container::-webkit-scrollbar {
    width: 8px;
}

.chatapp__chat-container::-webkit-scrollbar-track {
    background: #000000;
}

.chatapp__chat-container::-webkit-scrollbar-thumb {
    background-color: #222222;
    border-radius: 10px;
    border: 2px solid #000000;
}

.chatapp__message {
    margin-bottom: 14px;
    padding: 10px 16px;
    border-radius: 20px;
    max-width: 70%;
    word-wrap: break-word;
    font-size: 1rem;
    line-height: 1.4;
    clear: both;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.chatapp__message--sent {
    background-color: #dcd9f5;
    color: #2e2e2e;
    float: right;
    box-shadow: 0 2px 8px rgba(159, 132, 245, 0.3);
}

.chatapp__message--received {
    background-color: #f0f0f0;
    color: #333;
    float: left;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
}

.chatapp__form {
    margin-top: 15px;
    width: 100%;
    display: flex;
    gap: 12px;
    justify-content: space-between;
    align-items: center;
}
.chatapp__input {
    padding: 6px 12px;
    border-radius: 12px;
    border: none;
    outline: none;
    background: rgba(255, 255, 255, 0.2);
    color: #fff;
    font-size: 16px;
    width: 240px;
    height: 36px;
    box-sizing: border-box;
}
.chatapp__input::placeholder {
    color: grey;
}

.chatapp__button {
    padding: 12px 28px;
    border-radius: 30px;
    border: none;
    background-color: #3a2a6a;
    background-image: linear-gradient(45deg, #3a2a6a 0%, #0050ff 100%);
    color: #fff;
    font-size: 20px;
    font-weight: 700;
    cursor: pointer;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    box-shadow: 0 4px 10px rgba(0, 100, 255, 0.4);
}
.chatapp__button:hover {
    transform: scale(1.01);
    box-shadow: 0 2px 10px rgba(0, 180, 255, 0.7);
}

h3 {
   background: linear-gradient(135deg, #348aff, #ff5ef7);
   -webkit-background-clip: text;
   -webkit-text-fill-color: transparent;
   text-align: center;
   margin-bottom: 20px;
}
p {
    background: linear-gradient(135deg, #348aff, #ff5ef7);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    text-align: center;
    font-style: italic;
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

    <div class="chatapp__container">

    <ul class="chatapp__friends-list">
        <?php foreach ($friends as $friend): ?>
            <li class="chatapp__friend-item">
                <a class="chatapp__friend-link" href="?with=<?= htmlspecialchars($friend['id']) ?>">
                    <i class="fa fa-user" aria-hidden="true"></i>
                    <?= htmlspecialchars($friend['username']) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>

    <?php if ($with): ?>
        <div style="flex-grow: 1; display: flex; flex-direction: column;">
            <h3>Chat with <?= htmlspecialchars(array_column($friends, 'username', 'id')[$with] ?? 'Unknown') ?></h3>
            <div class="chatapp__chat-container" id="chat-box">
                <?php foreach ($messages as $msg): ?>
                    <div class="chatapp__message <?= $msg['sender_id'] == $currentUserId ? 'chatapp__message--sent' : 'chatapp__message--received' ?>">
                        <?= htmlspecialchars($msg['message']) ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <form class="chatapp__form" method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) . '?with=' . $with ?>">
                <input class="chatapp__input" type="text" name="message" placeholder="Type your message..." required autofocus autocomplete="off" />
                <input type="hidden" name="to" value="<?= $with ?>" />
                <button class="chatapp__button" type="submit">Send</button>
            </form>
        </div>
    <?php else: ?>
        <p>Select a friend to start chatting.</p>
    <?php endif; ?>

</div>


    <script>
        const chatBox = document.getElementById('chat-box');
        if (chatBox) {
            chatBox.scrollTop = chatBox.scrollHeight;
        }

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
