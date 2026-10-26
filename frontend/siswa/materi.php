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

$q_materi = mysqli_query($conn,
    "SELECT * FROM materi ORDER BY materi_MAPEL, materi_TITLE"
);

$q_mapel = mysqli_query($conn, "SELECT DISTINCT materi_MAPEL FROM materi ORDER BY materi_MAPEL");
$list_mapel = [];
while ($m = mysqli_fetch_assoc($q_mapel)) {
    $list_mapel[] = $m['materi_MAPEL'];
}

$materi_list = [];
while ($row = mysqli_fetch_assoc($q_materi)) {
    $materi_list[] = $row;
}

$activePage = 'materi';
$pageTitle  = 'Study Materials';
$pageSub    = 'Browse topics, watch videos, and read PDFs';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Materials – EduZone</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css"/>
  <?php include("templates/head.php"); ?>
  <style>
    .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; gap: 16px; flex-wrap: wrap; }
    .topbar-right { display: flex; gap: 12px; align-items: center; flex-wrap: wrap; }

    .search-wrap, .select-wrap { position: relative; }
    .search-wrap i, .select-wrap i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-size: 16px; color: #9aabb8; pointer-events: none; }
    .search-wrap input, .select-wrap select { padding: 9px 12px 9px 36px; border: 1px solid #d0daea; border-radius: 8px; background: #f5f7fa; font-size: 13px; color: #1a2a3a; outline: none; min-width: 200px; transition: border-color 0.2s, background 0.2s; }
    .search-wrap input:focus, .select-wrap select:focus { border-color: #378ADD; background: #fff; }
    .select-wrap select { appearance: none; padding-right: 32px; cursor: pointer; }
    .select-wrap::after { content: '\ea5f'; font-family: 'tabler-icons'; position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: 14px; color: #9aabb8; pointer-events: none; }

    .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px; }

    .card { background: #fff; border: 1px solid #e4eaf2; border-radius: 12px; overflow: hidden; display: flex; flex-direction: column; transition: border-color 0.15s, transform 0.15s, box-shadow 0.15s; }
    .card:hover { border-color: #b0c8f0; transform: translateY(-2px); box-shadow: 0 4px 16px rgba(55,138,221,0.09); }

    .card-header { padding: 16px 16px 12px; display: flex; align-items: flex-start; gap: 12px; }
    .card-icon { width: 38px; height: 38px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; background: #F5F7FA; color: #5a6a7a; }
    .card-meta { flex: 1; min-width: 0; }
    .card-mapel { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 3px; color: #5a6a7a; }
    .card-title { font-size: 14px; font-weight: 600; color: #1a2a3a; line-height: 1.35; }

    .card-desc { padding: 0 16px 12px; font-size: 12px; color: #6a7a8a; line-height: 1.6; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .card-divider { height: 1px; background: #f0f4f8; margin: 0 16px; }

    .card-actions { padding: 12px 16px; display: flex; gap: 8px; flex-wrap: wrap; }
    .btn-action { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer; border: none; text-decoration: none; transition: opacity 0.15s, transform 0.1s; white-space: nowrap; }
    .btn-action:active { transform: scale(0.97); }
    .btn-action:hover { opacity: 0.82; }
    .btn-video { background: #E6F1FB; color: #185FA5; }
    .btn-pdf   { background: #FAEEDA; color: #854F0B; }
    .btn-none  { background: #f0f4f8; color: #c0cdd8; cursor: default; font-weight: 400; font-style: italic; }

    .card-badges { padding: 0 16px 10px; display: flex; gap: 6px; flex-wrap: wrap; }
    .badge { font-size: 10px; padding: 2px 8px; border-radius: 20px; background: #f0f4f8; color: #6a7a8a; font-weight: 500; }

    .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 300; align-items: center; justify-content: center; padding: 16px; }
    .modal-overlay.open { display: flex; }
    .modal-box { background: #fff; border-radius: 14px; width: min(740px, 96vw); overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.25); }
    .modal-top { display: flex; align-items: center; justify-content: space-between; padding: 14px 18px; border-bottom: 1px solid #e4eaf2; }
    .modal-top h3 { font-size: 15px; font-weight: 700; color: #1a2a3a; margin: 0; }
    .modal-close { background: none; border: none; cursor: pointer; color: #888; font-size: 20px; display: flex; align-items: center; padding: 0; line-height: 1; }
    .modal-close:hover { color: #333; }
    .modal-body { padding: 16px; background: #0a0a0a; }
    .video-wrap { position: relative; padding-bottom: 56.25%; height: 0; border-radius: 6px; overflow: hidden; background: #000; }
    .video-wrap video { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: contain; }

    .pdf-modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 300; align-items: center; justify-content: center; padding: 16px; }
    .pdf-modal-overlay.open { display: flex; }
    .pdf-modal-box { background: #fff; border-radius: 14px; width: min(900px, 96vw); height: 90vh; overflow: hidden; display: flex; flex-direction: column; box-shadow: 0 20px 60px rgba(0,0,0,0.25); }
    .pdf-modal-box .modal-top { flex-shrink: 0; }
    .pdf-frame { flex: 1; width: 100%; border: none; background: #525659; }

    .empty { text-align: center; padding: 56px 20px; color: #9aabb8; grid-column: 1/-1; display: none; flex-direction: column; align-items: center; justify-content: center; }
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
      <div class="topbar-right">
        <div class="select-wrap">
          <i class="ti ti-filter"></i>
          <select id="mapelSelect" onchange="filterCards()">
            <option value="semua">All Subjects</option>
            <?php foreach ($list_mapel as $mp): ?>
              <option value="<?= htmlspecialchars($mp, ENT_QUOTES) ?>"><?= htmlspecialchars($mp) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="search-wrap">
          <i class="ti ti-search"></i>
          <input type="text" id="searchInput" placeholder="Search materials..." oninput="filterCards()">
        </div>
      </div>
    </div>

    <div class="content">
      <div class="grid" id="cardGrid">

        <?php foreach ($materi_list as $row):
          $mapel = $row['materi_MAPEL'];
          $punya_video = !empty($row['video_PATH']);
          $video_url   = $punya_video ? '/eduzone/' . $row['video_PATH'] : '';
          $punya_pdf   = !empty($row['pdf_PATH']);
          $pdf_url     = $punya_pdf ? '/eduzone/' . $row['pdf_PATH'] : '';
          $video_url_esc = htmlspecialchars($video_url, ENT_QUOTES);
          $pdf_url_esc   = htmlspecialchars($pdf_url,   ENT_QUOTES);
          $title_esc     = htmlspecialchars($row['materi_TITLE'], ENT_QUOTES);
        ?>
        <div class="card"
             data-mapel="<?= htmlspecialchars($mapel) ?>"
             data-title="<?= htmlspecialchars(strtolower($row['materi_TITLE'])) ?>">

          <div class="card-header">
            <div class="card-icon"><i class="ti ti-book"></i></div>
            <div class="card-meta">
              <p class="card-mapel"><?= htmlspecialchars($mapel) ?></p>
              <p class="card-title"><?= htmlspecialchars($row['materi_TITLE']) ?></p>
            </div>
          </div>

          <?php if (!empty($row['materi_DESC'])): ?>
          <p class="card-desc"><?= htmlspecialchars($row['materi_DESC']) ?></p>
          <?php endif; ?>

          <div class="card-badges">
            <?php if (!empty($row['materi_TINGKAT'])): ?>
              <span class="badge"><i class="ti ti-school" style="font-size:10px"></i> <?= htmlspecialchars($row['materi_TINGKAT']) ?></span>
            <?php endif; ?>
            <?php if (!empty($row['materi_KELAS'])): ?>
              <span class="badge">Class <?= htmlspecialchars($row['materi_KELAS']) ?></span>
            <?php endif; ?>
          </div>

          <div class="card-divider"></div>

          <div class="card-actions">
            <?php if ($punya_video): ?>
              <button class="btn-action btn-video" onclick="bukaVideo('<?= $title_esc ?>', '<?= $video_url_esc ?>')">
                <i class="ti ti-player-play"></i> Watch Video
              </button>
            <?php else: ?>
              <span class="btn-action btn-none"><i class="ti ti-player-play"></i> No Video</span>
            <?php endif; ?>

            <?php if ($punya_pdf): ?>
              <button class="btn-action btn-pdf" onclick="bukaPDF('<?= $title_esc ?>', '<?= $pdf_url_esc ?>')">
                <i class="ti ti-file-type-pdf"></i> Open PDF
              </button>
            <?php else: ?>
              <span class="btn-action btn-none"><i class="ti ti-file-type-pdf"></i> No PDF</span>
            <?php endif; ?>
          </div>

        </div>
        <?php endforeach; ?>

        <div class="empty" id="emptyState">
          <i class="ti ti-mood-empty"></i>
          <p>No materials found.</p>
        </div>

      </div>
    </div>
  </div>
</div>

<!-- Video Modal -->
<div class="modal-overlay" id="videoModal" onclick="handleOverlayClick(event,'videoModal')">
  <div class="modal-box">
    <div class="modal-top">
      <h3 id="videoModalTitle">Video</h3>
      <button class="modal-close" onclick="tutupVideo()"><i class="ti ti-x"></i></button>
    </div>
    <div class="modal-body">
      <div class="video-wrap">
        <video id="videoPlayer" controls controlslist="nodownload"></video>
      </div>
    </div>
  </div>
</div>

<!-- PDF Modal -->
<div class="pdf-modal-overlay" id="pdfModal" onclick="handleOverlayClick(event,'pdfModal')">
  <div class="pdf-modal-box">
    <div class="modal-top">
      <h3 id="pdfModalTitle">PDF</h3>
      <div style="display:flex;gap:8px;align-items:center">
        <a id="pdfDownloadLink" href="#" target="_blank" style="font-size:12px;color:#378ADD;text-decoration:none;display:flex;align-items:center;gap:4px">
          <i class="ti ti-external-link"></i> Open in new tab
        </a>
        <button class="modal-close" onclick="tutupPDF()"><i class="ti ti-x"></i></button>
      </div>
    </div>
    <iframe id="pdfFrame" class="pdf-frame" src=""></iframe>
  </div>
</div>

<script>
  function filterCards() {
    const searchKeyword = document.getElementById('searchInput').value.toLowerCase();
    const selectedMapel = document.getElementById('mapelSelect').value.toLowerCase();
    const cards         = document.querySelectorAll('#cardGrid .card[data-mapel]');
    let visibleCount    = 0;

    cards.forEach(card => {
      const cardMapel = card.dataset.mapel.toLowerCase();
      const cardTitle = card.dataset.title;
      const matchFilter = (selectedMapel === 'semua' || cardMapel.includes(selectedMapel));
      const matchSearch = (!searchKeyword || cardTitle.includes(searchKeyword) || cardMapel.includes(searchKeyword));
      if (matchFilter && matchSearch) { card.style.display = ''; visibleCount++; }
      else { card.style.display = 'none'; }
    });

    document.getElementById('emptyState').style.display = (visibleCount === 0) ? 'flex' : 'none';
  }

  function bukaVideo(judul, path) {
    document.getElementById('videoModalTitle').textContent = judul;
    const player = document.getElementById('videoPlayer');
    player.src = path; player.load();
    document.getElementById('videoModal').classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  function tutupVideo() {
    const player = document.getElementById('videoPlayer');
    player.pause(); player.src = '';
    document.getElementById('videoModal').classList.remove('open');
    document.body.style.overflow = '';
  }

  function bukaPDF(judul, path) {
    document.getElementById('pdfModalTitle').textContent = judul;
    document.getElementById('pdfDownloadLink').href = path;
    document.getElementById('pdfFrame').src = path + '#toolbar=0&navpanes=0';
    document.getElementById('pdfModal').classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  function tutupPDF() {
    document.getElementById('pdfFrame').src = '';
    document.getElementById('pdfModal').classList.remove('open');
    document.body.style.overflow = '';
  }

  function handleOverlayClick(e, modalId) {
    if (e.target.id === modalId) {
      if (modalId === 'videoModal') tutupVideo();
      else tutupPDF();
    }
  }

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') { tutupVideo(); tutupPDF(); }
  });
</script>
</body>
</html>
<?php mysqli_close($conn); ?>