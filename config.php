<?php
// =============================================
// 1. MULAI SESSION (simpan status login antar halaman)
//    Session wajib dipanggil paling atas sebelum HTML
// =============================================
session_start();

// =============================================
// 2. Alamat server MySQL
// =============================================
$host = 'localhost';

// =============================================
// 3. Username bawaan MySQL di Laragon
// =============================================
$user = 'root';

// =============================================
// 4. Password bawaan Laragon (kosong)
// =============================================
$pass = '';

// =============================================
// 5. Nama database sesuai file db_kasir.sql
// =============================================
$db = 'db_kasir';

// =============================================
// 6. Koneksi ke MySQL memakai object mysqli
// =============================================
$conn = new mysqli($host, $user, $pass, $db);

// =============================================
// 7. Cek apakah koneksi berhasil
// =============================================
if ($conn->connect_error) {
    die('Koneksi gagal: ' . $conn->connect_error);
}

// =============================================
// 8. Set karakter koneksi biar huruf/angka aman
// =============================================
$conn->set_charset('utf8mb4');

// =============================================
// 9. INISIALISASI AWAL: kalau tabel users kosong,
//    buat otomatis akun admin (username: admin,
//    password: 123456). Jadi aplikasi selalu siap pakai.
// =============================================
$cekUser = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc();
if ((int) $cekUser['total'] === 0) {
    // Hash password memakai fungsi bawaan PHP
    $hash = password_hash('123456', PASSWORD_DEFAULT);
    $conn->query("INSERT INTO users (username, password, nama)
                  VALUES ('admin', '$hash', 'Admin Kasir')");
}

// =============================================
// 10. FUNGSI PENGAMAN HALAMAN
//     Dipanggil di setiap halaman yang butuh login.
//     Kalau belum login, langsung pindah ke login.php
// =============================================
function cek_login() {
    // Kalau session 'login' belum di-set, artinya belum login
    if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
        header('Location: login.php');
        exit;
    }
}

// =============================================
// 11. FUNGSI FORMAT ANGKA RUPIAH
//     Contoh: 15000  -> Rp 15.000
// =============================================
function rupiah($angka) {
    return 'Rp ' . number_format((int) $angka, 0, ',', '.');
}