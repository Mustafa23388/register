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
body{background:#f5f7fb}
.card{border:0;border-radius:18px;box-shadow:0 10px 30px rgba(0,0,0,.08)}
.brand{font-weight:800;letter-spacing:.5px}
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
</html>
