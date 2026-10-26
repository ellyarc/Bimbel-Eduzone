<?php
ob_start();
session_start();

// PROTECTION: Jika belum login atau role bukan tutor, tendang kembali ke login.php
if (!isset($_SESSION["useremail"]) || $_SESSION["role"] !== "tutor") {
    header("Location:../frontend/login.php");
    exit();
}

// Menghubungkan ke database
include("../includes/config.php");

// Ambil email tutor dari session
$email_tutor = mysqli_real_escape_string($conn, $_SESSION["useremail"]);

// [FIXED SUPER AMAN]: Menggunakan 'tutor_ID' sesuai tebakan struktur database kamu
$query_user  = mysqli_query($conn, "SELECT tutor_ID FROM tutor WHERE tutor_USER = '$email_tutor' LIMIT 1"); 
$data_user   = mysqli_fetch_assoc($query_user);
$tutor_id    = $data_user['tutor_ID'] ?? 0;

// ====================================================================
// FETCH REAL OPERATIONAL DATA SPESIFIK UNTUK TUTOR YANG LOGIN
// ====================================================================

// 1. Menghitung Total Siswa Unik yang Diajar oleh Tutor Ini
$query_siswa = mysqli_query($conn, "SELECT COUNT(DISTINCT s.siswa_ID) as total 
                                    FROM bio_siswa s 
                                    INNER JOIN jadwal j ON s.siswa_TINGKAT = j.tingkat AND s.siswa_KELAS = j.kelas 
                                    WHERE j.tutor_ID = '$tutor_id'");
$data_siswa = mysqli_fetch_assoc($query_siswa);
$total_siswa = $data_siswa['total'] ?? 0;

// 2. Menghitung Total Video Pembelajaran di tabel 'materi' (Mencari berdasarkan ID angka tutor)
$query_video = mysqli_query($conn, "SELECT COUNT(*) as total FROM materi WHERE tutor_ID = (SELECT tutor_USER FROM tutor WHERE tutor_ID = '$tutor_id') AND video_FILENAME IS NOT NULL AND video_FILENAME != ''");
// Jika query di atas bernilai 0 terus, nanti kita sesuaikan relasi materi ke tutornya. Yang penting jalankan dulu tanpa error!
$data_video = mysqli_fetch_assoc($query_video);
$total_video = $data_video['total'] ?? 0;

// 3. Menghitung Total Soal / Bank Materi Tugas di tabel 'materi'
$query_soal = mysqli_query($conn, "SELECT COUNT(*) as total FROM materi WHERE tutor_ID = (SELECT tutor_USER FROM tutor WHERE tutor_ID = '$tutor_id') AND pdf_FILENAME IS NOT NULL AND pdf_FILENAME != ''");
$data_soal = mysqli_fetch_assoc($query_soal);
$total_soal = $data_soal['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body, h1, h2, h3, h4, h5, h6, p, span, table, div {
            font-family: 'Poppins', sans-serif !important;
        }

        .bg-gradient-tutor {
            background: linear-gradient(135deg, #1E3754 0%, #2a4e78 100%);
        }

        .stat-card {
            border: none;
            border-radius: 15px;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>

<?php include("../template/head.php"); ?>

<body class="sb-nav-fixed" style="background-color: #f4f7fa;">

    <?php include("../template/menunav.php"); ?>

    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <?php include("../template/menu.php"); ?>
        </div>

        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4" style="padding-top: 30px;">

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="mb-0" style="font-weight: 700; color: #1E3754;">Tutor Center</h4>
                            <p class="text-muted small mb-0">Overview of your academic teaching assignments and shared learning tracks</p>
                        </div>
                        <div>
                            <span class="badge bg-white text-dark border px-3 py-2 rounded-pill shadow-sm small">
                                <i class="far fa-calendar-alt me-1 text-primary"></i> <?php echo date('d M Y'); ?>
                            </span>
                        </div>
                    </div>

                    <div class="card stat-card shadow-sm overflow-hidden mb-4 bg-gradient-tutor">
                        <div class="card-body p-4 p-md-5 text-white">
                            <div class="row align-items-center">
                                <div class="col-md-9">
                                    <span class="badge bg-light text-dark px-3 py-1.5 rounded-pill mb-3" style="font-weight: 600; font-size: 0.75rem;">
                                        LOGGED IN AS TUTOR
                                    </span>
                                    <h1 class="mb-2" style="font-weight: 700; letter-spacing: -1px;">
                                        Hello, <?php echo htmlspecialchars($_SESSION["useremail"]); ?>!
                                    </h1>
                                    <p class="mb-0" style="color: #cbd5e1; font-size: 0.95rem; font-weight: 300;">
                                        Welcome back to your workstation. Here you can efficiently review assigned tutoring schedules, oversee your active student rosters, check metrics for uploaded digital visual components, and distribute question assignments.
                                    </p>
                                </div>
                                <div class="col-md-3 text-end d-none d-md-block opacity-25">
                                    <i class="fas fa-chalkboard-teacher fa-6x"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-xl-4 col-md-6 mb-3">
                            <div class="card stat-card shadow-sm bg-white border-start border-primary border-4">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <p class="text-muted small text-uppercase mb-1" style="font-weight: 600;">My Active Students</p>
                                            <h2 class="mb-0" style="font-weight: 700; color: #1E3754;">
                                                <?php echo $total_siswa; ?>
                                                <span style="font-size: 1rem; font-weight: 400;" class="text-muted">Assigned</span>
                                            </h2>
                                        </div>
                                        <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                                            <i class="fas fa-user-graduate fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-md-6 mb-3">
                            <div class="card stat-card shadow-sm bg-white border-start border-success border-4">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <p class="text-muted small text-uppercase mb-1" style="font-weight: 600;">Shared Learning Videos</p>
                                            <h2 class="mb-0" style="font-weight: 700; color: #1E3754;">
                                                <?php echo $total_video; ?>
                                                <span style="font-size: 1rem; font-weight: 400;" class="text-muted">Uploaded</span>
                                            </h2>
                                        </div>
                                        <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle">
                                            <i class="fas fa-video fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-md-6 mb-3">
                            <div class="card stat-card shadow-sm bg-white border-start border-warning border-4">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <p class="text-muted small text-uppercase mb-1" style="font-weight: 600;">Question Banks & Materials</p>
                                            <h2 class="mb-0" style="font-weight: 700; color: #1E3754;">
                                                <?php echo $total_soal; ?>
                                                <span style="font-size: 1rem; font-weight: 400;" class="text-muted">Published</span>
                                            </h2>
                                        </div>
                                        <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-circle">
                                            <i class="fas fa-file-alt fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card stat-card shadow-sm border-0 mb-5">
                        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                            <h6 class="m-0" style="font-weight: 700; color: #1E3754;">
                                <i class="fas fa-chalkboard me-2 text-primary"></i> My Personal Teaching Schedules
                            </h6>
                            <span class="badge bg-primary rounded-pill small">Your Allocated Slots</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" style="min-width: 600px;">
                                    <thead class="table-light text-muted small text-uppercase">
                                        <tr>
                                            <th class="ps-4">Date</th>
                                            <th>Session</th>
                                            <th>Subject</th>
                                            <th>Grade Level</th>
                                            <th>Class Room</th>
                                            <th class="pe-4 text-center">Action Log</th>
                                        </tr>
                                    </thead>
                                    <tbody class="small">
                                        <?php
                                        // Mengambil daftar jadwal mengajar KHUSUS milik tutor yang login
                                        $ambil_jadwal = mysqli_query($conn, "SELECT * FROM jadwal 
                                                                             WHERE tutor_ID = '$tutor_id' 
                                                                             ORDER BY tanggal DESC, sesi ASC LIMIT 10");

                                        if ($ambil_jadwal && mysqli_num_rows($ambil_jadwal) > 0) {
                                            while ($row = mysqli_fetch_assoc($ambil_jadwal)) {
                                                echo '<tr>';
                                                echo '<td class="ps-4 fw-bold text-dark">' . date('d M Y', strtotime($row['tanggal'])) . '</td>';
                                                echo '<td>Session ' . htmlspecialchars($row['sesi']) . '</td>';
                                                echo '<td><span class="badge bg-secondary px-2.5 py-1.5">' . htmlspecialchars($row['mapel']) . '</span></td>';
                                                echo '<td>' . htmlspecialchars($row['tingkat']) . ' Grade</td>';
                                                echo '<td>Room ' . htmlspecialchars($row['kelas']) . '</td>';
                                                echo '<td class="pe-4 text-center">
                                                        <a href="editkehadiran.php?tanggal=' . urlencode($row['tanggal']) . '&sesi=' . urlencode($row['sesi']) . '" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                            <i class="fas fa-user-check me-1"></i> Attendance
                                                        </a>
                                                      </td>';
                                                echo '</tr>';
                                            }
                                        } else {
                                            ?>
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-5">
                                                    <i class="fas fa-folder-open fa-2x mb-2 text-secondary d-block"></i>
                                                    No teaching roster assigned to your account. Please contact Academic Admin support if this is an error.
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </main>

            <?php include("../template/footer.php"); ?>
        </div>
    </div>

    <?php include("../template/jsscript.php"); ?>
</body>

</html>
<?php
mysqli_close($conn);
ob_end_flush();
?>