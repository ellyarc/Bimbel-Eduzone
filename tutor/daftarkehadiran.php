<?php
ob_start();
session_start();

// Protection for tutor pages
if (!isset($_SESSION["useremail"]) || $_SESSION["role"] !== "tutor") {
    header("location:../frontend/login.php");
    exit();
}

include("../includes/config.php");

$email_tutor = mysqli_real_escape_string($conn, $_SESSION["useremail"]);

// Fetch tutor ID based on session email
$query_tutor = mysqli_query($conn, "SELECT tutor_ID FROM tutor WHERE tutor_USER = '$email_tutor' LIMIT 1");
$data_tutor  = mysqli_fetch_assoc($query_tutor);
$tutor_id    = $data_tutor ? $data_tutor['tutor_ID'] : 0;

// FIX 1: Ubah default value menjadi string kosong ('') agar jika tidak memilih tanggal, semua data tanggal langsung keluar.
$filter_tanggal = isset($_GET['filter_tanggal']) ? $_GET['filter_tanggal'] : '';
$filter_sesi    = isset($_GET['filter_sesi']) ? $_GET['filter_sesi'] : '';
$filter_tingkat = isset($_GET['filter_tingkat']) ? $_GET['filter_tingkat'] : '';
$filter_kelas   = isset($_GET['filter_kelas']) ? $_GET['filter_kelas'] : '';

// Build Dynamic Teaching Schedule Query
$query = "SELECT * FROM jadwal WHERE tutor_ID = '$tutor_id'";

if (!empty($filter_tanggal)) {
    $query .= " AND tanggal = '" . mysqli_real_escape_string($conn, $filter_tanggal) . "'";
}
if (!empty($filter_sesi)) {
    $query .= " AND sesi = '" . mysqli_real_escape_string($conn, $filter_sesi) . "'";
}
if (!empty($filter_tingkat)) {
    $query .= " AND tingkat = '" . mysqli_real_escape_string($conn, $filter_tingkat) . "'";
}
if (!empty($filter_kelas)) {
    $query .= " AND kelas = '" . mysqli_real_escape_string($conn, $filter_kelas) . "'";
}

