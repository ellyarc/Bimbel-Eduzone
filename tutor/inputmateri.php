<?php
ob_start();
session_start();

// Proteksi halaman tutor - Ditambahkan pengecekan tutor_id
if (!isset($_SESSION["useremail"]) || $_SESSION["role"] !== "tutor") {
    header("Location:../frontend/login.php");
    exit();
}

include("../includes/config.php");

// Helper label jenjang
function tingkatLabel($t)
{
    $map = ['SD' => 'Elementary School', 'SMP' => 'Junior High School', 'SMA' => 'Senior High School'];
    return $map[$t] ?? $t;
}

// FIXED: Menggunakan session tutor_id sesuai dengan struktur tabel materi yang baru
$tutor_id = $_SESSION["tutor_id"];
$tutor_email = $_SESSION["useremail"]; // Tetap dipertahankan untuk tampilan submit bar di bawah
$success_msg = "";
$error_msg = "";

// ─── DIREKTORI UPLOAD (MURNI MANUAL TANPA AI THUMBNAIL) ─────────────────────
$upload_base = "../uploads/materi/";
$video_dir = $upload_base . "videos/";
$pdf_dir = $upload_base . "pdfs/";

foreach ([$video_dir, $pdf_dir] as $dir) {
    if (!is_dir($dir))
        mkdir($dir, 0755, true);
}

