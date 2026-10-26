<?php
ob_start();
session_start();

if (!isset($_SESSION["useremail"]) || $_SESSION["role"] !== "admin") {
    header("location:../frontend/login.php");
    exit();
}

include("../includes/config.php");

// ─── Helper: tingkat code → full English name ─────────────────────────────────
function tingkatLabel($tingkat) {
    $map = [
        'SD'  => 'Elementary School',
        'SMP' => 'Junior High School',
        'SMA' => 'Senior High School',
    ];
    return isset($map[$tingkat]) ? $map[$tingkat] : $tingkat;
}

// ─── Filter ───────────────────────────────────────────────────────────────────
$selected_month = isset($_GET['filter_month']) && $_GET['filter_month'] !== '' ? (int) $_GET['filter_month'] : (int) date('m');
$selected_year  = isset($_GET['filter_year'])  && $_GET['filter_year']  !== '' ? (int) $_GET['filter_year']  : (int) date('Y');

// ─── Main report query ────────────────────────────────────────────────────────
$report_query_str = "SELECT 
                        s.siswa_ID, 
                        s.siswa_NAME, 
                        s.siswa_TINGKAT, 
                        s.siswa_KELAS,
                        COUNT(CASE WHEN a.absen_STATUS = 'Present' THEN 1 END) as total_present,
                        COUNT(CASE WHEN a.absen_STATUS = 'Sick'    THEN 1 END) as total_sick,
                        COUNT(CASE WHEN a.absen_STATUS = 'Excused' THEN 1 END) as total_excused,
                        COUNT(CASE WHEN a.absen_STATUS = 'Absent'  THEN 1 END) as total_absent,
                        COUNT(a.absen_ID) as total_sessions
                     FROM bio_siswa s
                     LEFT JOIN absensi a ON s.siswa_ID = a.siswa_ID 
                         AND MONTH(a.absen_DATE) = ? 
                         AND YEAR(a.absen_DATE)  = ?
                     GROUP BY s.siswa_ID, s.siswa_NAME, s.siswa_TINGKAT, s.siswa_KELAS
                     ORDER BY s.siswa_TINGKAT ASC, s.siswa_KELAS ASC, s.siswa_NAME ASC";

$stmt = $conn->prepare($report_query_str);
$stmt->bind_param("ii", $selected_month, $selected_year);
$stmt->execute();
$result = $stmt->get_result();

$records          = [];
$grand_present    = 0;
$grand_sick       = 0;
$grand_excused    = 0;
$grand_absent     = 0;

while ($row = $result->fetch_assoc()) {
    $records[]      = $row;
    $grand_present += $row['total_present'];
    $grand_sick    += $row['total_sick'];
    $grand_excused += $row['total_excused'];
    $grand_absent  += $row['total_absent'];
}