// Urutkan berdasarkan tanggal terbaru lalu sesinya
$query .= " ORDER BY tanggal DESC, sesi ASC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule & Attendance List</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        *, body, table, th, td, select, input, button { font-family: 'Poppins', sans-serif !important; }
        .main-card { background: white; border: none; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,0.05); padding: 24px; }
        .filter-card { background: #f8f9fc; border: 1px solid #e3e6f0; border-radius: 12px; padding: 16px; }
        .table th { background-color: #f4f6f9; color: #495057; font-weight: 700; font-size: 0.85rem; text-transform: uppercase; border-bottom: 2px solid #e3e6f0; }
        .table td { font-size: 0.875rem; vertical-align: middle; color: #5a5c69; }
        .btn-action { font-size: 0.8rem; font-weight: 600; border-radius: 8px; padding: 6px 16px; }
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
                            <i class="fas fa-calendar-check me-2 text-primary"></i>Teaching Schedule &amp; Attendance
                        </h4>
                        <p class="text-muted small mb-0">Select your active schedule to submit or update class attendance records</p>
                    </div>

                    <div class="main-card">
                        <form method="GET" action="" class="filter-card mb-4">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold text-muted">Date</label>
                                    <input type="date" name="filter_tanggal" class="form-control form-control-sm" value="<?php echo htmlspecialchars($filter_tanggal); ?>">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold text-muted">Session</label>
                                    <select name="filter_sesi" class="form-select form-select-sm">
                                        <option value="">-- All Sessions --</option>
                                        <option value="1" <?php echo $filter_sesi === '1' ? 'selected' : ''; ?>>Session 1</option>
                                        <option value="2" <?php echo $filter_sesi === '2' ? 'selected' : ''; ?>>Session 2</option>
                                        <option value="3" <?php echo $filter_sesi === '3' ? 'selected' : ''; ?>>Session 3</option>
                                        <option value="4" <?php echo $filter_sesi === '4' ? 'selected' : ''; ?>>Session 4</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-muted">Education Level</label>
                                    <select name="filter_tingkat" id="filter_tingkat" class="form-select form-select-sm" onchange="updateFilterKelas()">
                                        <option value="">-- All Levels --</option>
                                        <option value="SD" <?php echo $filter_tingkat === 'SD' ? 'selected' : ''; ?>>SD (Elementary)</option>
                                        <option value="SMP" <?php echo $filter_tingkat === 'SMP' ? 'selected' : ''; ?>>SMP (Junior High)</option>
                                        <option value="SMA" <?php echo $filter_tingkat === 'SMA' ? 'selected' : ''; ?>>SMA (Senior High)</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold text-muted">Class / Grade</label>
                                    <select name="filter_kelas" id="filter_kelas" class="form-select form-select-sm">
                                        <option value="">-- All Grades --</option>
                                    </select>
                                </div>
                                <div class="col-md-3 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1"><i class="fas fa-search me-1"></i> Search</button>
                                    <a href="daftarkehadiran.php" class="btn btn-secondary btn-sm text-white"><i class="fas fa-sync-alt"></i></a>
                                </div>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Session</th>
                                        <th>Subject</th>
                                        <th>Level / Class</th>
                                        <th>Attendance Status</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    if (mysqli_num_rows($result) > 0): 
                                        while ($row = mysqli_fetch_assoc($result)):
                                            $tgl_cek = $row['tanggal'];
                                            $sesi_cek = $row['sesi'];
                                            
                                            // Validate attendance status using composite key mapping
                                            $check_absen = mysqli_query($conn, "SELECT COUNT(*) as total FROM absensi WHERE absen_DATE = '$tgl_cek' AND absen_SESSION = '$sesi_cek'");
                                            $data_absen  = mysqli_fetch_assoc($check_absen);
                                            $sudah_absen = ($data_absen['total'] > 0);
                                    ?>
                                        <tr>
                                            <td class="fw-bold text-primary">Session <?php echo htmlspecialchars($row['sesi']); ?></td>
                                            <td>
                                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['mapel']); ?></div>
                                                <div class="text-muted small"><i class="far fa-calendar-alt me-1"></i><?php echo date('d M Y', strtotime($row['tanggal'])); ?></div>
                                            </td>
                                            <td><span class="badge bg-secondary rounded-pill me-1"><?php echo $row['tingkat']; ?></span> Grade <?php echo $row['kelas']; ?></td>
                                            <td>
                                                <?php if ($sudah_absen): ?>
                                                    <span class="badge bg-success text-white px-2 py-1"><i class="fas fa-check-circle me-1"></i> Completed</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning text-dark px-2 py-1"><i class="fas fa-exclamation-circle me-1"></i> Unsubmitted</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if (!$sudah_absen): ?>
                                                    <a href="inputkehadiran.php?tanggal=<?php echo $tgl_cek; ?>&sesi=<?php echo $sesi_cek; ?>" class="btn btn-success btn-action text-white">
                                                        <i class="fas fa-user-check me-1"></i> Input Attendance
                                                    </a>
                                                <?php else: ?>
                                                    <a href="editkehadiran.php?tanggal=<?php echo $tgl_cek; ?>&sesi=<?php echo $sesi_cek; ?>" class="btn btn-warning btn-action text-dark">
                                                        <i class="fas fa-edit me-1"></i> Edit Attendance
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php 
                                        endwhile; 
                                    else: 
                                    ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-5 text-muted">No teaching schedules found matching your selected filters.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
            <?php include("../template/footer.php"); ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
    
    <script>
    function updateFilterKelas() {
        const tingkat = document.getElementById('filter_tingkat').value;
        const kelasSelect = document.getElementById('filter_kelas');
        const kelasDipilih = "<?php echo $filter_kelas; ?>"; // Mengambil data filter kelas aktif dari PHP
        
        kelasSelect.innerHTML = '<option value="">-- All Grades --</option>';
        let options = [];
        
        if (tingkat === 'SD') options = [1, 2, 3, 4, 5, 6];
        else if (tingkat === 'SMP') options = [7, 8, 9];
        else if (tingkat === 'SMA') options = [10, 11, 12];
        
        options.forEach(grade => {
            const opt = document.createElement('option');
            opt.value = grade; 
            opt.innerHTML = 'Grade ' + grade;
            
            // Cek jika grade ini yang sedang dipilih pada URL filter
            if(grade.toString() === kelasDipilih.toString()) {
                opt.selected = true;
            }
            kelasSelect.appendChild(opt);
        });
    }

    // Eksekusi fungsi otomatis saat halaman pertama kali selesai dimuat (DOM Ready)
    document.addEventListener("DOMContentLoaded", function() {
        updateFilterKelas();
    });
    </script>
</body>
</html>