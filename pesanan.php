<?php
/** Riwayat pesanan dengan filter status, dan aksi "Pesan lagi". */

require __DIR__ . '/includes/bootstrap.php';

if (is_post() && input('aksi') === 'ulang') {
    csrf_periksa();
    $ditambah = pesanan_ulang((int) input('id'));

    if ($ditambah > 0) {
        flash('sukses', 'Item pesanan sebelumnya dimasukkan ke keranjang.');
        redirect('keranjang.php');
    }
    flash('error', 'Menu pada pesanan itu sudah tidak tersedia.');
    redirect('pesanan.php');
}

$status = query('status');
if (!in_array($status, STATUS_PESANAN, true)) {
    $status = '';
}

render('pesanan', [
    'judul'  => 'Riwayat pesanan',
    'menu'   => 'riwayat',
    'daftar' => pesanan_daftar($status),
    'status' => $status,
    'hitung' => pesanan_hitung_status(),
]);
