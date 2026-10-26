<?php
ob_start();
session_start();

// Protection: Redirect if not logged in or if the user is not an administrator
if (!isset($_SESSION["useremail"]) || $_SESSION["role"] !== "admin") {
    header("location:../frontend/login.php");
    exit();
}

// Connect to the database
include("../includes/config.php");

// ====================================================================
// CONFIGURATION & FILTER HANDLING (Matching eduzone database columns)
// ====================================================================
$payment_table_name = "pembayaran";

// Menggunakan pembayaran_TANGGAL karena data yang difilter HANYA yang sudah lunas/ada tanggal bayarnya
$date_column = "pembayaran_TANGGAL";

// Default filter values from URL GET parameters
$selected_month = isset($_GET['filter_month']) && $_GET['filter_month'] !== '' ? (int) $_GET['filter_month'] : '';
$selected_year = isset($_GET['filter_year']) && $_GET['filter_year'] !== '' ? (int) $_GET['filter_year'] : '';

// WAJIB PAID / LUNAS: Kondisi dasar agar NOT PAID tidak ikut terbawa
$where_clauses = array("(pembayaran_STATUS = 'PAID' OR pembayaran_STATUS = 'LUNAS')");
$bind_types = "";
$bind_params = array();

if ($selected_month !== '') {
    $where_clauses[] = "MONTH($date_column) = ?";
    $bind_types .= "i";
    $bind_params[] = $selected_month;
}

if ($selected_year !== '') {
    $where_clauses[] = "YEAR($date_column) = ?";
    $bind_types .= "i";
    $bind_params[] = $selected_year;
}

// Menggabungkan semua klausa WHERE dengan AND
$where_sql = " WHERE " . implode(" AND ", $where_clauses);

// 1. Fetch filtered records directly from the real PEMBAYARAN table (Hanya yang PAID)
$data_query_str = "SELECT * FROM " . $payment_table_name . $where_sql . " ORDER BY $date_column DESC LIMIT 100";
$stmt_data = $conn->prepare($data_query_str);

if (!empty($bind_types)) {
    $stmt_data->bind_param($bind_types, ...$bind_params);
}

$stmt_data->execute();
$report_result = $stmt_data->get_result();

// 2. Count the total number of transaction records found (Otomatis hanya menghitung yang PAID)
$total_transactions = $report_result->num_rows;

// 3. Calculate dynamic total revenue from the 'pembayaran_NOMINAL' column
$total_revenue = 0;
$payment_records = [];

while ($row = $report_result->fetch_assoc()) {
    $payment_records[] = $row;

    // Bersihkan karakter non-angka dan jumlahkan ke pendapatan
    $clean_nominal = preg_replace('/[^0-9]/', '', $row['pembayaran_NOMINAL']);
    $total_revenue += (int) $clean_nominal;
}

