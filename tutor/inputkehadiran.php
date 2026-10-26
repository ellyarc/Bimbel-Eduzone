<?php
ob_start();
session_start();

if (!isset($_SESSION["useremail"]) || $_SESSION["role"] !== "tutor") {
    header("Location:../frontend/login.php");
    exit();
}

include("../includes/config.php");

// Variabel ini disesuaikan untuk diinput ke kolom tutor_id
$tutor_id = mysqli_real_escape_string($conn, $_SESSION["user_id"]);

if (!isset($_GET['tanggal']) || !isset($_GET['sesi'])) {
    header("Location: daftarkehadiran.php");
    exit();
}

$tanggal = mysqli_real_escape_string($conn, $_GET['tanggal']);
$sesi    = mysqli_real_escape_string($conn, $_GET['sesi']);

// PROTEKSI AMAN: Cek apakah hari & sesi ini sebenarnya sudah pernah di-input?
// Jika sudah, langsung lempar ke halaman editkehadiran.php agar tidak terjadi double data.
$cek_data_eksisting = mysqli_query($conn, "SELECT 1 FROM absensi WHERE absen_DATE = '$tanggal' AND absen_SESSION = '$sesi' LIMIT 1");
if (mysqli_num_rows($cek_data_eksisting) > 0) {
    header("Location: editkehadiran.php?tanggal=$tanggal&sesi=$sesi");
    exit();
}

// Load data jadwal
$q_aktif = mysqli_query($conn, "SELECT * FROM jadwal WHERE tanggal = '$tanggal' AND sesi = '$sesi' LIMIT 1");
if (mysqli_num_rows($q_aktif) == 0) {
    header("Location: daftarkehadiran.php");
    exit();
}

$jadwal = mysqli_fetch_assoc($q_aktif);
$tingkat = $jadwal['tingkat'];
$kelas   = $jadwal['kelas'];

$success_msg = ""; $error_msg = "";

// Proses simpan absen BARU (INSERT MURNI)
if (isset($_POST['submit_attendance'])) {
    $attendance_data = isset($_POST['status']) ? $_POST['status'] : [];
    if (!empty($attendance_data)) {
        $has_error = false;
        foreach ($attendance_data as $id_siswa => $status) {
            $id_esc   = mysqli_real_escape_string($conn, $id_siswa);
            $st_esc   = mysqli_real_escape_string($conn, $status);
            
            // UPDATE: Nama kolom diubah dari tutor_USER menjadi tutor_id
            $sql = "INSERT INTO absensi (siswa_ID, absen_DATE, absen_SESSION, absen_STATUS, tutor_ID) 
                    VALUES ('$id_esc', '$tanggal', '$sesi', '$st_esc', '$tutor_id')";
            if (!mysqli_query($conn, $sql)) $has_error = true;
        }

        if (!$has_error) {
            $success_msg = "Attendance records saved successfully!";
            header("refresh:1.5;url=daftarkehadiran.php?filter_tanggal=".$tanggal);
        } else {
            $error_msg = "Failed to save some of the attendance records.";
        }
    }
}

