<?php
// upload.php
session_start();
include 'db.php';


if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = $_FILES['photo']['type'];

        if (in_array($fileType, $allowedTypes)) {
            $maxFileSize = 5 * 1024 * 1024; 
            if ($_FILES['photo']['size'] <= $maxFileSize) {

                $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                $newFileName = uniqid('photo_', true) . '.' . $ext;

               
                $uploadDir = __DIR__ . '/upload/';
                
                
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $uploadPath = $uploadDir . $newFileName;

                if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadPath)) {

                    $caption = trim($_POST['caption'] ?? '');

                    $stmt = $conn->prepare("INSERT INTO photos (user_id, image_path, caption) VALUES (?, ?, ?)");
                    $stmt->bind_param("iss", $_SESSION['user']['id'], $newFileName, $caption);

                    if ($stmt->execute()) {
                        $success = "Photo uploaded successfully!";
                    } else {
                        $error = "Database error: " . $stmt->error;
                        unlink($uploadPath); 
                    }

                    $stmt->close();
                } else {
                    $error = "Failed to move uploaded file.";
                }
            } else {
                $error = "File size exceeds 5MB limit.";
            }
        } else {
            $error = "Invalid file type. Only JPG, PNG and GIF allowed.";
        }
    } else {
        $error = "No file uploaded or upload error.";
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
    <title>Upload Photo</title>
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
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            justify-content: center;
        }
        .upload-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            max-width: 400px;
            width: 100%;
            animation: fadeIn 1s ease-in-out;
            transition: all 0.9s ease-in-out;
            z-index: 10;
            position: relative;
            filter: drop-shadow(0 10px 25px rgba(0, 0, 0, 0.7));
 backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.2); /* kufi i lehtë si te xhami */
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2); /* pak hije për thellësi */
}


        .upload-container:hover {
           background: linear-gradient(135deg, rgba(20, 0, 40, 0.95), rgba(0, 30, 100, 0.95));
            transform: scale(1.019);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.35);
        }
        h2 {
            background: linear-gradient(135deg, #348aff, #ff5ef7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-align: center;
            margin-bottom: 20px;
        }
        .upload-container input[type="file"]{
            width: 95%;
    padding: 12px;
    margin: 25px 0 15px 0;
    border-radius: 12px;
    border: none;
    outline: none;
     display: block;       
    background-color: rgba(255, 255, 255, 0.15);
    color: #fff;
    font-size: 16px;
    direction: ltr !important;
    text-align: left !important;
    float: none !important;
}
.upload-container input[type="file"]::placeholder {
            color: rgba(128, 128, 128, 0.7); 
        }

.upload-container input[type="text"] {
    width: 95%;
    padding: 12.5px;
    margin: 25px 0 15px 0;
    border-radius: 12px;
    border: none;
    outline: none;
     display: block;       
    background-color: rgba(255, 255, 255, 0.15);
    color: #fff;
    font-size: 16px;
}

.upload-container input[type="text",]::placeholder {
    color: rgba(200, 200, 200, 0.7);
}

        button {
           width: 100%;
            padding: 15px 0;
            margin-top: 15px;
            border: none;
            border-radius: 25px;
            background-color: #3a2a6a;
            background-image: linear-gradient(45deg, #3a2a6a 0%, #0050ff 100%);
            color: #fff;
            font-size: 20px;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 100, 255, 0.4);
        }.button:hover {
            transform: scale(1.01);
            box-shadow: 0 2px 10px rgba(0, 180, 255, 0.7);
        }
        .message {
            margin-top: 15px;
            padding: 10px;
            border-radius: 12px;
            text-align: center;
        }
        .success {
            background-color: #2d6a2d;
            color: #b9f6ca;
        }
        .error {
            background-color: #a42d2d;
            color: #f6b9b9;
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


<div class="upload-container">
    <h2>Upload Photo</h2>
    <?php if ($error): ?>
        <div class="message error"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
        <div class="message success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form action="upload.php" method="POST" enctype="multipart/form-data" autocomplete="off">
        <input type="file" name="photo" accept="image/*" required />
        <input type="text" name="caption" placeholder="Write a caption (optional)" maxlength="255" />
        <button type="submit">Upload</button>
    </form>
    <a href="home.php" class="back-link">
  <i class="fas fa-arrow-left"></i> Back to feed
</a>

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
