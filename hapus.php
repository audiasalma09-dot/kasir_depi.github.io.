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
// 3. Ambil type & id dari URL, paksa jadi aman
// =============================================
$tipe = $_GET['tipe'] ?? '';
$id   = (int) ($_GET['id'] ?? 0);

// =============================================
// 4. HAPUS BARANG
//    Ikut menghapus file gambar di folder uploads
// =============================================
if ($tipe === 'barang') {
    // Cari data barang dulu biar tahu nama gambar
    $data = $conn->query("SELECT * FROM barang WHERE id = $id")->fetch_assoc();

    if ($data) {
        // Hapus file gambar dari folder uploads kalau ada
        if ($data['gambar'] && file_exists('uploads/' . $data['gambar'])) {
            unlink('uploads/' . $data['gambar']);
        }
        // Hapus data dari tabel
        $conn->query("DELETE FROM barang WHERE id = $id");
    }
}

// =============================================
// 5. HAPUS PEMASUKAN (catatan penjualan)
//    Stok barang dikembalikan karena transaksi dibatalkan
// =============================================
elseif ($tipe === 'pemasukan') {
    // Cari data transaksinya dulu
    $data = $conn->query("SELECT * FROM pemasukan WHERE id = $id")->fetch_assoc();

    if ($data) {
        // Stok barang dinaikkan lagi sesuai qty yang dibatalkan
        $conn->query("UPDATE barang SET stok = stok + {$data['qty']} WHERE id = {$data['id_barang']}");
        // Hapus catatan pemasukan
        $conn->query("DELETE FROM pemasukan WHERE id = $id");
    }
}

// =============================================
// 6. HAPUS PENGELUARAN
// =============================================
elseif ($tipe === 'pengeluaran') {
    $conn->query("DELETE FROM pengeluaran WHERE id = $id");
}

// =============================================
// 7. HAPUS CLOSINGAN (laporan tutup buku)
// =============================================
elseif ($tipe === 'closing') {
    $conn->query("DELETE FROM closing WHERE id = $id");
}

// =============================================
// 8. Kembali ke halaman asal (via referer atau dashboard)
// =============================================
$kembali = $_SERVER['HTTP_REFERER'] ?? 'index.php';
header('Location: ' . $kembali);
exit;