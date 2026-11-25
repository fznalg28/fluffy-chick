<?php
/**
 * Pengaturan aplikasi Fluffy Chicken.
 * Semua angka bisnis (tarif, biaya layanan, jam buka) diubah di sini.
 */

date_default_timezone_set('Asia/Jakarta');

const APP_NAME    = 'Fluffy Chicken';
const APP_TAGLINE = 'Crispy, Juicy dan Fluffy';

// Aplikasi belum memakai login: semua data milik satu pengguna ini
const PENGGUNA_ID = 1;

// Outlet
const OUTLET_ALAMAT = 'Jl. Melati Raya No. 8';
const OUTLET_BUKA   = 10;   // jam buka (pukul 10.00)
const OUTLET_TUTUP  = 22;   // jam tutup (pukul 22.00)

// Kontak bantuan (halaman FAQ)
const KONTAK_WA      = '6281200000000';
const KONTAK_TELEPON = '0800-1234-567';
const KONTAK_EMAIL   = 'halo@fluffychicken.id';

// Biaya
const BIAYA_LAYANAN = 2000;

// Pengiriman GoSend: tarif dasar sampai KM_DASAR, lalu tambahan per km (dibulatkan ke atas)
const GOSEND_TARIF_DASAR  = 10000;
const GOSEND_KM_DASAR     = 3;
const GOSEND_TARIF_PER_KM = 1500;
const GOSEND_KM_MAKS      = 25;

// Keranjang
const KERANJANG_MAKS_PER_ITEM = 20;

// Kategori menu (urutan tab di halaman Produk)
const KATEGORI_MENU = ['Ayam', 'Paket', 'Pelengkap', 'Minuman'];

// Metode pembayaran yang tersedia saat checkout
const METODE_BAYAR = [
    'QRIS'        => 'QRIS',
    'Transfer VA' => 'Transfer Virtual Account',
];

// Status pesanan berurutan. Dibatalkan hanya bisa dari Menunggu bayar.
const STATUS_PESANAN = ['Menunggu bayar', 'Dimasak', 'Diantar', 'Selesai', 'Dibatalkan'];

session_start();
