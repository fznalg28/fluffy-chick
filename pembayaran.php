<?php
/** Pembayaran: metode yang tersedia, voucher saya (lihat, pakai, tambah, hapus), dan transaksi terakhir. */

require __DIR__ . '/includes/bootstrap.php';

$form   = voucher_form_kosong();
$errors = [];

if (is_post()) {
    csrf_periksa();

    switch (input('aksi')) {
        case 'pakai':
            [$voucher, $error] = voucher_periksa(input('kode'), keranjang_subtotal(keranjang_isi()));
            if ($voucher === null) {
                flash('error', $error);
                redirect('pembayaran.php');
            }
            $_SESSION['voucher_kode'] = $voucher['kode'];
            flash('sukses', 'Voucher ' . $voucher['kode'] . ' dipasang di keranjang.');
            redirect('keranjang.php');

        case 'hapus':
            voucher_hapus((int) input('id'));
            flash('sukses', 'Voucher dihapus.');
            redirect('pembayaran.php');

        case 'tambah':
            $form = [
                'kode'           => input('kode'),
                'jenis'          => input('jenis'),
                'nilai'          => input('nilai'),
                'maks_potongan'  => input('maks_potongan'),
                'min_belanja'    => input('min_belanja'),
                'berlaku_sampai' => input('berlaku_sampai'),
            ];
            $errors = voucher_validasi($form);
            if (!$errors) {
                voucher_simpan($form);
                flash('sukses', 'Voucher ' . strtoupper($form['kode']) . ' ditambahkan.');
                redirect('pembayaran.php');
            }
            break;
    }
}

render('pembayaran', [
    'judul'    => 'Pembayaran',
    'menu'     => 'pembayaran',
    'voucher'  => voucher_aktif_daftar(),
    'form'     => $form,
    'errors'   => $errors,
    'transaksi' => pesanan_terakhir(5),
]);
