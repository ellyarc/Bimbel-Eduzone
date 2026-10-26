<?php
// Fungsi bantu autentikasi: query terparameter (anti SQL injection untuk login)
// dan password ter-hash. Password lama yang masih teks biasa tetap bisa dipakai
// login satu kali, lalu otomatis diubah menjadi hash.

function password_sudah_hash($tersimpan)
{
    return password_get_info(rtrim($tersimpan))["algoName"] !== "unknown";
}

// Nilai yang boleh ditampilkan di kolom form: hash tidak pernah ditampilkan.
function password_tampil($tersimpan)
{
    return password_sudah_hash($tersimpan) ? "" : $tersimpan;
}

// Simpan password baru (sebagai hash) untuk satu akun. $kolom_user dan $tabel
// dipilih dari daftar tetap di bawah, bukan dari input pengguna.
function simpan_password($conn, $tabel, $kolom_cari, $nilai_cari, $password_baru)
{
    $peta = [
        "admin" => ["admin_PASS", ["admin_USER", "admin_ID"]],
        "tutor" => ["tutor_PASS", ["tutor_USER", "tutor_ID"]],
        "siswa" => ["siswa_PASS", ["siswa_USER", "siswa_ID"]],
    ];
    if (!isset($peta[$tabel]) || !in_array($kolom_cari, $peta[$tabel][1], true)) {
        return false;
    }
    $hash = password_hash($password_baru, PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($conn, "UPDATE $tabel SET {$peta[$tabel][0]} = ? WHERE $kolom_cari = ?");
    mysqli_stmt_bind_param($stmt, "ss", $hash, $nilai_cari);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

// Cari akun di tabel admin / tutor / siswa. Mengembalikan baris akun jika
// username dan password cocok, atau null jika tidak.
function cari_akun($conn, $tabel, $user, $pass)
{
    $peta = [
        "admin" => ["admin_USER", "admin_PASS"],
        "tutor" => ["tutor_USER", "tutor_PASS"],
        "siswa" => ["siswa_USER", "siswa_PASS"],
    ];
    if (!isset($peta[$tabel])) {
        return null;
    }
    list($kolom_user, $kolom_pass) = $peta[$tabel];

    $stmt = mysqli_prepare($conn, "SELECT * FROM $tabel WHERE $kolom_user = ?");
    mysqli_stmt_bind_param($stmt, "s", $user);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if (!$row) {
        return null;
    }

    $tersimpan = rtrim($row[$kolom_pass]);
    if (password_sudah_hash($tersimpan)) {
        return password_verify($pass, $tersimpan) ? $row : null;
    }

    // Password lama (teks biasa): cocokkan, lalu ubah menjadi hash
    if (hash_equals($tersimpan, $pass)) {
        simpan_password($conn, $tabel, $kolom_user, $user, $pass);
        return $row;
    }
    return null;
}
