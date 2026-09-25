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
// 4. Cek apakah form pengeluaran sudah dikirim
// =============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // =============================================
    // 5. Ambil dan bersihkan input
    // =============================================
    $keterangan = $conn->real_escape_string(trim($_POST['keterangan']));
    $jumlah     = (int) $_POST['jumlah'];
    $tanggal    = $conn->real_escape_string($_POST['tanggal']);

    // =============================================
    // 6. Validasi sederhana
    // =============================================
    if ($keterangan === '') {
        $pesan = 'Keterangan wajib diisi!';
    } elseif ($jumlah <= 0) {
        $pesan = 'Jumlah harus lebih dari 0!';
    } else {
        // =============================================
        // 7. Simpan catatan pengeluaran ke database
        // =============================================
        $conn->query("INSERT INTO pengeluaran (keterangan, jumlah, tanggal)
                      VALUES ('$keterangan', $jumlah, '$tanggal')");
        $pesan = 'Pengeluaran tercatat!';
    }
}

// =============================================
// 8. Ambil semua catatan pengeluaran
// =============================================
$pengeluaran = $conn->query("SELECT * FROM pengeluaran ORDER BY tanggal DESC, id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengeluaran - Kasir Cantik</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="container">

        <h2 class="page-title">Pengeluaran (Biaya)</h2>

        <!-- Tampilkan pesan sukses -->
        <?php if ($pesan !== ''): ?>
            <div class="alert" style="background:#e7f8ec;color:#2f9e4f;"><?= htmlspecialchars($pesan) ?></div>
        <?php endif; ?>

        <div class="two-col">

            <!-- Kolom kiri: FORM catat pengeluaran -->
            <div class="box">
                <h3>&#128176; Catat Pengeluaran</h3>
                <form method="POST" action="">

                    <!-- Keterangan pengeluaran -->
                    <label>Keterangan</label>
                    <input type="text" name="keterangan" placeholder="Contoh: beli gas, bayar sewa" required>

                    <!-- Nominal pengeluaran -->
                    <label>Jumlah (Rp)</label>
                    <input type="number" name="jumlah" min="1" required>

                    <!-- Tanggal penerima -->
                    <label>Tanggal</label>
                    <input type="date" name="tanggal" value="<?= date('Y-m-d') ?>" required>

                    <!-- Tombol simpan -->
                    <button type="submit" class="btn" style="margin-top:18px;">Simpan Pengeluaran</button>
                </form>
            </div>

            <!-- Kolom kanan: RIWAYAT pengeluaran -->
            <div class="box">
                <h3>&#128221; Riwayat Pengeluaran</h3>
                <div class="table-wrap">
                    <table>
                        <tr>
                            <th>Tanggal</th>
                            <th>Keterangan</th>
                            <th>Jumlah</th>
                            <th>Aksi</th>
                        </tr>

                        <?php while ($k = $pengeluaran->fetch_assoc()): ?>
                        <!-- Satu baris pengeluaran -->
                        <tr>
                            <td><?= $k['tanggal'] ?></td>
                            <td><?= htmlspecialchars($k['keterangan']) ?></td>
                            <td class="uang-out"><?= rupiah($k['jumlah']) ?></td>
                            <td>
                                <!-- Tombol hapus pengeluaran -->
                                <a href="hapus.php?tipe=pengeluaran&id=<?= $k['id'] ?>"
                                   class="btn btn-sm btn-delete"
                                   data-konfirmasi="Hapus pengeluaran '<?= htmlspecialchars($k['keterangan']) ?>'?">Hapus</a>
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