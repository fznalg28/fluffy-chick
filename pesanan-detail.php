<?php
/**
 * Detail pesanan: instruksi bayar, tahapan, dan aksi.
 * Catatan: belum ada payment gateway maupun panel outlet, jadi "konfirmasi bayar" dan
 * "lanjutkan status" adalah simulasi. Di sinilah nantinya gateway/outlet menyambung.
 */

require __DIR__ . '/includes/bootstrap.php';

$id      = (int) query('id', (string) input('id'));
$pesanan = pesanan_cari($id);

if ($pesanan === null) {
    flash('error', 'Pesanan tidak ditemukan.');
    redirect('pesanan.php');
}

if (is_post()) {
    csrf_periksa();

    switch (input('aksi')) {
        case 'bayar':
            pesanan_bayar($id)
                ? flash('sukses', 'Pembayaran diterima. Pesananmu sedang dimasak.')
                : flash('error', 'Pesanan ini tidak sedang menunggu pembayaran.');
            break;
        case 'lanjut':
            pesanan_lanjut($id)
                ? flash('sukses', 'Status pesanan diperbarui.')
                : flash('error', 'Status pesanan tidak bisa dimajukan lagi.');
            break;
        case 'batal':
            pesanan_batal($id)
                ? flash('sukses', 'Pesanan dibatalkan.')
                : flash('error', 'Hanya pesanan yang belum dibayar yang bisa dibatalkan.');
            break;
        case 'ulang':
            if (pesanan_ulang($id) > 0) {
                flash('sukses', 'Item pesanan dimasukkan ke keranjang.');
                redirect('keranjang.php');
            }
            flash('error', 'Menu pada pesanan itu sudah tidak tersedia.');
            break;
    }

    redirect('pesanan-detail.php?id=' . $id);
}

render('pesanan-detail', [
    'judul'   => $pesanan['no_pesanan'],
    'menu'    => 'riwayat',
    'pesanan' => $pesanan,
    'item'    => pesanan_item($id),
]);
