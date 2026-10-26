<?php
ob_start();
session_start();

// Session Protection: Ensure only logged-in users can execute this action
if (!isset($_SESSION["useremail"])) {
    header("location:../frontend/login.php");
    exit();
}

// Include database configuration connection
include "../includes/config.php";

// 1. Check if the 'id_tutor' parameter is sent via the URL query string
if (isset($_GET["id_tutor"])) {
    
    $id_tutor = $_GET["id_tutor"];

    // Begin Database Transaction to safely clean data from both tables together
    $conn->begin_transaction();

    try {
        // 2. Delete account access credentials from the 'tutor' table
        $stmt_akun = $conn->prepare("DELETE FROM tutor WHERE tutor_ID = ?");
        $stmt_akun->bind_param("s", $id_tutor);
        $stmt_akun->execute();
        $stmt_akun->close();

        // 3. Delete personal bio records from the 'bio_tutor' table
        $stmt_bio = $conn->prepare("DELETE FROM bio_tutor WHERE tutor_ID = ?");
        $stmt_bio->bind_param("s", $id_tutor);
        $stmt_bio->execute();
        $stmt_bio->close();

        // Commit transaction if both rows are cleared out successfully
        $conn->commit();

        echo "<script>
                alert('Success! Tutor profile and portal account have been completely deleted.');
                window.location.href = 'inputtutor.php';
              </script>";
        exit();

    } catch (Exception $e) {
        // Rollback structural updates to the database if any error disrupts execution
        $conn->rollback();
        
        $error_message = addslashes($e->getMessage());
        echo "<script>
                alert('Action Failed! System was unable to complete tutor deletion sequence. Error: " . $error_message . "');
                window.location.href = 'inputtutor.php';
              </script>";
        exit();
    }
} else {
    // Triggers if the payload is missing the required ID criteria
    echo "<script>
            alert('Access Denied! The required parameter \'id_tutor\' was not recognized by the system.');
            window.location.href = 'inputtutor.php';
          </script>";
    exit();
}
?>