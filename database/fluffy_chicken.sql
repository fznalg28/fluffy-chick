-- ============================================================
--  Fluffy Chicken - skema database + data awal
--
--  Cara pakai: buka phpMyAdmin > tab Import > pilih file ini.
--  Database dibuat otomatis. Mengimpor ulang akan MENGHAPUS semua data.
--
--  Aplikasi dimulai dalam keadaan baru: hanya ada menu dan satu profil.
--  Keranjang, pesanan, riwayat, voucher, dan alamat masih kosong.
-- ============================================================

SET NAMES utf8mb4;   -- agar emoji pada menu tersimpan dengan benar

CREATE DATABASE IF NOT EXISTS fluffy_chicken
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fluffy_chicken;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS pesanan_item, pesanan, keranjang, voucher, alamat, menu, pengguna;
SET FOREIGN_KEY_CHECKS = 1;

-- Profil pemilik akun (belum ada login, jadi hanya satu baris)
CREATE TABLE pengguna (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nama          VARCHAR(100) NOT NULL,
  whatsapp      VARCHAR(20)  NOT NULL DEFAULT '',
  email         VARCHAR(120) NOT NULL DEFAULT '',
  notif_status  TINYINT(1)   NOT NULL DEFAULT 1,   -- kabar status pesanan
  notif_promo   TINYINT(1)   NOT NULL DEFAULT 1,   -- info promo dan voucher
  notif_wa      TINYINT(1)   NOT NULL DEFAULT 0,   -- kirim lewat WhatsApp
  notif_email   TINYINT(1)   NOT NULL DEFAULT 0,   -- kirim struk lewat email
  PRIMARY KEY (id)
) ENGINE=InnoDB;

-- Alamat pengantaran. jarak_km = jarak dari outlet, dipakai menghitung ongkir GoSend.
CREATE TABLE alamat (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  pengguna_id INT UNSIGNED NOT NULL,
  label       VARCHAR(40)  NOT NULL,
  alamat      VARCHAR(255) NOT NULL,
  jarak_km    DECIMAL(4,1) NOT NULL,
  utama       TINYINT(1)   NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  CONSTRAINT fk_alamat_pengguna FOREIGN KEY (pengguna_id) REFERENCES pengguna (id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE menu (
  id        INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nama      VARCHAR(100) NOT NULL,
  kategori  ENUM('Ayam','Paket','Pelengkap','Minuman') NOT NULL,
  deskripsi VARCHAR(255) NOT NULL,
  harga     INT UNSIGNED NOT NULL,
  ikon      VARCHAR(8)   NOT NULL,                 -- emoji penanda menu
  label     VARCHAR(20)  NULL,                     -- penanda kecil, mis. "Pedas"
  tersedia  TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE keranjang (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  pengguna_id INT UNSIGNED NOT NULL,
  menu_id     INT UNSIGNED NOT NULL,
  jumlah      INT UNSIGNED NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_keranjang (pengguna_id, menu_id),
  CONSTRAINT fk_keranjang_pengguna FOREIGN KEY (pengguna_id) REFERENCES pengguna (id) ON DELETE CASCADE,
  CONSTRAINT fk_keranjang_menu     FOREIGN KEY (menu_id)     REFERENCES menu (id)     ON DELETE CASCADE
) ENGINE=InnoDB;

-- jenis: persen  = diskon % (nilai = persen, maks_potongan = batas rupiah)
--        nominal = potongan rupiah tetap (nilai)
--        ongkir  = potongan ongkir (maks_potongan = batas rupiah, kosong = gratis penuh)
CREATE TABLE voucher (
  id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  kode           VARCHAR(30)  NOT NULL,
  jenis          ENUM('persen','nominal','ongkir') NOT NULL,
  nilai          INT UNSIGNED NOT NULL DEFAULT 0,
  maks_potongan  INT UNSIGNED NULL,
  min_belanja    INT UNSIGNED NOT NULL DEFAULT 0,
  berlaku_sampai DATE         NULL,
  aktif          TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY uq_voucher_kode (kode)
) ENGINE=InnoDB;

-- Nama dan harga item disalin ke pesanan_item, jadi riwayat tidak berubah walau menu diubah.
CREATE TABLE pesanan (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  no_pesanan    VARCHAR(20)  NOT NULL,             -- contoh: FC-261126-0001
  pengguna_id   INT UNSIGNED NOT NULL,
  status        ENUM('Menunggu bayar','Dimasak','Diantar','Selesai','Dibatalkan') NOT NULL DEFAULT 'Menunggu bayar',
  metode_bayar  ENUM('QRIS','Transfer VA') NOT NULL,
  kode_bayar    VARCHAR(30)  NOT NULL,             -- nomor VA atau kode QRIS
  alamat_label  VARCHAR(40)  NOT NULL,
  alamat_teks   VARCHAR(255) NOT NULL,
  jarak_km      DECIMAL(4,1) NOT NULL,
  catatan       VARCHAR(200) NOT NULL DEFAULT '',
  subtotal      INT UNSIGNED NOT NULL,
  biaya_layanan INT UNSIGNED NOT NULL,
  ongkir        INT UNSIGNED NOT NULL,
  potongan      INT UNSIGNED NOT NULL DEFAULT 0,
  voucher_kode  VARCHAR(30)  NULL,
  total         INT UNSIGNED NOT NULL,
  dibayar_pada  DATETIME     NULL,
  dibuat_pada   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_pesanan_no (no_pesanan),
  KEY idx_pesanan_status (status),
  KEY idx_pesanan_dibuat (dibuat_pada),
  CONSTRAINT fk_pesanan_pengguna FOREIGN KEY (pengguna_id) REFERENCES pengguna (id)
) ENGINE=InnoDB;

CREATE TABLE pesanan_item (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  pesanan_id INT UNSIGNED NOT NULL,
  menu_id    INT UNSIGNED NULL,
  nama       VARCHAR(100) NOT NULL,
  ikon       VARCHAR(8)   NOT NULL,
  harga      INT UNSIGNED NOT NULL,
  jumlah     INT UNSIGNED NOT NULL,
  PRIMARY KEY (id),
  CONSTRAINT fk_item_pesanan FOREIGN KEY (pesanan_id) REFERENCES pesanan (id) ON DELETE CASCADE,
  CONSTRAINT fk_item_menu    FOREIGN KEY (menu_id)    REFERENCES menu (id)    ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------- Data awal ----------------

INSERT INTO pengguna (id, nama) VALUES (1, 'Pelanggan');

INSERT INTO menu (nama, kategori, deskripsi, harga, ikon, label) VALUES
  ('Fluffy Crispy Original', 'Ayam',      'Ayam berbumbu rempah dengan balutan tepung renyah.',          18000, '🍗', NULL),
  ('Fire Chicken',           'Ayam',      'Tepung renyah dengan bumbu cabai bakar.',                      20000, '🔥', 'Pedas'),
  ('Paket Hemat Fluffy',     'Paket',     'Satu potong ayam, nasi putih hangat, dan es teh manis.',       29000, '🍱', 'Hemat'),
  ('French Fries Large',     'Pelengkap', 'Kentang goreng tipis, garing, ditaburi garam laut.',           16000, '🍟', NULL),
  ('Es Teh Manis',           'Minuman',   'Teh seduh segar dengan gula secukupnya, disajikan dingin.',     7000, '🥤', NULL),
  ('Lemon Tea Segar',        'Minuman',   'Teh dengan perasan lemon asli. Asam-manisnya menyegarkan.',    10000, '🍋', NULL);
