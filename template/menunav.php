<nav class="sb-topnav navbar navbar-expand navbar-dark" style="background-color: #1E3754;">
    <div class="d-flex align-items-center">
        <button class="btn btn-link btn-sm me-2 p-0 ms-3" id="sidebarToggle" href="#!">
            <i class="fas fa-bars fa-lg" style="color: white;"></i>
        </button>
        <a class="navbar-brand ps-2" href="dashboard.php" style="color: white; font-weight: 600;">Dashboard</a>
    </div>


    <ul class="navbar-nav ms-auto me-3 me-lg-4">
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown"
                aria-expanded="false">
                <img src="../assets/images/user-white.png" style="width:32px; height:32px; object-fit:cover; border-radius:50%;">
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                <li><a class="dropdown-item" href="akun.php">Profile</a></li>

                <li>
                    <hr class="dropdown-divider" />
                </li>

                <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
            </ul>
        </li>
    </ul>
</nav>