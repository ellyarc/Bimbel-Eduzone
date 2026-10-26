<?php 
ob_start();
session_start();

// 1. PAGE PROTECTION (Login required before accessing)
if (!isset($_SESSION["useremail"])) {
    header("location:login.php");
    exit();
}

// Connecting to the database
include("../includes/config.php");
include("../includes/auth.php");

// Get the logged-in username stored in the session
$session_login = $_SESSION["useremail"];

// 2. FETCH SISWA DATA BASED ON THE ORIGINAL ATTRIBUTE (siswa_USER)
$query_siswa = mysqli_query($conn, "SELECT * FROM siswa WHERE siswa_USER = '$session_login'");
$data_siswa = mysqli_fetch_array($query_siswa);

// Store data into variables based on your actual database columns
$siswa_id    = $data_siswa['siswa_ID'];
$siswa_user  = $data_siswa['siswa_USER'];
$siswa_pass  = $data_siswa['siswa_PASS'];

// 3. PROCESS PASSWORD UPDATE (siswa_PASS)
$success_message = "";
$error_message = "";

if (isset($_POST["Update"])) {
    $new_password = $_POST["passwordSiswa"];

    if ($new_password === "" || password_sudah_hash($new_password) || ($new_password === $siswa_pass && password_sudah_hash($siswa_pass))) {
        $error_message = "Masukkan password baru.";
    } elseif (simpan_password($conn, "siswa", "siswa_USER", $session_login, $new_password)) {
        $siswa_pass = password_hash($new_password, PASSWORD_DEFAULT); // form tidak menampilkan hash
        $success_message = "Password has been successfully updated!";
    } else {
        $error_message = "Failed to update password.";
    }
}
?>

<?php include("template/head.php"); ?>
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
        body, h1, h2, h3, h4, h5, h6, p, span, input, label, button, .breadcrumb-item {
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
        .form-control {
            border-radius: 8px;
            border: 1px solid #d1d3e2;
            padding: 10px 15px;
            font-size: 0.9rem;
        }
        .form-control:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }
        .form-control[readonly] {
            background-color: #f8f9fc !important;
            color: #6e707e;
            opacity: 1;
            border: 1px solid #eaecf4;
        }
        
        /* Input Groups Icon */
        .input-group-text {
            border-radius: 8px 0 0 8px;
            background-color: #f8f9fc;
            border: 1px solid #d1d3e2;
            color: #4e73df;
        }
        .input-group .form-control {
            border-radius: 0 8px 8px 0;
        }
        
        /* Button Pill */
        .btn-pill {
            border-radius: 30px;
            padding: 8px 24px;
            font-weight: 500;
            font-size: 0.9rem;
        }
    </style>
</head>

<body class="sb-nav-fixed" style="background-color: #f4f7fa;">
    <?php include("template/menunav.php"); ?>
    
    <div id="layoutSidenav">
        <?php include("template/menu.php"); ?>
        
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    
                    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
                        <div>
                            <h2 style="font-weight: 700; color: #1E3754; margin-bottom: 2px;">Account Information</h2>
                            <p class="text-muted small mb-0">Manage your personal profile data and update your account security credentials periodically</p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-xl-8 col-lg-10">
                            
                            <?php if (!empty($success_message)) { ?>
                                <div class="alert alert-success alert-dismissible fade show mb-4 shadow-sm" role="alert" style="border-radius: 10px;">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-check-circle-fill me-3 fs-4"></i>
                                        <div><strong>Success!</strong> <?php echo $success_message; ?></div>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php } ?>
                            
                            <?php if (!empty($error_message)) { ?>
                                <div class="alert alert-danger alert-dismissible fade show mb-4 shadow-sm" role="alert" style="border-radius: 10px;">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-exclamation-triangle-fill me-3 fs-4"></i>
                                        <div><strong>Error!</strong> <?php echo $error_message; ?></div>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php } ?>

                            <div class="card custom-card">
                                <div class="card-header-custom">
                                    <h5 class="m-0" style="font-weight: 700; color: #4e73df;">
                                        <i class="fas fa-user-shield me-2"></i> Siswa System Profile
                                    </h5>
                                </div>
                                <div class="card-body p-4">
                                    <form method="POST">
                                        
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold text-muted small">Siswa ID</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
                                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($siswa_id); ?>" readonly>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold text-muted small">Siswa Full Name</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($siswa_nama); ?>" readonly>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold text-muted small">Username Login</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="bi bi-envelope-at-fill"></i></span>
                                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($siswa_user); ?>" readonly>
                                            </div>
                                        </div>

                                        <hr class="my-4" style="border-top: 1px dashed #d1d3e2;">

                                        <div class="mb-4">
                                            <label for="passwordSiswa" class="form-label fw-semibold text-danger">New System Password</label>
                                            <div class="input-group">
                                                <span class="input-group-text" style="background-color: #fff5f5; border-color: #fab6b6; color: #e74a3b;"><i class="bi bi-shield-lock-fill"></i></span>
                                                <input type="text" class="form-control" id="passwordSiswa" name="passwordSiswa" value="<?php echo htmlspecialchars(password_tampil($siswa_pass)); ?>" placeholder="Password baru" required style="border-color: #fab6b6; font-weight: 500;">
                                            </div>
                                            <div class="form-text text-muted small mt-2">
                                                <i class="bi bi-info-circle me-1"></i> Please clear the old text above and type your new password to update your system login credentials.
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex justify-content-end border-top pt-3">
                                            <a href="dashboard.php" class="btn btn-light btn-pill border me-2">
                                                <i class="fas fa-arrow-left me-1"></i> Dashboard
                                            </a>
                                            <button type="submit" name="Update" class="btn btn-success btn-pill shadow-sm">
                                                <i class="fas fa-save me-1"></i> Update Password
                                            </button>
                                        </div>

                                    </form>
                                </div>
                            </div>

                        </div>
                    </div>
                    
                </div>
            </main>
            
            <?php include("template/footer.php"); ?>
        </div> 
    </div> 
    
    <?php include("template/jsscript.php"); ?>
</body>
</html>