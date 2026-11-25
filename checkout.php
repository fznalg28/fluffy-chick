<?php
/** Checkout: pilih alamat, metode bayar (QRIS / Transfer VA), lalu buat pesanan. */

require __DIR__ . '/includes/bootstrap.php';

$isi = keranjang_isi();
if (!$isi) {
    flash('error', 'Keranjang masih kosong. Pilih menu dulu.');
    redirect('produk.php');
}

$daftarAlamat = alamat_daftar();
$kode         = $_SESSION['voucher_kode'] ?? '';

if (is_post()) {
    csrf_periksa();

    $alamat = alamat_cari((int) input('alamat_id'));
    if ($alamat === null) {
        flash('error', 'Pilih alamat pengantaran terlebih dahulu.');
        redirect('checkout.php');
    }

    $catatan = mb_substr(input('catatan'), 0, 200);
    [$pesananId, $error] = pesanan_buat($alamat, input('metode'), $catatan, $kode);

    if ($pesananId === null) {
        flash('error', $error);
        redirect('checkout.php?alamat=' . (int) $alamat['id']);
    }

    unset($_SESSION['voucher_kode']);
    flash('sukses', 'Pesanan dibuat. Selesaikan pembayaran agar pesananmu diproses.');
    redirect('pesanan-detail.php?id=' . $pesananId);
}

// Alamat terpilih: dari ?alamat=, kalau tidak ada pakai alamat utama
$terpilih = alamat_cari((int) query('alamat')) ?? alamat_utama();

[$rincian, $errorVoucher] = pesanan_rincian($isi, $terpilih, $kode);
if ($errorVoucher !== null) {
    unset($_SESSION['voucher_kode']);
    flash('error', $errorVoucher . ' Voucher dilepas.');
    $kode = '';
    [$rincian] = pesanan_rincian($isi, $terpilih, '');
}

render('checkout', [
    'judul'        => 'Checkout',
    'menu'         => 'keranjang',
    'isi'          => $isi,
    'daftarAlamat' => $daftarAlamat,
    'terpilih'     => $terpilih,
    'rincian'      => $rincian,
    'kode'         => $kode,
]);
