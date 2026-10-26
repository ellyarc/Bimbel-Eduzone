<?php
ob_start();
session_start();

// Database Connection.
// Access the admin/includes/ folder to fetch config.php
include("admin/includes/config.php");
include("includes/auth.php");

// 1. PROTECTION: IF ALREADY LOGGED IN, REDIRECT DIRECTLY TO RESPECTIVE DASHBOARD
if (isset($_SESSION["useremail"])) {
    if ($_SESSION["role"] == "admin") {
        header("Location: admin/dashboard.php");
        exit();
    } else if ($_SESSION["role"] == "tutor") {
        header("Location: tutor/dashboard.php");
        exit();
    }
}

// 2. PROCESS WHEN THE LOGIN BUTTON IS CLICKED
if (isset($_POST["login"])) {
    $useremail = $_POST["user"];
    $userpass = $_POST["pass"];

    if ($row_admin = cari_akun($conn, "admin", $useremail, $userpass)) {
        session_regenerate_id(true);
        $_SESSION["admin_id"] = $row_admin["admin_ID"];
        $_SESSION["user_id"] = $row_admin["admin_ID"];
        $_SESSION["useremail"] = $row_admin["admin_USER"];
        $_SESSION["role"] = "admin";

        header("Location: admin/dashboard.php");
        exit();
    } elseif ($row_tutor = cari_akun($conn, "tutor", $useremail, $userpass)) {
        session_regenerate_id(true);
        $_SESSION["tutor_id"] = $row_tutor["tutor_ID"];
        $_SESSION["user_id"] = $row_tutor["tutor_ID"];
        $_SESSION["useremail"] = $row_tutor["tutor_USER"];
        $_SESSION["role"] = "tutor";

        header("Location: tutor/dashboard.php");
        exit();
    } else {
        // Login gagal: bersihkan session
        $_SESSION = array();
        session_unset();

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();
        echo "<script>alert('Incorrect email or password! Your session has been cleared.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eduzone Bimbel - Login Portal</title>
    <link rel="stylesheet" type="text/css" href="admin/css/bootstrap.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7fa;
            height: 100vh;
            display: flex;
            align-items: center;
        }

        .login-container {
            background: #ffffff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(30, 55, 84, 0.1);
            transition: all 0.3s ease;
        }

        .left-box {
            background: linear-gradient(135deg, #1E3754 0%, #2a4e78 100%);
            padding: 50px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .right-box {
            padding: 50px;
        }

        .btn-login {
            background-color: #1E3754;
            color: white;
            font-weight: 500;
            padding: 12px;
            border-radius: 10px;
            transition: all 0.3s;
        }

        .btn-login:hover {
            background-color: #16293f;
            color: white;
            transform: translateY(-2px);
        }

        .form-control {
            padding: 12px;
            border-radius: 10px;
            border: 1px solid #ced4da;
            background-color: #f8f9fa;
        }

        .form-control:focus {
            background-color: #fff;
            border-color: #1E3754;
            box-shadow: 0 0 0 0.25rem rgba(30, 55, 84, 0.25);
        }

        .forgot-link {
            color: #475569;
            text-decoration: none;
            font-size: 0.8rem;
            transition: color 0.2s;
        }

        .forgot-link:hover {
            color: #1E3754;
            text-decoration: underline;
        }

        .btn-wa {
            background-color: #25D366;
            color: white;
            border-radius: 10px;
            padding: 10px 15px;
            text-decoration: none;
            display: inline-block;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-wa:hover {
            background-color: #1da851;
            color: white;
            transform: translateY(-2px);
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-9 login-container">
                <div class="row">

                    <div class="col-md-5 left-box text-white text-center text-md-start">
                        <div class="mb-5">
                            <span class="badge bg-light text-dark px-3 py-2 rounded-pill mb-2"
                                style="font-weight: 600; font-size: 0.8rem;">EDUZONE SYSTEMS</span>
                            <h1 style="font-weight: 700; letter-spacing: -1px;">EDUZONE</h1>
                            <p class="lead" style="font-size: 1.1rem; color: #cbd5e1;">Tutoring Center & Academic Portal
                            </p>
                        </div>
                        <div class="mt-auto d-none d-md-block">
                            <h6 class="mb-1" style="font-weight: 600;">Welcome Back!</h6>
                            <p class="small" style="color: #94a3b8;">Please log in using your Admin or Tutor account to
                                manage schedules, students, and learning tracks.</p>
                        </div>
                    </div>

                    <div class="col-md-7 right-box">
                        <div class="mb-4">
                            <h3 style="font-weight: 700; color: #1E3754;">Account Login</h3>
                            <p class="text-muted small">Enter your registered email and password</p>
                        </div>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label small" style="font-weight: 500; color: #475569;">Email /
                                    Username</label>
                                <input type="email" class="form-control" placeholder="name@eduzone.com" name="user"
                                    required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label small"
                                    style="font-weight: 500; color: #475569;">Password</label>
                                <input type="password" class="form-control" placeholder="••••" name="pass" required>
                            </div>

                            <div class="mb-4 text-center">
                                <small class="text-muted d-block mb-2">
                                    Having trouble logging in?
                                </small>
                                <a href="https://wa.me/6281234567890?text=Hello%20Eduzone%20Admin,%20I%20am%20experiencing%20issues%20logging%20in."
                                    target="_blank" class="btn-wa">
                                    📱 Contact Admin
                                </a>
                            </div>

                            <div class="d-grid gap-2">
                                <input type="submit" class="btn btn-login shadow-sm" value="Log In Now" name="login">
                            </div>
                        </form>

                        <div class="mt-5 text-center">
                            <p class="small text-muted">&copy; 2026 Eduzone Bimbel. All rights reserved.</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</body>

<?php
mysqli_close($conn);
ob_end_flush();
?>

</html>