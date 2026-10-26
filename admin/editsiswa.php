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
        
        /* Form Styling */
        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #d1d3e2;
            padding: 10px 15px;
        }
        .form-control:focus, .form-select:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }
        
        /* Pill Button */
        .btn-pill {
            border-radius: 30px;
            padding: 8px 20px;
            font-weight: 500;
        }
    </style>
</head>

<body class="sb-nav-fixed" style="background-color: #f4f7fa;">
    <?php include("../template/menunav.php"); ?>

    <div id="layoutSidenav">
        <?php include("menu.php"); ?>

        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    
                    <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
                        <div>
                            <h2 style="font-weight: 700; color: #1E3754; margin-bottom: 2px;">Edit Student Data</h2>
                            <p class="text-muted small mb-0">Update student profile biodata and login account credentials</p>
                        </div>
                    </div>

                    <?php
                    include("../includes/config.php");

                    // 1. FETCH RECORDED DATA BASED ON ID PARAMETER FROM URL
                    if (isset($_GET["ubahsiswa"])) {
                        $siswa_ID = mysqli_real_escape_string($conn, $_GET["ubahsiswa"]);
                        
                        // JOIN query to retrieve data from bio_siswa and student account tables simultaneously
                        $query_select = mysqli_query($conn, "
                            SELECT b.*, a.siswa_USER, a.siswa_PASS 
                            FROM bio_siswa b
                            INNER JOIN siswa a ON b.siswa_ID = a.siswa_ID
                            WHERE b.siswa_ID = '$siswa_ID'
                        ");

                        if (mysqli_num_rows($query_select) > 0) {
                            $data = mysqli_fetch_array($query_select);
                            $siswa_NAME_old = $data["siswa_NAME"];
                            $siswa_TINGKAT_old = $data["siswa_TINGKAT"];
                            $siswa_KELAS_old = $data["siswa_KELAS"];
                            $siswa_USER_old = $data["siswa_USER"];
                            $siswa_PASS_old = $data["siswa_PASS"];
                        } else {
                            echo "<div class='alert alert-danger mt-3'>Student data not found!</div>";
                            exit();
                        }
                    } else {
                        header("location:inputsiswa.php");
                        exit();
                    }

                    // 2. PROCESS TRANSACTION UPDATE WHEN "Update" BUTTON IS CLICKED
                    if (isset($_POST["Update"])) {
                        $siswa_NAME = mysqli_real_escape_string($conn, $_POST["namaSiswa"]);
                        $siswa_TINGKAT = mysqli_real_escape_string($conn, $_POST["tingkatSiswa"]);
                        $siswa_KELAS = mysqli_real_escape_string($conn, $_POST["kelasSiswa"]);
                        $siswa_PASS = mysqli_real_escape_string($conn, $_POST["passwordSiswa"]);

                        // Update profile biodata table (bio_siswa)
                        $update_bio = mysqli_query($conn, "UPDATE bio_siswa SET 
                            siswa_NAME = '$siswa_NAME', 
                            siswa_TINGKAT = '$siswa_TINGKAT', 
                            siswa_KELAS = '$siswa_KELAS' 
                            WHERE siswa_ID = '$siswa_ID'");

                        if ($update_bio) {
                            // Update login credentials table (siswa) - Only Password gets updated, Username is bypassed
                            $update_akun = mysqli_query($conn, "UPDATE siswa SET 
                                siswa_PASS = '$siswa_PASS' 
                                WHERE siswa_ID = '$siswa_ID'");

                            if ($update_akun) {
                                header("location:inputsiswa.php");
                                exit();
                            } else {
                                echo "<div class='alert alert-danger'>Failed to update account data: " . mysqli_error($conn) . "</div>";
                            }
                        } else {
                            echo "<div class='alert alert-danger'>Failed to update biodata: " . mysqli_error($conn) . "</div>";
                        }
                    }
                    ?>

                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card custom-card">
                                <div class="card-header-custom">
                                    <h5 class="m-0" style="font-weight: 700; color: #4e73df;">
                                        <i class="fas fa-user-edit me-2"></i>Student Data Modification Form
                                    </h5>
                                </div>
                                <div class="card-body p-4">
                                    <form method="POST">
                                        
                                        <p class="text-primary small mb-3" style="font-weight: 600; letter-spacing: 0.5px;">A. STUDENT BIODATA</p>
                                        
                                        <div class="row mb-3">
                                            <label for="idSiswa" class="col-sm-2 col-form-label">Student ID</label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control bg-light" id="idSiswa" value="<?php echo htmlspecialchars($siswa_ID); ?>" readonly style="cursor: not-allowed;">
                                                <div class="form-text text-muted small">Student ID is uniquely generated and cannot be modified.</div>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="namaSiswa" class="col-sm-2 col-form-label">Full Name</label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control" id="namaSiswa" name="namaSiswa" value="<?php echo htmlspecialchars($siswa_NAME_old); ?>" required>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="tingkatSiswa" class="col-sm-2 col-form-label">Grade Level</label>
                                            <div class="col-sm-10">
                                                <select class="form-select" id="tingkatSiswa" name="tingkatSiswa" required onchange="updateKelas()">
                                                    <option value="">-- Select Grade Level --</option>
                                                    <option value="SD" <?php if ($siswa_TINGKAT_old == "SD") echo "selected"; ?>>Elementary School (SD)</option>
                                                    <option value="SMP" <?php if ($siswa_TINGKAT_old == "SMP") echo "selected"; ?>>Junior High School (SMP)</option>
                                                    <option value="SMA" <?php if ($siswa_TINGKAT_old == "SMA") echo "selected"; ?>>Senior High School (SMA)</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row mb-4">
                                            <label for="kelasSiswa" class="col-sm-2 col-form-label">Class</label>
                                            <div class="col-sm-10">
                                                <select class="form-select" id="kelasSiswa" name="kelasSiswa" required>
                                                </select>
                                            </div>
                                        </div>

                                        <hr class="my-4" style="border-color: #edf2f9;">
                                        <p class="text-success small mb-3" style="font-weight: 600; letter-spacing: 0.5px;">B. STUDENT LOGIN ACCOUNT</p>

                                        <div class="row mb-3">
                                            <label for="usernameSiswa" class="col-sm-2 col-form-label">Username / Email</label>
                                            <div class="col-sm-10">
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                                                    <input type="text" class="form-control bg-light" id="usernameSiswa" value="<?php echo htmlspecialchars($siswa_USER_old); ?>" readonly style="cursor: not-allowed;">
                                                </div>
                                                <div class="form-text text-muted small">Username is permanently locked to the account and cannot be modified to protect data log integrity.</div>
                                            </div>
                                        </div>
                                        <div class="row mb-4">
                                            <label for="passwordSiswa" class="col-sm-2 col-form-label">Password</label>
                                            <div class="col-sm-10">
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                                                    <input type="text" class="form-control" id="passwordSiswa" name="passwordSiswa" value="<?php echo htmlspecialchars($siswa_PASS_old); ?>" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-2"></div>
                                            <div class="col-sm-10">
                                                <button type="submit" class="btn btn-success btn-pill shadow-sm me-2" name="Update">
                                                    <i class="fas fa-save me-1"></i> Update Data
                                                </button>
                                                <a href="inputsiswa.php" class="btn btn-light btn-pill border">
                                                    Cancel
                                                </a>
                                            </div>
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

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script type="text/javascript">
        function updateKelas() {
            const tingkat = document.getElementById("tingkatSiswa").value;
            const kelasSelect = document.getElementById("kelasSiswa");
            
            // Assign old class from database to JavaScript variable
            const kelasLama = "<?php echo $siswa_KELAS_old; ?>";

            kelasSelect.innerHTML = '<option value="">-- Select Class --</option>';
            let opsiKelas = [];

            if (tingkat === "SD") {
                opsiKelas = [1, 2, 3, 4, 5, 6];
            } else if (tingkat === "SMP") {
                opsiKelas = [7, 8, 9];
            } else if (tingkat === "SMA") {
                opsiKelas = [10, 11, 12];
            }

            if (opsiKelas.length > 0) {
                kelasSelect.disabled = false;
                opsiKelas.forEach(function (kelas) {
                    const option = document.createElement("option");
                    option.value = kelas;
                    option.text = "Class " + kelas;
                    
                    // Automatically pre-select the recorded database class value
                    if (kelas == kelasLama) {
                        option.selected = true;
                    }
                    
                    kelasSelect.appendChild(option);
                });
            } else {
                kelasSelect.disabled = true;
                kelasSelect.innerHTML = '<option value="">-- Select Grade Level First --</option>';
            }
        }

        // Trigger the updateKelas structure immediately once the page elements fully load
        window.onload = function() {
            updateKelas();
        };
    </script>
    <?php include("../template/jsscript.php"); ?>
</body>
</html>