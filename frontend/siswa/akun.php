<?php
ob_start();
session_start();

if (!isset($_SESSION["useremail"]) || $_SESSION["role"] !== "siswa") {
    header("location: ../login.php");
    exit();
}

include("../../includes/config.php");
include("../../includes/auth.php");

$siswa_ID = $_SESSION['siswa_ID'] ?? null;
if (!$siswa_ID) die("Session not found. Please log in again.");

$query_siswa = mysqli_query($conn,
    "SELECT s.siswa_ID, s.siswa_USER, s.siswa_PASS,
            b.siswa_NAME, b.siswa_TINGKAT, b.siswa_KELAS
     FROM siswa s
     LEFT JOIN bio_siswa b ON s.siswa_ID = b.siswa_ID
     WHERE s.siswa_ID = '$siswa_ID'
     LIMIT 1"
);
$data_siswa = mysqli_fetch_assoc($query_siswa);

$siswa_NAME    = $data_siswa['siswa_NAME']    ?? 'Student';
$siswa_TINGKAT = $data_siswa['siswa_TINGKAT'] ?? '-';
$siswa_KELAS   = $data_siswa['siswa_KELAS']   ?? '-';
$siswa_user    = $data_siswa['siswa_USER']    ?? '-';
$siswa_pass    = $data_siswa['siswa_PASS']    ?? '';

$success_message = "";
$error_message   = "";

if (isset($_POST["Update"])) {
    $new_password = $_POST["passwordSiswa"];

    if ($new_password === "" || password_sudah_hash($new_password) || ($new_password === $siswa_pass && password_sudah_hash($siswa_pass))) {
        $error_message = "Masukkan password baru.";
    } elseif (simpan_password($conn, "siswa", "siswa_ID", $siswa_ID, $new_password)) {
        $siswa_pass      = password_hash($new_password, PASSWORD_DEFAULT); // form tidak menampilkan hash
        $success_message = "Password updated successfully!";
    } else {
        $error_message = "Failed to update password.";
    }
}

