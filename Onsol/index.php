<?php
session_start();
$redirect_url = 'login.php';
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    $redirect_url = 'index.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Onsol - Welcome</title>
  <link rel="icon" href="images/logo.png" sizes="32x32" type="image/png" />
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Orbitron', sans-serif;
    }

    body {
      background: linear-gradient(135deg, #000000, #0a1a3f);
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      overflow: hidden;
      position: relative;
      color: white;
    }

    .logo {
      display: flex;
      gap: 15px;
      perspective: 800px;
    }

    .letter {
      font-size: 100px;
      font-weight: 700;
      color: transparent;
      background: linear-gradient(90deg, #00f2ff, #ff00d4);
      -webkit-background-clip: text;
      animation: flipIn 1s forwards;
      transform: rotateY(90deg);
      opacity: 0;
    }

    .letter:nth-child(1) { animation-delay: 0.2s; }
    .letter:nth-child(2) { animation-delay: 0.4s; }
    .letter:nth-child(3) { animation-delay: 0.6s; }
    .letter:nth-child(4) { animation-delay: 0.8s; }
    .letter:nth-child(5) { animation-delay: 1s; }

    @keyframes flipIn {
      to {
        transform: rotateY(0deg);
        opacity: 1;
      }
    }

    canvas {
      position: absolute;
      top: 0;
      left: 0;
      z-index: -1;
    }
  </style>
</head>
<body>

<canvas id="particles"></canvas>

<div class="logo" id="logo">
  <span class="letter">O</span>
  <span class="letter">N</span>
  <span class="letter">S</span>
  <span class="letter">O</span>
  <span class="letter">L</span>
</div>

<script>
// Particle Background
const canvas = document.getElementById('particles');
const ctx = canvas.getContext('2d');
let particles = [];

canvas.width = window.innerWidth;
canvas.height = window.innerHeight;

window.onresize = () => {
  canvas.width = window.innerWidth;
  canvas.height = window.innerHeight;
};

class Particle {
  constructor() {
    this.x = Math.random() * canvas.width;
    this.y = Math.random() * canvas.height;
    this.radius = Math.random() * 1.5;
    this.dx = (Math.random() - 0.5) * 0.7;
    this.dy = (Math.random() - 0.5) * 0.7;
    this.opacity = Math.random();
  }

  draw() {
    ctx.beginPath();
    ctx.fillStyle = `rgba(0, 255, 255, ${this.opacity})`;
    ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
    ctx.fill();
  }

  update() {
    this.x += this.dx;
    this.y += this.dy;

    if (this.x < 0 || this.x > canvas.width) this.dx *= -1;
    if (this.y < 0 || this.y > canvas.height) this.dy *= -1;

    this.draw();
  }
}

function initParticles(count) {
  for (let i = 0; i < count; i++) {
    particles.push(new Particle());
  }
}

function animateParticles() {
  ctx.clearRect(0, 0, canvas.width, canvas.height);
  particles.forEach(p => p.update());
  requestAnimationFrame(animateParticles);
}

initParticles(200);
animateParticles();

// Redirect after animation
setTimeout(() => {
  window.location.href = "<?= $redirect_url ?>";
}, 4000);
</script>

</body>
</html>