// Ambil data siswa
$query_siswa = mysqli_query($conn, "SELECT siswa_ID, siswa_NAME FROM bio_siswa WHERE siswa_TINGKAT = '$tingkat' AND siswa_KELAS = '$kelas' ORDER BY siswa_NAME ASC");
$jumlah_siswa = mysqli_num_rows($query_siswa);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Input Attendance</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        *, body { font-family: 'Poppins', sans-serif !important; }
        .jadwal-info { background: linear-gradient(135deg, #198754 0%, #146c43 100%); color: white; border-radius: 12px; padding: 15px; }
        .radio-group input[type="radio"] { display: none; }
        .radio-group label { padding: 6px 14px; font-size: 0.8rem; font-weight: 500; border-radius: 20px; cursor: pointer; border: 1.5px solid #dee2e6; background: #f8f9fa; color: #6c757d; margin: 0 4px; }
        .radio-group input[type="radio"].r-present:checked + label { background: #198754; color: white; border-color: #198754; }
        .radio-group input[type="radio"].r-sick:checked + label { background: #0dcaf0; color: #000; border-color: #0dcaf0; }
        .radio-group input[type="radio"].r-excused:checked + label { background: #ffc107; color: #000; border-color: #ffc107; }
        .radio-group input[type="radio"].r-absent:checked + label { background: #dc3545; color: white; border-color: #dc3545; }
    </style>
</head>
<body class="sb-nav-fixed" style="background-color: #f4f7fa;">
    <?php include("../template/menunav.php"); ?>
    <div id="layoutSidenav">
        <div id="layoutSidenav_nav"><?php include("../template/menu.php"); ?></div>
        <div id="layoutSidenav_content">
            <main class="container-fluid px-4 pt-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="fw-bold text-dark"><i class="fas fa-user-plus text-success me-2"></i>Input New Attendance</h4>
                    </div>
                    <a href="daftarkehadiran.php?filter_tanggal=<?php echo $tanggal; ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
                </div>

                <?php if($success_msg): ?> <div class="alert alert-success"><?php echo $success_msg; ?></div> <?php endif; ?>
                <?php if($error_msg): ?> <div class="alert alert-danger"><?php echo $error_msg; ?></div> <?php endif; ?>

                <div class="jadwal-info mb-4">
                    <small class="d-block text-white-50 fw-bold text-uppercase">Class Details</small>
                    <strong><?php echo $jadwal['mapel']; ?></strong> — <?php echo $tingkat; ?> Grade <?php echo $kelas; ?> | Session <?php echo $sesi; ?> (<?php echo date('d M Y', strtotime($tanggal)); ?>)
                </div>

                <form method="POST" action="">
                    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%" class="ps-3">#</th>
                                    <th>Student Full Name</th>
                                    <th class="text-center" width="55%">Attendance Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($jumlah_siswa > 0): $no=1; while($s = mysqli_fetch_assoc($query_siswa)): ?>
                                <tr>
                                    <td class="ps-3"><?php echo $no++; ?></td>
                                    <td class="fw-bold"><?php echo htmlspecialchars($s['siswa_NAME']); ?></td>
                                    <td>
                                        <div class="radio-group d-flex justify-content-center">
                                            <input type="radio" id="P_<?php echo $s['siswa_ID']; ?>" name="status[<?php echo $s['siswa_ID']; ?>]" value="Present" class="r-present" checked>
                                            <label for="P_<?php echo $s['siswa_ID']; ?>">Present</label>
                                            
                                            <input type="radio" id="S_<?php echo $s['siswa_ID']; ?>" name="status[<?php echo $s['siswa_ID']; ?>]" value="Sick" class="r-sick">
                                            <label for="S_<?php echo $s['siswa_ID']; ?>">Sick</label>
                                            
                                            <input type="radio" id="E_<?php echo $s['siswa_ID']; ?>" name="status[<?php echo $s['siswa_ID']; ?>]" value="Excused" class="r-excused">
                                            <label for="E_<?php echo $s['siswa_ID']; ?>">Excused</label>
                                            
                                            <input type="radio" id="A_<?php echo $s['siswa_ID']; ?>" name="status[<?php echo $s['siswa_ID']; ?>]" value="Absent" class="r-absent">
                                            <label for="A_<?php echo $s['siswa_ID']; ?>">Absent</label>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; else: ?>
                                <tr><td colspan="3" class="text-center py-4">No students found assigned to this class configuration.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <?php if($jumlah_siswa > 0): ?>
                        <div class="card-footer bg-white text-end py-3">
                            <button type="submit" name="submit_attendance" class="btn btn-success px-4"><i class="fas fa-save me-1"></i> Save Attendance</button>
                        </div>
                        <?php endif; ?>
                    </div>
                </form>
            </main>
        </div>
    </div>
</body>
</html>