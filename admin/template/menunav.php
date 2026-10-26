<nav class="sb-topnav navbar navbar-expand navbar-dark" style="background-color: #1E3754;">
    <div class="d-flex align-items-center">
        <button class="btn btn-link btn-sm me-2 p-0 ms-3" id="sidebarToggle" href="#!">
            <i class="fas fa-bars fa-lg" style="color: white;"></i>
        </button>
        <a class="navbar-brand ps-2" href="dashboard.php" style="color: white; font-weight: 600;">Dashboard</a>
    </div>

    <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
        <div class="input-group">
            <input class="form-control" type="text" placeholder="Search for..." aria-label="Search for..."
                aria-describedby="btnNavbarSearch" />
            <button class="btn btn-primary" id="btnNavbarSearch" type="button">
                <i class="fas fa-search"></i>
            </button>
        </div>
    </form>

    <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown"
                aria-expanded="false">
                <img src="images/user.png" style="width:32px; height:32px; object-fit:cover; border-radius:50%;">
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                <li><a class="dropdown-item" href="akun.php">Profile</a></li>

                <li>
                    <hr class="dropdown-divider" />
                </li>

                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
            </ul>
        </li>
    </ul>
</nav>