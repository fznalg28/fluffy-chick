<?php
/**
 * Kerangka halaman: sidebar, topbar, dan pesan flash.
 * Variabel dari render(): $judul, $menu, $jumlahKeranjang, $alamatUtama, $profil, $pesanFlash, $cari.
 */
$navigasi = [
    'dashboard'  => ['Dashboard',        'index.php',       '🏠'],
    'produk'     => ['Produk',           'produk.php',      '🍗'],
    'keranjang'  => ['Keranjang',        'keranjang.php',   '🛒'],
    'riwayat'    => ['Riwayat pesanan',  'pesanan.php',     '🧾'],
    'pembayaran' => ['Pembayaran',       'pembayaran.php',  '💳'],
    'pengiriman' => ['Pengiriman',       'pengiriman.php',  '🛵'],
    'faq'        => ['FAQ',              'faq.php',         '❓'],
    'pengaturan' => ['Pengaturan',       'pengaturan.php',  '⚙️'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($judul) ?> - <?= APP_NAME ?></title>
    <link rel="icon" type="image/png" href="<?= aset('img/favicon.png') ?>">
    <link rel="stylesheet" href="<?= aset('css/style.css') ?>">
</head>
<body>
<div class="app">

    <aside class="sidebar">
        <a href="<?= url('index.php') ?>" class="sidebar__logo">
            <img src="<?= aset('img/logo.png') ?>" alt="<?= APP_NAME ?>">
        </a>
        <nav class="sidebar__nav">
            <?php foreach ($navigasi as $kunci => [$label, $alamat, $ikon]): ?>
                <a href="<?= url($alamat) ?>" class="<?= $menu === $kunci ? 'aktif' : '' ?>">
                    <span class="ikon"><?= $ikon ?></span>
                    <span><?= e($label) ?></span>
                    <?php if ($kunci === 'keranjang' && $jumlahKeranjang > 0): ?>
                        <span class="lencana"><?= $jumlahKeranjang ?></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="sidebar__bantuan">
            <strong>Butuh bantuan?</strong>
            <span>Tim kami siap membalas setiap hari, <?= sprintf('%02d.00-%02d.00', OUTLET_BUKA, OUTLET_TUTUP) ?>.</span>
        </div>
    </aside>

    <div class="utama">
        <header class="topbar">
            <form action="<?= url('produk.php') ?>" method="get" class="topbar__cari">
                <input type="search" name="q" value="<?= e($cari) ?>" placeholder="Cari menu: ayam, paket, minuman..." aria-label="Cari menu">
            </form>
            <?php if ($alamatUtama): ?>
                <a href="<?= url('pengaturan.php#alamat') ?>" class="topbar__alamat" title="<?= e($alamatUtama['alamat']) ?>">
                    📍 Dikirim ke <strong><?= e($alamatUtama['label']) ?></strong> · <?= e($alamatUtama['alamat']) ?>
                </a>
            <?php else: ?>
                <a href="<?= url('pengaturan.php#alamat') ?>" class="topbar__alamat">📍 Belum ada alamat. <strong>Tambah alamat</strong></a>
            <?php endif; ?>
            <a href="<?= url('keranjang.php') ?>" class="topbar__ikon" aria-label="Keranjang">
                🛒<?php if ($jumlahKeranjang > 0): ?><span class="lencana"><?= $jumlahKeranjang ?></span><?php endif; ?>
            </a>
            <a href="<?= url('pengaturan.php') ?>" class="avatar" aria-label="Pengaturan"><?= e(inisial($profil['nama'])) ?></a>
        </header>

        <main class="konten">
            <?php foreach ($pesanFlash as $f): ?>
                <div class="notif notif--<?= e($f['jenis']) ?>"><?= e($f['pesan']) ?></div>
            <?php endforeach; ?>
