<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href = "multikolom.css" rel = "stylesheet" type = "text/css">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .navbar-custom{
            background-color:#1f3a5f;
            padding:15px 25px;
        }

        .judul-header{
            color:white;
            font-size:30px;
            font-weight:bold;
            margin-left:10px;
        }
    </style>
</head>
<body>

<div class="navbar-custom d-flex align-items-center">

    <button class="btn btn-outline-light"
            data-bs-toggle="offcanvas"
            data-bs-target="#menuSidebar">
        ☰
    </button>

    <span class="judul-header">Latihan Soal</span>

</div>

<div class="offcanvas offcanvas-start"
     tabindex="-1"
     id="menuSidebar">

    <div class="offcanvas-header">
        <h5>Menu</h5>

        <button type="button"
                class="btn-close"
                data-bs-dismiss="offcanvas">
        </button>
    </div>

    <div class="offcanvas-body">

        <a href="dashboard.php" class="d-block mb-3">
            Dashboard
        </a>

        <a href="latsol.php" class="d-block mb-3">
            Latihan Soal
        </a>

        <a href="nilai.php" class="d-block mb-3">
            Nilai
        </a>

        <a href="logout.php" class="d-block mb-3">
            Logout
        </a>

    </div>

</div>