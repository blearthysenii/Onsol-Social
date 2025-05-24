<?php
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

                $uploadDir = 'uploads/';
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
    <title>Upload Photo - Onsol</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #000000, #0a1a3f);
            color: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }
        .upload-container {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(20px);
            padding: 30px;
            border-radius: 20px;
            width: 350px;
            box-shadow: 0 0 15px rgba(0,80,255,0.7);
        }
        h2 {
            background: linear-gradient(135deg, #348aff, #ff5ef7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-align: center;
            margin-bottom: 20px;
        }
        input[type="file"],
        input[type="text"],
        button {
            width: 100%;
            padding: 12px;
            margin-top: 15px;
            border-radius: 12px;
            border: none;
            font-size: 16px;
            outline: none;
        }
        input[type="file"] {
            background-color: rgba(255,255,255,0.15);
            color: #fff;
        }
        input[type="text"] {
            background-color: rgba(255,255,255,0.15);
            color: #fff;
        }
        button {
            background-image: linear-gradient(45deg, #3a2a6a 0%, #0050ff 100%);
            color: white;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0,100,255,0.4);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        button:hover {
            transform: scale(1.03);
            box-shadow: 0 6px 15px rgba(0,180,255,0.7);
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
            display: block;
            margin-top: 25px;
            text-align: center;
            color: #aaaaff;
            text-decoration: underline;
        }
    </style>
</head>
<body>

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
    <a href="index.php" class="back-link">← Back to feed</a>
</div>

</body>
</html>
