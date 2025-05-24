<?php
include 'db.php';  

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $username = $conn->real_escape_string(trim($_POST['username']));
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['user'] = $user; 
            header("Location: index.php");
            exit;
        } else {
            $errorMessage = "Incorrect password!";
        }
    } else {
        $errorMessage = "User not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Login</title>
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #000000, #0a1a3f);
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: #fff;
            overflow: hidden;
            position: relative;
        }

        .login-container {
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
        }

        .login-container:hover {
           background: linear-gradient(135deg, rgba(20, 0, 40, 0.95), rgba(0, 30, 100, 0.95));
            transform: scale(1.019);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.35);
            box-shadow: 0 6px 25px rgba(0, 180, 255, 0.7);
        }

        .login-container h2 {
            margin-bottom: 20px;
            font-size: 28px;
            color: white;
            transition: all 0.4s ease-in-out;
            background: linear-gradient(135deg, #348aff, #ff5ef7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .login-container input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: none;
            border-radius: 12px;
            outline: none;
            background-color: rgba(255, 255, 255, 0.15);
            color: #fff;
            font-size: 16px;
        }

        .login-container input::placeholder {
            color: #eee;
        }

        .login-container button {
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            border: none;
            border-radius: 12px;
            background-color: #3a2a6a;
            background-image: linear-gradient(45deg, #3a2a6a 0%, #0050ff 100%);
            color: #e0e8ff;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s ease, background-color 0.3s ease;
        }

        .login-container button:hover {
            transform: scale(1.05);
            background-color: #2a1f55;
            background-image: linear-gradient(45deg, #2a1f55 0%, #0040cc 100%);
        }

        .login-container p {
            margin-top: 20px;
        }

        .login-container a {
            color: #fff;
            text-decoration: underline;
        }

        .login-message {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%) translateY(-20px);
            z-index: 100000;
            background: linear-gradient(135deg, rgba(20, 10, 40, 0.9), rgba(0, 40, 120, 0.9));
            padding: 15px 25px;
            border-radius: 15px;
            color: #a0eaff;
            font-size: 18px;
            max-width: 500px;
            box-shadow: 0 4px 20px rgba(0, 100, 255, 0.4);
            text-align: center;
            margin: 0;
            user-select: none;
            cursor: default;
            opacity: 0;
            animation: slideDownFadeIn 0.7s forwards ease-in-out;
            transition: transform 0.3s ease, box-shadow 0.3s ease, color 0.3s ease, background-color 0.3s ease;
        }

        .login-message:hover {
            background: linear-gradient(135deg, rgba(30, 15, 60, 1), rgba(0, 50, 180, 1));
            box-shadow: 0 6px 25px rgba(0, 180, 255, 0.7);
            transform: translateX(-50%) scale(1.03);
            color: #00ffff;
        }.signup-text {
  margin-top: 25px;
  font-size: 16px;
  color: #ddd;
  font-weight: 500;
  user-select: none;
}

.signup-link {
  color: #82aaff;
  font-weight: 700;
  text-decoration: none;
  background: linear-gradient(135deg, #348aff, #ff5ef7);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  transition: all 0.3s ease;
  cursor: pointer;
}

.signup-link:hover,
.signup-link:focus {
  text-shadow: 0 0 8px #ff61a6, 0 0 12px #82aaff;
  transform: scale(1.05);
  outline: none;
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
    </style>
</head>
<body>

<?php
if (isset($errorMessage)) {
    echo '<div class="login-message error">' . htmlspecialchars($errorMessage) . '</div>';
} elseif ($_SERVER["REQUEST_METHOD"] != "POST") {
   echo '<div class="login-message">Say hi to Onsol, your space for innovation!</div>';

}
?>

<div class="login-container">
    <h2>Login!</h2>
    <form action="login.php" method="POST" autocomplete="off">
        <input type="text" name="username" placeholder="Username" required />
        <input type="password" name="password" placeholder="Password" required />
        <button type="submit">Login</button>
    </form>
    <p class="signup-text">
  Don't have an account? 
  <a href="register.php" class="signup-link">Create one!</a>
</p>
</div>

</body>
</html>
