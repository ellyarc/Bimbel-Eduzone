<?php
ob_start();
session_start();

// PROTECTION: If not logged in or the role is not admin, redirect back to login.php
if (!isset($_SESSION["useremail"]) || $_SESSION["role"] !== "admin") {
    header("location:../frontend/login.php");
    exit();
}

// Connecting to the database
include("../includes/config.php");

// ====================================================================
// FETCH REAL OPERATIONAL DATA FROM DATABASE TABLES
// ====================================================================

// 1. Count Total Students from the 'siswa' table
$query_siswa = mysqli_query($conn, "SELECT COUNT(*) as total FROM siswa");
$data_siswa = mysqli_fetch_assoc($query_siswa);
$total_siswa = $data_siswa['total'];

// 2. Count Total Tutors from the 'tutor' table
$query_tutor = mysqli_query($conn, "SELECT COUNT(*) as total FROM tutor");
$data_tutor = mysqli_fetch_assoc($query_tutor);
$total_tutor = $data_tutor['total'];

// 3. Count Active Class Sessions from the 'jadwal' table
$query_kelas = mysqli_query($conn, "SELECT COUNT(*) as total FROM jadwal");
$data_kelas = mysqli_fetch_assoc($query_kelas);
$kelas_aktif = $data_kelas['total'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
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
        div {
            font-family: 'Poppins', sans-serif !important;
        }

        .bg-gradient-eduzone {
            background: linear-gradient(135deg, #1E3754 0%, #2a4e78 100%);
        }

        .stat-card {
            border: none;
            border-radius: 15px;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
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

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="mb-0" style="font-weight: 700; color: #1E3754;">Main Dashboard</h4>
                            <p class="text-muted small mb-0">Overview of Eduzone Bimbel operational activities</p>
                        </div>
                        <div>
                            <span class="badge bg-white text-dark border px-3 py-2 rounded-pill shadow-sm small">
                                <i class="far fa-calendar-alt me-1 text-primary"></i> <?php echo date('d M Y'); ?>
                            </span>
                        </div>
                    </div>

                    <div class="card stat-card shadow-sm overflow-hidden mb-4 bg-gradient-eduzone">
                        <div class="card-body p-4 p-md-5 text-white">
                            <div class="row align-items-center">
                                <div class="col-md-9">
                                    <span class="badge bg-light text-dark px-3 py-1.5 rounded-pill mb-3"
                                        style="font-weight: 600; font-size: 0.75rem;">
                                        LOGGED IN AS ADMIN
                                    </span>
                                    <h1 class="mb-2" style="font-weight: 700; letter-spacing: -1px;">
                                        Hello, <?php echo htmlspecialchars($_SESSION["useremail"]); ?>!
                                    </h1>
                                    <p class="mb-0" style="color: #cbd5e1; font-size: 0.95rem; font-weight: 300;">
                                        System is ready for deployment. You have full administrator controls to manage student registrations, 
                                        track tutor attendance, and configure today's academic tutoring schedule.
                                    </p>
                                </div>
                                <div class="col-md-3 text-end d-none d-md-block opacity-25">
                                    <i class="fas fa-chart-pie fa-6x"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-xl-4 col-md-6 mb-3">
                            <div class="card stat-card shadow-sm bg-white border-start border-primary border-4">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <p class="text-muted small text-uppercase mb-1" style="font-weight: 600;">
                                                Total Active Students</p>
                                            <h2 class="mb-0" style="font-weight: 700; color: #1E3754;">
                                                <?php echo $total_siswa; ?>
                                                <span style="font-size: 1rem; font-weight: 400;"
                                                    class="text-muted">Students</span>
                                            </h2>
                                        </div>
                                        <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                                            <i class="fas fa-user-graduate fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-md-6 mb-3">
                            <div class="card stat-card shadow-sm bg-white border-start border-success border-4">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <p class="text-muted small text-uppercase mb-1" style="font-weight: 600;">
                                                Active Tutors / Faculty</p>
                                            <h2 class="mb-0" style="font-weight: 700; color: #1E3754;">
                                                <?php echo $total_tutor; ?>
                                                <span style="font-size: 1rem; font-weight: 400;"
                                                    class="text-muted">Teachers</span>
                                            </h2>
                                        </div>
                                        <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle">
                                            <i class="fas fa-chalkboard-teacher fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-md-6 mb-3">
                            <div class="card stat-card shadow-sm bg-white border-start border-warning border-4">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <p class="text-muted small text-uppercase mb-1" style="font-weight: 600;">
                                                Class Sessions Scheduled</p>
                                            <h2 class="mb-0" style="font-weight: 700; color: #1E3754;">
                                                <?php echo $kelas_aktif; ?>
                                                <span style="font-size: 1rem; font-weight: 400;"
                                                    class="text-muted">Slots</span>
                                            </h2>
                                        </div>
                                        <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-circle">
                                            <i class="fas fa-book-reader fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card stat-card shadow-sm border-0 mb-5">
                        <div class="card-header bg-white py-3 border-0">
                            <h6 class="m-0" style="font-weight: 700; color: #1E3754;">
                                <i class="fas fa-clock me-2 text-primary"></i> Real-Time Schedule Monitoring Hub
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" style="min-width: 600px;">
                                    <thead class="table-light text-muted small text-uppercase">
                                        <tr>
                                            <?php
                                            // Fetch schedule rows to dynamically discover structure names in the DB
                                            $ambil_jadwal = mysqli_query($conn, "SELECT * FROM jadwal LIMIT 5");

                                            if ($ambil_jadwal && mysqli_num_rows($ambil_jadwal) > 0) {
                                                // Extract all raw system column headers from the schema
                                                $finfo = mysqli_fetch_fields($ambil_jadwal);
                                                foreach ($finfo as $val) {
                                                    echo '<th class="ps-3 text-primary" style="font-weight: 600;">' . htmlspecialchars($val->name) . '</th>';
                                                }
                                                echo '<th class="pe-4">Status</th>';
                                            } else {
                                                echo '<th class="ps-4">Schedule Information Log</th>';
                                            }
                                            ?>
                                        </tr>
                                    </thead>
                                    <tbody class="small">
                                        <?php
                                        if ($ambil_jadwal && mysqli_num_rows($ambil_jadwal) > 0) {
                                            // Reset pointer loop stream back to index 0
                                            mysqli_data_seek($ambil_jadwal, 0);

                                            while ($row = mysqli_fetch_assoc($ambil_jadwal)) {
                                                echo '<tr>';
                                                foreach ($row as $key => $value) {
                                                    // Print cell data value; if null or empty, yield an unassigned label
                                                    $cetak_nilai = ($value !== null && $value !== '') ? $value : '<span class="text-danger"><i>Not Specified</i></span>';
                                                    echo '<td class="ps-3">' . $cetak_nilai . '</td>';
                                                }
                                                // Dynamic right-side status badge
                                                echo '<td class="pe-4"><span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1.5">Active</span></td>';
                                                echo '</tr>';
                                            }
                                        } else {
                                            ?>
                                            <tr>
                                                <td class="text-center text-muted py-5">
                                                    <i class="fas fa-folder-open fa-2x mb-2 text-secondary d-block"></i>
                                                    No schedule parameters found inside the <b>jadwal</b> table core. Please populate structural data inside your system database first.
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
            </main>

            <?php include("../template/footer.php"); ?>
        </div>
    </div>

    <?php include("../template/jsscript.php"); ?>
</body>

</html>
<?php
mysqli_close($conn);
ob_end_flush();
?>