<?php
ob_start();
session_start();

if (!isset($_SESSION["useremail"]) || $_SESSION["role"] !== "siswa") {
    header("location: ../login.php");
    exit();
}

include("../../includes/config.php");

$siswa_ID = $_SESSION['siswa_ID'] ?? null;
if (!$siswa_ID) die("Session siswa_ID not found. Please log in again.");

$query = mysqli_query($conn,
    "SELECT s.siswa_ID, s.siswa_USER, s.siswa_PASS,
            b.siswa_NAME, b.siswa_TINGKAT, b.siswa_KELAS
     FROM siswa s
     LEFT JOIN bio_siswa b ON s.siswa_ID = b.siswa_ID
     WHERE s.siswa_ID = '$siswa_ID'"
);
$data = mysqli_fetch_array($query);

$siswa_NAME    = $data['siswa_NAME']    ?? 'Student';
$siswa_TINGKAT = $data['siswa_TINGKAT'] ?? '-';
$siswa_KELAS   = $data['siswa_KELAS']   ?? '-';

// Total materials
$q_materi     = mysqli_query($conn, "SELECT COUNT(*) as total FROM materi");
$total_materi = mysqli_fetch_assoc($q_materi)['total'] ?? 0;

// Attendance — from absensi table
$q_hadir = mysqli_query($conn,
    "SELECT COUNT(*) as hadir FROM absensi
     WHERE siswa_ID = '$siswa_ID'
     AND (absen_STATUS = 'Present' OR absen_STATUS = 'Hadir')"
);
$q_total = mysqli_query($conn,
    "SELECT COUNT(*) as total FROM absensi
     WHERE siswa_ID = '$siswa_ID'"
);
$jml_hadir = mysqli_fetch_assoc($q_hadir)['hadir'] ?? 0;
$jml_total = mysqli_fetch_assoc($q_total)['total'] ?? 0;
$pct_hadir = $jml_total > 0 ? round(($jml_hadir / $jml_total) * 100) : 0;

// Payments
$q_bayar = mysqli_query($conn,
    "SELECT pembayaran_STATUS, pembayaran_TANGGAL,
            pembayaran_NOMINAL, pembayaran_TENGGAT
     FROM pembayaran
     WHERE siswa_ID = '$siswa_ID'
     ORDER BY pembayaran_TENGGAT DESC
     LIMIT 2"
);

// Donut SVG
$circ   = 226.2;
$offset = $circ * (1 - $pct_hadir / 100);

$today = date('l, F j, Y');

