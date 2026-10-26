<?php 
ob_start();
session_start();
if (!isset($_SESSION["useremail"])) {
    header("location:../frontend/login.php");
    exit();
}
?>
<?php include("../template/head.php"); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* Global Font Alignment */
        body, h1, h2, h3, h4, h5, h6, p, span, table, th, td, input, select, label, button {
            font-family: 'Poppins', sans-serif !important;
        }
        
        /* Modern Card Design */
        .custom-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            background: #ffffff;
            margin-bottom: 30px;
        }
        .card-header-custom {
            background-color: #ffffff;
            border-bottom: 1px solid #edf2f9;
            padding: 18px 24px;
            border-top-left-radius: 15px !important;
            border-top-right-radius: 15px !important;
        }
        
        /* Form & Input Styling */
        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #d1d3e2;
            padding: 10px 15px;
        }
        .form-control:focus, .form-select:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }
        .input-group-text {
            border-radius: 8px 0 0 8px;
            background-color: #f8f9fc;
            border: 1px solid #d1d3e2;
            color: #4e73df;
        }
        .input-group .form-control, .input-group .form-select {
            border-radius: 0 8px 8px 0;
        }
        
        /* Pill Button */
        .btn-pill {
            border-radius: 30px;
            padding: 8px 24px;
            font-weight: 500;
        }
    </style>
</head>

