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
// 3. Tentukan tanggal yang dibuka
//    Urutan prioritas: POST form simpan > GET filter > hari ini
// =============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal = $conn->real_escape_string($_POST['tanggal']);
} else {
    $tanggal = $conn->real_escape_string($_GET['tanggal'] ?? date('Y-m-d'));
}

// =============================================
// 4. Pesan sukses / error
// =============================================
$pesan = '';

// =============================================
// 5. PROSES SIMPAN CLOSINGAN (form hitung stok dikirim)
// =============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // =============================================
    // 6. Ambil data stok fisik hasil hitung (array)
    //    Format: stok[ID_BARANG] = angka
    // =============================================
    $stokPost = $_POST['stok'] ?? array();
    $detail   = array();   // nanti jadi array hasil hitung

    // Ambil semua barang untuk mencocokkan sistem vs fisik
    $semuaBarang = $conn->query("SELECT * FROM barang ORDER BY nama ASC");

    // =============================================
    // 7. Loop setiap barang, bandingkan stok fisik vs sistem
    // =============================================
    while ($b = $semuaBarang->fetch_assoc()) {

        // Stok sistem = angka yang tercatat di tabel barang
        $stokSistem = (int) $b['stok'];

        // Stok fisik = angka yang diketik user (default 0)
        $stokFisik = (int) ($stokPost[$b['id']] ?? 0);

        // Selisih = fisik - sistem (bisa minus / plus / nol)
        $selisih = $stokFisik - $stokSistem;

        // Simpan hasil per barang ke dalam array detail
        $detail[] = array(
            'nama'        => $b['nama'],
            'stok_sistem' => $stokSistem,
            'stok_fisik'  => $stokFisik,
            'selisih'     => $selisih,
        );
    }

    // =============================================
    // 8. Hitung total pemasukan & pengeluaran tanggal itu
    // =============================================
    $totalMasuk = $conn->query("SELECT COALESCE(SUM(total), 0) AS tot
                                FROM pemasukan WHERE tanggal = '$tanggal'")->fetch_assoc()['tot'];

    $totalKeluar = $conn->query("SELECT COALESCE(SUM(jumlah), 0) AS tot
                                 FROM pengeluaran WHERE tanggal = '$tanggal'")->fetch_assoc()['tot'];

    $selisihUang = (int) $totalMasuk - (int) $totalKeluar;

    // =============================================
    // 9. Ubah hasil hitung jadi string JSON
    //    supaya mudah disimpan di satu kolom
    // =============================================
    $jsonDetail = json_encode($detail, JSON_UNESCAPED_UNICODE);

    // =============================================
    // 10. Simpan / perbarui closingan tanggal itu
    //     Pakai ON DUPLICATE KEY supaya tidak dobel
    // =============================================
    $conn->query("INSERT INTO closing (tanggal, total_pemasukan, total_pengeluaran, selisih, data_stok)
                  VALUES ('$tanggal', $totalMasuk, $totalKeluar, $selisihUang, '" . $conn->real_escape_string($jsonDetail) . "')
                  ON DUPLICATE KEY UPDATE
                    total_pemasukan   = $totalMasuk,
                    total_pengeluaran = $totalKeluar,
                    selisih           = $selisihUang,
                    data_stok         = '" . $conn->real_escape_string($jsonDetail) . "'");

    $pesan = 'Closingan tanggal ' . $tanggal . ' berhasil disimpan!';
}

// =============================================
// 11. Ambil total pemasukan & pengeluaran untuk tanggal yang dibuka
// =============================================
$totalMasukTgl = $conn->query("SELECT COALESCE(SUM(total), 0) AS tot
                               FROM pemasukan WHERE tanggal = '$tanggal'")->fetch_assoc()['tot'];

$totalKeluarTgl = $conn->query("SELECT COALESCE(SUM(jumlah), 0) AS tot
                                FROM pengeluaran WHERE tanggal = '$tanggal'")->fetch_assoc()['tot'];

$selisihTgl = (int) $totalMasukTgl - (int) $totalKeluarTgl;

// =============================================
// 12. Ambil daftar barang untuk tabel hitung persediaan
// =============================================
$daftarBarang = $conn->query("SELECT * FROM barang ORDER BY nama ASC");

// =============================================
// 13. Cek apakah sudah ada closingan untuk tanggal ini
//     supaya angka stok fisik bisa diisi ulang dari data lama
// =============================================
$closingTgl = $conn->query("SELECT * FROM closing WHERE tanggal = '$tanggal'")->fetch_assoc();
$stokLama = array();
if ($closingTgl) {
    foreach ((array) json_decode($closingTgl['data_stok'], true) as $rowLama) {
        // Stok fisik lama disimpan per nama barang
        $stokLama[$rowLama['nama']] = (int) $rowLama['stok_fisik'];
    }
}

// =============================================
// 14. Ambil riwayat semua closingan (untuk daftar bawah)
// =============================================
$riwayat = $conn->query("SELECT * FROM closing ORDER BY tanggal DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Closingan - Kasir Cantik</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="container">

        <h2 class="page-title">Closingan &amp; Hitung Persediaan</h2>

        <!-- Pesan sukses -->
        <?php if ($pesan !== ''): ?>
            <div class="alert" style="background:#e7f8ec;color:#2f9e4f;"><?= htmlspecialchars($pesan) ?></div>
        <?php endif; ?>

        <!-- =====================================
             FORM PILIH TANGGAL (filter)
        ===================================== -->
        <div class="box" style="padding:18px;">
            <form method="GET" action="" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
                <!-- Pilihan tanggal -->
                <div>
                    <label>Pilih Tanggal</label>
                    <input type="date" name="tanggal" value="<?= $tanggal ?>">
                </div>

                <!-- Tombol tampilkan -->
                <div>
                    <button type="submit" class="btn">Tampilkan</button>
                </div>
            </form>
        </div>

        <!-- =====================================
             RINGKASAN PENUTUP TANGGAL
        ===================================== -->
        <div class="stats">
            <!-- Total pemasukan hari itu -->
            <div class="stat-card" style="border-color:#5aa9f7;">
                <div class="label">Pemasukan</div>
                <div class="angka" style="color:#3f8ef0;"><?= rupiah($totalMasukTgl) ?></div>
            </div>

            <!-- Total pengeluaran hari itu -->
            <div class="stat-card" style="border-color:#f47c9b;">
                <div class="label">Pengeluaran</div>
                <div class="angka" style="color:#e84a8a;"><?= rupiah($totalKeluarTgl) ?></div>
            </div>

            <!-- Selisih (keuntungan) -->
            <div class="stat-card" style="border-color:#8fd3a0;">
                <div class="label">Selisih / Omzet</div>
                <div class="angka" style="color:#2f9e4f;"><?= rupiah($selisihTgl) ?></div>
            </div>
        </div>

        <!-- =====================================
             FORM HITUNG PERSEDIAAN
             Form ini mengirim data stok fisik per barang
        ===================================== -->
        <form method="POST" action="" id="formClosing">
            <!-- Tanggal ikut terkirim -->
            <input type="hidden" name="tanggal" value="<?= $tanggal ?>">

            <div class="box">
                <h3>&#128221; Hitung Persediaan (<?= $tanggal ?>)</h3>
                <!-- Keterangan singkat cara pakai -->
                <p style="font-size:13px;color:var(--text-soft);margin-bottom:12px;">
                    Cek stok fisik di rak, lalu isi jumlahnya. Selisih dihitung otomatis.
                </p>

                <div class="table-wrap">
                    <table>
                        <tr>
                            <th>Nama Barang</th>
                            <th>Stok Sistem</th>
                            <th>Stok Fisik</th>
                            <th>Selisih</th>
                        </tr>

                        <?php while ($b = $daftarBarang->fetch_assoc()): ?>
                        <!-- Satu baris barang -->
                        <tr>
                            <td><?= htmlspecialchars($b['nama']) ?></td>
                            <td><?= $b['stok'] ?></td>
                            <td>
                                <!-- Input stok fisik, diisi nilai lama bila pernah closing -->
                                <input type="number"
                                       class="stok-input"
                                       name="stok[<?= $b['id'] ?>]"
                                       min="0"
                                       value="<?= $stokLama[$b['nama']] ?? $b['stok'] ?>">
                            </td>
                            <td>
                                <!-- Selisih dihitung manual: fisik - sistem -->
                                <?php $fisik = $stokLama[$b['nama']] ?? $b['stok']; ?>
                                <?php $sel = (int) $fisik - (int) $b['stok']; ?>
                                <?php if ($sel === 0): ?>
                                    <!-- Tidak ada selisih -->
                                    <span class="selisih ok">0</span>
                                <?php else: ?>
                                    <!-- Ada selisih -->
                                    <span class="selisih warn"><?= $sel > 0 ? '+' . $sel : $sel ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </table>
                </div>

                <!-- Tombol simpan hasil hitung -->
                <button type="submit" class="btn" style="margin-top:18px;">Simpan Closingan</button>
            </div>
        </form>

        <!-- =====================================
             RIWAYAT CLOSINGAN (arsip harian)
        ===================================== -->
        <div class="box">
            <h3>&#128190; Riwayat Closingan</h3>
            <div class="table-wrap">
                <table>
                    <tr>
                        <th>Tanggal</th>
                        <th>Pemasukan</th>
                        <th>Pengeluaran</th>
                        <th>Selisih</th>
                        <th>Detail Stok</th>
                        <th>Aksi</th>
                    </tr>

                    <?php while ($r = $riwayat->fetch_assoc()): ?>
                    <!-- Satu baris riwayat closing -->
                    <tr>
                        <td><?= $r['tanggal'] ?></td>
                        <td class="uang-in"><?= rupiah($r['total_pemasukan']) ?></td>
                        <td class="uang-out"><?= rupiah($r['total_pengeluaran']) ?></td>
                        <td class="uang-in"><?= rupiah($r['selisih']) ?></td>

                        <!-- Detail stok bisa dibuka-tutup -->
                        <td>
                            <details style="font-size:12px;">
                                <!-- Klik untuk membuka -->
                                <summary style="cursor:pointer;">Lihat</summary>
                                <?php
                                // Ubah JSON hasil hitung jadi daftar
                                $items = (array) json_decode($r['data_stok'], true);
                                foreach ($items as $item) {
                                    // Tampilkan nama + selisih tiap barang
                                    echo htmlspecialchars($item['nama'])
                                        . ' → fisik ' . $item['stok_fisik']
                                        . ' (sistem ' . $item['stok_sistem'] . ')<br>';
                                }
                                ?>
                            </details>
                        </td>

                        <td>
                            <!-- Tombol hapus closingan -->
                            <a href="hapus.php?tipe=closing&id=<?= $r['id'] ?>"
                               class="btn btn-sm btn-delete"
                               data-konfirmasi="Hapus closingan tanggal <?= $r['tanggal'] ?>?">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </table>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>