$activePage = 'dashboard';
$pageTitle  = 'Dashboard';
$pageSub    = 'Welcome back, ' . htmlspecialchars($siswa_NAME) . '!';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard – EduZone</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" />
  <?php include("templates/head.php"); ?>
  <style>
    /* ── FEATURE CARDS ── */
    .feature-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
      gap: 12px;
      margin-bottom: 24px;
    }
    .feature-card {
      background: #fff;
      border: 1px solid #e4eaf2;
      border-radius: 14px;
      padding: 18px 14px;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 10px;
      text-decoration: none;
      transition: transform 0.15s, box-shadow 0.15s, border-color 0.15s;
      cursor: pointer;
    }
    .feature-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(55,138,221,0.12);
      border-color: #b0c8f0;
      text-decoration: none;
    }
    .feature-card:active { transform: scale(0.97); }
    .feature-icon {
      width: 48px; height: 48px;
      border-radius: 12px;
      display: flex; align-items: center; justify-content: center;
      font-size: 22px;
    }
    .fi-blue   { background: #E6F1FB; color: #378ADD; }
    .fi-green  { background: #E1F5EE; color: #1D9E75; }
    .fi-orange { background: #FEF3E2; color: #EF9F27; }
    .fi-purple { background: #F0EEFF; color: #7C5CFC; }
    .fi-red    { background: #FCEBEB; color: #E53935; }
    .fi-teal   { background: #E0F7F4; color: #00897B; }
    .feature-label {
      font-size: 12px;
      font-weight: 600;
      color: #1a2a3a;
      text-align: center;
      line-height: 1.3;
    }
    .feature-sub {
      font-size: 10px;
      color: #9aabb8;
      text-align: center;
    }
  </style>
</head>
<body>
<div class="app">

  <?php include("templates/sidebar.php"); ?>

  <div class="main">

    <!-- TOPBAR -->
    <div class="topbar">
      <div class="topbar-left">
        <h1><?= htmlspecialchars($pageTitle) ?></h1>
        <p><?= htmlspecialchars($pageSub) ?></p>
      </div>
      <div class="topbar-right">
        <span class="topbar-date">
          <i class="ti ti-calendar" style="font-size:13px;"></i> <?= $today ?>
        </span>
        <div class="notif-btn">
          <i class="ti ti-bell" style="font-size:16px;"></i>
          <span class="notif-dot"></span>
        </div>
      </div>
    </div>

    <!-- CONTENT -->
    <div class="content">

      <!-- Greeting banner -->
      <div class="greeting-banner">
        <div class="greeting-text">
          <h2>Hey, <?= htmlspecialchars($siswa_NAME) ?>! 👋</h2>
          <p>Keep up the great work today!</p>
        </div>
        <span class="greeting-emoji">📚</span>
      </div>

      <!-- Stat cards -->
      <div class="stat-grid">
        <div class="stat-card">
          <div class="stat-icon si-blue"><i class="ti ti-book"></i></div>
          <div class="stat-body">
            <span class="stat-val"><?= $total_materi ?></span>
            <span class="stat-label">Study Materials</span>
            <span class="stat-sub">Topics available</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon si-orange"><i class="ti ti-user-check"></i></div>
          <div class="stat-body">
            <span class="stat-val"><?= $pct_hadir ?>%</span>
            <span class="stat-label">Attendance Rate</span>
            <span class="stat-sub">This semester</span>
          </div>
        </div>
      </div>

      <!-- ── QUICK ACCESS FEATURE CARDS ── -->
      <p style="font-size:13px;font-weight:700;color:#5a6a7a;margin-bottom:10px;letter-spacing:.4px;">QUICK ACCESS</p>
      <div class="feature-grid">

        <a href="materi.php" class="feature-card">
          <div class="feature-icon fi-blue"><i class="ti ti-book-2"></i></div>
          <span class="feature-label">Study Materials</span>
          <span class="feature-sub">Browse topics</span>
        </a>

        <a href="jadwal.php" class="feature-card">
          <div class="feature-icon fi-purple"><i class="ti ti-calendar-event"></i></div>
          <span class="feature-label">Schedule</span>
          <span class="feature-sub">View timetable</span>
        </a>

        <a href="cekabsensi.php" class="feature-card">
          <div class="feature-icon fi-green"><i class="ti ti-user-check"></i></div>
          <span class="feature-label">Attendance</span>
          <span class="feature-sub">Check in today</span>
        </a>

        <a href="cekpembayaran.php" class="feature-card">
          <div class="feature-icon fi-orange"><i class="ti ti-credit-card"></i></div>
          <span class="feature-label">Payments</span>
          <span class="feature-sub">View bills</span>
        </a>

        <a href="akun.php" class="feature-card">
          <div class="feature-icon fi-teal"><i class="ti ti-user-circle"></i></div>
          <span class="feature-label">My Profile</span>
          <span class="feature-sub">Manage account</span>
        </a>

      </div>

      <!-- Bottom row -->
      <div class="bottom-row">
        <div class="info-side">

          <!-- Attendance donut -->
          <div class="panel">
            <p class="panel-title">Attendance</p>
            <div class="kehadiran-donut">
              <div class="donut-wrap">
                <svg width="90" height="90" viewBox="0 0 90 90">
                  <circle cx="45" cy="45" r="36" fill="none" stroke="#e4eaf2" stroke-width="10"/>
                  <circle cx="45" cy="45" r="36" fill="none" stroke="#1D9E75" stroke-width="10"
                    stroke-dasharray="<?= $circ ?>"
                    stroke-dashoffset="<?= $offset ?>"
                    stroke-linecap="round"
                    transform="rotate(-90 45 45)"/>
                </svg>
                <div class="donut-center">
                  <span class="donut-pct"><?= $pct_hadir ?>%</span>
                  <span class="donut-lbl">Present</span>
                </div>
              </div>
            </div>
            <div class="kehadiran-meta">
              <p><?= $jml_hadir ?> of <?= $jml_total ?> sessions</p>
              <span>This semester</span>
            </div>
          </div>

          <!-- Payment status -->
          <div class="panel">
            <p class="panel-title">Payments</p>
            <?php if (mysqli_num_rows($q_bayar) > 0):
              $first = true;
              while ($b = mysqli_fetch_assoc($q_bayar)):
                $isLunas    = strtoupper($b['pembayaran_STATUS']) === 'PAID';
                $iconClass  = $isLunas ? 'pay-icon lunas' : 'pay-icon';
                $badgeClass = $isLunas ? 'badge-lunas' : 'badge-belum';
                $badgeText  = $isLunas ? 'Paid' : 'Unpaid';
                $borderTop  = $first ? '' : 'border-top:1px solid #f0f4f8;padding-top:10px;margin-top:2px;';
                $first      = false;
            ?>
              <div class="payment-status" style="<?= $borderTop ?>">
                <div class="<?= $iconClass ?>">
                  <i class="ti ti-receipt"></i>
                </div>
                <div class="pay-info">
                  <p>Bill Rp<?= number_format($b['pembayaran_NOMINAL'], 0, ',', '.') ?></p>
                  <span>Due: <?= date('d M Y', strtotime($b['pembayaran_TENGGAT'])) ?></span>
                </div>
                <span class="pay-badge <?= $badgeClass ?>"><?= $badgeText ?></span>
              </div>
            <?php endwhile; ?>
            <?php else: ?>
              <div class="empty-state" style="padding:20px">
                <i class="ti ti-receipt-off"></i>
                No payment data found.
              </div>
            <?php endif; ?>
          </div>

        </div>
      </div>

    </div>
  </div>
</div>
</body>
</html>
<?php
mysqli_close($conn);
ob_end_flush();
?>