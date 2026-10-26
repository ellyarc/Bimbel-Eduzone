<?php
ob_start();
session_start();

// 1. Pengecekan Login & Role
if (!isset($_SESSION["useremail"]) || $_SESSION["role"] !== "tutor") {
    header("location:../frontend/login.php");
    exit();
}

include("../includes/config.php");
$useremail = $_SESSION["useremail"];

// 2. Ambil tutor_ID berdasarkan email session (Meniru daftarmateri.php)
$queryTutor = mysqli_query($conn, "SELECT tutor_ID FROM tutor WHERE tutor_USER = '$useremail'");
$dataTutor = mysqli_fetch_assoc($queryTutor);
$tutor_id = $dataTutor['tutor_ID'];

// 3. Tangkap ID dari URL (?materi_ID= atau ?id=)
$materi_id = 0;
if (isset($_GET['materi_ID'])) {
    $materi_id = intval($_GET['materi_ID']);
} elseif (isset($_GET['id'])) {
    $materi_id = intval($_GET['id']);
}

if ($materi_id == 0) {
    header("Location: daftarmateri.php");
    exit();
}

// 4. Load data materi berdasarkan struktur database asli (materi_ID & tutor_ID)
$q_materi = mysqli_query($conn, "SELECT * FROM materi WHERE materi_ID = $materi_id AND tutor_ID = '$tutor_id' LIMIT 1");
if (mysqli_num_rows($q_materi) == 0) {
    header("Location: daftarmateri.php");
    exit();
}

$materi = mysqli_fetch_assoc($q_materi);
$success_msg = ""; 
$error_msg = "";

// Direktori Upload disesuaikan dengan inputmateri.php
$upload_base = "../uploads/materi/";
$video_dir = $upload_base . "videos/";
$pdf_dir = $upload_base . "pdfs/";

