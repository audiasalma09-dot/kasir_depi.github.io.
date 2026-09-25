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
// 3. Data awal form (nilai default kosong)
// =============================================
$nama  = '';
$harga = 0;
$stok  = 0;

// =============================================
// 4. Variabel menampung pesan sukses/error
// =============================================
$pesan = '';

// =============================================
// 5. Cek apakah form sudah dikirim
// =============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // =============================================
    // 6. Ambil dan bersihkan input teks
    // =============================================
    $nama  = $conn->real_escape_string(trim($_POST['nama']));
    $harga = (int) $_POST['harga'];
    $stok  = (int) $_POST['stok'];

    // =============================================
    // 7. Proses UPLOAD GAMBAR ke folder "uploads"
    // =============================================
    $namaFile = '';

    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {

        // a. Ambil info file yang dikirim
        $fileInfo = pathinfo($_FILES['gambar']['name']);
        // b. Ekstensi file diubah jadi huruf kecil
        $ekstensi = strtolower($fileInfo['extension'] ?? '');
        // c. Daftar ekstensi yang boleh masuk
        $boleh = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        // d. Batas ukuran file (2 MB)
        $maksUkuran = 2 * 1024 * 1024;

        // e. Validasi ekstensi
        if (!in_array($ekstensi, $boleh)) {
            $pesan = 'Format gambar tidak didukung! (jpg, jpeg, png, gif, webp)';
        }
        // f. Validasi ukuran file
        elseif ($_FILES['gambar']['size'] > $maksUkuran) {
            $pesan = 'Ukuran gambar terlalu besar (maksimal 2 MB)!';
        } else {
            // g. Nama unik biar tidak tertimpa file lain
            $namaFile = 'barang_' . time() . '_' . rand(1000, 9999) . '.' . $ekstensi;
            // h. Tujuan simpan: folder uploads/
            $tujuan = 'uploads/' . $namaFile;

            // i. Pindahkan file dari folder sementara ke uploads/
            if (!move_uploaded_file($_FILES['gambar']['tmp_name'], $tujuan)) {
                $namaFile = '';
                $pesan = 'Gagal mengupload gambar!';
            }
        }
    }

    // =============================================
    // 8. Simpan ke database kalau tidak ada error
    // =============================================
    if ($pesan === '') {
        $conn->query("INSERT INTO barang (nama, harga, stok, gambar)
                      VALUES ('$nama', $harga, $stok, '$namaFile')");
        // Pesan sukses lalu ke halaman daftar barang
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
    <title>Tambah Barang - Kasir Cantik</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- =============================================
        NAVBAR (menu navigasi atas)
    ============================================= -->
    <?php include 'navbar.php'; ?>

    <div class="container">

        <!-- Judul halaman -->
        <h2 class="page-title">Tambah Barang</h2>

        <!-- Formulir tambah barang -->
        <div class="box" style="max-width:520px;">
            <form method="POST" action="" enctype="multipart/form-data">
                <!-- Input nama barang -->
                <label>Nama Barang</label>
                <input type="text" name="nama" value="<?= htmlspecialchars($nama) ?>" required>

                <!-- Input harga jual -->
                <label>Harga Jual (Rp)</label>
                <input type="number" name="harga" value="<?= $harga ?>" min="0" required>

                <!-- Input stok awal -->
                <label>Stok Awal</label>
                <input type="number" name="stok" value="<?= $stok ?>" min="0" required>

                <!-- Input file gambar -->
                <label>Foto Barang</label>
                <input type="file" name="gambar" accept="image/*">

                <!-- Tombol simpan -->
                <button type="submit" class="btn" style="margin-top:18px;">Simpan Barang</button>
            </form>

            <!-- Tampilkan pesan error kalau ada -->
            <?php if ($pesan !== ''): ?>
                <div class="alert"><?= htmlspecialchars($pesan) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Panggil JavaScript -->
    <script src="script.js"></script>
</body>
</html>