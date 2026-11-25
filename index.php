<?php
/** Dashboard: ringkasan pesanan, voucher, belanja bulanan, menu favorit, dan pesanan terakhir. */

require __DIR__ . '/includes/bootstrap.php';

$voucher  = voucher_aktif_daftar();
$terakhir = pesanan_terakhir(3);

render('dashboard', [
    'judul'    => 'Dashboard',
    'menu'     => 'dashboard',
    'bulanIni' => pesanan_ringkasan_bulan(),
    'voucher'  => $voucher,
    'aktif'    => pesanan_aktif(),
    'grafik'   => pesanan_belanja_bulanan(6),
    'favorit'  => menu_favorit(4),
    'terakhir' => $terakhir,
]);
