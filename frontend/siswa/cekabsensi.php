<?php
session_start();

if (!isset($_SESSION["useremail"]) || $_SESSION["role"] !== "siswa") {
    header("location: ../login.php");
    exit();
}

include("../../includes/config.php");

$siswa_ID = $_SESSION['siswa_ID'] ?? null;
if (!$siswa_ID) die("Session not found. Please log in again.");

// ── STUDENT DATA ──
$q = mysqli_query($conn,
    "SELECT s.siswa_ID, b.siswa_NAME, b.siswa_TINGKAT, b.siswa_KELAS
     FROM siswa s
     LEFT JOIN bio_siswa b ON s.siswa_ID = b.siswa_ID
     WHERE s.siswa_ID = '$siswa_ID'
     LIMIT 1"
);
$siswa         = mysqli_fetch_assoc($q);
$siswa_NAME    = $siswa['siswa_NAME']    ?? 'Student';
$siswa_TINGKAT = $siswa['siswa_TINGKAT'] ?? '-';
$siswa_KELAS   = $siswa['siswa_KELAS']   ?? '-';

$today     = date('Y-m-d');
$pesan     = '';
$tipePesan = '';

// ── PROCESS CHECK-IN (POST) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sesi   = intval($_POST['sesi'] ?? 0);
    $status = $_POST['status'] ?? 'Present';

    if ($sesi < 1 || $sesi > 4) {
        $pesan     = 'Invalid session number.';
        $tipePesan = 'error';
    } else {
        $cek = mysqli_query($conn,
            "SELECT absen_ID FROM absensi
             WHERE siswa_ID = '$siswa_ID'
             AND absen_DATE = '$today'
             AND absen_SESSION = '$sesi'
             LIMIT 1"
        );

        if (mysqli_num_rows($cek) > 0) {
            $pesan     = 'You have already checked in for session ' . $sesi . ' today.';
            $tipePesan = 'warning';
        } else {
            $ins = mysqli_query($conn,
                "INSERT INTO absensi (siswa_ID, absen_DATE, absen_SESSION, absen_STATUS)
                 VALUES ('$siswa_ID', '$today', '$sesi', '$status')"
            );
            if ($ins) {
                $pesan     = 'Attendance for session ' . $sesi . ' recorded successfully!';
                $tipePesan = 'success';
            } else {
                $pesan     = 'Failed to save attendance. Please try again: ' . mysqli_error($conn);
                $tipePesan = 'error';
            }
        }
    }
}

// ── ATTENDANCE HISTORY ──
$q_riwayat = mysqli_query($conn,
    "SELECT * FROM absensi
     WHERE siswa_ID = '$siswa_ID'
     ORDER BY absen_DATE DESC, absen_SESSION ASC
     LIMIT 30"
);

// ── STATISTICS ──
$q_stat = mysqli_query($conn,
    "SELECT
        COUNT(*) as total,
        SUM(absen_STATUS = 'Present' OR absen_STATUS = 'Hadir') as hadir,
        SUM(absen_STATUS = 'Excused' OR absen_STATUS = 'Izin' OR absen_STATUS = 'Permission') as izin,
        SUM(absen_STATUS = 'Sick' OR absen_STATUS = 'Sakit') as sakit,
        SUM(absen_STATUS = 'Absent' OR absen_STATUS = 'Alpha') as alpha
     FROM absensi
     WHERE siswa_ID = '$siswa_ID'"
);
$stat  = mysqli_fetch_assoc($q_stat);
$total = $stat['total'] ?: 0;
$hadir = $stat['hadir'] ?: 0;
$izin  = $stat['izin']  ?: 0;
$sakit = $stat['sakit'] ?: 0;
$alpha = $stat['alpha'] ?: 0;
$pct   = $total > 0 ? round(($hadir / $total) * 100) : 0;

// ── SESSIONS ALREADY CHECKED TODAY ──
$q_absen_hari = mysqli_query($conn,
    "SELECT absen_SESSION FROM absensi
     WHERE siswa_ID = '$siswa_ID' AND absen_DATE = '$today'"
);
$sudah_absen = [];
while ($r = mysqli_fetch_assoc($q_absen_hari)) {
    $sudah_absen[] = (int)$r['absen_SESSION'];
}