$activePage = 'akun';
$pageTitle  = 'My Profile';
$pageSub    = 'Manage your account and security settings';
$today      = date('l, F j, Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>My Profile – EduZone</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" />
  <?php include("templates/head.php"); ?>
  <style>
    .profile-layout {
      display: grid;
      grid-template-columns: 220px 1fr;
      gap: 20px;
      align-items: start;
    }
    @media (max-width: 700px) { .profile-layout { grid-template-columns: 1fr; } }

    /* ── PROFILE CARD (left) ── */
    .profile-card {
      background: #fff;
      border: 1px solid #e4eaf2;
      border-radius: 14px;
      padding: 28px 20px;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 10px;
      text-align: center;
    }
    .profile-avatar-lg {
      width: 72px; height: 72px;
      border-radius: 50%;
      background: linear-gradient(135deg, #378ADD, #1D9E75);
      color: #fff;
      font-size: 26px;
      font-weight: 700;
      display: flex; align-items: center; justify-content: center;
      margin-bottom: 4px;
    }
    .profile-name  { font-size: 15px; font-weight: 700; color: #1a2a3a; margin: 0; }
    .profile-kelas { font-size: 12px; color: #8a9ab0; margin: 0; }
    .profile-divider { width: 100%; height: 1px; background: #f0f4f8; margin: 8px 0; }
    .profile-meta-row {
      width: 100%;
      display: flex; align-items: center; gap: 8px;
      font-size: 12px; color: #5a6a7a;
      padding: 6px 0;
    }
    .profile-meta-row i { font-size: 15px; color: #378ADD; }

    /* ── FORM PANEL (right) ── */
    .form-panel {
      background: #fff;
      border: 1px solid #e4eaf2;
      border-radius: 14px;
      overflow: hidden;
    }
    .form-panel-header {
      padding: 16px 20px;
      border-bottom: 1px solid #f0f4f8;
      display: flex; align-items: center; gap: 8px;
    }
    .form-panel-header h3 { font-size: 14px; font-weight: 700; color: #1a2a3a; margin: 0; }
    .form-panel-body { padding: 20px; display: flex; flex-direction: column; gap: 16px; }

    .field-label { font-size: 11px; font-weight: 600; color: #8a9ab0; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 6px; display: block; }
    .field-input {
      width: 100%; padding: 10px 14px;
      border: 1.5px solid #e4eaf2; border-radius: 10px;
      font-size: 13px; color: #1a2a3a;
      background: #f8fafc;
      box-sizing: border-box;
      outline: none;
      transition: border-color 0.15s;
    }
    .field-input:focus { border-color: #378ADD; background: #fff; }
    .field-input[readonly] { cursor: default; color: #8a9ab0; }
    .field-input-wrap { position: relative; }
    .field-input-wrap i {
      position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
      font-size: 15px; color: #9aabb8; pointer-events: none;
    }
    .field-input-wrap .field-input { padding-left: 36px; }

    /* password field special */
    .field-input.password-field { border-color: #fca5a5; background: #fff5f5; }
    .field-input.password-field:focus { border-color: #E53935; background: #fff; }

    .field-hint { font-size: 11px; color: #9aabb8; margin-top: 5px; display: flex; align-items: center; gap: 4px; }

    /* alert */
    .alert { padding: 10px 14px; border-radius: 8px; font-size: 13px; font-weight: 500; display: flex; align-items: center; gap: 8px; }
    .alert-success { background: #E1F5EE; color: #0F6E56; border: 1px solid #9FE1CB; }
    .alert-error   { background: #FCEBEB; color: #A32D2D; border: 1px solid #f5c6c6; }

    /* actions */
    .form-actions {
      display: flex; justify-content: flex-end; gap: 8px;
      padding-top: 12px; border-top: 1px solid #f0f4f8;
    }
    .btn-back {
      padding: 9px 18px; border-radius: 8px;
      border: 1.5px solid #e4eaf2; background: #fff;
      font-size: 13px; font-weight: 600; color: #5a6a7a;
      cursor: pointer; text-decoration: none;
      display: inline-flex; align-items: center; gap: 6px;
      transition: background 0.15s;
    }
    .btn-back:hover { background: #f5f7fa; text-decoration: none; }
    .btn-save {
      padding: 9px 20px; border-radius: 8px;
      border: none; background: #1D9E75;
      font-size: 13px; font-weight: 700; color: #fff;
      cursor: pointer;
      display: inline-flex; align-items: center; gap: 6px;
      transition: background 0.15s, transform 0.1s;
    }
    .btn-save:hover  { background: #0F6E56; }
    .btn-save:active { transform: scale(0.97); }
  </style>
</head>
<body>
<div class="app">

  <?php include("templates/sidebar.php"); ?>

  <div class="main">

    <!-- TOPBAR -->
    <div class="topbar">
      <div class="topbar-left">
        <h1><?= htmlspecialchars($pageTitle) ?></h1>
        <p><?= htmlspecialchars($pageSub) ?></p>
      </div>
      <div class="topbar-right">
        <span class="topbar-date">
          <i class="ti ti-calendar" style="font-size:13px;"></i> <?= $today ?>
        </span>
      </div>
    </div>

    <!-- CONTENT -->
    <div class="content">
      <div class="profile-layout">

        <!-- LEFT: Profile summary card -->
        <div class="profile-card">
          <div class="profile-avatar-lg">
            <?= strtoupper(substr($siswa_NAME, 0, 2)) ?>
          </div>
          <p class="profile-name"><?= htmlspecialchars($siswa_NAME) ?></p>
          <p class="profile-kelas">Class <?= htmlspecialchars($siswa_TINGKAT . ' ' . $siswa_KELAS) ?></p>

          <div class="profile-divider"></div>

          <div class="profile-meta-row">
            <i class="ti ti-id-badge"></i>
            <span>ID: <?= htmlspecialchars($data_siswa['siswa_ID']) ?></span>
          </div>
          <div class="profile-meta-row">
            <i class="ti ti-mail"></i>
            <span><?= htmlspecialchars($siswa_user) ?></span>
          </div>
          <div class="profile-meta-row">
            <i class="ti ti-school"></i>
            <span>Grade <?= htmlspecialchars($siswa_TINGKAT) ?></span>
          </div>
        </div>

        <!-- RIGHT: Edit form -->
        <div class="form-panel">
          <div class="form-panel-header">
            <i class="ti ti-shield-lock" style="font-size:18px;color:#378ADD;"></i>
            <h3>Account Information</h3>
          </div>
          <div class="form-panel-body">

            <?php if ($success_message): ?>
              <div class="alert alert-success">
                <i class="ti ti-circle-check"></i> <?= htmlspecialchars($success_message) ?>
              </div>
            <?php endif; ?>
            <?php if ($error_message): ?>
              <div class="alert alert-error">
                <i class="ti ti-circle-x"></i> <?= htmlspecialchars($error_message) ?>
              </div>
            <?php endif; ?>

            <form method="POST">

              <!-- Student ID (readonly) -->
              <div>
                <label class="field-label">Student ID</label>
                <div class="field-input-wrap">
                  <i class="ti ti-key"></i>
                  <input type="text" class="field-input" value="<?= htmlspecialchars($data_siswa['siswa_ID']) ?>" readonly>
                </div>
              </div>

              <!-- Full Name (readonly) -->
              <div>
                <label class="field-label">Full Name</label>
                <div class="field-input-wrap">
                  <i class="ti ti-user"></i>
                  <input type="text" class="field-input" value="<?= htmlspecialchars($siswa_NAME) ?>" readonly>
                </div>
              </div>

              <!-- Username / Email (readonly) -->
              <div>
                <label class="field-label">Username / Email</label>
                <div class="field-input-wrap">
                  <i class="ti ti-at"></i>
                  <input type="text" class="field-input" value="<?= htmlspecialchars($siswa_user) ?>" readonly>
                </div>
              </div>

              <hr style="border:none;border-top:1.5px dashed #e4eaf2;margin:4px 0;">

              <!-- New Password (editable) -->
              <div>
                <label class="field-label" style="color:#E53935;">New Password</label>
                <div class="field-input-wrap">
                  <i class="ti ti-lock" style="color:#E53935;"></i>
                  <input type="text" class="field-input password-field"
                         id="passwordSiswa" name="passwordSiswa"
                         value="<?= htmlspecialchars(password_tampil($siswa_pass)) ?>" placeholder="Password baru" required>
                </div>
                <p class="field-hint">
                  <i class="ti ti-info-circle"></i>
                  Clear the field above and type your new password to update it.
                </p>
              </div>

              <div class="form-actions">
                <a href="home.php" class="btn-back">
                  <i class="ti ti-arrow-left"></i> Back
                </a>
                <button type="submit" name="Update" class="btn-save">
                  <i class="ti ti-device-floppy"></i> Save Changes
                </button>
              </div>

            </form>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>
</body>
</html>
<?php
mysqli_close($conn);
ob_end_flush();
?>