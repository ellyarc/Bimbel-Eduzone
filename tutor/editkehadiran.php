<?php
ob_start();
session_start();

// Protection for tutor pages
if (!isset($_SESSION["useremail"]) || $_SESSION["role"] !== "tutor") {
    header("Location:../frontend/login.php");
    exit();
}

include("../includes/config.php");

// FIXED: Mengubah nama variabel agar selaras dengan query UPDATE di bawah ($tutor_id)
$tutor_id = mysqli_real_escape_string($conn, $_SESSION["user_id"]);

if (!isset($_GET['tanggal']) || !isset($_GET['sesi'])) {
    header("Location: daftarkehadiran.php");
    exit();
}

$tanggal = mysqli_real_escape_string($conn, $_GET['tanggal']);
$sesi    = mysqli_real_escape_string($conn, $_GET['sesi']);

// Load active schedule data
$q_aktif = mysqli_query($conn, "SELECT * FROM jadwal WHERE tanggal = '$tanggal' AND sesi = '$sesi' LIMIT 1");
if (mysqli_num_rows($q_aktif) == 0) {
    header("Location: daftarkehadiran.php");
    exit();
}

$jadwal = mysqli_fetch_assoc($q_aktif);
$tingkat = $jadwal['tingkat'];
$kelas   = $jadwal['kelas'];

$success_msg = ""; $error_msg = "";

// Proses update absen yang sudah ada (UPDATE)
if (isset($_POST['update_attendance'])) {
    $attendance_data = isset($_POST['status']) ? $_POST['status'] : [];
    if (!empty($attendance_data)) {
        $has_error = false;
        foreach ($attendance_data as $id_siswa => $status) {
            $id_esc   = mysqli_real_escape_string($conn, $id_siswa);
            $st_esc   = mysqli_real_escape_string($conn, $status);
            
            // Menggunakan query UPDATE murni agar tidak menambah baris baru di database
            // Kolom sudah menggunakan tutor_id dan variabel $tutor_id
            $sql = "UPDATE absensi 
                    SET absen_STATUS = '$st_esc', tutor_ID = '$tutor_id' 
                    WHERE siswa_ID = '$id_esc' AND absen_DATE = '$tanggal' AND absen_SESSION = '$sesi'";
                    
            if (!mysqli_query($conn, $sql)) {
                $has_error = true;
            }
        }

        if (!$has_error) {
            $success_msg = "Attendance records updated successfully!";
            header("refresh:1.5;url=daftarkehadiran.php?filter_tanggal=".$tanggal);
        } else {
            $error_msg = "Failed to update some of the attendance records.";
        }
    }
}

// Fetch student roster
$query_siswa = mysqli_query($conn, "SELECT siswa_ID, siswa_NAME FROM bio_siswa WHERE siswa_TINGKAT = '$tingkat' AND siswa_KELAS = '$kelas' ORDER BY siswa_NAME ASC");
$jumlah_siswa = mysqli_num_rows($query_siswa);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Attendance</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        *, body { font-family: 'Poppins', sans-serif !important; }
        .jadwal-info { background: linear-gradient(135deg, #ffc107 0%, #d39e00 100%); color: #212529; border-radius: 12px; padding: 15px; }
        .radio-group input[type="radio"] { display: none; }
        .radio-group label { padding: 6px 14px; font-size: 0.8rem; font-weight: 500; border-radius: 20px; cursor: pointer; border: 1.5px solid #dee2e6; background: #f8f9fa; color: #6c757d; margin: 0 4px; transition: all 0.2s ease; }
        
        .radio-group input[type="radio"].r-present:checked + label { background: #198754; color: white; border-color: #198754; }
        .radio-group input[type="radio"].r-sick:checked + label { background: #0dcaf0; color: #000; border-color: #0dcaf0; }
        .radio-group input[type="radio"].r-excused:checked + label { background: #ffc107; color: #000; border-color: #ffc107; }
        .radio-group input[type="radio"].r-absent:checked + label { background: #dc3545; color: white; border-color: #dc3545; }

        /* Solusi Z-index Overlay */
        .sb-topnav { z-index: 1030 !important; }
        #layoutSidenav_nav { z-index: 1020 !important; }
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
                        <h4 class="fw-bold text-dark"><i class="fas fa-edit text-warning me-2"></i>Edit Attendance Records</h4>
                    </div>
                    <a href="daftarkehadiran.php?filter_tanggal=<?php echo $tanggal; ?>" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
                </div>

                <?php if($success_msg): ?> <div class="alert alert-success"><?php echo $success_msg; ?></div> <?php endif; ?>
                <?php if($error_msg): ?> <div class="alert alert-danger"><?php echo $error_msg; ?></div> <?php endif; ?>

                <div class="jadwal-info mb-4">
                    <small class="d-block text-dark-50 fw-bold text-uppercase">Class Details (Edit Mode)</small>
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
                                <?php 
                                if($jumlah_siswa > 0): 
                                    $no = 1; 
                                    while($s = mysqli_fetch_assoc($query_siswa)): 
                                        $id_siswa_cek = $s['siswa_ID'];
                                        
                                        // Ambil data yang sudah ada di database
                                        $q_absen_lama = mysqli_query($conn, "SELECT absen_STATUS FROM absensi WHERE siswa_ID = '$id_siswa_cek' AND absen_DATE = '$tanggal' AND absen_SESSION = '$sesi' LIMIT 1");
                                        $status_lama = "Present"; // Default fallback
                                        if (mysqli_num_rows($q_absen_lama) > 0) {
                                            $d_absen_lama = mysqli_fetch_assoc($q_absen_lama);
                                            $status_lama = $d_absen_lama['absen_STATUS'];
                                        }
                                ?>
                                <tr>
                                    <td class="ps-3"><?php echo $no++; ?></td>
                                    <td class="fw-bold"><?php echo htmlspecialchars($s['siswa_NAME']); ?></td>
                                    <td>
                                        <div class="radio-group d-flex justify-content-center">
                                            <input type="radio" id="P_<?php echo $s['siswa_ID']; ?>" name="status[<?php echo $s['siswa_ID']; ?>]" value="Present" class="r-present" <?php echo ($status_lama === 'Present') ? 'checked' : ''; ?>>
                                            <label for="P_<?php echo $s['siswa_ID']; ?>">Present</label>
                                            
                                            <input type="radio" id="S_<?php echo $s['siswa_ID']; ?>" name="status[<?php echo $s['siswa_ID']; ?>]" value="Sick" class="r-sick" <?php echo ($status_lama === 'Sick') ? 'checked' : ''; ?>>
                                            <label for="S_<?php echo $s['siswa_ID']; ?>">Sick</label>
                                            
                                            <input type="radio" id="E_<?php echo $s['siswa_ID']; ?>" name="status[<?php echo $s['siswa_ID']; ?>]" value="Excused" class="r-excused" <?php echo ($status_lama === 'Excused') ? 'checked' : ''; ?>>
                                            <label for="E_<?php echo $s['siswa_ID']; ?>">Excused</label>
                                            
                                            <input type="radio" id="A_<?php echo $s['siswa_ID']; ?>" name="status[<?php echo $s['siswa_ID']; ?>]" value="Absent" class="r-absent" <?php echo ($status_lama === 'Absent') ? 'checked' : ''; ?>>
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
                            <button type="submit" name="update_attendance" class="btn btn-warning text-dark px-4 fw-bold"><i class="fas fa-save me-1"></i> Update Attendance</button>
                        </div>
                        <?php endif; ?>
                    </div>
                </form>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
</body>
</html>