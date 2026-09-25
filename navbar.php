<?php
// =============================================
// 1. NAVBAR / MENU NAVIGASI (dipakai semua halaman)
//    File ini di-'include' di setiap halaman setelah config.php
// =============================================

// Aktifkan menu sesuai halaman yang sedang dibuka
// Ambil nama file halaman sekarang dari URL
$halaman = basename($_SERVER['PHP_SELF']);
?>
<!-- =============================================
     2. Bagian atas (header) berwarna pink
============================================= -->
<header class="navbar">
    <!-- Logo aplikasi -->
    <div class="nav-logo">&#10047; Kasir Cantik</div>

    <!-- Daftar menu navigasi -->
    <nav class="nav-links">
        <!-- Menu dashboard -->
        <a href="index.php" class="<?= $halaman === 'index.php' ? 'active' : '' ?>">Dashboard</a>

        <!-- Menu barang -->
        <a href="barang.php" class="<?= ($halaman === 'barang.php' || $halaman === 'barang_tambah.php' || $halaman === 'barang_edit.php') ? 'active' : '' ?>">Barang</a>

        <!-- Menu pemasukan -->
        <a href="pemasukan.php" class="<?= $halaman === 'pemasukan.php' ? 'active' : '' ?>">Pemasukan</a>

        <!-- Menu pengeluaran -->
        <a href="pengeluaran.php" class="<?= $halaman === 'pengeluaran.php' ? 'active' : '' ?>">Pengeluaran</a>

        <!-- Menu closingan -->
        <a href="closing.php" class="<?= $halaman === 'closing.php' ? 'active' : '' ?>">Closingan</a>

        <!-- Tombol keluar -->
        <a href="logout.php" class="btn btn-logout">Keluar</a>
    </nav>

    <!-- Tampilkan nama user yang sedang login -->
    <div class="nav-user">Hai, <strong><?= htmlspecialchars($_SESSION['nama_user'] ?? '') ?></strong> &#9786;</div>
</header>