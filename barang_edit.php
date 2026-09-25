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
// 3. Ambil ID barang dari URL, paksa jadi angka
// =============================================
$id = (int) $_GET['id'];

// =============================================
// 4. Cari data barang sesuai ID
// =============================================
$data = $conn->query("SELECT * FROM barang WHERE id = $id")->fetch_assoc();

// =============================================
// 5. Kalau barang tidak ada, hentikan program
// =============================================
if (!$data) {
    die('Barang tidak ditemukan');
}

// =============================================
// 6. Variabel menampung pesan error
// =============================================
$pesan = '';

// =============================================
// 7. Cek apakah form edit sudah dikirim
// =============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // =============================================
    // 8. Ambil dan bersihkan input teks
    // =============================================
    $nama  = $conn->real_escape_string(trim($_POST['nama']));
    $harga = (int) $_POST['harga'];
    $stok  = (int) $_POST['stok'];

    // =============================================
    // 9. Mulai pakai gambar lama sebagai default
    // =============================================
    $namaFile = $data['gambar'];

    // =============================================
    // 10. Kalau ada gambar baru di-upload
    // =============================================
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {

        // a. Ambil ekstensi file baru
        $fileInfo = pathinfo($_FILES['gambar']['name']);
        $ekstensi = strtolower($fileInfo['extension'] ?? '');
        $boleh = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $maksUkuran = 2 * 1024 * 1024;

        // b. Validasi ekstensi
        if (!in_array($ekstensi, $boleh)) {
            $pesan = 'Format gambar tidak didukung! (jpg, jpeg, png, gif, webp)';
        }
        // c. Validasi ukuran
        elseif ($_FILES['gambar']['size'] > $maksUkuran) {
            $pesan = 'Ukuran gambar terlalu besar (maksimal 2 MB)!';
        } else {
            // d. Nama unik untuk gambar baru
            $namaFile = 'barang_' . time() . '_' . rand(1000, 9999) . '.' . $ekstensi;
            $tujuan = 'uploads/' . $namaFile;

            // e. Pindahkan gambar baru ke folder uploads
            if (move_uploaded_file($_FILES['gambar']['tmp_name'], $tujuan)) {
                // f. Hapus gambar lama biar tidak menumpuk
                if ($data['gambar'] && file_exists('uploads/' . $data['gambar'])) {
                    unlink('uploads/' . $data['gambar']);
                }
            } else {
                $namaFile = $data['gambar'];
                $pesan = 'Gagal mengupload gambar!';
            }
        }
    }

    // =============================================
    // 11. Perbarui data di database kalau tidak error
    // =============================================
    if ($pesan === '') {
        $conn->query("UPDATE barang
                      SET nama = '$nama', harga = $harga, stok = $stok, gambar = '$namaFile'
                      WHERE id = $id");
        header('Location: barang.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Barang - Kasir Cantik</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="container">

        <h2 class="page-title">Edit Barang</h2>

        <!-- Formulir edit barang -->
        <div class="box" style="max-width:520px;">
            <form method="POST" action="" enctype="multipart/form-data">

                <!-- Nama barang (terisi data lama) -->
                <label>Nama Barang</label>
                <input type="text" name="nama" value="<?= htmlspecialchars($data['nama']) ?>" required>

                <!-- Harga barang (terisi data lama) -->
                <label>Harga Jual (Rp)</label>
                <input type="number" name="harga" value="<?= $data['harga'] ?>" min="0" required>

                <!-- Stok barang (terisi data lama) -->
                <label>Stok</label>
                <input type="number" name="stok" value="<?= $data['stok'] ?>" min="0" required>

                <!-- Foto lama tampil sebagai pratinjau -->
                <label>Foto Sekarang</label>
                <div style="margin:6px 0;">
                    <img class="thumb" style="width:90px;height:90px;"
                         src="<?= $data['gambar'] ? 'uploads/' . $data['gambar'] : 'https://picsum.photos/seed/kasir/100/100' ?>"
                         alt="<?= htmlspecialchars($data['nama']) ?>">
                </div>

                <!-- Upload foto baru (opsional) -->
                <label>Ganti Foto (opsional)</label>
                <input type="file" name="gambar" accept="image/*">

                <!-- Tombol simpan perubahan -->
                <button type="submit" class="btn" style="margin-top:18px;">Simpan Perubahan</button>
            </form>

            <?php if ($pesan !== ''): ?>
                <div class="alert"><?= htmlspecialchars($pesan) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>