<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user']['id'])) {
    echo "<p style='color:red; font-weight:bold;'>You are not logged in!</p>";
    exit;
}

$current_user_id = $_SESSION['user']['id'];

// Fetch pending friend requests
$sql_requests = "SELECT fr.id, fr.sender_id, u.username 
        FROM friend_requests fr
        JOIN users u ON fr.sender_id = u.id
        WHERE fr.receiver_id = ? AND fr.status = 'pending'";

$stmt = $conn->prepare($sql_requests);
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$result_requests = $stmt->get_result();

// Fetch accepted friends
$sql_friends = "SELECT u.id, u.username 
                FROM friendships f
                JOIN users u ON f.friend_id = u.id
                WHERE f.user_id = ? AND f.status = 'accepted'

                UNION

                SELECT u.id, u.username
                FROM friendships f
                JOIN users u ON f.user_id = u.id
                WHERE f.friend_id = ? AND f.status = 'accepted'";

$stmt_friends = $conn->prepare($sql_friends);
$stmt_friends->bind_param("ii", $current_user_id, $current_user_id);
$stmt_friends->execute();
$result_friends = $stmt_friends->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <link rel="icon" href="images/logo.png" sizes="32x32" type="image/png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
    <title>Friend Requests & Friends</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #000000, #0a1a3f);
            color: #fff;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        .page-content {
            display: flex;
            justify-content: center;
            margin-left: 100px;
            margin-right: 100px;
            padding-top: 60px;
        }

        .wrapper {
            display: flex;
            gap: 60px;
            width: 100%;
            max-width: 1200px;
        }

        .box {
            flex: 1;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 40px;
            padding: 60px;
            animation: fadeIn 1s ease-in-out;
            transition: all 0.9s ease-in-out;
            position: relative;
            z-index: 10;
             backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2); 
}



        .box:hover {
            background: linear-gradient(135deg, rgba(20, 0, 40, 0.95), rgba(0, 30, 100, 0.95));
            transform: scale(1.019);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.35);
        }

        h2 {
            color: #fff;
            margin-top: 0;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 700;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #348aff, #ff5ef7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .friend-request, .friend {
            background: #fff;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 12px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #333;
        }

        .friend-request .username, .friend .username {
            font-weight: 600;
            font-size: 16px;
        }

        .friend-request .actions a {
            text-decoration: none;
            margin-left: 10px;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 14px;
            transition: background-color 0.3s ease;
            color: white;
        }

        .friend-request .actions a.accept {
            background-color: #28a745;
        }

        .friend-request .actions a.accept:hover {
            background-color: #218838;
        }

        .friend-request .actions a.reject {
            background-color: #dc3545;
        }

        .friend-request .actions a.reject:hover {
            background-color: #c82333;
        }

        .no-requests, .no-friends {
            text-align: center;
            color: #bbb;
            font-size: 16px;
            margin-top: 20px;
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

    <div class="page-content">
        <div class="wrapper">
            <!-- Friend Requests -->
            <div class="box">
                <h2>Pending Friend Requests</h2>
                <?php
                if ($result_requests->num_rows === 0) {
                    echo "<p class='no-requests'>No new friend requests.</p>";
                } else {
                    while ($req = $result_requests->fetch_assoc()) {
                        $request_id = $req['id'];
                        $username = htmlspecialchars($req['username']);
                        echo "<div class='friend-request'>
                                <div class='username'>$username</div>
                                <div class='actions'>
                                    <a class='accept' href='handle_request.php?request_id=$request_id&action=accept'>Accept</a>
                                    <a class='reject' href='handle_request.php?request_id=$request_id&action=reject'>Reject</a>
                                </div>
                              </div>";
                    }
                }
                ?>
            </div>

            <!-- Friends List -->
            <div class="box">
                <h2>Your Friends</h2>
                <?php
                if ($result_friends->num_rows === 0) {
                    echo "<p class='no-friends'>You don’t have any friends yet.</p>";
                } else {
                    while ($friend = $result_friends->fetch_assoc()) {
                        $friend_username = htmlspecialchars($friend['username']);
                        echo "<div class='friend'>
                                <div class='username'>$friend_username</div>
                              </div>";
                    }
                }
                ?>
            </div>
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
