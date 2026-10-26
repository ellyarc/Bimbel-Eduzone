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
        .form-control[readonly] {
            background-color: #f8f9fc !important;
            opacity: 1;
            color: #6e707e;
            font-weight: 500;
        }
        .input-group-text {
            border-radius: 8px 0 0 8px;
            background-color: #f8f9fc;
            border: 1px solid #d1d3e2;
            color: #4e73df;
        }
        .input-group .form-control {
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
        <?php include("menu.php"); ?>

        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    
                    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
                        <div>
                            <h2 style="font-weight: 700; color: #1E3754; margin-bottom: 2px;">Payment Management</h2>
                            <p class="text-muted small mb-0">Modify and update student tuition fee payment transactions</p>
                        </div>
                    </div>

                    <?php
                    include("../includes/config.php");

                    // 1. FETCH RECORDED DATA BASED ON ID PARAMETER FROM URL (Termasuk mengambil data kode_va)
                    if (isset($_GET["ubahpembayaran"])) {
                        $pembayaran_ID = mysqli_real_escape_string($conn, $_GET["ubahpembayaran"]);
                        
                        // Menambahkan p.kode_va ke dalam penarikan data
                        $query_select = mysqli_query($conn, "
                            SELECT p.*, s.siswa_NAME 
                            FROM pembayaran p
                            JOIN bio_siswa s ON p.siswa_ID = s.siswa_ID
                            WHERE p.pembayaran_ID = '$pembayaran_ID'
                        ");
                        
                        if (mysqli_num_rows($query_select) > 0) {
                            $data = mysqli_fetch_array($query_select);
                            $siswa_ID_old = $data["siswa_ID"];
                            $siswa_NAME_old = $data["siswa_NAME"];
                            $pembayaran_STATUS_old = $data["pembayaran_STATUS"];
                            $pembayaran_NOMINAL_old = $data["pembayaran_NOMINAL"]; 
                            $pembayaran_TENGGAT_old = $data["pembayaran_TENGGAT"];
                            $pembayaran_TANGGAL_old = $data["pembayaran_TANGGAL"];
                            $kode_VA_old = $data["kode_va"]; // MENAMPUNG DATA LAMA KODE VA
                        } else {
                            echo "<div class='alert alert-danger mt-3' style='border-radius: 10px;'>Payment data not found!</div>";
                            exit();
                        }
                    } else {
                        header("location:inputpembayaran.php");
                        exit();
                    }

                    // 2. PROCESS TRANSACTION UPDATE WHEN "Update" BUTTON IS CLICKED
                    if (isset($_POST["Update"])) {
                        $pembayaran_STATUS = mysqli_real_escape_string($conn, $_POST["pembayaranSTATUS"]);
                        $pembayaran_NOMINAL = mysqli_real_escape_string($conn, $_POST["pembayaranNOMINAL"]); 
                        $pembayaran_TENGGAT = mysqli_real_escape_string($conn, $_POST["pembayaranTENGGAT"]);
                        $pembayaran_TANGGAL = mysqli_real_escape_string($conn, $_POST["pembayaranTANGGAL"]);
                        $kode_VA = mysqli_real_escape_string($conn, $_POST["kodeVA"]); // MENANGKAP INPUT DATA BARU KODE VA

                        // If payment date is left blank, assign a NULL value in the database
                        if (empty($pembayaran_TANGGAL)) {
                            $pembayaran_TANGGAL_val = "NULL";
                        } else {
                            $pembayaran_TANGGAL_val = "'$pembayaran_TANGGAL'";
                        }

                        // Execute Update Query (Ditambahkan pembaruan field kode_va)
                        mysqli_query($conn, "UPDATE pembayaran SET 
                            pembayaran_STATUS = '$pembayaran_STATUS', 
                            pembayaran_NOMINAL = '$pembayaran_NOMINAL', 
                            pembayaran_TENGGAT = '$pembayaran_TENGGAT', 
                            pembayaran_TANGGAL = $pembayaran_TANGGAL_val,
                            kode_va = '$kode_VA' 
                            WHERE pembayaran_ID = '$pembayaran_ID'") or die(mysqli_error($conn));
                        
                        header("location:inputpembayaran.php");
                        exit();
                    }
                    ?>

                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card custom-card">
                                <div class="card-header-custom">
                                    <h5 class="m-0" style="font-weight: 700; color: #4e73df;">
                                        <i class="fas fa-edit me-2"></i> Edit Payment Transaction
                                    </h5>
                                </div>
                                <div class="card-body p-4">
                                    <form method="POST">
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label font-weight-bold">Payment ID</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-hash"></i></span>
                                                    <input type="text" class="form-control" value="<?php echo $pembayaran_ID; ?>" readonly>
                                                </div>
                                            </div>
                                            <!-- MENAMBAHKAN FORM INPUT KODE VA (EDITABLE) -->
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label font-weight-bold">Kode Virtual Account (VA)</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-credit-card-2-front"></i></span>
                                                    <input type="text" class="form-control" name="kodeVA" value="<?php echo htmlspecialchars($kode_VA_old ?? ''); ?>" placeholder="e.g., 880123456789" required>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Student Name</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($siswa_NAME_old); ?>" readonly>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Status</label>
                                                <select class="form-select" name="pembayaranSTATUS" required>
                                                    <option value="">-- Choose Status --</option>
                                                    <option value="PAID" <?php if ($pembayaran_STATUS_old == "PAID" || $pembayaran_STATUS_old == "LUNAS") echo "selected"; ?>>PAID</option>
                                                    <option value="NOT PAID" <?php if ($pembayaran_STATUS_old == "NOT PAID") echo "selected"; ?>>NOT PAID</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Nominal Amount (Rp)</label>
                                                <div class="input-group">
                                                    <span class="input-group-text" style="font-weight: 600; font-size: 0.85rem;">Rp</span>
                                                    <input type="number" class="form-control" name="pembayaranNOMINAL" value="<?php echo htmlspecialchars($pembayaran_NOMINAL_old); ?>" placeholder="Enter digits only, e.g., 500000" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mb-4">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Due Date</label>
                                                <input type="date" class="form-control" name="pembayaranTENGGAT" value="<?php echo htmlspecialchars($pembayaran_TENGGAT_old); ?>" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Payment Date</label>
                                                <input type="date" class="form-control" name="pembayaranTANGGAL" value="<?php echo htmlspecialchars($pembayaran_TANGGAL_old); ?>">
                                                <div class="form-text text-muted small">Leave empty if the payment status is still <strong>NOT PAID</strong>.</div>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-end border-top pt-3">
                                            <a href="inputpembayaran.php" class="btn btn-light btn-pill border me-2">
                                                <i class="fas fa-times me-1"></i> Cancel
                                            </a>
                                            <button type="submit" class="btn btn-success btn-pill shadow-sm" name="Update">
                                                <i class="fas fa-save me-1"></i> Update Data
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

    <?php include("../template/jsscript.php"); ?>
</body>
</html>