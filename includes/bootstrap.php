<?php
/** Dimuat di baris pertama setiap halaman: konfigurasi, koneksi, dan model. */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/helpers.php';

foreach (['pengguna', 'alamat', 'menu', 'keranjang', 'voucher', 'pesanan'] as $model) {
    require_once __DIR__ . '/../models/' . $model . '.php';
}