// ─── PROSES SUBMIT FORM ──────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_materi'])) {

    $title = trim(mysqli_real_escape_string($conn, $_POST['materi_title'] ?? ''));
    $desc = trim(mysqli_real_escape_string($conn, $_POST['materi_desc'] ?? ''));
    $tingkat = trim(mysqli_real_escape_string($conn, $_POST['materi_tingkat'] ?? ''));
    $kelas = intval($_POST['materi_kelas'] ?? 0);
    $mapel = trim(mysqli_real_escape_string($conn, $_POST['materi_mapel'] ?? ''));
    $tipe_konten = trim(mysqli_real_escape_string($conn, $_POST['tipe_konten'] ?? ''));
    $tutor = mysqli_real_escape_string($conn, $tutor_id); // FIXED: Mengisi nilai tutor_ID ke query, bukan email lagi

    // Backend Validation: Memastikan kecocokan Jenjang dan Grade (SD:1-6, SMP:7-9, SMA:10-12)
    $grade_valid = false;
    if ($tingkat === 'SD' && $kelas >= 1 && $kelas <= 6)
        $grade_valid = true;
    elseif ($tingkat === 'SMP' && $kelas >= 7 && $kelas <= 9)
        $grade_valid = true;
    elseif ($tingkat === 'SMA' && $kelas >= 10 && $kelas <= 12)
        $grade_valid = true;

    if (empty($title) || empty($tingkat) || empty($kelas) || empty($mapel) || empty($tipe_konten)) {
        $error_msg = "Please fill in all required fields.";
    } elseif (!$grade_valid) {
        $error_msg = "Invalid Grade for the selected Level.";
    } else {

        $video_filename = null;
        $video_path = null;
        $pdf_filename = null;
        $pdf_path = null;

        // Proses File Video (Hanya jika tipe konten adalah VIDEO)
        if ($tipe_konten === 'VIDEO' && !empty($_FILES['video_file']['name'])) {
            $video_ext = strtolower(pathinfo($_FILES['video_file']['name'], PATHINFO_EXTENSION));
            $allowed_vid = ['mp4', 'mov', 'avi', 'mkv', 'webm'];

            if (!in_array($video_ext, $allowed_vid)) {
                $error_msg = "Invalid video format. Allowed: MP4, MOV, AVI, MKV, WEBM.";
            } elseif ($_FILES['video_file']['size'] > 500 * 1024 * 1024) {
                $error_msg = "Video file is too large. Maximum size is 500MB.";
            } else {
                $video_filename = 'VID_' . time() . '_' . uniqid() . '.' . $video_ext;
                if (move_uploaded_file($_FILES['video_file']['tmp_name'], $video_dir . $video_filename)) {
                    $video_path = "uploads/materi/videos/" . $video_filename;
                } else {
                    $error_msg = "Failed to upload video.";
                }
            }
        }

        // Proses File PDF (Hanya jika tipe konten adalah SOAL_LATIHAN)
        if ($tipe_konten === 'SOAL_LATIHAN' && !empty($_FILES['pdf_file']['name'])) {
            $pdf_ext = strtolower(pathinfo($_FILES['pdf_file']['name'], PATHINFO_EXTENSION));
            if ($pdf_ext !== 'pdf') {
                $error_msg = "Only PDF files are allowed for the assignment.";
            } elseif ($_FILES['pdf_file']['size'] > 20 * 1024 * 1024) {
                $error_msg = "PDF file is too large. Maximum size is 20MB.";
            } else {
                $pdf_filename = 'PDF_' . time() . '_' . uniqid() . '.pdf';
                if (move_uploaded_file($_FILES['pdf_file']['tmp_name'], $pdf_dir . $pdf_filename)) {
                    $pdf_path = "uploads/materi/pdfs/" . $pdf_filename;
                } else {
                    $error_msg = "Failed to upload PDF.";
                }
            }
        }

        // Simpan ke Database jika validasi lolos
        if (empty($error_msg)) {
            // Set nilai ke string NULL jika filenya tidak diupload
            $vf = $video_filename ? "'" . mysqli_real_escape_string($conn, $video_filename) . "'" : "NULL";
            $vp = $video_path ? "'" . mysqli_real_escape_string($conn, $video_path) . "'" : "NULL";
            $pf = $pdf_filename ? "'" . mysqli_real_escape_string($conn, $pdf_filename) . "'" : "NULL";
            $pp = $pdf_path ? "'" . mysqli_real_escape_string($conn, $pdf_path) . "'" : "NULL";

            $useremail = $_SESSION["useremail"];

            $getTutor = mysqli_query(
                $conn,
                "SELECT tutor_ID FROM tutor WHERE tutor_USER = '$useremail'"
            );

            $dataTutor = mysqli_fetch_assoc($getTutor);
            $tutor = $dataTutor['tutor_ID']; 

            // FIXED: Kolom diubah dari tutor_USERNAME menjadi tutor_ID
            $sql = "INSERT INTO materi 
                        (materi_TITLE, materi_DESC, materi_TINGKAT, materi_KELAS, materi_MAPEL, 
                         video_FILENAME, video_PATH, pdf_FILENAME, pdf_PATH, tutor_ID)
                    VALUES 
                        ('$title', '$desc', '$tingkat', '$kelas', '$mapel', 
                         $vf, $vp, $pf, $pp, '$tutor')";

            if (mysqli_query($conn, $sql)) {
                $success_msg = "Learning material <strong>" . htmlspecialchars(stripslashes($title)) . "</strong> has been saved successfully!";
            } else {
                $error_msg = "Database error: " . mysqli_error($conn);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Learning Material</title>

    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
        *,
        body,
        input,
        select,
        textarea,
        button {
            font-family: 'Poppins', sans-serif !important;
        }

        .form-card {
            background: white;
            border: none;
            border-radius: 16px;
            box-shadow: 0 2px 16px rgba(0, 0, 0, 0.05);
        }

        .section-title {
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: #4e73df;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-title::after {
            content: '';
            flex: 1;
            height: 1.5px;
            background: #e9ecef;
        }

        .form-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: #495057;
            margin-bottom: 6px;
        }

        .form-control,
        .form-select {
            border-radius: 10px;
            border: 1.5px solid #e9ecef;
            font-size: 0.875rem;
            padding: 9px 14px;
            transition: border-color 0.2s;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #4e73df;
            outline: none;
            box-shadow: none;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        .upload-zone {
            border: 2px dashed #d0d7e3;
            border-radius: 14px;
            padding: 30px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            background: #f8f9fc;
            position: relative;
        }

        .upload-zone:hover,
        .upload-zone.drag-over {
            border-color: #4e73df;
            background: #eef1fb;
        }

        .upload-zone input[type="file"] {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
        }

        .upload-zone .zone-icon {
            font-size: 2rem;
            color: #adb5bd;
            margin-bottom: 10px;
        }

        .upload-zone .zone-text {
            font-size: 0.85rem;
            font-weight: 600;
            color: #6c757d;
        }

        .upload-zone .zone-hint {
            font-size: 0.75rem;
            color: #adb5bd;
            margin-top: 4px;
        }

        .upload-zone.has-file {
            border-color: #198754;
            background: rgba(25, 135, 84, 0.05);
        }

        .upload-zone.has-file .zone-icon {
            color: #198754;
        }

        .btn-submit {
            background: linear-gradient(135deg, #4e73df, #224abe);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 11px 35px;
            font-size: 0.875rem;
            font-weight: 600;
            box-shadow: 0 4px 14px rgba(78, 115, 223, 0.35);
            transition: all 0.2s;
        }

        .btn-submit:hover {
            opacity: 0.92;
            transform: translateY(-1px);
            color: white;
        }
    </style>
</head>

<body class="sb-nav-fixed" style="background-color: #f4f7fa;">

    <?php include("../template/menunav.php"); ?>

    <div id="layoutSidenav">

        <div id="layoutSidenav_nav">
            <?php include("../template/menu.php"); ?>
        </div>

        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4" style="padding-top: 30px; padding-bottom: 50px;">

                    <div class="mb-4">
                        <h4 class="mb-0" style="font-weight: 700; color: #1E3754;">
                            <i class="fas fa-book-open me-2 text-primary"></i>Input Learning Material
                        </h4>
                        <p class="text-muted small mb-0">Upload study materials, video, or assignments manually</p>
                    </div>

                    <?php if (!empty($success_msg)): ?>
                        <div class="alert alert-success border-0 rounded-3 shadow-sm mb-4">
                            <?php echo $success_msg; ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($error_msg)): ?>
                        <div class="alert alert-danger border-0 rounded-3 shadow-sm mb-4">
                            <?php echo $error_msg; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" enctype="multipart/form-data" id="form-materi">
                        <div class="row g-4">

                            <div class="col-lg-7">
                                <div class="form-card p-4 mb-4">
                                    <div class="section-title"><i class="fas fa-info-circle"></i> Material Information
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label">Material Title <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="materi_title" class="form-control"
                                                placeholder="e.g. Perkalian Kuadrat Dasar" required>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Level <span class="text-danger">*</span></label>
                                            <select name="materi_tingkat" id="materi_tingkat" class="form-select"
                                                onchange="updateGradeOptions()" required>
                                                <option value="">-- Select --</option>
                                                <option value="SD">Elementary School (SD)</option>
                                                <option value="SMP">Junior High School (SMP)</option>
                                                <option value="SMA">Senior High School (SMA)</option>
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Grade<span class="text-danger">*</span></label>
                                            <select name="materi_kelas" id="materi_kelas" class="form-select" required>
                                                <option value="">-- Select Level First --</option>
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Subject <span class="text-danger">*</span></label>
                                            <input type="text" name="materi_mapel" class="form-control"
                                                placeholder="e.g. Matematika" required>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label">Description</label>
                                            <textarea name="materi_desc" class="form-control"
                                                placeholder="Write material summary or descriptions here..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-5">
                                <div class="form-card p-4">
                                    <div class="section-title"><i class="fas fa-photo-video"></i> Content Type &amp;
                                        Media</div>

                                    <div class="mb-4">
                                        <label class="form-label d-block">Choose Content Type: <span
                                                class="text-danger">*</span></label>
                                        <div class="form-check form-check-inline mt-1">
                                            <input class="form-check-input" type="radio" name="tipe_konten"
                                                id="type_video" value="VIDEO" onchange="toggleMediaInput()" required>
                                            <label class="form-check-label fw-500" for="type_video">Lesson Video</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="tipe_konten"
                                                id="type_pdf" value="SOAL_LATIHAN" onchange="toggleMediaInput()">
                                            <label class="form-check-label fw-500" for="type_pdf">Soal Latihan
                                                (PDF)</label>
                                        </div>
                                    </div>

                                    <div id="input_video_wrapper" style="display:none;">
                                        <label class="form-label">Upload Video File</label>
                                        <div class="upload-zone" id="video-zone">
                                            <input type="file" name="video_file" id="video_file" accept="video/*">
                                            <div class="zone-icon"><i class="fas fa-film"></i></div>
                                            <div class="zone-text">Click or drag & drop video here</div>
                                            <div class="zone-hint">MP4, MOV, AVI · Max 500MB</div>
                                        </div>
                                        <div id="video-name" class="mt-2 small text-success" style="display:none;">
                                            <i class="fas fa-check-circle me-1"></i><span></span>
                                        </div>
                                    </div>

                                    <div id="input_pdf_wrapper" style="display:none;">
                                        <label class="form-label">Upload Worksheet / PDF File</label>
                                        <div class="upload-zone" id="pdf-zone">
                                            <input type="file" name="pdf_file" id="pdf_file" accept=".pdf">
                                            <div class="zone-icon"><i class="fas fa-file-pdf text-danger"></i></div>
                                            <div class="zone-text">Click or drag & drop PDF here</div>
                                            <div class="zone-hint">PDF format only · Max 20MB</div>
                                        </div>
                                        <div id="pdf-name" class="mt-2 small text-success" style="display:none;">
                                            <i class="fas fa-check-circle me-1"></i><span></span>
                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div>

                        <div
                            class="form-card mt-4 p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <div class="text-muted small">Submitting as
                                <strong><?php echo htmlspecialchars($tutor_email); ?></strong>
                            </div>

                            <div class="d-flex align-items-center gap-2">
                                <a href="daftarmateri.php"
                                    class="btn btn-outline-secondary rounded-pill px-4 d-inline-flex align-items-center justify-content-center"
                                    style="font-size:0.875rem; height: 42px;">Cancel</a>

                                <button type="submit" name="submit_materi" class="btn-submit"
                                    style="height: 42px; display: flex; align-items: center; justify-content: center;">Save
                                    Material</button>
                            </div>
                        </div>
                    </form>

                </div>
            </main>

            <?php include("../template/footer.php"); ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"
        crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>

    <script>
        // 1. Fungsi penentu kelas (Posisikan paling atas di dalam tag script)
        function updateGradeOptions() {
            const tingkatElement = document.getElementById('materi_tingkat');
            const kelasSelect = document.getElementById('materi_kelas');

            // Proteksi jika element tidak ditemukan di HTML
            if (!tingkatElement || !kelasSelect) {
                console.error("Element materi_tingkat atau materi_kelas tidak ditemukan!");
                return;
            }

            const tingkat = tingkatElement.value;

            // Reset dropdown kelas terlebih dahulu
            kelasSelect.innerHTML = '<option value="">-- Select --</option>';

            let options = [];

            if (tingkat === 'SD') {
                options = [1, 2, 3, 4, 5, 6];
            } else if (tingkat === 'SMP') {
                options = [7, 8, 9];
            } else if (tingkat === 'SMA') {
                options = [10, 11, 12];
            } else {
                kelasSelect.innerHTML = '<option value="">-- Select Level First --</option>';
                return;
            }

            // Generate elemen <option> baru
            options.forEach(grade => {
                const opt = document.createElement('option');
                opt.value = grade;
                opt.innerHTML = 'Grade ' + grade;
                kelasSelect.appendChild(opt);
            });
        }

        // 2. Fungsi toggleMediaInput (Untuk memisahkan Video / PDF)
        function toggleMediaInput() {
            const typeVideo = document.getElementById('type_video').checked;
            const wrapperVideo = document.getElementById('input_video_wrapper');
            const wrapperPdf = document.getElementById('input_pdf_wrapper');

            if (typeVideo) {
                if (wrapperVideo) wrapperVideo.style.display = 'block';
                if (wrapperPdf) wrapperPdf.style.display = 'none';
            } else {
                if (wrapperVideo) wrapperVideo.style.display = 'none';
                if (wrapperPdf) wrapperPdf.style.display = 'block';
            }
        }

        // 3. Fungsi setup upload zone drag and drop
        function setupUploadZone(zoneId, inputId, nameDisplayId) {
            const zone = document.getElementById(zoneId);
            const input = document.getElementById(inputId);
            const disp = document.getElementById(nameDisplayId);

            if (!zone || !input) return;

            ['dragenter', 'dragover'].forEach(e => zone.addEventListener(e, ev => {
                ev.preventDefault(); zone.classList.add('drag-over');
            }));
            ['dragleave', 'drop'].forEach(e => zone.addEventListener(e, ev => {
                ev.preventDefault(); zone.classList.remove('drag-over');
            }));
            zone.addEventListener('drop', ev => {
                ev.preventDefault();
                input.files = ev.dataTransfer.files;
                input.dispatchEvent(new Event('change'));
            });
            input.addEventListener('change', () => {
                if (input.files.length > 0) {
                    const name = input.files[0].name;
                    zone.classList.add('has-file');
                    if (disp) {
                        disp.style.display = 'block';
                        disp.querySelector('span').textContent = name;
                    }
                }
            });
        }

        // Jalankan inisialisasi zona upload
        setupUploadZone('video-zone', 'video_file', 'video-name');
        setupUploadZone('pdf-zone', 'pdf_file', 'pdf-name');
    </script>
</body>

</html>