<?php
ob_start();
session_start();

// Session Protection: Ensure only logged-in users can execute the deletion process
if (!isset($_SESSION["useremail"])) {
    header("location:../frontend/login.php");
    exit();
}

// Include database configuration connection
include "../includes/config.php";

// Check if 'tanggal' (date) and 'sesi' (session) parameters exist in the URL string
if (isset($_GET["tanggal"]) && isset($_GET["sesi"])) {
    
    // Sanitize parameter values to prevent SQL Injection vulnerabilities
    $tanggal = mysqli_real_escape_string($conn, $_GET["tanggal"]);
    $sesi = mysqli_real_escape_string($conn, $_GET["sesi"]);

    // Execute DELETE query targeting specific DATE and SESSION criteria
    $query_delete = mysqli_query($conn, "DELETE FROM jadwal WHERE tanggal = '$tanggal' AND sesi = '$sesi'");

    if ($query_delete) {
        // Display a structural success alert matching the rest of the CRUD components
        echo "<script>
                alert('Success! The schedule data has been successfully deleted.');
                window.location.href = 'inputjadwal.php';
              </script>";
        exit();
    } else {
        // Fallback contingency handling for potential database engine errors
        echo "<script>
                alert('Error! Failed to delete data: " . mysqli_error($conn) . "');
                window.location.href = 'inputjadwal.php';
              </script>";
        exit();
    }
} else {
    // Prevent random direct file execution attempts without proper URL query parameters
    echo "<script>
            alert('Access Denied! Invalid or missing parameters.');
            window.location.href = 'inputjadwal.php';
          </script>";
    exit();
}
?>