$month_names = [
    1=>"January",2=>"February",3=>"March",4=>"April",
    5=>"May",6=>"June",7=>"July",8=>"August",
    9=>"September",10=>"October",11=>"November",12=>"December"
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Monthly Attendance Report</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, body, h1, h2, h3, h4, h5, h6, p, span, table, th, td, div, select, button {
            font-family: 'Poppins', sans-serif !important;
        }
        .bg-gradient-monthly {
            background: linear-gradient(135deg, #4b6cb7 0%, #182848 100%);
        }
        .stat-card {
            border: none;
            border-radius: 15px;
            transition: all 0.3s ease;
        }
        .stat-card:hover { transform: translateY(-3px); }
        .form-select-pill {
            border-radius: 30px;
            padding-left: 15px;
            padding-right: 30px;
            height: 38px;
            font-size: 0.85rem;
        }
        .summary-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 30px;
            font-size: 0.82rem;
            font-weight: 600;
            border: 1.5px solid;
        }
        .summary-badge.present  { background: rgba(25,135,84,0.08);  color: #198754; border-color: rgba(25,135,84,0.2); }
        .summary-badge.sick     { background: rgba(255,193,7,0.10);  color: #b07800; border-color: rgba(255,193,7,0.3); }
        .summary-badge.excused  { background: rgba(13,202,240,0.10); color: #0a8fa8; border-color: rgba(13,202,240,0.3); }
        .summary-badge.absent   { background: rgba(220,53,69,0.08);  color: #dc3545; border-color: rgba(220,53,69,0.2); }

        .table thead th {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            background: #f8f9fc;
            border-bottom: 1.5px solid #e9ecef;
            padding: 12px 14px;
        }
        .table tbody td {
            padding: 12px 14px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f3f7;
        }
        .table tbody tr:last-child td { border-bottom: none; }
        .table tbody tr:hover { background: #f8f9fc; }

        @media print {
            #layoutSidenav_nav,
            .sb-topnav,
            .bg-gradient-monthly,
            .filter-section,
            .summary-row,
            .btn-print,
            footer { display: none !important; }
            #layoutSidenav_content { padding-left: 0 !important; top: 0 !important; margin: 0 !important; }
            body { background-color: #fff !important; padding: 0 !important; margin: 0 !important; }
            .stat-card { box-shadow: none !important; border: 1px solid #dee2e6 !important; }
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
                <div class="container-fluid px-4" style="padding-top: 30px; padding-bottom: 40px;">

                    <!-- Page Title -->
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                        <div>
                            <h4 class="mb-0" style="font-weight: 700; color: #1E3754;">Monthly Attendance Report</h4>
                            <p class="text-muted small mb-0">Monthly summary of student attendance across all classes</p>
                        </div>
                        <span class="badge bg-white text-dark border px-3 py-2 rounded-pill shadow-sm small">
                            <i class="far fa-calendar-alt me-1 text-primary"></i><?php echo date('d M Y'); ?>
                        </span>
                    </div>

                    <!-- Hero Banner -->
                    <div class="card stat-card shadow-sm overflow-hidden mb-4 bg-gradient-monthly">
                        <div class="card-body p-4 p-md-5 text-white">
                            <div class="row align-items-center">
                                <div class="col-md-9">
                                    <span class="badge bg-white bg-opacity-20 text-white px-3 py-1 rounded-pill mb-3"
                                          style="font-weight: 600; font-size: 0.75rem; letter-spacing: 0.5px; background: rgba(255,255,255,0.2) !important;">
                                        MONTHLY ATTENDANCE ANALYTICS
                                    </span>
                                    <h1 class="mb-2" style="font-weight: 700; letter-spacing: -1px;">
                                        Attendance Recapitulation
                                    </h1>
                                    <p class="mb-0" style="color: #cbd5e1; font-size: 0.95rem; font-weight: 300;">
                                        Showing attendance breakdown for
                                        <strong><?php echo $month_names[$selected_month] . ' ' . $selected_year; ?></strong>.
                                    </p>
                                </div>
                                <div class="col-md-3 text-end d-none d-md-block" style="opacity: 0.15;">
                                    <i class="fas fa-chart-pie fa-6x text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filter -->
                    <div class="card stat-card shadow-sm border-0 mb-4 bg-white filter-section">
                        <div class="card-body p-3">
                            <form method="GET" action="laporankehadiran.php" class="row g-2 align-items-center m-0">
                                <div class="col-auto">
                                    <span class="text-dark small" style="font-weight: 600;">
                                        <i class="fas fa-filter me-1 text-secondary"></i> Filter Period:
                                    </span>
                                </div>
                                <div class="col-md-2 col-sm-5">
                                    <select name="filter_year" class="form-select form-select-pill border">
                                        <?php
                                        $years_query = mysqli_query($conn, "SELECT DISTINCT YEAR(absen_DATE) as year_num FROM absensi WHERE absen_DATE IS NOT NULL ORDER BY year_num DESC");
                                        $has_years = false;
                                        if ($years_query && mysqli_num_rows($years_query) > 0) {
                                            while ($y = mysqli_fetch_assoc($years_query)) {
                                                $y_num = (int) $y['year_num'];
                                                $sel = ($selected_year === $y_num) ? 'selected' : '';
                                                echo "<option value='$y_num' $sel>$y_num</option>";
                                                $has_years = true;
                                            }
                                        }
                                        if (!$has_years) {
                                            echo "<option value='" . date('Y') . "' selected>" . date('Y') . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-3 col-sm-5">
                                    <select name="filter_month" class="form-select form-select-pill border">
                                        <?php
                                        for ($m = 1; $m <= 12; $m++) {
                                            $sel = ($selected_month === $m) ? 'selected' : '';
                                            echo "<option value='$m' $sel>{$month_names[$m]}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4" style="height: 38px;">
                                        <i class="fas fa-sync-alt me-1"></i> Apply Filter
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Grand Total Summary -->
                    <div class="d-flex flex-wrap gap-2 mb-4 summary-row">
                        <span class="summary-badge present">
                            <i class="fas fa-check-circle"></i> Present: <?php echo $grand_present; ?> sessions
                        </span>
                        <span class="summary-badge sick">
                            <i class="fas fa-thermometer-half"></i> Sick: <?php echo $grand_sick; ?> sessions
                        </span>
                        <span class="summary-badge excused">
                            <i class="fas fa-envelope"></i> Excused: <?php echo $grand_excused; ?> sessions
                        </span>
                        <span class="summary-badge absent">
                            <i class="fas fa-times-circle"></i> Absent: <?php echo $grand_absent; ?> sessions
                        </span>
                    </div>

                    <!-- Table -->
                    <div class="card stat-card shadow-sm border-0 mb-5">
                        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h6 class="m-0" style="font-weight: 700; color: #1E3754;">
                                <i class="fas fa-th-list me-2 text-primary"></i>
                                Student Attendance Roster — <?php echo $month_names[$selected_month] . ' ' . $selected_year; ?>
                            </h6>
                            <button class="btn btn-sm btn-outline-secondary rounded-pill px-4 btn-print" onclick="window.print()">
                                <i class="fas fa-print me-1"></i> Print Report
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 text-center">
                                    <thead>
                                        <tr>
                                            <th class="ps-4 text-start" style="width: 40px;">#</th>
                                            <th class="text-start">Student Name</th>
                                            <th>Class</th>
                                            <th class="text-success">Present</th>
                                            <th style="color: #b07800;">Sick</th>
                                            <th style="color: #0a8fa8;">Excused</th>
                                            <th class="text-danger">Absent</th>
                                            <th>Total Sessions</th>
                                            <th class="pe-4" style="width: 14%;">Attendance Rate</th>
                                        </tr>
                                    </thead>
                                    <tbody class="small">
                                        <?php
                                        if (count($records) > 0) {
                                            $no = 1;
                                            foreach ($records as $row) {
                                                $rate = 0;
                                                if ($row['total_sessions'] > 0) {
                                                    $rate = round(($row['total_present'] / $row['total_sessions']) * 100);
                                                }
                                                if ($rate >= 85)     $bar_color = 'bg-success';
                                                elseif ($rate >= 70) $bar_color = 'bg-warning';
                                                else                 $bar_color = 'bg-danger';

                                                $class_label = tingkatLabel($row['siswa_TINGKAT']) . ' — Grade ' . $row['siswa_KELAS'];
                                                ?>
                                                <tr>
                                                    <td class="ps-4 text-muted"><?php echo $no++; ?></td>
                                                    <td class="text-start" style="font-weight: 600; color: #1E3754;">
                                                        <?php echo htmlspecialchars($row['siswa_NAME']); ?>
                                                        <br><small class="text-muted fw-normal" style="font-size: 10px;">ID #<?php echo $row['siswa_ID']; ?></small>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-light text-dark border fw-normal small">
                                                            <?php echo htmlspecialchars($class_label); ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-success fw-bold"><?php echo $row['total_present']; ?></td>
                                                    <td style="color: #b07800; font-weight: 500;"><?php echo $row['total_sick']; ?></td>
                                                    <td style="color: #0a8fa8; font-weight: 500;"><?php echo $row['total_excused']; ?></td>
                                                    <td class="text-danger fw-bold"><?php echo $row['total_absent']; ?></td>
                                                    <td class="fw-bold"><?php echo $row['total_sessions']; ?></td>
                                                    <td class="pe-4">
                                                        <?php if ($row['total_sessions'] > 0): ?>
                                                            <div class="d-flex align-items-center justify-content-center gap-2">
                                                                <div class="progress flex-grow-1" style="height: 6px; border-radius: 10px; min-width: 50px;">
                                                                    <div class="progress-bar <?php echo $bar_color; ?>" style="width: <?php echo $rate; ?>%"></div>
                                                                </div>
                                                                <span class="fw-bold" style="font-size: 11px; min-width: 34px;"><?php echo $rate; ?>%</span>
                                                            </div>
                                                        <?php else: ?>
                                                            <span class="text-muted small">—</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php
                                            }
                                        } else { ?>
                                            <tr>
                                                <td colspan="9" class="text-center text-muted py-5">
                                                    <i class="fas fa-folder-open fa-2x mb-2 text-secondary d-block"></i>
                                                    No attendance records found for <?php echo $month_names[$selected_month] . ' ' . $selected_year; ?>.
                                                </td>
                                            </tr>
                                        <?php } ?>
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
$stmt->close();
mysqli_close($conn);
ob_end_flush();
?>