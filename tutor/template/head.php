<?php
// Mendeteksi nama file yang sedang aktif secara otomatis (misal: "daftarkehadiran.php")
$current_page = basename($_SERVER['PHP_SELF']);

// Menentukan judul bahasa Inggris berdasarkan nama file baru
switch ($current_page) {
    case 'akun.php':
        $page_title = "My Account";
        break;
    case 'dashboard.php':
        $page_title = "Dashboard";
        break;
    
    // Kelompok KEHADIRAN (Attendance)
    case 'daftarkehadiran.php':
        $page_title = "Attendance List";
        break;
    case 'inputkehadiran.php':
        $page_title = "Record Attendance";
        break;
    case 'editkehadiran.php':
        $page_title = "Edit Attendance";
        break;

    // Kelompok MATERI (Learning Materials)
    case 'daftarmateri.php':
        $page_title = "Material List";
        break;
    case 'inputmateri.php':
        $page_title = "Upload Material";
        break;

    // Kelompok Sistem
    case 'logout.php':
        $page_title = "Sign Out";
        break;

    // Default cadangan jika ada file lain yang terbuka
    default:
        $page_title = "Tutor Portal";
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