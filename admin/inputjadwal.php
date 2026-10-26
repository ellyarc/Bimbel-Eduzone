<?php
ob_start();
session_start();
if (!isset($_SESSION["useremail"])) {
    header("location:../frontend/login.php");
    exit();
}
include("../includes/config.php");
$admin_id_session = $_SESSION["admin_id"];

$error_message = "";
?>
<!DOCTYPE html>
<html lang="en">

<?php include("../template/head.php"); ?>

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

    /* Button Pill */
    .btn-pill {
        border-radius: 30px;
        padding: 8px 24px;
        font-weight: 500;
    }

    /* Table Styling */
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

<body class="sb-nav-fixed" style="background-color: #f4f7fa;">
    <?php include("../template/menunav.php"); ?>

    <div id="layoutSidenav">
        <?php include("menu.php"); ?>

        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">

                    <div class="d-flex justify-content-between align-items-center mt-4 mb-4">
                        <div>
                            <h2 style="font-weight: 700; color: #1E3754; margin-bottom: 2px;">Schedule Management</h2>
                            <p class="text-muted small mb-0">Manage, schedule, and monitor all class teaching activities for tutors</p>
                        </div>
                    </div>

                    <?php
                    include("../includes/config.php");

                    if (isset($_POST["Simpan"])) {
                        $tutor_ID = mysqli_real_escape_string($conn, $_POST["tutorID"]);
                        $tanggal = mysqli_real_escape_string($conn, $_POST["tanggal"]);
                        $sesi = mysqli_real_escape_string($conn, $_POST["sesi"]);
                        $tingkat = mysqli_real_escape_string($conn, $_POST['tingkat']);
                        $kelas = mysqli_real_escape_string($conn, $_POST["kelas"]);
                        $mapel = mysqli_real_escape_string($conn, $_POST["mapel"]);

                        // 1. Check if the schedule slot already exists
                        $cek_jadwal = mysqli_query($conn, "SELECT * FROM jadwal WHERE tanggal = '$tanggal' AND sesi = '$sesi'");

                        if (mysqli_num_rows($cek_jadwal) > 0) {
                            // 2. JavaScript Alert in English
                            echo "<script>
                                    alert('Failed! A class already exists on date $tanggal for session $sesi.');
                                    window.location.href = 'inputjadwal.php';
                                </script>";
                            exit();
                        } else {
                            // 3. Insert schedule
                            mysqli_query($conn, "INSERT INTO jadwal (tutor_ID, tanggal, sesi, tingkat, kelas, mapel)
                                VALUES ('$tutor_ID', '$tanggal', '$sesi', '$tingkat', '$kelas', '$mapel')");

                            header("location:inputjadwal.php");
                            exit();
                        }
                    }

                    if (isset($_POST["kirim"])) {
                        $search = mysqli_real_escape_string($conn, $_POST["search"]);
                        $query = mysqli_query($conn, "
                            SELECT j.*, b.tutor_NAME
                            FROM jadwal j
                            JOIN bio_tutor b ON j.tutor_ID = b.tutor_ID
                            WHERE b.tutor_NAME LIKE '%$search%'
                            ORDER BY j.tanggal DESC, j.sesi ASC
                        ");
                    } else {
                        $query = mysqli_query($conn, "
                            SELECT j.*, b.tutor_NAME
                            FROM jadwal j
                            JOIN bio_tutor b ON j.tutor_ID = b.tutor_ID
                            ORDER BY j.tanggal DESC, j.sesi ASC
                        ");
                    }

                    $datatutor = mysqli_query($conn, "SELECT tutor_ID, tutor_NAME FROM bio_tutor");
                    ?>

                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card custom-card">
                                <div class="card-header-custom">
                                    <h5 class="m-0" style="font-weight: 700; color: #4e73df;">
                                        <i class="fas fa-calendar-plus me-2"></i> Input New Class Schedule
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
                                                            <option value="<?php echo $t["tutor_ID"]; ?>">
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
                                                    <input type="date" class="form-control" name="tanggal" required>
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
                                                        <option value="1">Session 1</option>
                                                        <option value="2">Session 2</option>
                                                        <option value="3">Session 3</option>
                                                        <option value="4">Session 4</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Level</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-mortarboard"></i></span>
                                                    <select class="form-select" name="tingkat" id="tingkat" required>
                                                        <option value="">-- Choose Level --</option>
                                                        <option value="SD">Elementary School (SD)</option>
                                                        <option value="SMP">Middle School (SMP)</option>
                                                        <option value="SMA">High School (SMA)</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mb-4">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Grade</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-layers"></i></span>
                                                    <select class="form-select" name="kelas" id="kelas" required disabled>
                                                        <option value="">-- Choose Level First --</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Subjects</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-book"></i></span>
                                                    <input type="text" class="form-control" name="mapel" placeholder="Example: Mathematics, Physics" required>
                                                </div>
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

                                            tingkatSelect.addEventListener('change', function () {
                                                const tingkatTerpilih = this.value;

                                                kelasSelect.innerHTML = '<option value="">-- Choose Grade --</option>';

                                                if (tingkatTerpilih && listKelas[tingkatTerpilih]) {
                                                    kelasSelect.disabled = false;
                                                    listKelas[tingkatTerpilih].forEach(function (kelas) {
                                                        const option = document.createElement('option');
                                                        option.value = kelas;
                                                        option.textContent = 'Grade ' + kelas;
                                                        kelasSelect.appendChild(option);
                                                    });
                                                } else {
                                                    kelasSelect.disabled = true;
                                                    kelasSelect.innerHTML = '<option value="">-- Choose Level First --</option>';
                                                }
                                            });
                                        </script>

                                        <div class="d-flex justify-content-end border-top pt-3">
                                            <button type="reset" class="btn btn-light btn-pill border me-2">
                                                <i class="fas fa-sync-alt me-1"></i> Reset
                                            </button>
                                            <button type="submit" class="btn btn-success btn-pill shadow-sm" name="Simpan">
                                                <i class="fas fa-save me-1"></i> Save Schedule
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
                                        <i class="fas fa-list me-2"></i> Schedule List Data
                                    </h5>

                                    <form method="POST" class="d-flex align-items-center" style="max-width: 400px; width: 100%;">
                                        <div class="input-group">
                                            <input type="text" name="search" class="form-control" value="<?php if (isset($_POST["search"])) echo htmlspecialchars($_POST["search"]); ?>" placeholder="Search tutor name..." style="padding-top: 7px; padding-bottom: 7px;">
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
                                                    <th>Admin ID</th>
                                                    <th>Tutor Name</th>
                                                    <th>Date</th>
                                                    <th>Session</th>
                                                    <th>Level</th>
                                                    <th>Grade</th>
                                                    <th>Subjects</th>
                                                    <th class="text-center" style="width: 130px;">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if (mysqli_num_rows($query) > 0) {
                                                    while ($row = mysqli_fetch_array($query)) {
                                                        ?>
                                                        <tr>
                                                            <td style="font-weight: 600; color: #e74a3b;">
                                                                <span class="badge bg-danger-subtle text-danger px-2 py-1" style="border-radius: 6px;">
                                                                    ID: <?php echo htmlspecialchars($admin_id_session); ?>
                                                                </span>
                                                            </td>
                                                            <td style="font-weight: 600; color: #2e2f37;">
                                                                <?php echo htmlspecialchars($row["tutor_NAME"]); ?>
                                                            </td>
                                                            <td><i class="bi bi-calendar-event text-muted me-1"></i>
                                                                <?php echo htmlspecialchars($row["tanggal"]); ?></td>
                                                            <td>
                                                                <span class="badge bg-primary-subtle text-primary px-3 py-1.5 border border-primary-subtle" style="border-radius: 20px; font-weight: 500;">
                                                                    Session <?php echo htmlspecialchars($row["sesi"]); ?>
                                                                </span>
                                                            </td>
                                                            <td style="font-weight: 500;">
                                                                <?php echo htmlspecialchars($row["tingkat"]); ?></td>
                                                            <td>Grade <?php echo htmlspecialchars($row["kelas"]); ?></td>
                                                            <td style="font-weight: 500; color: #4e73df;">
                                                                <?php echo htmlspecialchars($row["mapel"]); ?></td>
                                                            <td class="text-center">
                                                                <div class="btn-group" role="group">
                                                                    <a href="editjadwal.php?ubahjadwal=<?php echo $row['tanggal']; ?>&sesi=<?php echo $row['sesi']; ?>" class="btn btn-outline-success btn-sm border-0" title="Edit" style="border-radius: 6px; margin-right: 4px;">
                                                                        <i class="bi bi-pencil-square fs-6"></i>
                                                                    </a>
                                                                    <a href="hapusjadwal.php?tanggal=<?php echo $row['tanggal']; ?>&sesi=<?php echo $row['sesi']; ?>" class="btn btn-outline-danger btn-sm border-0" title="Delete" onclick="return confirm('Are you sure you want to delete the schedule for Date <?php echo $row['tanggal']; ?> Session <?php echo $row['sesi']; ?>?');" style="border-radius: 6px;">
                                                                        <i class="bi bi-trash fs-6"></i>
                                                                    </a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php
                                                    }
                                                } else {
                                                    ?>
                                                    <tr>
                                                        <td colspan="8" class="text-center py-5 text-muted">
                                                            <i class="bi bi-folder-x display-6 d-block mb-2 text-gray-400"></i>
                                                            No schedule records found.
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
</body>

</html>