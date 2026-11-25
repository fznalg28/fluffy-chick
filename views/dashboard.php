<?php
/** Variabel: $profil, $bulanIni, $voucher, $aktif, $grafik, $favorit, $terakhir. */
$tertinggi = max(array_column($grafik, 'total'));
?>
<section class="hero">
    <div>
        <h1>Halo, <?= e($profil['nama']) ?>!</h1>
        <p>
            Outlet <?= outlet_buka() ? '<strong>buka</strong>' : '<strong>tutup</strong>' ?>
            · <?= sprintf('%02d.00-%02d.00', OUTLET_BUKA, OUTLET_TUTUP) ?> · <?= e(OUTLET_ALAMAT) ?>
        </p>
    </div>
    <div class="hero__aksi">
        <a href="<?= url('produk.php') ?>" class="tombol tombol--kuning">Pesan sekarang</a>
        <?php if ($terakhir): ?>
            <form method="post" action="<?= url('pesanan.php') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="aksi" value="ulang">
                <input type="hidden" name="id" value="<?= (int) $terakhir[0]['id'] ?>">
                <button type="submit" class="tombol tombol--putih">Pesan ulang yang terakhir</button>
            </form>
        <?php endif; ?>
    </div>
</section>

<section class="kartu-statistik">
    <div class="kartu">
        <p class="label">Pesanan bulan ini</p>
        <p class="angka"><?= angka($bulanIni['jumlah']) ?></p>
    </div>
    <div class="kartu">
        <p class="label">Belanja bulan ini</p>
        <p class="angka"><?= rupiah($bulanIni['belanja']) ?></p>
    </div>
    <div class="kartu">
        <p class="label">Voucher aktif</p>
        <p class="angka"><?= count($voucher) ?></p>
    </div>
</section>

<?php if ($voucher): ?>
    <section class="kartu kartu--voucher">
        <div>
            <strong><?= e($voucher[0]['kode']) ?></strong>
            <span><?= e(voucher_deskripsi($voucher[0])) ?></span>
        </div>
        <a href="<?= url('pembayaran.php') ?>" class="tombol tombol--kecil">Lihat voucher</a>
    </section>
<?php endif; ?>

<div class="dua-kolom">
    <section class="kartu">
        <h2>Pesanan berjalan</h2>
        <?php if ($aktif): ?>
            <p class="tebal"><?= e($aktif['no_pesanan']) ?> <span class="badge <?= status_kelas($aktif['status']) ?>"><?= e($aktif['status']) ?></span></p>
            <?php $status = $aktif['status']; require __DIR__ . '/partials/stepper.php'; ?>
            <a href="<?= url('pesanan-detail.php?id=' . (int) $aktif['id']) ?>" class="tombol tombol--kecil">Lihat detail</a>
        <?php else: ?>
            <p class="kosong">Belum ada pesanan yang sedang berjalan.</p>
        <?php endif; ?>
    </section>

    <section class="kartu">
        <h2>Menu favorit</h2>
        <?php if ($favorit): ?>
            <ul class="daftar">
                <?php foreach ($favorit as $m): ?>
                    <li>
                        <span><?= e($m['ikon']) ?> <?= e($m['nama']) ?></span>
                        <span class="redup"><?= angka((int) $m['terjual']) ?> terjual</span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="kosong">Belum ada menu favorit. Pesan dulu, nanti muncul di sini.</p>
        <?php endif; ?>
    </section>
</div>

<section class="kartu">
    <h2>Belanja 6 bulan terakhir</h2>
    <div class="grafik">
        <?php foreach ($grafik as $bulan): ?>
            <?php $tinggi = $tertinggi > 0 ? max(3, (int) round($bulan['total'] / $tertinggi * 100)) : 3; ?>
            <div class="grafik__kolom">
                <span class="grafik__nilai"><?= rupiah($bulan['total']) ?></span>
                <div class="grafik__batang" style="height: <?= $tinggi ?>%"></div>
                <span class="grafik__label"><?= e($bulan['label']) ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="kartu">
    <header class="kartu__kepala">
        <h2>Pesanan terakhir</h2>
        <a href="<?= url('pesanan.php') ?>">Lihat riwayat</a>
    </header>
    <?php if ($terakhir): ?>
        <ul class="daftar">
            <?php foreach ($terakhir as $p): ?>
                <li>
                    <a href="<?= url('pesanan-detail.php?id=' . (int) $p['id']) ?>">
                        <strong><?= e($p['no_pesanan']) ?></strong>
                        <span class="redup"><?= e(tanggal_waktu($p['dibuat_pada'])) ?></span>
                    </a>
                    <span><?= rupiah((int) $p['total']) ?> <span class="badge <?= status_kelas($p['status']) ?>"><?= e($p['status']) ?></span></span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p class="kosong">Belum ada pesanan. <a href="<?= url('produk.php') ?>">Lihat menu</a> untuk mulai memesan.</p>
    <?php endif; ?>
</section>
