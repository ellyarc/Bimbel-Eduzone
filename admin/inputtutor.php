<?php
ob_start();
session_start();

// Session Protection: Ensure only logged-in users can execute this file
if (!isset($_SESSION["useremail"])) {
    header("location:../frontend/login.php");
    exit();
}
?>
<?php include("../template/head.php"); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Tutor Management System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* Global Font Alignment */
        body, h1, h2, h3, h4, h5, h6, p, span, table, th, td, input, label, button, select {
            font-family: 'Poppins', sans-serif !important;
        }

        /* Modern Card & Form Layout Styling */
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

        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #d1d3e2;
            padding: 10px 15px;
        }

        .form-control:focus, .form-select:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }

        /* Premium Admin Table Style */
        .table-modern {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
        }

        .table-modern thead th {
            background-color: #f8f9fc;
            color: #4e5d78;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            padding: 14px 16px;
            border-bottom: 2px solid #e3e6f0;
        }

        .table-modern tbody td {
            padding: 14px 16px;
            color: #566a7f;
            border-bottom: 1px solid #edf2f9;
            vertical-align: middle;
        }

        .table-modern tbody tr:hover {
            background-color: #f8f9fa;
        }

        /* Button Rounded Pill Styling */
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
                            <h2 style="font-weight: 700; color: #1E3754; margin-bottom: 2px;">Tutor Profile Management</h2>
                            <p class="text-muted small mb-0">Register personal profiles and setup account access credentials for tutors</p>
                        </div>
                    </div>

                    <?php
                    include("../includes/config.php");

                    // PROCESS SAVE DATA (USING PREPARED STATEMENTS)
                    if (isset($_POST["Simpan"])) {
                        if (empty($_POST["idTutor"])) {
                            echo "<h1>Sorry, invalid input</h1>";
                            die("Tutor ID is required");
                        }

                        $tutor_ID = $_POST["idTutor"];
                        $tutor_NAME = $_POST["namaTutor"];
                        $tutor_BOD = $_POST["bodTutor"];
                        $tutor_KET = $_POST["ketTutor"];
                        $tutor_USER = $_POST["usernameTutor"];
                        $tutor_PASS = $_POST["passwordTutor"]; // Stored as plain text per your specifications

                        // VALIDATION: CHECK IF TUTOR ID ALREADY EXISTS
                        $stmt_cek = $conn->prepare("SELECT tutor_ID FROM bio_tutor WHERE tutor_ID = ?");
                        $stmt_cek->bind_param("s", $tutor_ID);
                        $stmt_cek->execute();
                        $res_cek = $stmt_cek->get_result();

                        if ($res_cek->num_rows > 0) {
                            echo "<script>
                                    alert('Registration Failed! Tutor ID \"" . htmlspecialchars($tutor_ID, ENT_QUOTES) . "\" is already registered. Please assign a different ID.');
                                    window.location.href='inputtutor.php';
                                  </script>";
                            exit();
                        }
                        $stmt_cek->close();

                        // Begin Database Transaction to safely execute modifications
                        $conn->begin_transaction();

                        try {
                            // 1. Insert into biodata table (bio_tutor)
                            $stmt_bio = $conn->prepare("INSERT INTO bio_tutor (tutor_ID, tutor_NAME, tutor_BOD, tutor_KET) VALUES (?, ?, ?, ?)");
                            $stmt_bio->bind_param("ssss", $tutor_ID, $tutor_NAME, $tutor_BOD, $tutor_KET);
                            $stmt_bio->execute();
                            $stmt_bio->close();

                            // 2. Insert into account table (tutor)
                            $stmt_akun = $conn->prepare("INSERT INTO tutor (tutor_ID, tutor_USER, tutor_PASS) VALUES (?, ?, ?)");
                            $stmt_akun->bind_param("sss", $tutor_ID, $tutor_USER, $tutor_PASS);
                            $stmt_akun->execute();
                            $stmt_akun->close();

                            // Commit database operations simultaneously
                            $conn->commit();
                            
                            header("location:inputtutor.php");
                            exit;

                        } catch (Exception $e) {
                            $conn->rollback();
                            echo "<div class='alert alert-danger'>System Error! Failed to update data rows: " . htmlspecialchars($e->getMessage()) . "</div>";
                        }
                    }

                    // PROCESS SEARCH & RETRIEVE DATA
                    $search_value = "";
                    if (isset($_POST["kirim"]) && !empty($_POST["search"])) {
                        $search_value = $_POST["search"];
                        $search_param = "%" . $search_value . "%";
                        
                        $stmt_select = $conn->prepare("SELECT b.tutor_ID, b.tutor_NAME, b.tutor_BOD, b.tutor_KET, a.tutor_USER, a.tutor_PASS 
                                                      FROM bio_tutor b 
                                                      INNER JOIN tutor a ON b.tutor_ID = a.tutor_ID 
                                                      WHERE b.tutor_ID LIKE ? OR b.tutor_NAME LIKE ? OR a.tutor_USER LIKE ?");
                        $stmt_select->bind_param("sss", $search_param, $search_param, $search_param);
                    } else {
                        $stmt_select = $conn->prepare("SELECT b.tutor_ID, b.tutor_NAME, b.tutor_BOD, b.tutor_KET, a.tutor_USER, a.tutor_PASS 
                                                      FROM bio_tutor b 
                                                      INNER JOIN tutor a ON b.tutor_ID = a.tutor_ID");
                    }

                    $stmt_select->execute();
                    $query_result = $stmt_select->get_result();
                    ?>

                    <div class="card custom-card">
                        <div class="card-header-custom">
                            <h5 class="m-0" style="font-weight: 700; color: #4e73df;">
                                <i class="fas fa-user-plus me-2"></i>Add New Tutor Profile
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <form method="POST">

                                <p class="text-primary small mb-3" style="font-weight: 600; letter-spacing: 0.5px;">A. PERSONAL PROFILE DETAILED DATA</p>

                                <div class="row mb-3">
                                    <label for="idTutor" class="col-sm-2 col-form-label font-weight-500">Tutor ID</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="idTutor" name="idTutor" placeholder="Enter unique Tutor ID number" required>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="namaTutor" class="col-sm-2 col-form-label">Full Name</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="namaTutor" name="namaTutor" placeholder="Enter Full Name including academic titles" required>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label for="bodTutor" class="col-sm-2 col-form-label">Date of Birth</label>
                                    <div class="col-sm-10">
                                        <input type="date" class="form-control" id="bodTutor" name="bodTutor" required>
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <label for="ketTutor" class="col-sm-2 col-form-label">Expertise / Specialization</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="ketTutor" name="ketTutor" placeholder="e.g., Mathematics & Physics Expert Specialization">
                                    </div>
                                </div>

                                <hr class="my-4" style="border-color: #edf2f9;">
                                <p class="text-success small mb-3" style="font-weight: 600; letter-spacing: 0.5px;">B. PORTAL LOGIN SYSTEM ACCESS ACCOUNT</p>

                                <div class="row mb-3">
                                    <label for="usernameTutor" class="col-sm-2 col-form-label">Username / Email</label>
                                    <div class="col-sm-10">
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                                            <input type="text" class="form-control" id="usernameTutor" name="usernameTutor" placeholder="e.g., alex@eduzone.com or alex123" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <label for="passwordTutor" class="col-sm-2 col-form-label">Password</label>
                                    <div class="col-sm-10">
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                                            <input type="password" class="form-control" id="passwordTutor" name="passwordTutor" placeholder="Create a secure access password" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-sm-2"></div>
                                    <div class="col-sm-10">
                                        <button type="submit" class="btn btn-primary btn-pill shadow-sm me-2" name="Simpan">
                                            <i class="fas fa-save me-1"></i> Save Data
                                        </button>
                                        <button type="reset" class="btn btn-light btn-pill border" name="Batal">
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card custom-card">
                        <div class="card-header-custom d-flex flex-wrap align-items-center justify-content-between gap-3">
                            <h5 class="m-0" style="font-weight: 700; color: #1E3754;">
                                <i class="fas fa-users me-2 text-secondary"></i>Tutor Profile Directory & Master Registry
                            </h5>

                            <form method="POST" class="row g-2 align-items-center m-0">
                                <div class="col-auto">
                                    <input type="text" name="search" class="form-control form-control-sm rounded-pill px-3" id="search"
                                        value="<?php echo htmlspecialchars($search_value, ENT_QUOTES); ?>"
                                        placeholder="Search by ID, name, or user..." style="min-width: 240px; height: 38px;">
                                </div>
                                <div class="col-auto">
                                    <button type="submit" name="kirim" class="btn btn-sm btn-secondary rounded-pill px-3" style="height: 38px;">
                                        <i class="fas fa-search me-1"></i> Search
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table-modern table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th class="ps-4">Tutor ID</th>
                                            <th>Full Name</th>
                                            <th>Date of Birth</th>
                                            <th>Specialization Details</th>
                                            <th>Portal Username</th>
                                            <th>Password</th>
                                            <th class="pe-4 text-center" style="width: 120px;">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="small">
                                        <?php if ($query_result->num_rows > 0) {
                                            while ($row = $query_result->fetch_assoc()) { ?>
                                                <tr>
                                                    <td class="ps-4" style="color: #4e73df; font-weight: 600;">
                                                        <?php echo htmlspecialchars($row["tutor_ID"]); ?>
                                                    </td>
                                                    <td style="font-weight: 500; color: #2e384d;">
                                                        <?php echo htmlspecialchars($row["tutor_NAME"]); ?>
                                                    </td>
                                                    <td>
                                                        <i class="bi bi-calendar-event me-1 text-muted"></i>
                                                        <?php echo htmlspecialchars($row["tutor_BOD"]); ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-light text-dark border px-2 py-1">
                                                            <?php echo htmlspecialchars($row["tutor_KET"] ?: '-'); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <code style="font-size: 0.85rem; color: #e83e8c;">
                                                            <?php echo htmlspecialchars($row["tutor_USER"]); ?>
                                                        </code>
                                                    </td>
                                                    <td>
                                                        <span class="font-monospace text-muted"><?php echo htmlspecialchars($row["tutor_PASS"]); ?></span>
                                                    </td>
                                                    <td class="pe-4 text-center">
                                                        <div class="d-flex justify-content-center gap-1">
                                                            <a href="edittutor.php?ubahtutor=<?php echo urlencode($row["tutor_ID"]) ?>"
                                                               class="btn btn-sm btn-outline-primary btn-pill px-2.5 py-1" title="Edit Data Fields">
                                                                <i class="bi bi-pencil-square"></i>
                                                            </a>
                                                            <a href="hapustutor.php?id_tutor=<?php echo urlencode($row['tutor_ID']); ?>"
                                                               class="btn btn-outline-danger btn-sm border-0" title="Delete Profile"
                                                               onclick="return confirm('Are you sure you want to permanently delete the profile for Tutor ID <?php echo htmlspecialchars($row['tutor_ID'], ENT_QUOTES); ?>?');"
                                                               style="border-radius: 6px;">
                                                                <i class="bi bi-trash fs-6"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php }
                                        } else { ?>
                                            <tr>
                                                <td colspan="7" class="text-center text-muted py-5">
                                                    <i class="fas fa-folder-open fa-2x mb-2 text-secondary d-block"></i>
                                                    No matching tutor data files found in registry.
                                                </td>
                                            </tr>
                                        <?php } 
                                        $stmt_select->close();
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

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <?php include("../template/jsscript.php"); ?>
</body>

</html>
<?php
ob_end_flush();
?>