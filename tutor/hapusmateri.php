<?php
ob_start();
session_start();

// 1. Pengecekan Login & Role
if (!isset($_SESSION["useremail"]) || $_SESSION["role"] !== "tutor") {
    header("location:../frontend/login.php");
    exit();
}

include("../includes/config.php");
$useremail = $_SESSION["useremail"];

// 2. Ambil tutor_ID berdasarkan email session
$queryTutor = mysqli_query($conn, "SELECT tutor_ID FROM tutor WHERE tutor_USER = '$useremail'");
$dataTutor = mysqli_fetch_assoc($queryTutor);
$tutor_id = $dataTutor['tutor_ID'];

// 3. Tangkap ID materi yang mau dihapus dari URL
$materi_id = 0;
if (isset($_GET['materi_ID'])) {
    $materi_id = intval($_GET['materi_ID']);
} elseif (isset($_GET['id'])) {
    $materi_id = intval($_GET['id']);
}

// Jika ID tidak valid, balikkan ke halaman daftar
if ($materi_id == 0) {
    header("Location: daftarmateri.php");
    exit();
}

// 4. Cari data materi terlebih dahulu untuk mengambil path file video/PDF yang mau dihapus
$q_materi = mysqli_query($conn, "SELECT video_PATH, pdf_PATH FROM materi WHERE materi_ID = $materi_id AND tutor_ID = '$tutor_id' LIMIT 1");

if (mysqli_num_rows($q_materi) > 0) {
    $materi = mysqli_fetch_assoc($q_materi);

    // Hapus file video dari folder jika filenya ada
    if (!empty($materi['video_PATH']) && file_exists("../" . $materi['video_PATH'])) {
        unlink("../" . $materi['video_PATH']);
    }

    // Hapus file PDF dari folder jika filenya ada
    if (!empty($materi['pdf_PATH']) && file_exists("../" . $materi['pdf_PATH'])) {
        unlink("../" . $materi['pdf_PATH']);
    }

    // 5. Hapus data materi dari database
    $sql_delete = "DELETE FROM materi WHERE materi_ID = $materi_id AND tutor_ID = '$tutor_id'";
    mysqli_query($conn, $sql_delete);
}

// 6. Alihkan kembali ke halaman daftarmateri.php setelah selesai
header("Location: daftarmateri.php");
exit();
?>