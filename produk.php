<?php
/** Produk: daftar menu dengan filter kategori, pencarian (dari topbar), dan pengurutan. */

require __DIR__ . '/includes/bootstrap.php';

$kategori = query('kategori');
$cari     = query('q');
$urut     = query('urut', 'populer');

if (!in_array($kategori, KATEGORI_MENU, true)) {
    $kategori = '';
}

$hitung = menu_hitung_kategori($cari);

render('produk', [
    'judul'    => 'Produk',
    'menu'     => 'produk',
    'daftar'   => menu_daftar($kategori, $cari, $urut),
    'kategori' => $kategori,
    'urut'     => $urut,
    'hitung'   => $hitung,
    'semua'    => array_sum($hitung),
]);
