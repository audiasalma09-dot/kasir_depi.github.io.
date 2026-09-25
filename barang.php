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
// 3. Ambil semua data barang, yang terbaru di atas
// =============================================
$result = $conn->query("SELECT * FROM barang ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Barang - Kasir Cantik</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- =============================================
        NAVBAR (menu navigasi atas)
    ============================================= -->
    <?php include 'navbar.php'; ?>

    <div class="container">

        <!-- Judul halaman + tombol tambah -->
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
            <h2 class="page-title">Data Barang</h2>
            <a href="barang_tambah.php" class="btn btn-add">+ Tambah Barang</a>
        </div>

        <!-- Kamar data barang -->
        <div class="box">
            <div class="table-wrap">
                <table>
                    <!-- Header tabel -->
                    <tr>
                        <th>Foto</th>
                        <th>Nama Barang</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>

                    <?php while ($b = $result->fetch_assoc()): ?>
                    <!-- Satu baris data barang -->
                    <tr>
                        <!-- Gambar barang (dari folder uploads) -->
                        <td>
                            <img class="thumb"
                                 src="<?= $b['gambar'] ? 'uploads/' . $b['gambar'] : 'https://picsum.photos/seed/kasir/100/100' ?>"
                                 alt="<?= htmlspecialchars($b['nama']) ?>">
                        </td>

                        <!-- Nama barang -->
                        <td><?= htmlspecialchars($b['nama']) ?></td>

                        <!-- Harga barang -->
                        <td><?= rupiah($b['harga']) ?></td>

                        <!-- Stok / persediaan -->
                        <td><?= $b['stok'] ?></td>

                        <!-- Tombol aksi -->
                        <td>
                            <!-- Tombol edit -->
                            <a href="barang_edit.php?id=<?= $b['id'] ?>" class="btn btn-sm btn-edit">Edit</a>

                            <!-- Tombol hapus dengan konfirmasi -->
                            <a href="hapus.php?tipe=barang&id=<?= $b['id'] ?>"
                               class="btn btn-sm btn-delete"
                               data-konfirmasi="Yakin mau hapus barang '<?= htmlspecialchars($b['nama']) ?>'?">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </table>

                <!-- Pesan kalau belum ada barang -->
                <?php if ($result->num_rows === 0): ?>
                    <p style="text-align:center;color:#c4a1b4;padding:20px;">Belum ada barang.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Panggil JavaScript -->
    <script src="script.js"></script>
</body>
</html>