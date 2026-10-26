<?php
session_start();

// 1. Clear all session variables
$_SESSION = array();

// 2. Delete the session cookie if it exists
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Destroy the session on the server (The session is completely terminated at this moment)
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eduzone Bimbel - Logging out...</title>
    
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <meta http-equiv="refresh" content="2;url=../login.php">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }
        .logout-box {
            background: #ffffff;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(30, 55, 84, 0.1);
            max-width: 450px;
            width: 100%;
            text-align: center;
        }
        .spinner-border {
            color: #1E3754;
            width: 3rem;
            height: 3rem;
        }
        .btn-back {
            background-color: #1E3754;
            color: white;
            border-radius: 10px;
            padding: 10px 20px;
            text-decoration: none;
            font-weight: 500;
            display: inline-block;
            transition: all 0.3s;
        }
        .btn-back:hover {
            background-color: #16293f;
            color: white;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

    <div class="logout-box">
        <div class="mb-4">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
        
        <h4 style="font-weight: 700; color: #1E3754;" class="mb-2">Logged Out Successfully</h4>
        <p class="text-muted small mb-4">Your session has ended securely. Redirecting to the main login page...</p>
        
        <hr class="my-4" style="border-color: #e2e8f0;">
        
        <a href="../login.php" class="btn-back shadow-sm">Back to Login</a>
    </div>

    <script>
        setTimeout(function() {
            window.location.href = '../login.php';
        }, 1500);
    </script>
</body>
</html>