// Month array helper for the dropdown filter selection
$month_names = [
    1 => "January",
    2 => "February",
    3 => "March",
    4 => "April",
    5 => "May",
    6 => "June",
    7 => "July",
    8 => "August",
    9 => "September",
    10 => "October",
    11 => "November",
    12 => "December"
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Financial Payment Reports</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
        body,
        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        p,
        span,
        table,
        th,
        td,
        div,
        select,
        button {
            font-family: 'Poppins', sans-serif !important;
        }

        .bg-gradient-finance {
            background: linear-gradient(135deg, #0f2027 0%, #203a43 50%, #2c5364 100%);
        }

        .stat-card {
            border: none;
            border-radius: 15px;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .form-select-sm {
            border-radius: 30px;
            padding-left: 15px;
            padding-right: 30px;
            height: 38px;
            font-size: 0.85rem;
        }

        @media print {

            /* Sembunyikan navbar atas, sidebar kiri, banner, form filter, dan footer */
            #layoutSidenav_nav,
            .sb-topnav,
            .bg-gradient-monthly,
            .card.stat-card.shadow-sm.border-0.mb-4.bg-white,
            .card-header .btn,
            footer {
                display: none !important;
            }

            /* Hilangkan padding default layout agar tabelnya melebar pas di kertas */
            #layoutSidenav_content {
                padding-left: 0 !important;
                top: 0 !important;
                margin: 0 !important;
            }

            body {
                background-color: #fff !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            /* Hilangkan shadow/box pada card tabel saat diprint */
            .stat-card {
                box-shadow: none !important;
                border: none !important;
                background: transparent !important;
            }
        }
    </style>
</head>

<?php include("../template/head.php"); ?>

<body class="sb-nav-fixed" style="background-color: #f4f7fa;">

    <?php include("../template/menunav.php"); ?>

    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <?php include("menu.php"); ?>
        </div>

        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4" style="padding-top: 30px;">

                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                        <div>
                            <h4 class="mb-0" style="font-weight: 700; color: #1E3754;">Payment Statements & Invoices
                            </h4>
                            <p class="text-muted small mb-0">Overview of student transactional ledger records for
                                Eduzone Bimbel</p>
                        </div>
                        <div>
                            <span class="badge bg-white text-dark border px-3 py-2 rounded-pill shadow-sm small">
                                <i class="far fa-calendar-alt me-1 text-success"></i> <?php echo date('d M Y'); ?>
                            </span>
                        </div>
                    </div>

                    <div class="card stat-card shadow-sm overflow-hidden mb-4 bg-gradient-finance">
                        <div class="card-body p-4 p-md-5 text-white">
                            <div class="row align-items-center">
                                <div class="col-md-9">
                                    <span class="badge bg-success text-white px-3 py-1.5 rounded-pill mb-3"
                                        style="font-weight: 600; font-size: 0.75rem; letter-spacing: 0.5px;">
                                        FINANCE ARCHIVE LOG
                                    </span>
                                    <h1 class="mb-2" style="font-weight: 700; letter-spacing: -1px;">
                                        Cash Ledger & Revenue Accounting
                                    </h1>
                                    <p class="mb-0" style="color: #cbd5e1; font-size: 0.95rem; font-weight: 300;">
                                        Monitoring live transactional database logs extracted from the official
                                        <b>Eduzone</b> systems.
                                    </p>
                                </div>
                                <div class="col-md-3 text-end d-none d-md-block opacity-25">
                                    <i class="fas fa-file-invoice-dollar fa-6x text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card stat-card shadow-sm border-0 mb-4 bg-white">
                        <div class="card-body p-3">
                            <form method="GET" action="laporan.php" class="row g-2 align-items-center m-0">
                                <div class="col-auto">
                                    <span class="text-dark small" style="font-weight: 600;"><i
                                            class="fas fa-filter me-1 text-secondary"></i> Period Filter (Payment Date): </span>
                                </div>

                                <div class="col-md-2 col-sm-5">
                                    <select name="filter_year" class="form-select form-select-sm border">
                                        <option value="">-- Year --</option>
                                        <?php
                                        // Hanya memunculkan tahun pembayaran dari data yang sudah lunas/Paid
                                        $years_query = mysqli_query($conn, "SELECT DISTINCT YEAR(pembayaran_TANGGAL) as year_num FROM " . $payment_table_name . " WHERE (pembayaran_STATUS = 'PAID' OR pembayaran_STATUS = 'LUNAS') AND pembayaran_TANGGAL IS NOT NULL AND pembayaran_TANGGAL != '0000-00-00' ORDER BY year_num DESC");
                                        if ($years_query) {
                                            while ($y = mysqli_fetch_assoc($years_query)) {
                                                $y_num = (int) $y['year_num'];
                                                $is_selected = ($selected_year === $y_num) ? 'selected' : '';
                                                echo "<option value='$y_num' $is_selected>$y_num</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="col-md-3 col-sm-5">
                                    <select name="filter_month" class="form-select form-select-sm border">
                                        <option value="">-- All Months --</option>
                                        <?php
                                        for ($m_num = 1; $m_num <= 12; $m_num++) {
                                            $is_selected = ($selected_month === $m_num) ? 'selected' : '';
                                            echo "<option value='$m_num' $is_selected>" . $month_names[$m_num] . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="col-auto">
                                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4"
                                        style="height: 38px;">
                                        <i class="fas fa-sync-alt me-1"></i> Generate Report
                                    </button>
                                    <?php if ($selected_month !== '' || $selected_year !== ''): ?>
                                        <a href="laporan.php" class="btn btn-sm btn-light border rounded-pill px-3 ms-1"
                                            style="height: 38px; line-height: 26px;">
                                            Reset
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-xl-6 col-md-6 mb-3">
                            <div class="card stat-card shadow-sm bg-white border-start border-primary border-4">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <p class="text-muted small text-uppercase mb-1" style="font-weight: 600;">
                                                Paid Volume</p>
                                            <h2 class="mb-0" style="font-weight: 700; color: #1E3754;">
                                                <?php echo $total_transactions; ?> <span
                                                    style="font-size: 1rem; font-weight: 400;" class="text-muted">Paid
                                                    Records</span>
                                            </h2>
                                        </div>
                                        <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                                            <i class="fas fa-receipt fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-6 col-md-6 mb-3">
                            <div class="card stat-card shadow-sm bg-white border-start border-success border-4">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <p class="text-muted small text-uppercase mb-1" style="font-weight: 600;">
                                                Calculated Gross Earnings</p>
                                            <h2 class="mb-0" style="font-weight: 700; color: #198754;">
                                                Rp <?php echo number_format($total_revenue, 0, ',', '.'); ?>
                                            </h2>
                                        </div>
                                        <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle">
                                            <i class="fas fa-money-bill-wave fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card stat-card shadow-sm border-0 mb-5">
                        <div
                            class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h6 class="m-0" style="font-weight: 700; color: #1E3754;">
                                <i class="fas fa-list-alt me-2 text-success"></i> Income Ledger Statements (PAID Only)
                            </h6>
                            <button class="btn btn-sm btn-success rounded-pill px-4" onclick="window.print()">
                                <i class="fas fa-print me-1"></i> Print Current View
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light text-muted small text-uppercase">
                                        <tr>
                                            <th class="ps-4 text-dark" style="font-weight: 600;">PAYMENT ID</th>
                                            <th class="ps-4 text-dark" style="font-weight: 600;">STUDENT ID</th>
                                            <th class="ps-4 text-dark" style="font-weight: 600;">DUE DATE</th>
                                            <th class="ps-4 text-dark" style="font-weight: 600;">PAYMENT DATE</th>
                                            <th class="ps-4 text-dark" style="font-weight: 600;">NOMINAL</th>
                                            <th class="pe-4 text-center">STATUS</th>
                                        </tr>
                                    </thead>
                                    <tbody class="small">
                                        <?php
                                        if (count($payment_records) > 0) {
                                            foreach ($payment_records as $row) {
                                                $nominal_display = is_numeric($row['pembayaran_NOMINAL']) ? 'Rp ' . number_format($row['pembayaran_NOMINAL'], 0, ',', '.') : $row['pembayaran_NOMINAL'];

                                                echo '<tr>';
                                                echo '<td class="ps-4 py-3 text-dark" style="font-weight: 600;">#' . htmlspecialchars($row['pembayaran_ID']) . '</td>';
                                                echo '<td class="ps-4 py-3 text-secondary">' . htmlspecialchars($row['siswa_ID']) . '</td>';
                                                echo '<td class="ps-4 py-3 text-secondary">' . htmlspecialchars($row['pembayaran_TENGGAT']) . '</td>';
                                                echo '<td class="ps-4 py-3 text-secondary">' . (!empty($row['pembayaran_TANGGAL']) && $row['pembayaran_TANGGAL'] != '0000-00-00' ? htmlspecialchars($row['pembayaran_TANGGAL']) : '-') . '</td>';
                                                echo '<td class="ps-4 py-3 text-success" style="font-weight: 600;">' . htmlspecialchars($nominal_display) . '</td>';
                                                echo '<td class="pe-4 text-center"><span class="badge bg-success text-success bg-opacity-10 rounded-pill px-3 py-1.5">' . htmlspecialchars($row['pembayaran_STATUS']) . '</span></td>';
                                                echo '</tr>';
                                            }
                                        } else {
                                            ?>
                                            <tr>
                                                <td class="text-center text-muted py-5" colspan="6">
                                                    <i class="fas fa-folder-open fa-2x mb-2 text-secondary d-block"></i>
                                                    No paid ledger history statements found for this period.
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
if (isset($stmt_data)) {
    $stmt_data->close();
}
mysqli_close($conn);
ob_end_flush();
?>