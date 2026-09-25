-- =============================================
-- 1. Buat database baru bernama "db_kasir"
-- =============================================
CREATE DATABASE IF NOT EXISTS db_kasir;
USE db_kasir;

-- =============================================
-- 2. TABEL USERS (untuk sistem login)
--    Kolom password menyimpan hasil password_hash()
-- =============================================
CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,  -- nomor unik user
    username   VARCHAR(50) NOT NULL UNIQUE,     -- nama pengguna, tidak boleh sama
    password   VARCHAR(255) NOT NULL,           -- kata sandi yang sudah di-hash
    nama       VARCHAR(100),                    -- nama lengkap pengguna
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP -- tanggal akun dibuat
);

-- =============================================
-- 3. TABEL BARANG (data produk + stok)
-- =============================================
CREATE TABLE IF NOT EXISTS barang (
    id         INT AUTO_INCREMENT PRIMARY KEY,  -- nomor unik barang
    nama       VARCHAR(100) NOT NULL,           -- nama barang
    harga      INT NOT NULL,                    -- harga jual per item
    stok       INT NOT NULL DEFAULT 0,          -- jumlah persediaan
    gambar     VARCHAR(255),                    -- nama file gambar yang di-upload
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP -- tanggal barang dimasukkan
);

-- =============================================
-- 4. TABEL PEMASUKAN (catatan penjualan)
--    Setiap penjualan mengurangi stok barang
-- =============================================
CREATE TABLE IF NOT EXISTS pemasukan (
    id          INT AUTO_INCREMENT PRIMARY KEY,  -- nomor unik transaksi
    id_barang   INT,                             -- id barang yang dijual
    nama_barang VARCHAR(100) NOT NULL,           -- salinan nama barang
    qty         INT NOT NULL,                    -- jumlah yang terjual
    harga       INT NOT NULL,                    -- harga per item saat itu
    total       INT NOT NULL,                    -- qty x harga
    tanggal     DATE NOT NULL,                   -- tanggal transaksi
    catatan     VARCHAR(255),                    -- keterangan tambahan
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =============================================
-- 5. TABEL PENGELUARAN (catatan biaya/cost)
-- =============================================
CREATE TABLE IF NOT EXISTS pengeluaran (
    id          INT AUTO_INCREMENT PRIMARY KEY,  -- nomor unik pengeluaran
    keterangan  VARCHAR(255) NOT NULL,           -- apa saja pengeluarannya
    jumlah      INT NOT NULL,                    -- nominal pengeluaran
    tanggal     DATE NOT NULL,                   -- tanggal pengeluaran
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =============================================
-- 6. TABEL CLOSING (laporan tutup buku + hasil hitung persediaan)
--    data_stok  menyimpan hasil hitung fisik dalam bentuk JSON
-- =============================================
CREATE TABLE IF NOT EXISTS closing (
    id              INT AUTO_INCREMENT PRIMARY KEY,  -- nomor unik closing
    tanggal         DATE NOT NULL UNIQUE,            -- satu closing per tanggal
    total_pemasukan INT NOT NULL DEFAULT 0,          -- jumlah pemasukan hari itu
    total_pengeluaran INT NOT NULL DEFAULT 0,        -- jumlah pengeluaran hari itu
    selisih         INT NOT NULL DEFAULT 0,          -- pemasukan - pengeluaran
    data_stok       TEXT,                            -- hasil hitung stok format JSON
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =============================================
-- 7. AKUN LOGIN AWAL
--    Username : admin
--    Password : 123456 (tersimpan sebagai hash)
-- =============================================
INSERT INTO users (username, password, nama) VALUES
('admin', '$2y$10$PrEGx5IIjPuMzrLGRP0Ttedex0wAHngQyv65zdSe67iRZMMee/ZOe', 'Admin Kasir');