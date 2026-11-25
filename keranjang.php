<?php
/** Keranjang: tambah/ubah/hapus item, pasang voucher, dan ringkasan biaya. */

require __DIR__ . '/includes/bootstrap.php';

if (is_post()) {
    csrf_periksa();
    $aksi   = input('aksi');
    $menuId = (int) input('menu_id');

    switch ($aksi) {
        case 'tambah':
            $item = menu_cari_id($menuId);
            if ($item === null) {
                flash('error', 'Menu tidak tersedia.');
            } else {
                keranjang_tambah($menuId);
                flash('sukses', $item['nama'] . ' ditambahkan ke keranjang.');
            }
            redirect(kembali_aman(input('kembali'), 'keranjang.php'));

        case 'atur':
            keranjang_atur($menuId, (int) input('jumlah'));
            break;

        case 'hapus':
            keranjang_hapus($menuId);
            break;

        case 'kosongkan':
            keranjang_kosongkan();
            unset($_SESSION['voucher_kode']);
            flash('sukses', 'Keranjang dikosongkan.');
            break;

        case 'voucher_pakai':
            $kode = strtoupper(input('kode'));
            [$voucher, $error] = voucher_periksa($kode, keranjang_subtotal(keranjang_isi()));
            if ($voucher === null) {
                flash('error', $error);
            } else {
                $_SESSION['voucher_kode'] = $voucher['kode'];
                flash('sukses', 'Voucher ' . $voucher['kode'] . ' dipasang.');
            }
            break;

        case 'voucher_lepas':
            unset($_SESSION['voucher_kode']);
            flash('sukses', 'Voucher dilepas.');
            break;
    }

    redirect('keranjang.php');
}

$isi    = keranjang_isi();
$kode   = $_SESSION['voucher_kode'] ?? '';
$alamat = alamat_utama();

[$rincian, $errorVoucher] = pesanan_rincian($isi, $alamat, $kode);
if ($errorVoucher !== null) {
    // Voucher yang terpasang sudah tidak memenuhi syarat (mis. belanja berkurang): lepas otomatis
    unset($_SESSION['voucher_kode']);
    flash('error', $errorVoucher . ' Voucher dilepas.');
    $kode = '';
    [$rincian] = pesanan_rincian($isi, $alamat, '');
}

render('keranjang', [
    'judul'    => 'Keranjang',
    'menu'     => 'keranjang',
    'isi'      => $isi,
    'rincian'  => $rincian,
    'kode'     => $kode,
    'alamat'   => $alamat,
    'voucherAktif' => voucher_aktif_daftar(),
]);
