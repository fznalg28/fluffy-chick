<?php
/** Variabel: $aktif, $jarak. */
?>
<div class="judul-halaman">
    <h1>Pengiriman</h1>
    <p>Pesanan diantar lewat GoSend dari outlet kami.</p>
</div>

<div class="dua-kolom">
    <section class="kartu">
        <h2>Lacak pesanan</h2>
        <?php if ($aktif): ?>
            <p class="tebal"><?= e($aktif['no_pesanan']) ?> <span class="badge <?= status_kelas($aktif['status']) ?>"><?= e($aktif['status']) ?></span></p>
            <p class="redup">Ke <?= e($aktif['alamat_label']) ?> · GoSend · estimasi <?= e(gosend_estimasi((float) $aktif['jarak_km'])) ?></p>
            <?php $status = $aktif['status']; require __DIR__ . '/partials/stepper.php'; ?>
            <a href="<?= url('pesanan-detail.php?id=' . (int) $aktif['id']) ?>" class="tombol tombol--kecil">Lihat detail</a>
        <?php else: ?>
            <p class="kosong">Belum ada pesanan yang sedang diantar.</p>
        <?php endif; ?>
    </section>

    <section class="kartu">
        <h2>Cek estimasi ongkir</h2>
        <form method="get" class="baris-form">
            <label for="jarak">Jarak dari outlet (km)</label>
            <input type="number" id="jarak" name="jarak" min="0.5" max="<?= GOSEND_KM_MAKS ?>" step="0.5" value="<?= e((string) $jarak) ?>">
            <button type="submit" class="tombol tombol--kecil">Hitung</button>
        </form>
        <dl class="rincian">
            <div><dt>GoSend</dt><dd><?= rupiah(gosend_ongkir($jarak)) ?></dd></div>
            <div><dt>Estimasi tiba</dt><dd><?= e(gosend_estimasi($jarak)) ?></dd></div>
        </dl>
        <p class="redup kecil">Ongkir final mengikuti jarak alamat dan ditampilkan di halaman checkout.</p>
    </section>
</div>

<section class="kartu">
    <h2>GoSend</h2>
    <dl class="info">
        <div><dt>Jangkauan</dt><dd>Hingga <?= GOSEND_KM_MAKS ?> km dari outlet</dd></div>
        <div><dt>Tarif</dt><dd><?= rupiah(GOSEND_TARIF_DASAR) ?> (sampai <?= GOSEND_KM_DASAR ?> km), lalu +<?= rupiah(GOSEND_TARIF_PER_KM) ?> setiap km berikutnya</dd></div>
        <div><dt>Jam kirim</dt><dd><?= sprintf('%02d.00-%02d.00', OUTLET_BUKA, OUTLET_TUTUP) ?></dd></div>
        <div><dt>Outlet</dt><dd><?= e(OUTLET_ALAMAT) ?></dd></div>
        <div><dt>Pelacakan</dt><dd>Lewat aplikasi Gojek</dd></div>
    </dl>
</section>
