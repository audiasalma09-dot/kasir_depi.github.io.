<?php
// =============================================
// 1. Panggil config.php (session aktif)
// =============================================
include 'config.php';

// =============================================
// 2. Hapus semua data session (logik: keluar)
// =============================================
$_SESSION = [];               // kosongkan isi session
session_destroy();            // hancurkan session di server
session_start();              // mulai ulang biar bisa set pesan
$_SESSION['logout_msg'] = 'Sampai jumpa lagi!'; // pesan perpisahan

// =============================================
// 3. Arahkan kembali ke halaman login
// =============================================
header('Location: login.php');
exit;