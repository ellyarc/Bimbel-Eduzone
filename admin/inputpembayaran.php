<?php
ob_start();
session_start();
if (!isset($_SESSION["useremail"])) {
    header("location:../frontend/login.php");
    exit();
}

include("../includes/config.php");

$error_message = "";

// 1. PROCESS SAVE DATA (Secured with Prepared Statements)
if (isset($_POST["Simpan"])) {
    $pembayaran_ID = mysqli_real_escape_string($conn, $_POST["pembayaranID"]);
    $siswa_ID = mysqli_real_escape_string($conn, $_POST["siswaID"]);
    $pembayaran_STATUS = mysqli_real_escape_string($conn, $_POST["pembayaranSTATUS"]);
    $pembayaran_NOMINAL = mysqli_real_escape_string($conn, $_POST["pembayaranNOMINAL"]);
    $pembayaran_TENGGAT = mysqli_real_escape_string($conn, $_POST["pembayaranTENGGAT"]);
    $pembayaran_TANGGAL = mysqli_real_escape_string($conn, $_POST["pembayaranTANGGAL"]);
    $kode_VA = mysqli_real_escape_string($conn, $_POST["kodeVA"]); // MENAMBAHKAN INPUT KODE VA

    // Handle empty date values safely for MySQL
    $pembayaran_TANGGAL_val = empty($pembayaran_TANGGAL) ? null : $pembayaran_TANGGAL;

    // Check duplicate ID (Crucial now since users type it manually)
    $stmt_check = $conn->prepare("SELECT pembayaran_ID FROM pembayaran WHERE pembayaran_ID = ?");
    $stmt_check->bind_param("s", $pembayaran_ID);
    $stmt_check->execute();
    $res_check = $stmt_check->get_result();

    if ($res_check->num_rows > 0) {
        $error_message = "Error: Payment ID <strong>'$pembayaran_ID'</strong> is already registered.";
    } else {
        // Insert data securely using Prepared Statements (Menambahkan kolom kode_va)
        $stmt_insert = $conn->prepare("INSERT INTO pembayaran (pembayaran_ID, siswa_ID, pembayaran_STATUS, pembayaran_NOMINAL, pembayaran_TENGGAT, pembayaran_TANGGAL, kode_va) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt_insert->bind_param("sssssss", $pembayaran_ID, $siswa_ID, $pembayaran_STATUS, $pembayaran_NOMINAL, $pembayaran_TENGGAT, $pembayaran_TANGGAL_val, $kode_VA);

        if ($stmt_insert->execute()) {
            header("location:inputpembayaran.php");
            exit();
        } else {
            $error_message = "Database Error: Failed to save the transaction.";
        }
        $stmt_insert->close();
    }
    $stmt_check->close();
}

// 2. PROCESS SEARCH & VIEW DATA
if (isset($_POST["kirim"]) && !empty(trim($_POST["search"]))) {
    $search = "%" . trim($_POST["search"]) . "%";
    $stmt_search = $conn->prepare("
        SELECT p.*, s.siswa_NAME 
        FROM pembayaran p
        JOIN bio_siswa s ON p.siswa_ID = s.siswa_ID
        WHERE s.siswa_NAME LIKE ? OR p.kode_va LIKE ?
        ORDER BY p.pembayaran_TANGGAL DESC, p.pembayaran_ID DESC
    ");
    $stmt_search->bind_param("ss", $search, $search);
    $stmt_search->execute();
    $result_data = $stmt_search->get_result();
} else {
    // Default Query
    $result_data = $conn->query("
        SELECT p.*, s.siswa_NAME 
        FROM pembayaran p
        JOIN bio_siswa s ON p.siswa_ID = s.siswa_ID
        ORDER BY p.pembayaran_TANGGAL DESC, p.pembayaran_ID DESC
    ");
}

// Fetch students list for select option
$datasiswa = $conn->query("SELECT siswa_ID, siswa_NAME FROM bio_siswa ORDER BY siswa_NAME ASC");
?>
<?php include("../template/head.php"); ?>
<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body, h1, h2, h3, h4, h5, h6, p, span, table, th, td, input, select, label, button {
            font-family: 'Poppins', sans-serif !important;
        }
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
        .input-group .form-control {
            border-radius: 0 8px 8px 0;
        }
        .btn-pill {
            border-radius: 30px;
            padding: 8px 24px;
            font-weight: 500;
        }
        .table-responsive-custom {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
        }
        .custom-table {
            margin-bottom: 0;
        }
        .custom-table thead {
            background-color: #f8f9fc;
        }
        .custom-table th {
            color: #495057;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            padding: 15px;
            border-bottom: 2px solid #edf2f9;
        }
        .custom-table td {
            padding: 14px 15px;
            vertical-align: middle;
            color: #5a5c69;
            font-size: 0.875rem;
            border-bottom: 1px solid #edf2f9;
        }
        .custom-table tbody tr:hover {
            background-color: #f8f9fc;
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
                            <p class="text-muted small mb-0">Manage and monitor all student fee payment transactions</p>
                        </div>
                    </div>

                    <?php if (!empty($error_message)) { ?>
                        <div class="alert alert-danger alert-dismissible fade show mb-4 shadow-sm" role="alert" style="border-radius: 10px;">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-exclamation-triangle-fill me-3 fs-4"></i>
                                <div><?php echo $error_message; ?></div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php } ?>

                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card custom-card">
                                <div class="card-header-custom">
                                    <h5 class="m-0" style="font-weight: 700; color: #4e73df;">
                                        <i class="fas fa-wallet me-2"></i> Input New Payment Transaction
                                    </h5>
                                </div>
                                <div class="card-body p-4">
                                    <form method="POST">
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label font-weight-bold">Payment ID</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-hash"></i></span>
                                                    <input type="text" class="form-control" name="pembayaranID" placeholder="e.g., PAY-101, INV-2026-001" value="<?php if (isset($_POST['pembayaranID'])) echo htmlspecialchars($_POST['pembayaranID']); ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label font-weight-bold">Kode Virtual Account (VA)</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-credit-card-2-front"></i></span>
                                                    <input type="text" class="form-control" name="kodeVA" placeholder="e.g., 880123456789" value="<?php if (isset($_POST['kodeVA'])) echo htmlspecialchars($_POST['kodeVA']); ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Student Name</label>
                                                <select class="form-select" name="siswaID" required>
                                                    <option value="">-- Choose Student --</option>
                                                    <?php while ($s = $datasiswa->fetch_assoc()) { ?>
                                                        <option value="<?php echo $s["siswa_ID"]; ?>" <?php if (isset($_POST['siswaID']) && $_POST['siswaID'] == $s["siswa_ID"]) echo "selected"; ?>>
                                                            <?php echo htmlspecialchars($s["siswa_NAME"]); ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Status</label>
                                                <select class="form-select" name="pembayaranSTATUS" id="pembayaranSTATUS" required>
                                                    <option value="">-- Choose Status --</option>
                                                    <option value="PAID" <?php if (isset($_POST['pembayaranSTATUS']) && $_POST['pembayaranSTATUS'] == "PAID") echo "selected"; ?>>PAID</option>
                                                    <option value="NOT PAID" <?php if (isset($_POST['pembayaranSTATUS']) && $_POST['pembayaranSTATUS'] == "NOT PAID") echo "selected"; ?>>NOT PAID</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Nominal Amount (Rp)</label>
                                                <div class="input-group">
                                                    <span class="input-group-text" style="font-weight: 600; font-size: 0.85rem;">Rp</span>
                                                    <input type="number" class="form-control" name="pembayaranNOMINAL" placeholder="Just enter a number, for example: 500000" value="<?php if (isset($_POST['pembayaranNOMINAL'])) echo htmlspecialchars($_POST['pembayaranNOMINAL']); ?>" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mb-4">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Due Date</label>
                                                <input type="date" class="form-control" name="pembayaranTENGGAT" value="<?php if (isset($_POST['pembayaranTENGGAT'])) echo htmlspecialchars($_POST['pembayaranTENGGAT']); ?>" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Payment Date</label>
                                                <input type="date" class="form-control" name="pembayaranTANGGAL" id="pembayaranTANGGAL" value="<?php if (isset($_POST['pembayaranTANGGAL'])) echo htmlspecialchars($_POST['pembayaranTANGGAL']); ?>">
                                                <div class="form-text text-muted small">Leave blank if the status is still <strong>NOT PAID</strong>.</div>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-end border-top pt-3">
                                            <button type="reset" class="btn btn-light btn-pill border me-2">
                                                <i class="fas fa-sync-alt me-1"></i> Reset
                                            </button>
                                            <button type="submit" class="btn btn-success btn-pill shadow-sm" name="Simpan">
                                                <i class="fas fa-save me-1"></i> Save Transaction
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-12">
                            <div class="card custom-card">
                                <div class="card-header-custom d-flex flex-wrap justify-content-between align-items-center gap-3">
                                    <h5 class="m-0" style="font-weight: 700; color: #1e3754;">
                                        <i class="fas fa-list me-2"></i> Payment List History
                                    </h5>

                                    <form method="POST" class="d-flex align-items-center" style="max-width: 400px; width: 100%;">
                                        <div class="input-group">
                                            <input type="text" name="search" class="form-control" value="<?php if (isset($_POST["search"])) echo htmlspecialchars($_POST["search"]); ?>" placeholder="Search Name or VA..." style="padding-top: 7px; padding-bottom: 7px;">
                                            <button type="submit" name="kirim" class="btn btn-primary" style="border-top-right-radius: 8px; border-bottom-right-radius: 8px;">
                                                <i class="bi bi-search"></i>
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                <div class="card-body p-4">
                                    <div class="table-responsive table-responsive-custom">
                                        <table class="table custom-table table-hover align-middle">
                                            <thead>
                                                <tr>
                                                    <th>Payment ID</th>
                                                    <th>Kode VA</th> <th>Student Name</th>
                                                    <th>Status</th>
                                                    <th>Nominal</th>
                                                    <th>Due Date</th>
                                                    <th>Payment Date</th>
                                                    <th class="text-center" style="width: 130px;">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if ($result_data && $result_data->num_rows > 0) {
                                                    while ($row = $result_data->fetch_assoc()) { ?>
                                                        <tr>
                                                            <td style="font-weight: 600; color: #4e73df;"><?php echo htmlspecialchars($row["pembayaran_ID"]); ?></td>
                                                            <td style="font-family: monospace; font-weight: 600; color: #5a5c69;"><?php echo htmlspecialchars($row["kode_va"] ?? '-'); ?></td>
                                                            <td style="font-weight: 500; color: #2e2f37;"><?php echo htmlspecialchars($row["siswa_NAME"]); ?></td>
                                                            <td>
                                                                <?php if (strtoupper($row["pembayaran_STATUS"]) == 'PAID' || $row["pembayaran_STATUS"] == 'LUNAS') { ?>
                                                                    <span class="badge bg-success-subtle text-success px-3 py-2 border border-success-subtle" style="border-radius: 20px; font-weight: 600; font-size: 0.75rem;">PAID</span>
                                                                <?php } else { ?>
                                                                    <span class="badge bg-danger-subtle text-danger px-3 py-2 border border-danger-subtle" style="border-radius: 20px; font-weight: 600; font-size: 0.75rem;">NOT PAID</span>
                                                                <?php } ?>
                                                            </td>
                                                            <td style="font-weight: 600; color: #2e2f37;">Rp <?php echo number_format(($row["pembayaran_NOMINAL"] ?? 0), 0, ',', '.'); ?></td>
                                                            <td><i class="bi bi-calendar-event text-muted me-1"></i> <?php echo htmlspecialchars($row["pembayaran_TENGGAT"]); ?></td>
                                                            <td>
                                                                <?php if (!empty($row["pembayaran_TANGGAL"]) && $row["pembayaran_TANGGAL"] != '0000-00-00') { ?>
                                                                    <i class="bi bi-calendar-check text-success me-1"></i> <?php echo htmlspecialchars($row["pembayaran_TANGGAL"]); ?>
                                                                <?php } else { ?>
                                                                    <span class="text-muted">-</span>
                                                                <?php } ?>
                                                            </td>
                                                            <td class="text-center">
                                                                <div class="btn-group" role="group">
                                                                    <a href="editpembayaran.php?ubahpembayaran=<?php echo urlencode($row["pembayaran_ID"]); ?>" class="btn btn-outline-success btn-sm border-0" title="Edit" style="border-radius: 6px; margin-right: 4px;">
                                                                        <i class="bi bi-pencil-square fs-6"></i>
                                                                    </a>
                                                                    <a href="hapuspembayaran.php?id_pembayaran=<?php echo urlencode($row['pembayaran_ID']); ?>" class="btn btn-outline-danger btn-sm border-0" title="Delete" onclick="return confirm('Apakah Anda yakin ingin menghapus data pembayaran dengan ID <?php echo htmlspecialchars($row['pembayaran_ID']); ?>?');" style="border-radius: 6px;">
                                                                        <i class="bi bi-trash fs-6"></i>
                                                                    </a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php }
                                                } else { ?>
                                                    <tr>
                                                        <td colspan="8" class="text-center py-5 text-muted">
                                                            <i class="bi bi-folder-x display-6 d-block mb-2 text-gray-400"></i> Tidak ada data transaksi pembayaran ditemukan.
                                                        </td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
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

    <script>
        document.getElementById('pembayaranSTATUS').addEventListener('change', function () {
            var dateInput = document.getElementById('pembayaranTANGGAL');
            if (this.value === 'PAID') {
                var today = new Date().toISOString().split('T')[0];
                dateInput.value = today;
            } else {
                dateInput.value = '';
            }
        });
    </script>
</body>
</html>