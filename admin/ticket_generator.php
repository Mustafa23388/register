<?php

/*******************************************************
 * Admin — Ticket Generator (Browser + PDF identical)
 * Requirements:
 *   - PHP 8+
 *   - DOMPDF installed in ../dompdf/  (autoload.inc.php)
 *   - includes/db.php defines $conn = new mysqli(...)
 *******************************************************/
session_start();
if (empty($_SESSION['admin_logged_in'])) {
  header('Location: login.php');
  exit;
}

include('../includes/db.php'); // must define $conn = new mysqli(...)

// ---------------------------
// Helpers
// ---------------------------
function h($v)
{
  return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}
function val($arr, $key, $default = '')
{
  return isset($arr[$key]) ? $arr[$key] : $default;
}

// ---------------------------
// Load student by receipt id
// ---------------------------
$receipt_id = trim($_GET['receipt_id'] ?? $_POST['receipt_id'] ?? '');
$student    = null;
$msg        = '';

if ($receipt_id !== '') {
  $sql = "SELECT id, name, father_name, marital_status, year_of_passing_matric, profession,
                   contact_number, email, receipt_id, pass_status
            FROM students
            WHERE receipt_id = ?";
  if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, 's', $receipt_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $student = mysqli_fetch_assoc($res) ?: null;
    mysqli_stmt_close($stmt);
  }
  if (!$student) {
    $msg = "No student found for Receipt ID: " . h($receipt_id);
  }
}

// -----------------------------------------------------
// PDF generation — uses same HTML as browser preview
// -----------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'pdf' && $receipt_id && $student) {
  require_once __DIR__ . '/../dompdf/autoload.inc.php';

  $dompdf = new \Dompdf\Dompdf([
    'isRemoteEnabled'      => true,  // allow QR image
    'isHtml5ParserEnabled' => true
  ]);

  $ticketHtml = build_ticket_html($student, /*forPdf*/ true);

  // A5 landscape matches your layout proportions best
  $dompdf->loadHtml($ticketHtml, 'UTF-8');
  $dompdf->setPaper('A5', 'landscape');
  $dompdf->render();

  $pdfName = 'Ticket_' . preg_replace('/[^A-Za-z0-9_-]/', '', $student['receipt_id']) . '.pdf';
  $dompdf->stream($pdfName, ['Attachment' => true]);
  exit;
}

