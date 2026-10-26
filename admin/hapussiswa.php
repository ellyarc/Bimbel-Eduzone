<?php
ob_start();
session_start();
if (!isset($_SESSION["useremail"])) {
    header("location:../frontend/login.php");
    exit();
}

include "../includes/config.php";

if (isset($_GET["id_siswa"])) {
    $id_siswa = $_GET["id_siswa"];

    // Begin Database Transaction to ensure data integrity across both tables
    $conn->begin_transaction();

    try {
        // 1. Delete account credentials from the 'siswa' table
        $stmt_akun = $conn->prepare("DELETE FROM siswa WHERE siswa_ID = ?");
        $stmt_akun->bind_param("s", $id_siswa);
        $stmt_akun->execute();
        $stmt_akun->close();

        // 2. Delete personal profiles from the 'bio_siswa' table
        $stmt_bio = $conn->prepare("DELETE FROM bio_siswa WHERE siswa_ID = ?");
        $stmt_bio->bind_param("s", $id_siswa);
        $stmt_bio->execute();
        $stmt_bio->close();

        // Commit transaction if both operations execute successfully
        $conn->commit();

        echo "<script>
            alert('Success! Student profile and account have been permanently deleted.'); 
            window.location.href = 'inputsiswa.php';
        </script>";
        exit();

    } catch (Exception $e) {
        // Rollback all database modifications if any step fails
        $conn->rollback();
        
        $error_message = addslashes($e->getMessage());
        echo "<script>
            alert('Action Failed! System was unable to complete deletion. Error: " . $error_message . "'); 
            window.location.href = 'inputsiswa.php';
        </script>";
        exit();
    }
} else {
    echo "<script>
        alert('Access Denied! Required parameter \'id_siswa\' was not found.'); 
        window.location.href = 'inputsiswa.php';
    </script>";
    exit();
}
?>