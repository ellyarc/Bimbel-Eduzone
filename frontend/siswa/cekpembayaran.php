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

$q_bayar = mysqli_query($conn,
    "SELECT * FROM pembayaran
     WHERE siswa_ID = '$siswa_ID'
     ORDER BY pembayaran_TENGGAT DESC"
);

function formatTanggal($tgl) {
    if (!$tgl || $tgl === 'NULL') return '-';
    return date('d M Y', strtotime($tgl));
}

$activePage = 'pembayaran';
$pageTitle  = 'Payments';
$pageSub    = 'Your billing status and payment history';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Payments – EduZone</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css"/>
  <?php include("templates/head.php"); ?>
  <style>
    .filter-row { display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap; }
    .chip { padding: 5px 14px; border-radius: 20px; border: 1px solid #d0daea; background: #fff; font-size: 12px; color: #5a6a7a; cursor: pointer; transition: all 0.15s; white-space: nowrap; user-select: none; }
    .chip:hover { border-color: #8ab4f8; color: #1a2a3a; }
    .chip.active { background: #E6F1FB; border-color: #378ADD; color: #0C447C; font-weight: 600; }

    .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 14px; }

    .card { background: #fff; border: 1px solid #e4eaf2; border-radius: 12px; overflow: hidden; display: flex; flex-direction: column; transition: border-color 0.15s, transform 0.15s, box-shadow 0.15s; }
    .card:hover { border-color: #b0c8f0; transform: translateY(-2px); box-shadow: 0 4px 16px rgba(55,138,221,0.09); }
    .card.paid   { border-left: 4px solid #1D9E75; }
    .card.unpaid { border-left: 4px solid #E53935; }

    .card-header { padding: 14px 16px 10px; display: flex; align-items: center; gap: 12px; }
    .card-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; }
    .icon-paid   { background: #E1F5EE; color: #0F6E56; }
    .icon-unpaid { background: #FCEBEB; color: #A32D2D; }
    .card-meta { flex: 1; min-width: 0; }
    .card-id { font-size: 11px; color: #9aabb8; font-weight: 500; margin-bottom: 3px; }
    .card-nominal { font-size: 17px; font-weight: 700; color: #1a2a3a; }

    .status-badge { padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; white-space: nowrap; }
    .badge-paid   { background: #E1F5EE; color: #0F6E56; }
    .badge-unpaid { background: #FCEBEB; color: #A32D2D; }

    .card-body { padding: 0 16px 14px; display: flex; flex-direction: column; gap: 8px; }
    .card-divider { height: 1px; background: #f0f4f8; margin: 4px 0; }
    .info-row { display: flex; align-items: center; justify-content: space-between; font-size: 12px; }
    .info-label { color: #8a9ab0; display: flex; align-items: center; gap: 5px; }
    .info-label i { font-size: 13px; }
    .info-val { font-weight: 600; color: #1a2a3a; }
    .info-val.red { color: #c0392b; }

    .kode-va-wrap { background: #f5f7fa; border-radius: 8px; padding: 8px 12px; display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-top: 2px; }
    .kode-va-label { font-size: 11px; color: #8a9ab0; margin-bottom: 2px; }
    .kode-va-val   { font-size: 14px; font-weight: 700; color: #185FA5; letter-spacing: 1px; }
    .btn-copy { background: none; border: 1px solid #d0daea; border-radius: 6px; padding: 4px 10px; font-size: 11px; color: #5a6a7a; cursor: pointer; transition: all 0.15s; white-space: nowrap; display: flex; align-items: center; gap: 4px; }
    .btn-copy:hover { background: #E6F1FB; border-color: #378ADD; color: #185FA5; }

    .empty { text-align: center; padding: 56px 20px; color: #9aabb8; grid-column: 1/-1; }
    .empty i { font-size: 36px; display: block; margin-bottom: 10px; color: #c8d6e0; }

    .toast { position: fixed; bottom: 24px; right: 24px; background: #1a2a3a; color: #fff; padding: 10px 18px; border-radius: 8px; font-size: 13px; opacity: 0; transform: translateY(10px); transition: all 0.25s; pointer-events: none; z-index: 999; display: flex; align-items: center; gap: 7px; }
    .toast.show { opacity: 1; transform: translateY(0); }
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

      <div class="filter-row">
        <div class="chip active" onclick="setFilter(this,'semua')">All</div>
        <div class="chip" onclick="setFilter(this,'paid')">Paid</div>
        <div class="chip" onclick="setFilter(this,'not paid')">Unpaid</div>
      </div>

      <div class="grid" id="cardGrid">

        <?php if (mysqli_num_rows($q_bayar) > 0): ?>
          <?php while ($row = mysqli_fetch_assoc($q_bayar)):
            $isPaid     = strtoupper($row['pembayaran_STATUS']) === 'PAID';
            $statusKey  = strtolower($row['pembayaran_STATUS']);
            $cardClass  = $isPaid ? 'paid' : 'unpaid';
            $iconClass  = $isPaid ? 'icon-paid' : 'icon-unpaid';
            $badgeClass = $isPaid ? 'badge-paid' : 'badge-unpaid';
            $badgeText  = $isPaid ? '✓ Paid' : '✗ Unpaid';
            $tglTenggat = formatTanggal($row['pembayaran_TENGGAT']);
            $tglBayar   = formatTanggal($row['pembayaran_TANGGAL']);
            $nominal    = 'Rp ' . number_format($row['pembayaran_NOMINAL'], 0, ',', '.');
          ?>
          <div class="card <?= $cardClass ?>" data-status="<?= htmlspecialchars($statusKey) ?>">

            <div class="card-header">
              <div class="card-icon <?= $iconClass ?>">
                <i class="ti <?= $isPaid ? 'ti-receipt' : 'ti-receipt-off' ?>"></i>
              </div>
              <div class="card-meta">
                <p class="card-id"># <?= htmlspecialchars($row['pembayaran_ID']) ?></p>
                <p class="card-nominal"><?= $nominal ?></p>
              </div>
              <span class="status-badge <?= $badgeClass ?>"><?= $badgeText ?></span>
            </div>

            <div class="card-body">
              <div class="card-divider"></div>
              <div class="info-row">
                <span class="info-label"><i class="ti ti-calendar-due"></i> Due Date</span>
                <span class="info-val <?= !$isPaid ? 'red' : '' ?>"><?= $tglTenggat ?></span>
              </div>
              <div class="info-row">
                <span class="info-label"><i class="ti ti-calendar-check"></i> Paid On</span>
                <span class="info-val"><?= $tglBayar ?></span>
              </div>

              <?php if (!empty($row['kode_va'])): ?>
              <div class="card-divider"></div>
              <div class="kode-va-wrap">
                <div>
                  <p class="kode-va-label">VA Code</p>
                  <p class="kode-va-val"><?= htmlspecialchars($row['kode_va']) ?></p>
                </div>
                <button class="btn-copy" onclick="copyKode('<?= htmlspecialchars($row['kode_va']) ?>')">
                  <i class="ti ti-copy"></i> Copy
                </button>
              </div>
              <?php endif; ?>
            </div>

          </div>
          <?php endwhile; ?>
        <?php endif; ?>

        <div class="empty" id="emptyState" style="display:none;">
          <i class="ti ti-receipt-off"></i>
          <p>No payments found.</p>
        </div>

        <?php if (mysqli_num_rows($q_bayar) === 0): ?>
        <div class="empty">
          <i class="ti ti-receipt-off"></i>
          <p>No billing records yet.</p>
        </div>
        <?php endif; ?>

      </div>
    </div>
  </div>
</div>

<div class="toast" id="toast">
  <i class="ti ti-check"></i> VA code copied!
</div>

<script>
  let activeFilter = 'semua';

  function setFilter(el, val) {
    document.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
    el.classList.add('active');
    activeFilter = val;
    filterCards();
  }

  function filterCards() {
    const cards = document.querySelectorAll('#cardGrid .card[data-status]');
    let visible = 0;
    cards.forEach(card => {
      const show = activeFilter === 'semua' || card.dataset.status === activeFilter;
      card.style.display = show ? '' : 'none';
      if (show) visible++;
    });
    document.getElementById('emptyState').style.display = visible === 0 ? 'flex' : 'none';
  }

  function copyKode(kode) {
    navigator.clipboard.writeText(kode).then(() => {
      const toast = document.getElementById('toast');
      toast.classList.add('show');
      setTimeout(() => toast.classList.remove('show'), 2200);
    });
  }
</script>
</body>
</html>
<?php mysqli_close($conn); ?>