<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion" id="sidenavAccordion" style="background-color: #1E3754;">
        <div class="sb-sidenav-menu">
            <div class="nav">
                <div class="sb-sidenav-menu-heading" style="color: #a0b4c8;">Core</div>
                <a class="nav-link" href="dashboard.php" style="color: #cdd9e5;">
                    <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                    Dashboard
                </a>

                <div class="sb-sidenav-menu-heading" style="color: #a0b4c8;">Interface</div>
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseLayouts"
                    aria-expanded="false" aria-controls="collapseLayouts" style="color: #cdd9e5;">
                    <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
                    Input
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseLayouts" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link" href="inputkehadiran.php" style="color: #a0b4c8;">Attendance List</a>
                        <a class="nav-link" href="daftarmateri.php" style="color: #a0b4c8;">Material List</a>
                    </nav>
                </div>
            </div>
        </div>

        <div class="sb-sidenav-footer" style="background-color: #162d45; color: #a0b4c8;">
            <div class="small">Logged in as:</div>
            <?php echo $_SESSION['useremail'] ?? 'Start Bootstrap'; ?>
        </div>
    </nav>
</div>