// -------------------------------------------------------------------
// HTML builder — SAME markup for both Browser preview and DOMPDF PDF
// Notes for DOMPDF:
//  - Avoid CSS flex/grid. We use table/table-cell which DOMPDF renders
//  - Avoid web fonts; we keep Arial/Helvetica stack
//  - Keep borders/paddings numeric, no complex box-shadows/filters
// -------------------------------------------------------------------
function build_ticket_html(array $s, bool $forPdf = false)
{
  $ps = strtolower(val($s, 'pass_status', 'not_purchased'));
  $colorMap = [
    'collected'     => '#16a34a',  // green
    'purchased'     => '#f59e0b',  // amber
    'not_purchased' => '#ef4444',  // red
  ];
  $badgeColor = isset($colorMap[$ps]) ? $colorMap[$ps] : '#ef4444';
  $badgeText  = strtoupper($ps ?: 'NOT_PURCHASED');

  // Event copy (can be replaced by DB later)
  $EVENT_TITLE = "25th Silver Jubilee Anniversary";
  $EVENT_DATE  = "To Be Announced";
  $EVENT_TIME  = "To Be Announced";
  $EVENT_VENUE = "To Be Announced";

  // Local QR generation using phpqrcode library
  require_once __DIR__ . '/phpqrcode/qrlib.php';

  // Make a temporary file for QR
  $tmpDir = sys_get_temp_dir();
  $filePath = $tmpDir . '/qr_' . $s['receipt_id'] . '.png';

  $qrText = "TICKET|RID={$s['receipt_id']}|NAME={$s['name']}|CONTACT={$s['contact_number']}";
  QRcode::png($qrText, $filePath, QR_ECLEVEL_L, 5);

  // Convert to base64 for inline display
  $qrContent = file_get_contents($filePath);
  $qrBase64 = 'data:image/png;base64,' . base64_encode($qrContent);


  // Brand
  $brand = "#0097e6";

  // Build safe HTML/CSS (identical in browser and DOMPDF)
  $html = '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Event Ticket</title>
<meta name="viewport" content="width=device-width, initial-scale=1">';

  // Important: keep page margins tight in PDF, but normal in browser
  $html .= ($forPdf
    ? '<style>@page{margin:0;}</style>'
    : ''
  );

  $html .= '<style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: linear-gradient(135deg, #87CEEB 0%, #6DB9E8 50%, #87CEEB 100%);
      min-height: 100vh;
      padding: 0;
      margin: 0;
      position: relative;
      overflow-x: hidden;
    }

    .confetti {
      position: fixed;
      width: 10px;
      height: 10px;
      top: -10px;
      z-index: 1;
      animation: fall linear infinite;
    }

    @keyframes fall {
      to {
        transform: translateY(100vh) rotate(360deg);
      }
    }

    .balloon {
      position: fixed;
      width: 50px;
      height: 60px;
      border-radius: 50% 50% 50% 50% / 60% 60% 40% 40%;
      animation: float 6s ease-in-out infinite;
      z-index: 1;
    }

    .balloon::after {
      content: '';
      position: absolute;
      bottom: -20px;
      left: 50%;
      width: 2px;
      height: 20px;
      background: rgba(255, 255, 255, 0.5);
      transform: translateX(-50%);
    }

    @keyframes float {
      0%, 100% {
        transform: translateY(0) rotate(-5deg);
      }
      50% {
        transform: translateY(-20px) rotate(5deg);
      }
    }

    .navbar {
      background: rgba(255, 255, 255, 0.25);
      backdrop-filter: blur(10px);
      border-bottom: 2px solid rgba(255, 255, 255, 0.4);
      padding: 15px 0;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      position: relative;
      z-index: 10;
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
      color: white !important;
      font-weight: 700;
      font-size: 20px;
      text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
    }

    .btn {
      padding: 8px 20px;
      border: none;
      border-radius: 10px;
      font-size: 14px;
      font-weight: bold;
      cursor: pointer;
      transition: all 0.3s ease;
      text-decoration: none;
      display: inline-block;
    }

    .btn-outline-secondary {
      background: rgba(255, 255, 255, 0.9);
      color: #555;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .btn-outline-secondary:hover {
      background: white;
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
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
      background: rgba(255, 255, 255, 0.25);
      backdrop-filter: blur(10px);
      border: 2px solid rgba(255, 255, 255, 0.4);
      border-radius: 25px;
      padding: 35px 30px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
      margin-bottom: 30px;
    }

    .p-3 {
      padding: 1rem;
    }

    .p-md-4 {
      padding: 2rem;
    }

    h1, h2, .h4, .h5 {
      color: white;
      text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
      margin-bottom: 20px;
    }

    .h4 {
      font-size: 24px;
    }

    .h5 {
      font-size: 20px;
    }

    .mb-3 {
      margin-bottom: 1rem;
    }

    .form-control {
      width: 100%;
      padding: 14px 20px;
      border: none;
      border-radius: 12px;
      font-size: 15px;
      background: rgba(255, 255, 255, 0.9);
      color: #555;
      outline: none;
      transition: all 0.3s ease;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .form-control:focus {
      background: white;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
      transform: translateY(-2px);
    }

    .form-control::placeholder {
      color: #888;
    }

    .form-control-lg {
      padding: 16px 22px;
      font-size: 16px;
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
      gap: 10px;
    }

    .btn-primary {
      background: linear-gradient(135deg, #2196F3, #1976D2);
      color: white;
      box-shadow: 0 8px 25px rgba(33, 150, 243, 0.4);
      text-transform: uppercase;
      letter-spacing: 1px;
      padding: 14px 24px;
    }

    .btn-primary:hover {
      transform: translateY(-3px);
      box-shadow: 0 12px 35px rgba(33, 150, 243, 0.5);
    }

    .btn-success {
      background: linear-gradient(135deg, #4CAF50, #388E3C);
      color: white;
      box-shadow: 0 8px 25px rgba(76, 175, 80, 0.4);
      text-transform: uppercase;
      letter-spacing: 1px;
      padding: 14px 24px;
    }

    .btn-success:hover {
      transform: translateY(-3px);
      box-shadow: 0 12px 35px rgba(76, 175, 80, 0.5);
    }

    .btn-lg {
      padding: 14px 24px;
      font-size: 16px;
    }

    .btn-sm {
      padding: 6px 14px;
      font-size: 13px;
    }

    .alert {
      padding: 15px 20px;
      border-radius: 12px;
      margin-top: 20px;
      font-weight: 600;
      text-align: center;
    }

    .alert-warning {
      background: rgba(255, 193, 7, 0.3);
      border: 2px solid #FFC107;
      color: #856404;
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
      border: 0;
      border-radius: 14px;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
      background: #fff;
    }

    .d-none {
      display: none;
    }

    .navbar-expand-lg {
      display: block;
    }

    .bg-white {
      background: rgba(255, 255, 255, 0.25) !important;
      backdrop-filter: blur(10px);
    }

    .border-bottom {
      border-bottom: 2px solid rgba(255, 255, 255, 0.4) !important;
    }

    .shadow-sm {
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1) !important;
    }

    @media (max-width: 768px) {
      .container {
        padding: 20px 15px;
      }

      .card {
        padding: 25px 20px;
      }

      .h4 {
        font-size: 20px;
      }

      .h5 {
        font-size: 18px;
      }

      .col-md-6 {
        min-width: 100%;
      }

      .btn-lg {
        padding: 12px 18px;
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
<body>
<div class="wrapper">
  <div class="ticket">
    <div class="header">
      <h1>' . h($EVENT_TITLE) . '</h1>
      <small>Official Entry Ticket</small>
      <div class="badge">' . h($badgeText) . '</div>
      <div class="meta">
        Receipt ID: <b>' . h(val($s, "receipt_id")) . '</b><br>
        Issued: ' . date('d M Y') . '
      </div>
    </div>

    <div class="section">
      <div class="col left">
        <div class="label">Student Name</div>
        <div class="value">' . h(val($s, "name")) . '</div>
        <div class="divider"></div>

        <div class="label">Father\'s Name</div>
        <div class="value">' . h(val($s, "father_name")) . '</div>
        <div class="divider"></div>

        <div class="label">Contact</div>
        <div class="value">' . h(val($s, "contact_number")) . '</div>
        <div class="divider"></div>

        <div class="label">Email</div>
        <div class="value">' . h(val($s, "email")) . '</div>
        <div class="divider"></div>

        <div class="label">Marital Status</div>
        <div class="value">' . h(val($s, "marital_status")) . '</div>
        <div class="divider"></div>

        <div class="label">Year of Passing (Matric)</div>
        <div class="value">' . h(val($s, "year_of_passing_matric")) . '</div>
        <div class="divider"></div>

        <div class="label">Profession</div>
        <div class="value">' . h(val($s, "profession")) . '</div>
      </div>

      <div class="col right">
<img src="' . $qrBase64 . '" alt="QR" class="qr">
        <div class="tip">Scan at gate for verification</div>
        <div class="divider"></div>

        <div class="label">Event Date</div>
        <div class="value">' . h($EVENT_DATE) . '</div>
        <div class="divider"></div>

        <div class="label">Event Time</div>
        <div class="value">' . h($EVENT_TIME) . '</div>
        <div class="divider"></div>

        <div class="label">Venue</div>
        <div class="value">' . h($EVENT_VENUE) . '</div>
      </div>
    </div>

    <div class="footer">
      <div class="footwrap">
        <div class="leftf">
          Note: Bring a valid ID. Ticket is non-transferable. Receipt ID must match registration record.
        </div>
        <div class="rightf">© ' . date('Y') . ' Your School</div>
      </div>
    </div>
  </div>
</div>
</body>
</html>';

  return $html;
}
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Ticket Generator (Admin)</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Only for the admin UI (not used inside PDF) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: #eef3fb;
    }

    .card {
      border: 0;
      border-radius: 18px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, .08);
    }

    .navbar-brand {
      color: #0097e6 !important;
      font-weight: 700;
    }

    .ticket-preview iframe {
      width: 100%;
      min-height: 560px;
      border: 0;
      border-radius: 14px;
      box-shadow: 0 8px 24px rgba(0, 0, 0, .06);
      background: #fff;
    }
  </style>
</head>

<body>
  <nav class="navbar navbar-expand-lg bg-white border-bottom shadow-sm">
    <div class="container">
      <a class="navbar-brand" href="#">Admin — Ticket Generator</a>
      <div class="d-flex gap-2">
        <a class="btn btn-outline-secondary btn-sm" href="login.php?logout=1"
          onclick="event.preventDefault();document.getElementById('logoutForm').submit();">Logout</a>
        <form id="logoutForm" method="post" action="ticket_generator.php" class="d-none">
          <input type="hidden" name="__logout" value="1">
        </form>
      </div>
    </div>
  </nav>
  <?php
  // Handle logout post-back after navbar link
  if (!empty($_POST['__logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
  }
  ?>
  <div class="container my-4">
    <div class="card p-3 p-md-4">
      <h1 class="h4 mb-3">Generate Ticket by Receipt ID</h1>
      <form class="row g-2" method="get" autocomplete="off">
        <div class="col-md-6">
          <input name="receipt_id" value="<?= h($receipt_id) ?>"
            class="form-control form-control-lg"
            placeholder="Enter Receipt ID e.g. RCPT0002" required>
        </div>
        <div class="col-md-6 d-flex gap-2">
          <button class="btn btn-primary btn-lg">Fetch</button>
          <?php if ($student): ?>
            <a class="btn btn-success btn-lg" target="_blank"
              href="?receipt_id=<?= urlencode($receipt_id) ?>&action=pdf">Download PDF</a>
            <button type="button" onclick="printFrame()" class="btn btn-outline-secondary btn-lg">Print</button>
          <?php endif; ?>
        </div>
      </form>
      <?php if ($msg): ?>
        <div class="alert alert-warning mt-3 small"><?= $msg ?></div>
      <?php endif; ?>
    </div>

    <?php if ($student): ?>
      <div class="card p-3 p-md-4 mt-4">
        <h2 class="h5 mb-3">Ticket Preview</h2>
        <div class="ticket-preview">
          <?php
          $html = build_ticket_html($student, /*forPdf*/ false);
          $dataUrl = 'data:text/html;base64,' . base64_encode($html);
          ?>
          <iframe id="ticketFrame" src="<?= $dataUrl ?>"></iframe>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <script>
    function printFrame() {
      const f = document.getElementById('ticketFrame');
      if (f && f.contentWindow) f.contentWindow.print();
    }
  </script>
</body>

</html>