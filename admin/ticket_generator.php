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
/* Base */
body{
  margin:0; padding:0;
  background:#eef3fb;
  color:#1f2937;
  font:14px/1.35 Arial, Helvetica, sans-serif;
}
.wrapper{
  width:100%;
}
.ticket{
  width:100%;
  max-width:980px;
  margin:0 auto;
  background:#fff;
  border:1px solid #E5E7EB;
  border-radius:10px;
  overflow:hidden;
}

/* Header */
.header{
  background:' . $brand . ';
  color:#fff;
  padding:20px 28px;
  position:relative;
}
.header h1{
  margin:0 0 2px 0;
  font-size:22px;
  font-weight:bold;
}
.header small{font-size:12px; opacity:.95;}

.badge{
  position:absolute;
  top:16px; right:28px;
  padding:6px 12px;
  background:' . $badgeColor . ';
  color:#fff;
  border-radius:999px;
  font-size:11px;
  font-weight:bold;
  letter-spacing:.4px;
  text-transform:uppercase;
  text-align:center;
  min-width:110px;
}
.header .meta{
  position:absolute;
  right:28px; top:48px;
  text-align:right;
  font-size:12px;
  line-height:1.4;
}

/* Body layout: table/table-cell (DOMPDF friendly) */
.section{
  display:table;
  width:100%;
  table-layout:fixed;
  border-collapse:collapse;
  background:#fff;
}
.col{
  display:table-cell;
  vertical-align:top;
  padding:20px 28px;
}
.col.left{
  width:60%;
  border-right:1px solid #EEEEEE;
}
.col.right{
  width:40%;
  text-align:center;
}

/* Fields */
.label{
  font-size:11px;
  text-transform:uppercase;
  color:#6B7280;
  margin:0 0 2px 0;
  letter-spacing:.3px;
}
.value{
  font-size:14px;
  font-weight:bold;
  color:#111827;
  margin:0 0 8px 0;
}
.divider{
  height:1px;
  background:#EEE;
  margin:6px 0 8px;
}

/* QR & event info */
.qr{
  width:150px; height:150px;
  margin:8px auto 4px;
  display:block;
  border:4px solid #F9FAFB;
  border-radius:8px;
}
.tip{
  font-size:12px;
  color:#6B7280;
  margin:4px 0 10px 0;
}

/* Footer */
.footer{
  background:#F8FAFC;
  border-top:1px solid #E5E7EB;
  padding:12px 28px;
  font-size:11px;
  color:#6B7280;
}
.footwrap{
  display:table;
  width:100%;
}
.footwrap .leftf,.footwrap .rightf{
  display:table-cell;
  vertical-align:middle;
}
.footwrap .leftf{width:75%;}
.footwrap .rightf{width:25%; text-align:right;}

/* Print (browser) */
@media print{
  body{background:#fff;}
  .ticket{box-shadow:none;}
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