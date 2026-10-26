<?php
ob_start();
session_start();

// Proteksi Session: Pastikan hanya user login yang bisa mengeksekusi
if (!isset($_SESSION["useremail"])) {
    header("location:../frontend/login.php");
    exit();
}

// Mengikutsertakan koneksi database
include "../includes/config.php";

// Cek apakah parameter id_pembayaran ada di URL (Sesuai dengan href di tabel Anda)
if (isset($_GET["id_pembayaran"])) {
    
    // Mengamankan nilai parameter dari celah SQL Injection
    $id_pembayaran = mysqli_real_escape_string($conn, $_GET["id_pembayaran"]);

    // PERBAIKAN: Nama kolom disesuaikan menjadi 'pembayaran_ID' sesuai struktur tabel Anda
    $query_delete = mysqli_query($conn, "DELETE FROM pembayaran WHERE pembayaran_ID = '$id_pembayaran'");

    if ($query_delete) {
        echo "<script>
                alert('Success! Payment record has been successfully deleted.');
                window.location.href = 'inputpembayaran.php';
              </script>";
        exit();
    } else {
        echo "<script>
                alert('Error! Failed to delete payment data: " . mysqli_error($conn) . "');
                window.location.href = 'inputpembayaran.php';
              </script>";
        exit();
    }
} else {
    echo "<script>
            alert('Access Denied! Invalid or missing parameters.');
            window.location.href = 'inputpembayaran.php';
          </script>";
    exit();
}
?>