// 5. Proses Update Materi
if (isset($_POST['update_materi'])) {
    $judul     = trim(mysqli_real_escape_string($conn, $_POST['judul']));
    $deskripsi = trim(mysqli_real_escape_string($conn, $_POST['deskripsi']));
    $tingkat   = trim(mysqli_real_escape_string($conn, $_POST['tingkat']));
    $kelas     = intval($_POST['kelas']);
    $mapel     = trim(mysqli_real_escape_string($conn, $_POST['mapel']));
    
    $video_filename = $materi['video_FILENAME'];
    $video_path     = $materi['video_PATH'];
    $pdf_filename   = $materi['pdf_FILENAME'];
    $pdf_path       = $materi['pdf_PATH'];

    // Cek upload Video baru
    if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] == 0) {
        $video_ext = strtolower(pathinfo($_FILES['video_file']['name'], PATHINFO_EXTENSION));
        $video_filename = 'VID_' . time() . '_' . uniqid() . '.' . $video_ext;
        if (move_uploaded_file($_FILES['video_file']['tmp_name'], $video_dir . $video_filename)) {
            // Hapus file video lama jika ada
            if (!empty($materi['video_PATH']) && file_exists("../" . $materi['video_PATH'])) {
                unlink("../" . $materi['video_PATH']);
            }
            $video_path = "uploads/materi/videos/" . $video_filename;
        }
    }

    // Cek upload PDF baru
    if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] == 0) {
        $pdf_ext = strtolower(pathinfo($_FILES['pdf_file']['name'], PATHINFO_EXTENSION));
        $pdf_filename = 'PDF_' . time() . '_' . uniqid() . '.pdf';
        if (move_uploaded_file($_FILES['pdf_file']['tmp_name'], $pdf_dir . $pdf_filename)) {
            // Hapus file PDF lama jika ada
            if (!empty($materi['pdf_PATH']) && file_exists("../" . $materi['pdf_PATH'])) {
                unlink("../" . $materi['pdf_PATH']);
            }
            $pdf_path = "uploads/materi/pdfs/" . $pdf_filename;
        }
    }

    // Update Query disesuaikan dengan kolom database yang asli di foto
    $vf = $video_filename ? "'" . mysqli_real_escape_string($conn, $video_filename) . "'" : "NULL";
    $vp = $video_path ? "'" . mysqli_real_escape_string($conn, $video_path) . "'" : "NULL";
    $pf = $pdf_filename ? "'" . mysqli_real_escape_string($conn, $pdf_filename) . "'" : "NULL";
    $pp = $pdf_path ? "'" . mysqli_real_escape_string($conn, $pdf_path) . "'" : "NULL";

    $sql_update = "UPDATE materi SET 
                    materi_TITLE = '$judul', 
                    materi_DESC = '$deskripsi', 
                    materi_TINGKAT = '$tingkat', 
                    materi_KELAS = '$kelas', 
                    materi_MAPEL = '$mapel', 
                    video_FILENAME = $vf,
                    video_PATH = $vp, 
                    pdf_FILENAME = $pf,
                    pdf_PATH = $pp 
                   WHERE materi_ID = $materi_id AND tutor_ID = '$tutor_id'";

    if (mysqli_query($conn, $sql_update)) {
        $success_msg = "Learning material updated successfully!";
        header("refresh:1.5;url=daftarmateri.php");
    } else {
        $error_msg = "Failed to update learning material: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Material</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        *, body { font-family: 'Poppins', sans-serif !important; }
        .material-info { background: linear-gradient(135deg, #ffc107 0%, #d39e00 100%); color: #212529; border-radius: 12px; padding: 15px; }
        .sb-topnav { z-index: 1030 !important; }
        #layoutSidenav_nav { z-index: 1020 !important; }
        .upload-zone { border: 2px dashed #d0d7e3; border-radius: 14px; padding: 20px; text-align: center; background: #f8f9fc; position: relative; }
        .upload-zone input[type="file"] { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
    </style>
</head>
<body class="sb-nav-fixed" style="background-color: #f4f7fa;">
    <?php include("../template/menunav.php"); ?>
    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <?php include("../template/menu.php"); ?>
        </div>
        <div id="layoutSidenav_content">
            <main class="container-fluid px-4 pt-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="fw-bold text-dark"><i class="fas fa-edit text-warning me-2"></i>Edit Learning Material</h4>
                    </div>
                    <a href="daftarmateri.php" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
                </div>

                <?php if($success_msg): ?> <div class="alert alert-success"><?php echo $success_msg; ?></div> <?php endif; ?>
                <?php if($error_msg): ?> <div class="alert alert-danger"><?php echo $error_msg; ?></div> <?php endif; ?>

                <div class="material-info mb-4">
                    <small class="d-block text-dark-50 fw-bold text-uppercase">Material Identity (Edit Mode)</small>
                    <strong><?php echo htmlspecialchars($materi['materi_TITLE']); ?></strong> — <?php echo htmlspecialchars($materi['materi_TINGKAT']); ?> Grade <?php echo htmlspecialchars($materi['materi_KELAS']); ?> | <?php echo htmlspecialchars($materi['materi_MAPEL']); ?>
                </div>

                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="card border-0 shadow-sm rounded-3 p-4 mb-4 bg-white">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Material Title</label>
                            <input type="text" name="judul" class="form-control" value="<?php echo htmlspecialchars($materi['materi_TITLE']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Description</label>
                            <textarea name="deskripsi" class="form-control" rows="4" required><?php echo htmlspecialchars($materi['materi_DESC']); ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Grade Level (Tingkat)</label>
                                <select name="tingkat" id="materi_tingkat" class="form-select" onchange="updateGradeOptions()" required>
                                    <option value="SD" <?php echo ($materi['materi_TINGKAT'] == 'SD') ? 'selected' : ''; ?>>Elementary School (SD)</option>
                                    <option value="SMP" <?php echo ($materi['materi_TINGKAT'] == 'SMP') ? 'selected' : ''; ?>>Junior High School (SMP)</option>
                                    <option value="SMA" <?php echo ($materi['materi_TINGKAT'] == 'SMA') ? 'selected' : ''; ?>>Senior High School (SMA)</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Class (Kelas)</label>
                                <select name="kelas" id="materi_kelas" class="form-select" required>
                                    </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Subject (Mapel)</label>
                                <input type="text" name="mapel" class="form-control" value="<?php echo htmlspecialchars($materi['materi_MAPEL']); ?>" required>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Video File <span class="text-muted fw-normal">(Leave blank if unchanged)</span></label>
                                <div class="upload-zone">
                                    <input type="file" name="video_file" accept="video/*">
                                    <div class="zone-text"><i class="fas fa-film me-2"></i> Click to change Video</div>
                                </div>
                                <?php if(!empty($materi['video_PATH'])): ?>
                                    <small class="text-success d-block mt-1"><i class="fas fa-video me-1"></i> Current video: <?php echo htmlspecialchars($materi['video_FILENAME']); ?></small>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">PDF Document <span class="text-muted fw-normal">(Leave blank if unchanged)</span></label>
                                <div class="upload-zone">
                                    <input type="file" name="pdf_file" accept="application/pdf">
                                    <div class="zone-text"><i class="fas fa-file-pdf text-danger me-2"></i> Click to change PDF</div>
                                </div>
                                <?php if(!empty($materi['pdf_PATH'])): ?>
                                    <small class="text-danger d-block mt-1"><i class="fas fa-file-pdf me-1"></i> Current PDF: <?php echo htmlspecialchars($materi['pdf_FILENAME']); ?></small>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="text-end card-footer bg-white border-0 px-0 pt-3">
                            <button type="submit" name="update_materi" class="btn btn-warning text-dark px-4 fw-bold"><i class="fas fa-save me-1"></i> Update Material</button>
                        </div>
                    </div>
                </form>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script>
        function updateGradeOptions(currentGrade = null) {
            const tingkat = document.getElementById('materi_tingkat').value;
            const kelasSelect = document.getElementById('materi_kelas');
            kelasSelect.innerHTML = '<option value="">-- Select --</option>';
            let options = [];
            if (tingkat === 'SD') options = [1, 2, 3, 4, 5, 6];
            else if (tingkat === 'SMP') options = [7, 8, 9];
            else if (tingkat === 'SMA') options = [10, 11, 12];

            options.forEach(grade => {
                const opt = document.createElement('option');
                opt.value = grade;
                opt.innerHTML = 'Grade ' + grade;
                if(currentGrade && grade == currentGrade) opt.selected = true;
                kelasSelect.appendChild(opt);
            });
        }
        // Jalankan pas pertama kali load halaman biar dropdown kelas otomatis terpilih
        updateGradeOptions(<?php echo intval($materi['materi_KELAS']); ?>);
    </script>
</body>
</html>