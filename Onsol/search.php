<?php
if (isset($_GET['query'])) {
    $query = trim($_GET['query']);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <link rel="icon" href="images/logo.png" sizes="32x32" type="image/png" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
        <title>Search Results</title>
        <style>
            body {
                font-family: 'Poppins', sans-serif;
                background: linear-gradient(135deg, #000000, #0a1a3f);
                color: #fff;
                margin: 0;
                padding: 40px;
                display: flex;
                justify-content: center;
                align-items: flex-start;
                min-height: 100vh;
            }

            .results-container {
                background: rgba(255, 255, 255, 0.05);
                padding: 40px;
                border-radius: 20px;
                max-width: 700px;
                width: 100%;
                box-shadow: 0 8px 24px rgba(0,0,0,0.25);
                backdrop-filter: blur(15px);
            }

            h2 {
                margin-bottom: 30px;
                font-size: 24px;
                text-align: center;
                color: #a8b1ff;
            }

            ul {
                list-style: none;
                padding: 0;
                margin: 0;
            }

            li {
                background: rgba(255, 255, 255, 0.1);
                margin-bottom: 15px;
                padding: 20px;
                border-radius: 12px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                flex-wrap: wrap;
            }

            .username {
                font-size: 18px;
                font-weight: 600;
                color: #fff;
                margin-bottom: 8px;
            }

            .actions button,
            .actions a {
                display: inline-block;
                margin-left: 10px;
                margin-top: 5px;
                padding: 8px 16px;
                border-radius: 20px;
                font-size: 14px;
                text-decoration: none;
                color: #fff;
                transition: background-color 0.3s ease;
                border: none;
                cursor: pointer;
            }

            .view-profile {
                background-color: #007bff;
            }

            .view-profile:hover {
                background-color: #0056b3;
            }

            .add-friend {
                background-color: #28a745;
            }

            .add-friend:hover {
                background-color: #1e7e34;
            }

            .add-friend:disabled {
                background-color: #6c757d;
                cursor: default;
            }

            .no-results {
                text-align: center;
                color: #ccc;
                margin-top: 20px;
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

        <div class="results-container">
            <a href="home.php" class="back-link">
  <i class="fas fa-arrow-left"></i> Back to Home!
</a>
            <h2>Search results for: <em><?= htmlspecialchars($query) ?></em></h2>

            <?php
            $conn = new mysqli("localhost", "root", "", "onsol_db1");
            if ($conn->connect_error) {
                die("<p class='no-results'>Connection failed: " . $conn->connect_error . "</p>");
            }

            $stmt = $conn->prepare("SELECT * FROM users WHERE username LIKE CONCAT('%', ?, '%')");
            $stmt->bind_param("s", $query);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                echo "<ul>";
                while ($row = $result->fetch_assoc()) {
                    $userId = $row['id'];
                    $username = htmlspecialchars($row['username']);
                    echo "<li>
                            <div class='username'>$username</div>
                            <div class='actions'>
                                <a class='view-profile' href='user-profile.php?user_id=$userId'>View Profile</a>
                                <button class='add-friend' data-friend-id='$userId'>Add Friend</button>
                            </div>
                          </li>";
                }
                echo "</ul>";
            } else {
                echo "<p class='no-results'>No users found.</p>";
            }

            $stmt->close();
            $conn->close();
            ?>
        </div>

        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const buttons = document.querySelectorAll(".add-friend");

                buttons.forEach(button => {
                    button.addEventListener("click", function () {
                        const friendId = this.getAttribute("data-friend-id");

                        fetch("send_request.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: `friend_id=${friendId}`
                        })
                        .then(response => response.text())
                        .then(data => {
                            this.textContent = "Requested";
                            this.disabled = true;
                            this.style.backgroundColor = "#6c757d";
                        })
                        .catch(error => {
                            console.error("Error:", error);
                            alert("Something went wrong!");
                        });
                    });
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
    <?php
} else {
    echo "<p style='color: #ccc; font-family: Poppins, sans-serif; text-align:center; padding-top:50px;'>No search term entered.</p>";
}
?>
