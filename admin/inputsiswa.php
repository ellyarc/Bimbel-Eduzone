<?php
ob_start();
session_start();
if (!isset($_SESSION["useremail"])) {
    header("location:../frontend/login.php");
    exit();
}

include("../includes/config.php");

// 1. PROCESS SAVE DATA (USING PREPARED STATEMENTS)
if (isset($_POST["Save"])) {
    if (empty($_POST["idSiswa"])) {
        echo "<h1>Sorry, invalid input</h1>";
        die("Student ID is required");
    }

    $siswa_ID = $_POST["idSiswa"];
    $siswa_NAME = $_POST["namaSiswa"];
    $siswa_TINGKAT = $_POST["tingkatSiswa"];
    $siswa_KELAS = $_POST["kelasSiswa"];
    $siswa_USER = $_POST["usernameSiswa"];
    $siswa_PASS = $_POST["passwordSiswa"]; // Stores your raw text input directly

    // VALIDATION: CHECK IF STUDENT ID ALREADY EXISTS
    $stmt_cek = $conn->prepare("SELECT siswa_ID FROM bio_siswa WHERE siswa_ID = ?");
    $stmt_cek->bind_param("s", $siswa_ID);
    $stmt_cek->execute();
    $res_cek = $stmt_cek->get_result();

    if ($res_cek->num_rows > 0) {
        echo "<script>
            alert('Failed! Student ID " . htmlspecialchars($siswa_ID, ENT_QUOTES) . " is already registered in the system. Please use a different ID.');
            window.location.href='inputsiswa.php';
          </script>";
        exit();
    }
    $stmt_cek->close();

    // Begin Database Transaction to ensure both tables update together
    $conn->begin_transaction();

    try {
        // Insert data into bio_siswa table
        $stmt_bio = $conn->prepare("INSERT INTO bio_siswa (siswa_ID, siswa_NAME, siswa_TINGKAT, siswa_KELAS) VALUES (?, ?, ?, ?)");
        $stmt_bio->bind_param("ssss", $siswa_ID, $siswa_NAME, $siswa_TINGKAT, $siswa_KELAS);
        $stmt_bio->execute();
        $stmt_bio->close();

        // Insert data into siswa account table
        $stmt_akun = $conn->prepare("INSERT INTO siswa (siswa_ID, siswa_USER, siswa_PASS) VALUES (?, ?, ?)");
        $stmt_akun->bind_param("sss", $siswa_ID, $siswa_USER, $siswa_PASS);
        $stmt_akun->execute();
        $stmt_akun->close();

        // Commit transaction if both insert statements execute perfectly
        $conn->commit();
        
        header("location:inputsiswa.php");
        exit;

    } catch (Exception $e) {
        // Rollback database modifications if any query fails
        $conn->rollback();
        $error_msg = "Database Update Error: " . $e->getMessage();
    }
}

