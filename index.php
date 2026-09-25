<?php
// =============================================
// 1. Sambungkan ke database + mulai session
// =============================================
include 'config.php';

// =============================================
// 2. PENGAMAN: hanya bisa dibuka kalau sudah login
// =============================================
cek_login();

// =============================================
// 3. Ambil data statistik untuk dashboard
// =============================================

// a. Jumlah barang tersimpan + total nilai stok
$infoBarang = $conn->query("SELECT COUNT(*) AS jumlah, COALESCE(SUM(stok * harga), 0) AS nilai
                            FROM barang")->fetch_assoc();

// b. Total pemasukan (semua tanggal)
$totalMasuk = $conn->query("SELECT COALESCE(SUM(total), 0) AS total FROM pemasukan")->fetch_assoc()['total'];

// c. Total pengeluaran (semua tanggal)
$totalKeluar = $conn->query("SELECT COALESCE(SUM(jumlah), 0) AS total FROM pengeluaran")->fetch_assoc()['total'];

// d. Selisih pemasukan - pengeluaran
$saldo = $totalMasuk - $totalKeluar;

// e. 5 transaksi pemasukan terakhir
$pemasukan = $conn->query("SELECT * FROM pemasukan ORDER BY id DESC LIMIT 5");

// f. 5 transaksi pengeluaran terakhir
$pengeluaran = $conn->query("SELECT * FROM pengeluaran ORDER BY id DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Kasir Cantik</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- =============================================
        NAVBAR (menu navigasi atas)
    ============================================= -->
    <?php include 'navbar.php'; ?>

    <div class="container">

        <!-- Judul halaman + tombol tambah barang -->
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
            <h2 class="page-title">Dashboard Kasir</h2>
            <a href="barang_tambah.php" class="btn btn-add">+ Tambah Barang</a>
        </div>

        <!-- =============================================
            KARTU STATISTIK (4 kotak angka penting)
        ============================================= -->
        <div class="stats">
            <!-- 1. Jumlah barang -->
            <div class="stat-card">
                <div class="label">Total Barang</div>
                <div class="angka"><?= $infoBarang['jumlah'] ?> item</div>
            </div>

            <!-- 2. Total pemasukan -->
            <div class="stat-card" style="border-color:#5aa9f7;">
                <div class="label">Total Pemasukan</div>
                <div class="angka" style="color:#3f8ef0;"><?= rupiah($totalMasuk) ?></div>
            </div>

            <!-- 3. Total pengeluaran -->
            <div class="stat-card" style="border-color:#f47c9b;">
                <div class="label">Total Pengeluaran</div>
                <div class="angka" style="color:#e84a8a;"><?= rupiah($totalKeluar) ?></div>
            </div>

            <!-- 4. Sisa saldo -->
            <div class="stat-card" style="border-color:#8fd3a0;">
                <div class="label">Saldo Kas</div>
                <div class="angka" style="color:#2f9e4f;"><?= rupiah($saldo) ?></div>
            </div>
        </div>

        <!-- =============================================
            DAFTAR TRANSAKSI TERAKHIR (2 kolom)
        ============================================= -->
        <div class="two-col">

            <!-- Kolom kiri: pemasukan terakhir -->
            <div class="box">
                <h3>&#11088; Pemasukan Terakhir</h3>
                <div class="table-wrap">
                    <table>
                        <tr>
                            <th>Nama</th>
                            <th>Qty</th>
                            <th>Total</th>
                        </tr>
                        <?php while ($p = $pemasukan->fetch_assoc()): ?>
                        <!-- Satu baris pemasukan -->
                        <tr>
                            <td><?= htmlspecialchars($p['nama_barang']) ?></td>
                            <td><?= $p['qty'] ?></td>
                            <td class="uang-in"><?= rupiah($p['total']) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </table>
                </div>
            </div>

            <!-- Kolom kanan: pengeluaran terakhir -->
            <div class="box">
                <h3>&#128176; Pengeluaran Terakhir</h3>
                <div class="table-wrap">
                    <table>
                        <tr>
                            <th>Keterangan</th>
                            <th>Jumlah</th>
                        </tr>
                        <?php while ($k = $pengeluaran->fetch_assoc()): ?>
                        <!-- Satu baris pengeluaran -->
                        <tr>
                            <td><?= htmlspecialchars($k['keterangan']) ?></td>
                            <td class="uang-out"><?= rupiah($k['jumlah']) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Panggil JavaScript -->
    <script src="script.js"></script>
</body>
</html>