// ── SCHEDULED SESSIONS TODAY ──
$q_sesi_jadwal = mysqli_query($conn,
    "SELECT DISTINCT sesi FROM jadwal
     WHERE tingkat = '$siswa_TINGKAT'
     AND kelas = '$siswa_KELAS'
     AND tanggal = '$today'
     ORDER BY sesi ASC"
);
$sesi_dijadwalkan = [];
while ($r = mysqli_fetch_assoc($q_sesi_jadwal)) {
    $sesi_dijadwalkan[] = (int)$r['sesi'];
}

$today_label = date('l, F j, Y');

$activePage = 'absensi';
$pageTitle  = 'Attendance';
$pageSub    = 'Check in for today\'s sessions';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Attendance – EduZone</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css"/>
  <?php include("templates/head.php"); ?>
  <style>
    .page-grid { display: grid; grid-template-columns: 340px 1fr; gap: 20px; align-items: start; }
    @media (max-width: 800px) { .page-grid { grid-template-columns: 1fr; } }

    .absen-card { background: #fff; border: 1px solid #e4eaf2; border-radius: 14px; overflow: hidden; }
    .absen-card-header { background: linear-gradient(120deg, #2d3a4a 60%, #378ADD); padding: 20px; color: #fff; }
    .absen-card-header h2 { font-size: 16px; font-weight: 700; margin-bottom: 4px; }
    .absen-card-header p  { font-size: 12px; opacity: 0.8; }
    .absen-card-body { padding: 20px; display: flex; flex-direction: column; gap: 16px; }

    .form-label { font-size: 12px; font-weight: 600; color: #5a6a7a; margin-bottom: 6px; display: block; }

    .sesi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; }
    .sesi-btn { border: 1.5px solid #e4eaf2; border-radius: 10px; background: #f5f7fa; padding: 10px 0; text-align: center; cursor: pointer; transition: all 0.15s; }
    .sesi-btn input { display: none; }
    .sesi-num { font-size: 18px; font-weight: 700; color: #1a2a3a; display: block; }
    .sesi-lbl { font-size: 10px; color: #9aabb8; display: block; }
    .sesi-btn:has(input:checked) { border-color: #378ADD; background: #E6F1FB; }
    .sesi-btn:has(input:checked) .sesi-num { color: #185FA5; }
    .sesi-btn:has(input:checked) .sesi-lbl { color: #378ADD; }
    .sesi-btn.sudah { opacity: 0.45; cursor: not-allowed; pointer-events: none; background: #f0f4f8; }
    .sesi-btn.sudah .sesi-lbl { color: #c0392b; }

    .status-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; }
    .status-opt { border: 1.5px solid #e4eaf2; border-radius: 10px; background: #f5f7fa; padding: 10px 12px; display: flex; align-items: center; gap: 8px; cursor: pointer; transition: all 0.15s; }
    .status-opt input { display: none; }
    .status-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
    .status-opt span { font-size: 13px; font-weight: 500; color: #1a2a3a; }
    .status-opt:has(input:checked) { border-color: #378ADD; background: #E6F1FB; }
    .dot-present { background: #1D9E75; }
    .dot-excused { background: #378ADD; }
    .dot-sick    { background: #EF9F27; }
    .dot-absent  { background: #E53935; }

    .btn-absen { width: 100%; padding: 11px; border: none; border-radius: 10px; background: #378ADD; color: #fff; font-size: 14px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: background 0.15s, transform 0.1s; }
    .btn-absen:hover { background: #185FA5; }
    .btn-absen:active { transform: scale(0.98); }

    .alert { padding: 10px 14px; border-radius: 8px; font-size: 13px; font-weight: 500; display: flex; align-items: center; gap: 8px; }
    .alert-success { background: #E1F5EE; color: #0F6E56; border: 1px solid #9FE1CB; }
    .alert-error   { background: #FCEBEB; color: #A32D2D; border: 1px solid #f5c6c6; }
    .alert-warning { background: #FAEEDA; color: #854F0B; border: 1px solid #FAC775; }

    .stat-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin-bottom: 16px; }
    .stat-box { background: #fff; border: 1px solid #e4eaf2; border-radius: 10px; padding: 12px; text-align: center; }
    .stat-val   { font-size: 20px; font-weight: 700; color: #1a2a3a; display: block; }
    .stat-label { font-size: 11px; color: #8a9ab0; display: block; margin-top: 2px; }
    .prog-wrap  { display: flex; align-items: center; gap: 10px; margin-bottom: 16px; }
    .prog-bar   { flex: 1; height: 6px; background: #e4eaf2; border-radius: 3px; }
    .prog-fill  { height: 6px; border-radius: 3px; background: #1D9E75; }
    .prog-pct   { font-size: 13px; font-weight: 700; color: #0F6E56; width: 36px; text-align: right; }

    .panel-title { font-size: 14px; font-weight: 700; color: #1a2a3a; margin-bottom: 12px; }
    .riwayat-table { width: 100%; border-collapse: collapse; }
    .riwayat-table th { font-size: 11px; font-weight: 600; color: #8a9ab0; text-align: left; padding: 8px 10px; border-bottom: 1px solid #e4eaf2; white-space: nowrap; }
    .riwayat-table td { font-size: 13px; color: #1a2a3a; padding: 9px 10px; border-bottom: 1px solid #f0f4f8; vertical-align: middle; }
    .riwayat-table tr:last-child td { border-bottom: none; }
    .riwayat-table tr:hover td { background: #f8fafc; }

    /* ── PERBAIKAN CLASS PILL STATUS BADGE ── */
    .status-pill { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
    .pill-present { background: #E1F5EE; color: #0F6E56; }
    .pill-excused { background: #E6F1FB; color: #185FA5; }
    .pill-sick    { background: #FAEEDA; color: #854F0B; }
    .pill-absent  { background: #FCEBEB; color: #A32D2D; }

    .panel-box { background: #fff; border: 1px solid #e4eaf2; border-radius: 14px; padding: 18px; }
  </style>
</head>
<body>
<div class="app">

  <?php include("templates/sidebar.php"); ?>

  <div class="main">

    <div class="topbar">
      <div class="topbar-left">
        <h1><?= $pageTitle ?></h1>
        <p><?= $today_label ?></p>
      </div>
    </div>

    <div class="content">
      <div class="page-grid">

        <div>
          <div class="absen-card">
            <div class="absen-card-header">
              <h2>Check In Today</h2>
              <p><?= $today_label ?> · <?= htmlspecialchars($siswa_NAME) ?></p>
            </div>
            <div class="absen-card-body">

              <?php if ($pesan): ?>
              <div class="alert alert-<?= $tipePesan ?>">
                <i class="ti ti-<?= $tipePesan === 'success' ? 'circle-check' : ($tipePesan === 'warning' ? 'alert-triangle' : 'circle-x') ?>"></i>
                <?= htmlspecialchars($pesan) ?>
              </div>
              <?php endif; ?>

              <form method="POST">

                <label class="form-label">Select Session</label>
                <?php if (empty($sesi_dijadwalkan)): ?>
                  <div class="alert alert-warning">
                    <i class="ti ti-calendar-off"></i>
                    No sessions scheduled for you today.
                  </div>
                <?php else: ?>
                <div class="sesi-grid">
                  <?php
                  $first_checked = false;
                  foreach ($sesi_dijadwalkan as $s):
                    $sudah   = in_array($s, $sudah_absen);
                    $checked = (!$first_checked && !$sudah) ? 'checked' : '';
                    if ($checked) $first_checked = true;
                  ?>
                  <label class="sesi-btn <?= $sudah ? 'sudah' : '' ?>">
                    <input type="radio" name="sesi" value="<?= $s ?>"
                           <?= $sudah ? 'disabled' : '' ?>
                           <?= $checked ?>>
                    <span class="sesi-num"><?= $s ?></span>
                    <span class="sesi-lbl"><?= $sudah ? '✓ Done' : 'Session ' . $s ?></span>
                  </label>
                  <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <label class="form-label" style="margin-top:14px">Attendance Status</label>
                <div class="status-grid">
                  <?php
                  $statuses = [
                    'Present' => ['dot' => 'dot-present', 'label' => 'Present'],
                    'Excused' => ['dot' => 'dot-excused', 'label' => 'Excused'],
                    'Sick'    => ['dot' => 'dot-sick',    'label' => 'Sick'],
                    'Absent'  => ['dot' => 'dot-absent',  'label' => 'Absent'],
                  ];
                  foreach ($statuses as $val => $opt): ?>
                  <label class="status-opt">
                    <input type="radio" name="status" value="<?= $val ?>" <?= $val === 'Present' ? 'checked' : '' ?>>
                    <span class="status-dot <?= $opt['dot'] ?>"></span>
                    <span><?= $opt['label'] ?></span>
                  </label>
                  <?php endforeach; ?>
                </div>

                <?php
                $semua_sudah = !empty($sesi_dijadwalkan) &&
                               count(array_diff($sesi_dijadwalkan, $sudah_absen)) === 0;
                ?>
                <?php if (empty($sesi_dijadwalkan)): ?>
                <?php elseif ($semua_sudah): ?>
                  <div class="alert alert-success">
                    <i class="ti ti-circle-check"></i> All sessions for today have been checked in!
                  </div>
                <?php else: ?>
                  <button type="submit" class="btn-absen">
                    <i class="ti ti-check"></i> Save Attendance
                  </button>
                <?php endif; ?>

              </form>
            </div>
          </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:16px">

          <div class="panel-box">
            <p class="panel-title">Attendance Summary</p>
            <div class="stat-row">
              <div class="stat-box">
                <span class="stat-val" style="color:#1D9E75"><?= $hadir ?></span>
                <span class="stat-label">Present</span>
              </div>
              <div class="stat-box">
                <span class="stat-val" style="color:#378ADD"><?= $izin ?></span>
                <span class="stat-label">Excused</span>
              </div>
              <div class="stat-box">
                <span class="stat-val" style="color:#EF9F27"><?= $sakit ?></span>
                <span class="stat-label">Sick</span>
              </div>
              <div class="stat-box">
                <span class="stat-val" style="color:#E53935"><?= $alpha ?></span>
                <span class="stat-label">Absent</span>
              </div>
            </div>
            <div class="prog-wrap">
              <span style="font-size:12px;color:#5a6a7a;width:80px">% Present</span>
              <div class="prog-bar"><div class="prog-fill" style="width:<?= $pct ?>%"></div></div>
              <span class="prog-pct"><?= $pct ?>%</span>
            </div>
          </div>

          <div class="panel-box">
            <p class="panel-title">Attendance History</p>
            <?php if (mysqli_num_rows($q_riwayat) > 0): ?>
            <table class="riwayat-table">
              <thead>
                <tr>
                  <th>Date</th>
                  <th style="text-align:center">Session</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($r = mysqli_fetch_assoc($q_riwayat)):
                  $tgl = date('d M', strtotime($r['absen_DATE']));
                  $st  = strtolower($r['absen_STATUS']);
                  
                  // Menentukan class css pill berdasarkan teks status di database
                  $pillClass = match($st) {
                    'present', 'hadir'       => 'pill-present',
                    'excused', 'izin', 'permission' => 'pill-excused',
                    'sick', 'sakit'          => 'pill-sick',
                    default                  => 'pill-absent',
                  };

                  // Menentukan teks label display agar seragam bahasa Inggris
                  $statusLabel = match($st) {
                    'present', 'hadir'       => 'Present',
                    'izin', 'permission', 'excused' => 'Excused',
                    'sakit', 'sick'          => 'Sick',
                    default                  => 'Absent',
                  };
                ?>
                <tr>
                  <td style="white-space:nowrap"><?= $tgl ?></td>
                  <td style="text-align:center;font-weight:600"><?= $r['absen_SESSION'] ?></td>
                  <td><span class="status-pill <?= $pillClass ?>"><?= $statusLabel ?></span></td>
                </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
            <?php else: ?>
              <p style="font-size:13px;color:#9aabb8;text-align:center;padding:24px 0">No attendance records yet.</p>
            <?php endif; ?>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
<?php mysqli_close($conn); ?>