// 2. PROCESS SEARCH & RETRIEVE DATA
$search_value = "";
if (isset($_POST["search_submit"]) && !empty($_POST["search"])) {
    $search_value = $_POST["search"];
    $search_param = "%" . $search_value . "%";
    
    $stmt_select = $conn->prepare("SELECT b.siswa_ID, b.siswa_NAME, b.siswa_TINGKAT, b.siswa_KELAS, a.siswa_USER, a.siswa_PASS 
                                  FROM bio_siswa b 
                                  INNER JOIN siswa a ON b.siswa_ID = a.siswa_ID 
                                  WHERE b.siswa_ID LIKE ? OR b.siswa_NAME LIKE ? OR a.siswa_USER LIKE ?");
    $stmt_select->bind_param("sss", $search_param, $search_param, $search_param);
} else {
    $stmt_select = $conn->prepare("SELECT b.siswa_ID, b.siswa_NAME, b.siswa_TINGKAT, b.siswa_KELAS, a.siswa_USER, a.siswa_PASS 
                                  FROM bio_siswa b 
                                  INNER JOIN siswa a ON b.siswa_ID = a.siswa_ID");
}

$stmt_select->execute();
$query_result = $stmt_select->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Student Management System</title>
    <?php include("../template/head.php"); ?>
    
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
        .btn-pill { border-radius: 30px; padding: 8px 20px; font-weight: 500; }
        .badge-pill { border-radius: 20px; padding: 6px 12px; font-weight: 500; }
        .table-responsive-custom { border-radius: 12px; overflow: hidden; border: 1px solid #edf2f9; }
        .table-custom thead { background-color: #f8f9fc; }
        .table-custom th {
            color: #4e73df;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
            padding: 15px;
            border-bottom: 2px solid #edf2f9;
        }
        .table-custom td { padding: 15px; vertical-align: middle; color: #5a5c69; }
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
                            <h2 style="font-weight: 700; color: #1E3754; margin-bottom: 2px;">Student Management</h2>
                            <p class="text-muted small mb-0">Manage profile data and monitor complete tuition transaction records</p>
                        </div>
                    </div>

                    <?php if (isset($error_msg)): ?>
                        <div class='alert alert-danger'><?php echo htmlspecialchars($error_msg); ?></div>
                    <?php endif; ?>

                    <!-- REGISTRATION FORM -->
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card custom-card">
                                <div class="card-header-custom">
                                    <h5 class="m-0" style="font-weight: 700; color: #4e73df;">
                                        <i class="fas fa-user-plus me-2"></i>New Student Registration Form
                                    </h5>
                                </div>
                                <div class="card-body p-4">
                                    <form method="POST">
                                        <p class="text-primary small mb-3" style="font-weight: 600; letter-spacing: 0.5px;">A. STUDENT BIODATA</p>

                                        <div class="row mb-3">
                                            <label for="idSiswa" class="col-sm-2 col-form-label">Student ID</label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control" id="idSiswa" name="idSiswa" placeholder="Enter Student ID / Unique Registration Number" required>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="namaSiswa" class="col-sm-2 col-form-label">Full Name</label>
                                            <div class="col-sm-10">
                                                <input type="text" class="form-control" id="namaSiswa" name="namaSiswa" placeholder="Enter Student Full Name" required>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <label for="tingkatSiswa" class="col-sm-2 col-form-label">Grade Level</label>
                                            <div class="col-sm-10">
                                                <select class="form-select" id="tingkatSiswa" name="tingkatSiswa" required onchange="updateClass()">
                                                    <option value="">-- Select Grade Level --</option>
                                                    <option value="SD">Elementary School (SD)</option>
                                                    <option value="SMP">Junior High School (SMP)</option>
                                                    <option value="SMA">Senior High School (SMA)</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row mb-4">
                                            <label for="kelasSiswa" class="col-sm-2 col-form-label">Class</label>
                                            <div class="col-sm-10">
                                                <select class="form-select" id="kelasSiswa" name="kelasSiswa" required disabled>
                                                    <option value="">-- Select Grade Level First --</option>
                                                </select>
                                            </div>
                                        </div>

                                        <hr class="my-4" style="border-color: #edf2f9;">
                                        <p class="text-success small mb-3" style="font-weight: 600; letter-spacing: 0.5px;">B. STUDENT ACCOUNT CREDENTIALS</p>

                                        <div class="row mb-3">
                                            <label for="usernameSiswa" class="col-sm-2 col-form-label">Username / Email</label>
                                            <div class="col-sm-10">
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                                                    <input type="text" class="form-control" id="usernameSiswa" name="usernameSiswa" placeholder="e.g., mail@student.com or username123" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mb-4">
                                            <label for="passwordSiswa" class="col-sm-2 col-form-label">Password</label>
                                            <div class="col-sm-10">
                                                <div class="input-group">
                                                    <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                                                    <input type="password" class="form-control" id="passwordSiswa" name="passwordSiswa" placeholder="Enter manual account password" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-2"></div>
                                            <div class="col-sm-10">
                                                <button type="submit" class="btn btn-success btn-pill shadow-sm me-2" name="Save">
                                                    <i class="fas fa-save me-1"></i> Save Record
                                                </button>
                                                <button type="reset" class="btn btn-light btn-pill border" name="Cancel" onclick="resetClass()">
                                                    Cancel
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- DIRECTORY LISTING TABLE -->
                    <div class="row mt-2">
                        <div class="col-xl-12">
                            <div class="card custom-card">
                                <div class="card-header-custom d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                                    <h5 class="m-0" style="font-weight: 700; color: #1e3754;">
                                        <i class="fas fa-list me-2"></i>Student & Account Registry Directory
                                    </h5>
                                    <form method="POST" class="d-flex align-items-center gap-2 m-0">
                                        <input type="text" name="search" class="form-control form-control-sm" id="search"
                                            value="<?php echo htmlspecialchars($search_value, ENT_QUOTES); ?>"
                                            placeholder="Search by ID, Name, or User..." style="width: 250px; height: 38px;">
                                        <button type="submit" name="search_submit" class="btn btn-primary btn-sm px-3" style="height: 38px; border-radius: 8px;">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </form>
                                </div>
                                <div class="card-body p-4">
                                    <div class="table-responsive table-responsive-custom">
                                        <table class="table table-custom table-hover m-0">
                                            <thead>
                                                <tr>
                                                    <th>Student ID</th>
                                                    <th>Student Name</th>
                                                    <th>Grade Level</th>
                                                    <th>Class Room</th>
                                                    <th>Username</th>
                                                    <th>Password</th>
                                                    <th class="text-center">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if ($query_result->num_rows > 0) {
                                                    while ($row = $query_result->fetch_assoc()) {
                                                        ?>
                                                        <tr>
                                                            <td style="font-weight: 600; color: #4e73df;"><?php echo htmlspecialchars($row["siswa_ID"]); ?></td>
                                                            <td style="font-weight: 500;"><?php echo htmlspecialchars($row["siswa_NAME"]); ?></td>
                                                            <td><span class="badge bg-secondary badge-pill"><?php echo htmlspecialchars($row["siswa_TINGKAT"]); ?></span></td>
                                                            <td>Class <?php echo htmlspecialchars($row["siswa_KELAS"]); ?></td>
                                                            <td><?php echo htmlspecialchars($row["siswa_USER"]); ?></td>
                                                            <td>
                                                                <!-- Displays your manual plain text password entry accurately -->
                                                                <span class="text-muted font-monospace"><?php echo htmlspecialchars($row["siswa_PASS"]); ?></span>
                                                            </td>
                                                            <td class="text-center">
                                                                <div class="btn-group gap-1">
                                                                    <a href="editsiswa.php?ubahsiswa=<?php echo urlencode($row["siswa_ID"]); ?>" class="btn btn-outline-primary btn-sm" style="border-radius: 6px;" title="Edit Student">
                                                                        <i class="bi bi-pencil-square"></i>
                                                                    </a>
                                                                    <a href="hapussiswa.php?id_siswa=<?php echo urlencode($row['siswa_ID']); ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to permanently delete this record?');" title="Delete Profile">
                                                                        <i class="bi bi-trash"></i>
                                                                    </a>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <?php
                                                    }
                                                } else {
                                                    echo "<tr><td colspan='7' class='text-center py-4 text-muted'>No matching student data available.</td></tr>";
                                                }
                                                $stmt_select->close();
                                                ?>
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

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script type="text/javascript">
        function updateClass() {
            const tingkat = document.getElementById("tingkatSiswa").value;
            const kelasSelect = document.getElementById("kelasSiswa");

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
                    kelasSelect.appendChild(option);
                });
            } else {
                kelasSelect.disabled = true;
                kelasSelect.innerHTML = '<option value="">-- Select Grade Level First --</option>';
            }
        }

        function resetClass() {
            const kelasSelect = document.getElementById("kelasSiswa");
            kelasSelect.disabled = true;
            kelasSelect.innerHTML = '<option value="">-- Select Grade Level First --</option>';
        }
    </script>
    <?php include("../template/jsscript.php"); ?>
</body>

</html>