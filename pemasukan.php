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
// 3. Pesan sukses / error
// =============================================
$pesan = '';

// =============================================
// 4. Cek apakah form pemasukan sudah dikirim
// =============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // =============================================
    // 5. Ambil input dari form
    // =============================================
    $idBarang = (int) $_POST['id_barang'];
    $qty      = (int) $_POST['qty'];
    $tanggal  = $conn->real_escape_string($_POST['tanggal']);
    $catatan  = $conn->real_escape_string(trim($_POST['catatan']));

    // =============================================
    // 6. Ambil data barang yang dijual
    // =============================================
    $barang = $conn->query("SELECT * FROM barang WHERE id = $idBarang")->fetch_assoc();

    // Validasi: barang harus ada
    if (!$barang) {
        $pesan = 'Barang tidak ditemukan!';
    }
    // Validasi: jumlah harus lebih dari 0
    elseif ($qty <= 0) {
        $pesan = 'Jumlah harus lebih dari 0!';
    }
    // Validasi: stok mencukupi
    elseif ($qty > $barang['stok']) {
        $pesan = 'Stok tidak cukup! (tersisa ' . $barang['stok'] . ')';
    } else {

        // =============================================
        // 7. Hitung total = qty x harga
        // =============================================
        $total = $qty * $barang['harga'];

        // =============================================
        // 8. Kurangi stok barang di tabel barang
        // =============================================
        $conn->query("UPDATE barang SET stok = stok - $qty WHERE id = $idBarang");

        // =============================================
        // 9. Simpan catatan pemasukan
        //    Nama barang ikut disalin supaya tahan lama
        // =============================================
        $conn->query("INSERT INTO pemasukan (id_barang, nama_barang, qty, harga, total, tanggal, catatan)
                      VALUES ($idBarang, '{$barang['nama']}', $qty, {$barang['harga']}, $total, '$tanggal', '$catatan')");

        $pesan = 'Penjualan tercatat! Stok berkurang.';
    }
}

// =============================================
// 10. Ambil daftar barang (untuk pilihan di form)
// =============================================
$daftarBarang = $conn->query("SELECT * FROM barang WHERE stok > 0 ORDER BY nama ASC");

// =============================================
// 11. Ambil semua catatan pemasukan
// =============================================
$pemasukan = $conn->query("SELECT * FROM pemasukan ORDER BY tanggal DESC, id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemasukan - Kasir Cantik</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="container">

        <h2 class="page-title">Pemasukan (Penjualan)</h2>

        <!-- Tampilkan pesan sukses -->
        <?php if ($pesan !== ''): ?>
            <div class="alert" style="background:#e7f8ec;color:#2f9e4f;"><?= htmlspecialchars($pesan) ?></div>
        <?php endif; ?>

        <div class="two-col">

            <!-- Kolom kiri: FORM catat penjualan -->
            <div class="box">
                <h3>&#128722; Catat Penjualan</h3>
                <!-- Form dikirim ke halaman ini -->
                <form method="POST" action="">
                    <!-- Pilih barang yang dijual -->
                    <label>Pilih Barang</label>
                    <select name="id_barang" required>
                        <!-- Opsi default -->
                        <option value="">-- Pilih barang --</option>

                        <?php while ($b = $daftarBarang->fetch_assoc()): ?>
                        <!-- Satu opsi barang -->
                        <option value="<?= $b['id'] ?>">
                            <?= htmlspecialchars($b['nama']) ?> - Stok <?= $b['stok'] ?>
                        </option>
                        <?php endwhile; ?>
                    </select>

                    <!-- Jumlah terjual -->
                    <label>Jumlah (Qty)</label>
                    <input type="number" name="qty" min="1" value="1" required>

                    <!-- Tanggal transaksi (default hari ini) -->
                    <label>Tanggal</label>
                    <input type="date" name="tanggal" value="<?= date('Y-m-d') ?>" required>

                    <!-- Catatan tambahan -->
                    <label>Catatan (opsional)</label>
                    <input type="text" name="catatan" placeholder="Contoh: penjualan sore hari">

                    <!-- Tombol simpan -->
                    <button type="submit" class="btn" style="margin-top:18px;">Simpan Penjualan</button>
                </form>
            </div>

            <!-- Kolom kanan: RIWAYAT pemasukan -->
            <div class="box">
                <h3>&#128221; Riwayat Pemasukan</h3>
                <div class="table-wrap">
                    <table>
                        <tr>
                            <th>Tanggal</th>
                            <th>Barang</th>
                            <th>Qty</th>
                            <th>Total</th>
                            <th>Aksi</th>
                        </tr>

                        <?php while ($p = $pemasukan->fetch_assoc()): ?>
                        <!-- Satu baris pemasukan -->
                        <tr>
                            <td><?= $p['tanggal'] ?></td>
                            <td><?= htmlspecialchars($p['nama_barang']) ?></td>
                            <td><?= $p['qty'] ?></td>
                            <td class="uang-in"><?= rupiah($p['total']) ?></td>
                            <td>
                                <!-- Hapus transaksi (stok dikembalikan) -->
                                <a href="hapus.php?tipe=pemasukan&id=<?= $p['id'] ?>"
                                   class="btn btn-sm btn-delete"
                                   data-konfirmasi="Batal transaksi <?= htmlspecialchars($p['nama_barang']) ?> x<?= $p['qty'] ?>? Stok akan dikembalikan.">Hapus</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>