<body class="sb-nav-fixed" style="background-color: #f4f7fa;">
    <?php include("../template/menunav.php"); ?>

    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <?php include("menu.php"); ?>
        </div>

        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    
                    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
                        <div>
                            <h2 style="font-weight: 700; color: #1E3754; margin-bottom: 2px;">Edit Schedule</h2>
                            <p class="text-muted small mb-0">Update or re-adjust the tutor's teaching schedule information</p>
                        </div>
                    </div>

                    <?php
                    include("../includes/config.php");

                    // 1. FETCH OLD DATA BASED ON URL PARAMETERS (DATE & SESSION)
                    if (isset($_GET["ubahjadwal"]) && isset($_GET["sesi"])) {
                        $tanggal_lama = mysqli_real_escape_string($conn, $_GET["ubahjadwal"]);
                        $sesi_lama = mysqli_real_escape_string($conn, $_GET["sesi"]);

                        $query_select = mysqli_query($conn, "SELECT * FROM jadwal WHERE tanggal = '$tanggal_lama' AND sesi = '$sesi_lama'");
                        
                        if (mysqli_num_rows($query_select) > 0) {
                            $data = mysqli_fetch_array($query_select);
                            $tutor_ID_old = $data["tutor_ID"];
                            $tingkat_old = $data["tingkat"];
                            $kelas_old = $data["kelas"];
                            $mapel_old = $data["mapel"];
                        } else {
                            echo "<div class='alert alert-danger border-0 shadow-sm' style='border-radius:10px;'>Schedule data not found!</div>";
                            exit;
                        }
                    } else {
                        header("location:inputjadwal.php");
                        exit;
                    }

                    // 2. PROCESS DATA UPDATE WHEN "Update" BUTTON IS CLICKED
                    if (isset($_POST["Update"])) {
                        $tutor_ID_new = mysqli_real_escape_string($conn, $_POST["tutorID"]);
                        $tanggal_new = mysqli_real_escape_string($conn, $_POST["tanggal"]);
                        $sesi_new = mysqli_real_escape_string($conn, $_POST["sesi"]);
                        $tingkat_new = mysqli_real_escape_string($conn, $_POST['tingkat']);
                        $kelas_new = mysqli_real_escape_string($conn, $_POST["kelas"]);
                        $mapel_new = mysqli_real_escape_string($conn, $_POST["mapel"]);

                        // Validation: If the timeslot is moved, make sure the new slot is not already taken by another schedule
                        if ($tanggal_new != $tanggal_lama || $sesi_new != $sesi_lama) {
                            $cek_jadwal = mysqli_query($conn, "SELECT * FROM jadwal WHERE tanggal = '$tanggal_new' AND sesi = '$sesi_new'");
                            if (mysqli_num_rows($cek_jadwal) > 0) {
                                echo "<script>
                                    alert('Failed to Update! A class already exists on date $tanggal_new for session $sesi_new.');
                                    window.location.href = 'editjadwal.php?ubahjadwal=" . urlencode($tanggal_lama) . "&sesi=" . urlencode($sesi_lama) . "';
                                </script>";
                                exit();
                            }
                        }

                        // Execute Update
                        $query_update = mysqli_query($conn, "UPDATE jadwal SET 
                                            tutor_ID = '$tutor_ID_new', 
                                            tanggal = '$tanggal_new', 
                                            sesi = '$sesi_new', 
                                            tingkat = '$tingkat_new', 
                                            kelas = '$kelas_new', 
                                            mapel = '$mapel_new' 
                                            WHERE tanggal = '$tanggal_lama' AND sesi = '$sesi_lama'");

                        if ($query_update) {
                            header("location:inputjadwal.php");
                            exit;
                        } else {
                            echo "<div class='alert alert-danger border-0 shadow-sm' style='border-radius:10px;'>Failed to update schedule: " . mysqli_error($conn) . "</div>";
                        }
                    }

                    // Fetch tutor list to populate the dropdown options
                    $datatutor = mysqli_query($conn, "SELECT tutor_ID, tutor_NAME FROM bio_tutor");
                    ?>

                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card custom-card">
                                <div class="card-header-custom">
                                    <h5 class="m-0" style="font-weight: 700; color: #4e73df;">
                                        <i class="fas fa-edit me-2"></i> Schedule Adjustment Form
                                    </h5>
                                </div>
                                <div class="card-body p-4">
                                    <form method="POST">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Tutor Name</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                                                    <select class="form-select" name="tutorID" required>
                                                        <option value="">-- Choose Tutor --</option>
                                                        <?php while ($t = mysqli_fetch_array($datatutor)) { ?>
                                                            <option value="<?php echo $t["tutor_ID"]; ?>" <?php if($t["tutor_ID"] == $tutor_ID_old) echo "selected"; ?>>
                                                                <?php echo htmlspecialchars($t["tutor_NAME"]); ?>
                                                            </option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Date</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
                                                    <input type="date" class="form-control" name="tanggal" value="<?php echo htmlspecialchars($tanggal_lama); ?>" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Session</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-clock"></i></span>
                                                    <select class="form-select" name="sesi" required>
                                                        <option value="">-- Choose Session --</option>
                                                        <option value="1" <?php if($sesi_lama == "1") echo "selected"; ?>>Session 1</option>
                                                        <option value="2" <?php if($sesi_lama == "2") echo "selected"; ?>>Session 2</option>
                                                        <option value="3" <?php if($sesi_lama == "3") echo "selected"; ?>>Session 3</option>
                                                        <option value="4" <?php if($sesi_lama == "4") echo "selected"; ?>>Session 4</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Level</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-mortarboard"></i></span>
                                                    <select class="form-select" name="tingkat" id="tingkat" required>
                                                        <option value="">-- Choose Level --</option>
                                                        <option value="SD" <?php if($tingkat_old == "SD") echo "selected"; ?>>Elementary School (SD)</option>
                                                        <option value="SMP" <?php if($tingkat_old == "SMP") echo "selected"; ?>>Middle School (SMP)</option>
                                                        <option value="SMA" <?php if($tingkat_old == "SMA") echo "selected"; ?>>High School (SMA)</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mb-4">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Grade</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-layers"></i></span>
                                                    <select class="form-select" name="kelas" id="kelas" required>
                                                        </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Subject</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-book"></i></span>
                                                    <input type="text" class="form-control" name="mapel" value="<?php echo htmlspecialchars($mapel_old); ?>" placeholder="Subject Name" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-end border-top pt-3">
                                            <a href="inputjadwal.php" class="btn btn-light btn-pill border me-2">
                                                <i class="fas fa-times me-1"></i> Cancel
                                            </a>
                                            <button type="submit" class="btn btn-success btn-pill shadow-sm" name="Update">
                                                <i class="fas fa-save me-1"></i> Update Schedule
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
            <?php include("../template/footer.php"); ?>
        </div>
    </div>

    <script>
        const tingkatSelect = document.getElementById('tingkat');
        const kelasSelect = document.getElementById('kelas');

        const listKelas = {
            'SD': ['1', '2', '3', '4', '5', '6'],
            'SMP': ['7', '8', '9'],
            'SMA': ['10', '11', '12']
        };

        function updateKelasOptions(tingkatTerpilih, kelasAktif = '') {
            kelasSelect.innerHTML = '<option value="">-- Choose Grade --</option>';

            if (tingkatTerpilih && listKelas[tingkatTerpilih]) {
                kelasSelect.disabled = false;
                listKelas[tingkatTerpilih].forEach(function (kelas) {
                    const option = document.createElement('option');
                    option.value = kelas;
                    option.textContent = 'Grade ' + kelas;
                    if (String(kelas) === String(kelasAktif)) {
                        option.selected = true;
                    }
                    kelasSelect.appendChild(option);
                });
            } else {
                kelasSelect.disabled = true;
                kelasSelect.innerHTML = '<option value="">-- Choose Level First --</option>';
            }
        }

        // Jalankan otomatis saat halaman pertama kali dibuka untuk sinkronisasi data lama dari database
        updateKelasOptions(tingkatSelect.value, '<?php echo $kelas_old; ?>');

        // Event listener saat admin mengubah pilihan Tingkat Sekolah (SD/SMP/SMA)
        tingkatSelect.addEventListener('change', function () {
            updateKelasOptions(this.value);
        });
    </script>
    <?php include("../template/jsscript.php"); ?>
</body>
</html>