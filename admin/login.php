<?php
session_start();

// --- Admin creds (change these) ---
$ADMIN_USER = 'admin';
$ADMIN_PASS = 'pass@123'; // change me

// If already logged in, go to ticket page
if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: ticket_generator.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = trim($_POST['password'] ?? '');
    if ($u === $ADMIN_USER && $p === $ADMIN_PASS) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user'] = $u;
        header('Location: ticket_generator.php');
        exit;
    } else {
        $error = 'Invalid username or password';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Admin Login — Ticket Generator</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #fff5e6 0%, #ffe4b5 25%, #ffd700 50%, #ffe4b5 75%, #fff5e6 100%);
      background-size: 400% 400%;
      animation: gradientShift 15s ease infinite;
      min-height: 100vh;
      padding: 0;
      margin: 0;
      position: relative;
      overflow-x: hidden;
    }

    @keyframes gradientShift {
      0%, 100% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
    }

    /* Elegant floating stars */
    .star {
      position: fixed;
      font-size: 20px;
      animation: starFloat ease-in-out infinite;
      z-index: 1;
      opacity: 0.6;
    }

    @keyframes starFloat {
      0%, 100% {
        transform: translateY(0) rotate(0deg);
      }
      50% {
        transform: translateY(-30px) rotate(180deg);
      }
    }

    /* Celebration ribbons */
    .celebration-ribbon {
      position: fixed;
      width: 120px;
      height: 8px;
      z-index: 1;
      opacity: 0.3;
      animation: ribbonWave 4s ease-in-out infinite;
    }

    @keyframes ribbonWave {
      0%, 100% {
        transform: translateX(0) rotate(0deg);
      }
      50% {
        transform: translateX(20px) rotate(5deg);
      }
    }

    /* Anniversary badge */
    .jubilee-badge {
      position: fixed;
      top: 25px;
      right: 35px;
      z-index: 100;
    }

    .badge-circle {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 90px;
      height: 90px;
      background: linear-gradient(135deg, #ffd700, #ffed4e, #ffd700);
      border-radius: 50%;
      border: 5px solid #fff;
      box-shadow: 0 8px 30px rgba(255, 215, 0, 0.5),
                  inset 0 2px 10px rgba(255, 255, 255, 0.5);
      position: relative;
      animation: badgeSpin 20s linear infinite;
    }

    @keyframes badgeSpin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    .badge-number {
      font-size: 36px;
      font-weight: 900;
      color: #8b4513;
      text-shadow: 1px 1px 2px rgba(255, 255, 255, 0.8);
      font-family: 'Arial Black', sans-serif;
      animation: none;
      transform: rotate(0deg);
    }

    .badge-circle:hover {
      animation-play-state: paused;
    }

    .navbar {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(20px);
      border-bottom: 3px solid #ffd700;
      padding: 20px 0;
      box-shadow: 0 4px 20px rgba(255, 215, 0, 0.3);
      position: relative;
      z-index: 10;
    }

    .navbar::after {
      content: '';
      position: absolute;
      bottom: -3px;
      left: 0;
      right: 0;
      height: 3px;
      background: linear-gradient(90deg, #ffd700, #ffed4e, #ffd700);
      animation: shimmer 3s linear infinite;
      background-size: 200% 100%;
    }

    @keyframes shimmer {
      0% { background-position: -200% 0; }
      100% { background-position: 200% 0; }
    }

    .navbar .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .navbar-brand {
      color: #8b4513 !important;
      font-weight: 800;
      font-size: 22px;
      text-shadow: 1px 1px 2px rgba(255, 215, 0, 0.3);
      letter-spacing: 0.5px;
    }

    .btn {
      padding: 10px 24px;
      border: none;
      border-radius: 12px;
      font-size: 14px;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.3s ease;
      text-decoration: none;
      display: inline-block;
      position: relative;
    }

    .btn-outline-secondary {
      background: linear-gradient(135deg, #ff6b6b, #ff8787);
      color: white;
      box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
    }

    .btn-outline-secondary:hover {
      transform: translateY(-2px) scale(1.05);
      box-shadow: 0 6px 20px rgba(255, 107, 107, 0.5);
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 40px 20px;
      position: relative;
      z-index: 10;
    }

    .my-4 {
      margin-top: 1.5rem;
      margin-bottom: 1.5rem;
    }

    .mt-4 {
      margin-top: 1.5rem;
    }

    .card {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(15px);
      border: 3px solid #ffd700;
      border-radius: 25px;
      padding: 40px 35px;
      box-shadow: 0 15px 50px rgba(255, 215, 0, 0.3),
                  0 5px 15px rgba(0, 0, 0, 0.1);
      margin-bottom: 30px;
      position: relative;
    }

    .card::before {
      content: '✨';
      position: absolute;
      top: 15px;
      left: 15px;
      font-size: 24px;
      opacity: 0.5;
    }

    .card::after {
      content: '🎉';
      position: absolute;
      top: 15px;
      right: 15px;
      font-size: 24px;
      opacity: 0.5;
    }

    .p-3 {
      padding: 1rem;
    }

    .p-md-4 {
      padding: 2rem;
    }

    h1, h2, .h4, .h5 {
      color: #8b4513;
      text-shadow: 2px 2px 4px rgba(255, 215, 0, 0.3);
      margin-bottom: 20px;
      font-weight: 800;
      letter-spacing: 0.5px;
    }

    .h4 {
      font-size: 28px;
      background: linear-gradient(135deg, #8b4513, #d2691e);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .h5 {
      font-size: 22px;
      color: #d2691e;
    }

    .mb-3 {
      margin-bottom: 1rem;
    }

    .form-control {
      width: 100%;
      padding: 16px 22px;
      border: 2px solid #ffd700;
      border-radius: 15px;
      font-size: 15px;
      background: rgba(255, 255, 255, 0.98);
      color: #333;
      outline: none;
      transition: all 0.3s ease;
      box-shadow: 0 3px 10px rgba(255, 215, 0, 0.2);
    }

    .form-control:focus {
      background: white;
      border-color: #ffed4e;
      box-shadow: 0 5px 20px rgba(255, 237, 78, 0.4),
                  0 0 0 4px rgba(255, 215, 0, 0.1);
      transform: translateY(-2px);
    }

    .form-control::placeholder {
      color: #999;
      font-style: italic;
    }

    .form-control-lg {
      padding: 18px 24px;
      font-size: 16px;
      font-weight: 500;
    }

    .row {
      display: flex;
      gap: 15px;
      flex-wrap: wrap;
    }

    .col-md-6 {
      flex: 1;
      min-width: 250px;
    }

    .g-2 {
      gap: 10px;
    }

    .d-flex {
      display: flex;
    }

    .gap-2 {
      gap: 12px;
    }

    .btn-primary {
      background: linear-gradient(135deg, #4CAF50, #45a049, #4CAF50);
      background-size: 200% 200%;
      color: white;
      box-shadow: 0 6px 25px rgba(76, 175, 80, 0.4);
      text-transform: uppercase;
      letter-spacing: 1.2px;
      padding: 16px 32px;
      font-weight: 800;
      animation: btnPulse 2s ease infinite;
    }

    @keyframes btnPulse {
      0%, 100% {
        box-shadow: 0 6px 25px rgba(76, 175, 80, 0.4);
      }
      50% {
        box-shadow: 0 8px 35px rgba(76, 175, 80, 0.6);
      }
    }

    .btn-primary:hover {
      transform: translateY(-3px) scale(1.03);
      box-shadow: 0 10px 40px rgba(76, 175, 80, 0.6);
    }

    .btn-success {
      background: linear-gradient(135deg, #2196F3, #1976D2, #2196F3);
      background-size: 200% 200%;
      color: white;
      box-shadow: 0 6px 25px rgba(33, 150, 243, 0.4);
      text-transform: uppercase;
      letter-spacing: 1.2px;
      padding: 16px 32px;
      font-weight: 800;
    }

    .btn-success:hover {
      transform: translateY(-3px) scale(1.03);
      box-shadow: 0 10px 40px rgba(33, 150, 243, 0.6);
    }

    .btn-lg {
      padding: 16px 32px;
      font-size: 16px;
    }

    .btn-sm {
      padding: 8px 18px;
      font-size: 13px;
    }

    .alert {
      padding: 18px 24px;
      border-radius: 15px;
      margin-top: 20px;
      font-weight: 600;
      text-align: center;
    }

    .alert-warning {
      background: rgba(255, 152, 0, 0.15);
      border: 2px solid #ff9800;
      color: #e65100;
      box-shadow: 0 4px 15px rgba(255, 152, 0, 0.2);
    }

    .small {
      font-size: 14px;
    }

    .mt-3 {
      margin-top: 1rem;
    }

    .ticket-preview iframe {
      width: 100%;
      min-height: 560px;
      border: 4px solid #ffd700;
      border-radius: 20px;
      box-shadow: 0 10px 40px rgba(255, 215, 0, 0.3),
                  0 5px 15px rgba(0, 0, 0, 0.1);
      background: #fff;
    }

    .d-none {
      display: none;
    }

    .navbar-expand-lg {
      display: block;
    }

    .bg-white {
      background: rgba(255, 255, 255, 0.95) !important;
      backdrop-filter: blur(20px);
    }

    .border-bottom {
      border-bottom: 3px solid #ffd700 !important;
    }

    .shadow-sm {
      box-shadow: 0 4px 20px rgba(255, 215, 0, 0.3) !important;
    }

    @media (max-width: 768px) {
      .jubilee-badge {
        top: 15px;
        right: 15px;
      }

      .badge-circle {
        width: 70px;
        height: 70px;
      }

      .badge-number {
        font-size: 28px;
      }

      .container {
        padding: 20px 15px;
      }

      .card {
        padding: 30px 20px;
      }

      .h4 {
        font-size: 22px;
      }

      .h5 {
        font-size: 18px;
      }

      .col-md-6 {
        min-width: 100%;
      }

      .btn-lg {
        padding: 14px 24px;
        font-size: 14px;
      }

      .navbar-brand {
        font-size: 16px;
      }

      .p-md-4 {
        padding: 1.5rem;
      }
    }
  </style>


</head>
<body class="d-flex align-items-center" style="min-height:100vh">
<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="card p-4 p-md-5">
        <h3 class="brand mb-4 text-center">Ticket Generator — Admin</h3>
        <?php if($error): ?>
          <div class="alert alert-danger small py-2"><?=$error?></div>
        <?php endif; ?>
        <form method="post" autocomplete="off">
          <div class="mb-3">
            <label class="form-label">Username</label>
            <input name="username" class="form-control form-control-lg" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input name="password" type="password" class="form-control form-control-lg" required>
          </div>
          <button class="btn btn-primary btn-lg w-100">Login</button>
        </form>
        <p class="text-center text-muted small mt-3 mb-0">© <?=date('Y')?> Your School</p>
      </div>
    </div>
  </div>
</div>
</body>
<script>
  // Floating celebration stars
  function createStars() {
    const stars = ['⭐', '✨', '🌟', '💫'];
    const positions = [
      { left: '5%', top: '20%' },
      { left: '15%', top: '60%' },
      { right: '10%', top: '30%' },
      { right: '20%', top: '70%' },
      { left: '50%', top: '15%' },
      { left: '80%', top: '50%' }
    ];

    positions.forEach((pos, index) => {
      const star = document.createElement('div');
      star.className = 'star';
      star.textContent = stars[index % stars.length];
      Object.assign(star.style, pos);
      star.style.position = 'fixed';
      star.style.animationDuration = (Math.random() * 3 + 3) + 's';
      star.style.animationDelay = (index * 0.5) + 's';
      document.body.appendChild(star);
    });
  }

  // Celebration ribbons
  function createRibbons() {
    const colors = [
      'linear-gradient(90deg, #ff6b6b, #ff8787)',
      'linear-gradient(90deg, #4CAF50, #66bb6a)',
      'linear-gradient(90deg, #2196F3, #42a5f5)',
      'linear-gradient(90deg, #ffd700, #ffed4e)'
    ];

    for (let i = 0; i < 4; i++) {
      const ribbon = document.createElement('div');
      ribbon.className = 'celebration-ribbon';
      ribbon.style.background = colors[i];
      ribbon.style.top = (20 + i * 20) + '%';
      ribbon.style.left = (i % 2 === 0) ? '-60px' : 'auto';
      ribbon.style.right = (i % 2 === 1) ? '-60px' : 'auto';
      ribbon.style.animationDelay = (i * 0.5) + 's';
      document.body.appendChild(ribbon);
    }
  }

  // Anniversary badge
  function createJubileeBadge() {
    const badge = document.createElement('div');
    badge.className = 'jubilee-badge';
    badge.innerHTML = '<div class="badge-circle"><div class="badge-number">25</div></div>';
    badge.title = '25th Silver Jubilee Anniversary - Celebrating Excellence';
    document.body.appendChild(badge);
  }

  // Initialize all effects
  createStars();
  createRibbons();
  createJubileeBadge();
</script>


</html>
