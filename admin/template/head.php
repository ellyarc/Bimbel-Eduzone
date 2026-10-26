<?php
// Deteksi URL aman & paksa ke huruf kecil untuk menghindari masalah Case-Sensitive
$current_page = strtolower(basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)));

// Menentukan judul bahasa Inggris berdasarkan nama file
switch ($current_page) {
    case 'akun.php':
        $page_title = "My Account";
        break;
    case 'dashboard.php':
        $page_title = "Dashboard";
        break;
    
    // Kelompok JADWAL
    case 'inputjadwal.php':
    case 'jadwal.php': // Tambahan jika ada file master jadwal
        $page_title = "Add Schedule";
        break;
    case 'editjadwal.php':
        $page_title = "Edit Schedule";
        break;
    case 'hapusjadwal.php':
        $page_title = "Delete Schedule";
        break;

    // Kelompok SISWA
    case 'inputsiswa.php':
        $page_title = "Add Student";
        break;
    case 'editsiswa.php':
        $page_title = "Edit Student";
        break;
    case 'hapussiswa.php':
        $page_title = "Delete Student";
        break;

    // Kelompok TUTOR
    case 'inputtutor.php':
        $page_title = "Add Tutor";
        break;
    case 'edittutor.php':
        $page_title = "Edit Tutor";
        break;
    case 'hapustutor.php':
        $page_title = "Delete Tutor";
        break;

    // Kelompok PEMBAYARAN
    case 'inputpembayaran.php':
    case 'pembayaran.php': // Tambahan jika ada file master pembayaran
        $page_title = "Add Payment";
        break;
    case 'editpembayaran.php':
        $page_title = "Edit Payment";
        break;
    case 'hapuspembayaran.php':
        $page_title = "Delete Payment";
        break;

    // Kelompok LAPORAN & Sistem
    case 'laporan.php':
        $page_title = "General Report";
        break;
    case 'laporankehadiran.php':
        $page_title = "Attendance Report";
        break;
    case 'menu.php':
        $page_title = "Menu Navigation";
        break;
    case 'logout.php':
        $page_title = "Sign Out";
        break;

    // Default jika nama file tidak terdeteksi di list atas
    default:
        $page_title = "Management System";
        break;
}
?>
<head>
    <style>
        .sb-sidenav {
            background-color: #1E3754 !important;
        }
        .sb-sidenav .nav-link,
        .sb-sidenav-menu-heading {
            color: #cdd9e5 !important;
        }
        .sb-sidenav-menu-nested .nav-link {
            color: #a0b4c8 !important;
        }
        .sb-sidenav-footer {
            background-color: #162d45 !important;
            color: #a0b4c8 !important;
        }
    </style>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    
    <title><?php echo $page_title; ?> - Eduzone</title>
    
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.13.1/font/bootstrap-icons.min.css">
</head>