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
        body, h1, h2, h3, h4, h5, h6, p, span, table, th, td, input, label, button {
            font-family: 'Poppins', sans-serif !important;
        }
        
        /* Modern Card & Form Design */
        .custom-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            background: #ffffff;
            margin-bottom: 25px;
        }
        .card-header-custom {
            background-color: #ffffff;
            border-bottom: 1px solid #edf2f9;
            padding: 18px 24px;
            border-top-left-radius: 15px !important;
            border-top-right-radius: 15px !important;
        }
        .form-control:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }
        
        /* Button Rounded pill styling */
        .btn-pill {
            border-radius: 30px;
            padding: 6px 16px;
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
                            <h2 style="font-weight: 700; color: #1E3754; margin-bottom: 2px;">Edit Tutor Data</h2>
                            <p class="text-muted small mb-0">Update personal profile biodata or account login credentials for the selected tutor</p>
                        </div>
                    </div>

                    <?php
                    include("../includes/config.php");

                    // 1. FETCH PREVIOUS DATA BASED ON ID IN URL
                    if (isset($_GET["ubahtutor"])) {
                        $tutor_ID = mysqli_real_escape_string($conn, $_GET["ubahtutor"]);
                        
                        // JOIN query to fetch data from bio_tutor and tutor account table simultaneously
                        $query_select = mysqli_query($conn, "
                            SELECT b.*, a.tutor_USER, a.tutor_PASS 
                            FROM bio_tutor b
                            INNER JOIN tutor a ON b.tutor_ID = a.tutor_ID
                            WHERE b.tutor_ID = '$tutor_ID'
                        ");

                        if (mysqli_num_rows($query_select) > 0) {
                            $data = mysqli_fetch_array($query_select);
                            $tutor_NAME_old = $data["tutor_NAME"];
                            $tutor_BOD_old = $data["tutor_BOD"];
                            $tutor_KET_old = $data["tutor_KET"];
                            $tutor_USER_old = $data["tutor_USER"];
                            $tutor_PASS_old = $data["tutor_PASS"];
                        } else {
                            echo "<div class='alert alert-danger mt-3'>Tutor data not found in the system!</div>";
                            exit();
                        }
                    } else {
                        header("location:inputtutor.php");
                        exit();
                    }

                    // 2. PROCESS DATA UPDATE WHEN "Update" BUTTON IS CLICKED
                    if (isset($_POST["Update"])) {
                        $tutor_NAME = mysqli_real_escape_string($conn, $_POST["namaTutor"]);
                        $tutor_BOD = mysqli_real_escape_string($conn, $_POST["bodTutor"]);
                        $tutor_KET = mysqli_real_escape_string($conn, $_POST["ketTutor"]);
                        $tutor_PASS = mysqli_real_escape_string($conn, $_POST["passwordTutor"]);

                        // Update biodata table (bio_tutor)
                        $update_bio = mysqli_query($conn, "UPDATE bio_tutor SET 
                            tutor_NAME = '$tutor_NAME', 
                            tutor_BOD = '$tutor_BOD', 
                            tutor_KET = '$tutor_KET' 
                            WHERE tutor_ID = '$tutor_ID'");

                        if ($update_bio) {
                            // Update account table (tutor) - Only Password is updated, Username is bypassed
                            $update_akun = mysqli_query($conn, "UPDATE tutor SET 
                                tutor_PASS = '$tutor_PASS' 
                                WHERE tutor_ID = '$tutor_ID'");

                            if ($update_akun) {
                                header("location:inputtutor.php");
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
                                        <i class="fas fa-user-edit me-2"></i>Tutor Modification Form
                                    </h5>
                                </div>
                                <div class="card-body p-4">
                                    <form method="POST">
                                        
                                        <p class="text-primary small mb-3" style="font-weight: 600; letter-spacing: 0.5px;">A. TUTOR PERSONAL BIODATA</p>
                                        
                                        <div class="row mb-3">
                                            <label for="idTutor" class="col-sm-2 col-form-label">Tutor ID</label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control bg-light" id="idTutor" value="<?php echo htmlspecialchars($tutor_ID); ?>" readonly style="cursor: not-allowed;">
                                                <div class="form-text text-muted small"><i class="bi bi-info-circle"></i> Tutor ID is unique to the database and cannot be modified.</div>
                                            </div>
                                        </div>
                                        
                                        <div class="row mb-3">
                                            <label for="namaTutor" class="col-sm-2 col-form-label">Full Name</label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control" id="namaTutor" name="namaTutor" value="<?php echo htmlspecialchars($tutor_NAME_old); ?>" required>
                                            </div>
                                        </div>
                                        
                                        <div class="row mb-3">
                                            <label for="bodTutor" class="col-sm-2 col-form-label">Date of Birth</label>
                                            <div class="col-sm-10">
                                                <input type="date" class="form-control" id="bodTutor" name="bodTutor" value="<?php echo htmlspecialchars($tutor_BOD_old); ?>" required>
                                            </div>
                                        </div>
                                        
                                        <div class="row mb-4">
                                            <label for="ketTutor" class="col-sm-2 col-form-label">Description / Expertise</label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control" id="ketTutor" name="ketTutor" value="<?php echo htmlspecialchars($tutor_KET_old); ?>" placeholder="Example: Mathematics & Physics Expert">
                                            </div>
                                        </div>

                                        <hr class="my-4" style="border-color: #edf2f9;">
                                        <p class="text-success small mb-3" style="font-weight: 600; letter-spacing: 0.5px;">B. LOGIN PORTAL ACCESS ACCOUNT</p>

                                        <div class="row mb-3">
                                            <label for="usernameTutor" class="col-sm-2 col-form-label">Username / Email</label>
                                            <div class="col-sm-10">
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                                                    <input type="text" class="form-control bg-light" id="usernameTutor" value="<?php echo htmlspecialchars($tutor_USER_old); ?>" readonly style="cursor: not-allowed;">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row mb-4">
                                            <label for="passwordTutor" class="col-sm-2 col-form-label">Password</label>
                                            <div class="col-sm-10">
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                                                    <input type="text" class="form-control" id="passwordTutor" name="passwordTutor" value="<?php echo htmlspecialchars($tutor_PASS_old); ?>" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-2"></div>
                                            <div class="col-sm-10">
                                                <button type="submit" class="btn btn-primary btn-pill shadow-sm me-2" name="Update">
                                                    <i class="fas fa-save me-1"></i> Save Changes
                                                </button>
                                                <a href="inputtutor.php" class="btn btn-light btn-pill border">
                                                    Back
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
    <?php include("../template/jsscript.php"); ?>
</body>
</html>
<?php 
ob_end_flush(); 
?>