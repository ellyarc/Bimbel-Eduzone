<?php
ob_start();
session_start();
if (!isset($_SESSION["useremail"])) {
    header("location:../frontend/login.php");
    exit();
}

include("../includes/config.php");

$error_message = "";

$useremail = $_SESSION["useremail"];

// Ambil tutor_ID berdasarkan akun yang login
$queryTutor = mysqli_query($conn, "SELECT tutor_ID FROM tutor WHERE tutor_USER = '$useremail'");
$dataTutor = mysqli_fetch_assoc($queryTutor);

$tutor_id = $dataTutor['tutor_ID'];

// Ambil data materi milik tutor yang sedang login berdasarkan tutor_ID
$sql = "SELECT * FROM materi WHERE tutor_ID = '$tutor_id' ORDER BY materi_ID DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Materi</title>
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7fa; }
        .card-materi { background: white; border: none; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.04); }
    </style>
</head>

<body class="sb-nav-fixed">

    <?php include("../template/menunav.php"); ?>

    <div id="layoutSidenav">

        <div id="layoutSidenav_nav">
            <?php include("../template/menu.php"); ?>
        </div>

        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4" style="padding-top: 30px; padding-bottom: 50px;">
                    
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="mb-0" style="font-weight: 700; color: #1E3754;">
                                <i class="fas fa-list me-2 text-primary"></i>My Learning Materials
                            </h4>
                            <p class="text-muted small mb-0">Manage your uploaded videos and worksheets</p>
                        </div>
                        <a href="inputmateri.php" class="btn btn-primary rounded-pill px-4">
                            <i class="fas fa-plus me-2"></i>Add Material
                        </a>
                    </div>

                    <div class="card-materi p-4">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Title</th>
                                        <th>Level & Grade</th>
                                        <th>Subject</th>
                                        <th>File Type</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $no = 1;
                                    // FIXED: Menggunakan kurung kurawal standar {} agar menghindari token error
                                    if (mysqli_num_rows($result) > 0) {
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            $tipe = !empty($row['video_PATH']) ? 'Video' : 'PDF Worksheet';
                                            $badge_color = !empty($row['video_PATH']) ? 'bg-primary' : 'bg-danger';
                                    ?>
                                            <tr>
                                                <td><?php echo $no++; ?></td>
                                                <td><strong><?php echo htmlspecialchars($row['materi_TITLE']); ?></strong></td>
                                                <td>
                                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($row['materi_TINGKAT']); ?></span>
                                                    Grade <?php echo htmlspecialchars($row['materi_KELAS']); ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($row['materi_MAPEL']); ?></td>
                                                <td><span class="badge <?php echo $badge_color; ?>"><?php echo $tipe; ?></span></td>
                                                <td>
                                                    <a href="editmateri.php?materi_ID=<?php echo $row['materi_ID']; ?>" class="btn btn-sm btn-outline-warning rounded-pill px-3 me-1">Edit</a>
                                                    <a href="hapusmateri.php?id=<?php echo $row['materi_ID']; ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="return confirm('Are you sure?')">Delete</a>
                                                </td>
                                            </tr>
                                    <?php 
                                        } // Penutup loop while
                                    } else { 
                                    ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">No materials found. Upload your first material!</td>
                                        </tr>
                                    <?php 
                                    } // Penutup if
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
</body>

</html>