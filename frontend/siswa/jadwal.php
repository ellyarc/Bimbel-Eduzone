<?php
session_start();

if (!isset($_SESSION["useremail"]) || $_SESSION["role"] !== "siswa") {
    header("location: ../login.php");
    exit();
}

include("../../includes/config.php");

$siswa_ID = $_SESSION['siswa_ID'] ?? null;
if (!$siswa_ID) die("Session not found. Please log in again.");

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

$q_jadwal = mysqli_query($conn,
    "SELECT * FROM jadwal
     WHERE tingkat = '$siswa_TINGKAT' AND kelas = '$siswa_KELAS'
     ORDER BY tanggal ASC, sesi ASC"
);

function formatTanggal($tgl) {
    return date('l, F j, Y', strtotime($tgl));
}

function getWarnaMapel($mapel) {
    $m = strtolower($mapel);
    if (str_contains($m, 'matematika') || str_contains($m, 'math')) return ['bg'=>'#E6F1FB','text'=>'#185FA5','icon'=>'ti-math-function'];
    if (str_contains($m, 'ipa') || str_contains($m, 'fisika') || str_contains($m, 'physics')) return ['bg'=>'#FAEEDA','text'=>'#854F0B','icon'=>'ti-atom'];
    if (str_contains($m, 'kimia') || str_contains($m, 'chemistry')) return ['bg'=>'#E1F5EE','text'=>'#0F6E56','icon'=>'ti-flask'];
    if (str_contains($m, 'biologi') || str_contains($m, 'biology'))  return ['bg'=>'#EAF3DE','text'=>'#3B6D11','icon'=>'ti-leaf'];
    if (str_contains($m, 'bahasa') || str_contains($m, 'english') || str_contains($m, 'language')) return ['bg'=>'#EDE7F6','text'=>'#512DA8','icon'=>'ti-language'];
    return ['bg'=>'#F5F7FA','text'=>'#5a6a7a','icon'=>'ti-book'];
}

$grouped = [];
while ($row = mysqli_fetch_assoc($q_jadwal)) {
    $grouped[$row['tanggal']][] = $row;
}

$today = date('Y-m-d');

$activePage = 'jadwal';
$pageTitle  = 'Schedule';
$pageSub    = 'Your class schedule — Grade ' . $siswa_TINGKAT . ' · Class ' . $siswa_KELAS;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Schedule – EduZone</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css"/>
  <?php include("templates/head.php"); ?>
  <style>
    .timeline { display: flex; flex-direction: column; gap: 24px; max-width: 700px; }

    .tgl-header { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; }
    .tgl-dot { width: 12px; height: 12px; border-radius: 50%; background: #d0daea; flex-shrink: 0; }
    .tgl-dot.today { background: #378ADD; box-shadow: 0 0 0 3px #E6F1FB; }
    .tgl-line { flex: 1; height: 1px; background: #e4eaf2; }
    .tgl-label { font-size: 13px; font-weight: 700; color: #5a6a7a; white-space: nowrap; }
    .tgl-label.today { color: #185FA5; }
    .tgl-badge-today { background: #378ADD; color: #fff; font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 20px; margin-left: 6px; }

    .card { background: #fff; border: 1px solid #e4eaf2; border-radius: 12px; padding: 14px 16px; display: flex; align-items: center; gap: 14px; margin-bottom: 8px; transition: border-color 0.15s, transform 0.15s, box-shadow 0.15s; }
    .card:hover { border-color: #b0c8f0; transform: translateX(3px); box-shadow: 0 2px 12px rgba(55,138,221,0.08); }
    .card:last-child { margin-bottom: 0; }

    .sesi-box { width: 44px; height: 44px; border-radius: 10px; background: #f5f7fa; display: flex; flex-direction: column; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid #e4eaf2; }
    .sesi-label { font-size: 9px; color: #9aabb8; font-weight: 600; text-transform: uppercase; }
    .sesi-num   { font-size: 18px; font-weight: 700; color: #1a2a3a; line-height: 1; }

    .mapel-icon { width: 38px; height: 38px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }

    .card-info { flex: 1; min-width: 0; }
    .card-mapel { font-size: 13px; font-weight: 700; color: #1a2a3a; margin-bottom: 3px; }
    .card-sub   { font-size: 12px; color: #8a9ab0; display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .card-sub span { display: flex; align-items: center; gap: 4px; }
    .card-sub i { font-size: 13px; }

    .empty { text-align: center; padding: 56px 20px; color: #9aabb8; }
    .empty i { font-size: 36px; display: block; margin-bottom: 10px; color: #c8d6e0; }
  </style>
</head>
<body>
<div class="app">

  <?php include("templates/sidebar.php"); ?>

  <div class="main">

    <div class="topbar">
      <div class="topbar-left">
        <h1><?= $pageTitle ?></h1>
        <p><?= $pageSub ?></p>
      </div>
    </div>

    <div class="content">

      <?php if (empty($grouped)): ?>
        <div class="empty">
          <i class="ti ti-calendar-off"></i>
          <p>No schedule available yet.</p>
        </div>

      <?php else: ?>
        <div class="timeline">
          <?php foreach ($grouped as $tgl => $items):
            $isToday  = ($tgl === $today);
            $tglLabel = formatTanggal($tgl);
          ?>
          <div class="tgl-group">
            <div class="tgl-header">
              <div class="tgl-dot <?= $isToday ? 'today' : '' ?>"></div>
              <span class="tgl-label <?= $isToday ? 'today' : '' ?>">
                <?= $tglLabel ?>
                <?php if ($isToday): ?><span class="tgl-badge-today">Today</span><?php endif; ?>
              </span>
              <div class="tgl-line"></div>
            </div>

            <?php foreach ($items as $item):
              $w = getWarnaMapel($item['mapel']);
            ?>
            <div class="card">
              <div class="sesi-box">
                <span class="sesi-label">Sess.</span>
                <span class="sesi-num"><?= htmlspecialchars($item['sesi']) ?></span>
              </div>
              <div class="mapel-icon" style="background:<?= $w['bg'] ?>;color:<?= $w['text'] ?>">
                <i class="ti <?= $w['icon'] ?>"></i>
              </div>
              <div class="card-info">
                <p class="card-mapel"><?= htmlspecialchars($item['mapel']) ?></p>
                <div class="card-sub">
                  <span><i class="ti ti-school"></i>Grade <?= htmlspecialchars($item['tingkat']) ?></span>
                  <span><i class="ti ti-door"></i>Class <?= htmlspecialchars($item['kelas']) ?></span>
                  <?php if (!empty($item['tutor_ID'])): ?>
                  <span><i class="ti ti-user"></i>Tutor #<?= htmlspecialchars($item['tutor_ID']) ?></span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
            <?php endforeach; ?>

          </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </div>
  </div>
</div>
</body>
</html>
<?php mysqli_close($conn); ?>