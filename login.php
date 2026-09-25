<?php
// =============================================
// 1. Panggil config.php (session + koneksi db)
// =============================================
include 'config.php';

// =============================================
// 2. Kalau sudah login, langsung menuju dashboard
// =============================================
if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
    header('Location: index.php');
    exit;
}

// =============================================
// 3. Variabel untuk pesan error (tampil kalau gagal)
// =============================================
$error = '';

// =============================================
// 4. Cek apakah form login sudah dikirim
// =============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Ambil input lalu bersihkan
    $username = $conn->real_escape_string(trim($_POST['username']));
    $password = $_POST['password'];

    // Cari user sesuai username di database
    $result = $conn->query("SELECT * FROM users WHERE username = '$username'");

    // Kalau user ditemukan, verifikasi password
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // password_verify dipakai untuk mencocokkan hash
        if (password_verify($password, $user['password'])) {
            // Simpan data login ke session
            $_SESSION['login']     = true;
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['nama_user'] = $user['nama'];

            // Arahkan ke dashboard
            header('Location: index.php');
            exit;
        }
    }

    // Kalau username atau password salah
    $error = 'Username atau password salah!';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Kasir Cantik</title>
    <!-- File CSS tema girly -->
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-body">

    <!-- Kotak formulir login di tengah layar -->
    <div class="login-box">
        <!-- Ikon bunga sebagai hiasan -->
        <div class="login-icon">&#10047;</div>

        <!-- Judul aplikasi -->
        <h1>Kasir Cantik</h1>
        <!-- Keterangan singkat -->
        <p class="login-sub">Silakan masuk untuk mulai berjualan</p>

        <!-- Form login -->
        <form method="POST" action="">
            <!-- Input username -->
            <label>Username</label>
            <input type="text" name="username" required placeholder="admin">

            <!-- Input password -->
            <label>Password</label>
            <input type="password" name="password" required placeholder="123456">

            <!-- Tombol masuk -->
            <button type="submit" class="btn btn-block">Masuk</button>
        </form>

        <!-- Pesan error (kalau ada) -->
        <?php if ($error !== ''): ?>
            <div class="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Info akun bawaan biar tidak bingung -->
        <p class="login-hint">Akun bawaan: <strong>admin</strong> / <strong>123456</strong></p>
    </div>

</body>
</html>