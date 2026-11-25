<?php
/** Pengiriman: pelacakan pesanan berjalan, estimasi ongkir GoSend, dan info layanan. */

require __DIR__ . '/includes/bootstrap.php';

$jarak = (float) str_replace(',', '.', query('jarak', '3'));
$jarak = max(0.5, min($jarak, GOSEND_KM_MAKS));

render('pengiriman', [
    'judul' => 'Pengiriman',
    'menu'  => 'pengiriman',
    'aktif' => pesanan_aktif(),
    'jarak' => $jarak,
]);
