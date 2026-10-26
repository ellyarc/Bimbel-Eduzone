<aside class="sidebar">
  <div class="sidebar-logo">
    <div class="logo-icon"><i class="ti ti-book-2"></i></div>
    <span class="logo-text">EduZone</span>
  </div>

  <!-- Profile avatar — clicks through to akun.php -->
  <a href="akun.php" class="sidebar-user" style="text-decoration:none; display:flex; align-items:center; gap:10px; padding: 12px 16px; border-radius:12px; margin: 0 8px 4px; transition: background 0.15s;" 
     onmouseover="this.style.background='#f0f4f8'" onmouseout="this.style.background='transparent'">
    <div class="avatar" id="sidebarAvatar">
      <?= strtoupper(substr($siswa_NAME, 0, 2)) ?>
    </div>
    <div class="user-info">
      <p id="sidebarName" style="margin:0; font-size:13px; font-weight:600; color:#1a2a3a;">
        <?= htmlspecialchars($siswa_NAME) ?>
      </p>
      <span id="sidebarKelas" style="font-size:11px; color:#8a9ab0;">
        Class <?= htmlspecialchars($siswa_TINGKAT . ' ' . $siswa_KELAS) ?>
      </span>
    </div>
    <i class="ti ti-chevron-right" style="font-size:13px; color:#c0ccd8; margin-left:auto;"></i>
  </a>

  <div class="nav-section">
    <span class="nav-label">Menu</span>
    <a href="home.php" class="nav-item <?= ($activePage === 'dashboard') ? 'active' : '' ?>" data-nav="dashboard">
      <i class="ti ti-layout-dashboard"></i> Dashboard
    </a>
    <a href="materi.php" class="nav-item <?= ($activePage === 'materi') ? 'active' : '' ?>" data-nav="materi">
      <i class="ti ti-pencil"></i> Materials
      <span class="nav-badge"></span>
    </a>
    <a href="jadwal.php" class="nav-item <?= ($activePage === 'jadwal') ? 'active' : '' ?>" data-nav="jadwal">
      <i class="ti ti-calendar-event"></i> Schedule
    </a>
    <a href="cekabsensi.php" class="nav-item <?= ($activePage === 'absensi') ? 'active' : '' ?>" data-nav="absensi">
      <i class="ti ti-user-check"></i> Attendance
    </a>
    <a href="cekpembayaran.php" class="nav-item <?= ($activePage === 'pembayaran') ? 'active' : '' ?>" data-nav="pembayaran">
      <i class="ti ti-credit-card"></i> Payments
    </a>
  </div>

  <div class="nav-section">
    <span class="nav-label">Account</span>
    <a href="akun.php" class="nav-item <?= ($activePage === 'akun') ? 'active' : '' ?>" data-nav="akun">
      <i class="ti ti-user-circle"></i> My Profile
    </a>
  </div>

  <div class="sidebar-bottom">
    <a href="../../logout.php" class="btn-logout">
      <i class="ti ti-logout"></i> Log Out
    </a>
  </div>
</aside>