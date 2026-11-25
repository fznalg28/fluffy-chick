<?php
/** Variabel: $pesanan, $item. */
$status = $pesanan['status'];
?>
<div class="judul-halaman">
    <h1><?= e($pesanan['no_pesanan']) ?> <span class="badge <?= status_kelas($status) ?>"><?= e($status) ?></span></h1>
    <p><?= e(tanggal_waktu($pesanan['dibuat_pada'])) ?> · <a href="<?= url('pesanan.php') ?>">Kembali ke riwayat</a></p>
</div>

<?php if ($status === 'Menunggu bayar'): ?>
    <section class="kartu kartu--bayar">
        <h2>Selesaikan pembayaran</h2>
        <?php if ($pesanan['metode_bayar'] === 'QRIS'): ?>
            <p>Bayar <strong><?= rupiah((int) $pesanan['total']) ?></strong> lewat QRIS menggunakan aplikasi pembayaran apa saja.</p>
            <div class="kode-bayar">
                <span class="redup">Kode QRIS</span>
                <strong><?= e($pesanan['kode_bayar']) ?></strong>
            </div>
        <?php else: ?>
            <p>Transfer <strong><?= rupiah((int) $pesanan['total']) ?></strong> ke nomor Virtual Account berikut.</p>
            <div class="kode-bayar">
                <span class="redup">Nomor Virtual Account</span>
                <strong><?= e(trim(chunk_split($pesanan['kode_bayar'], 4, ' '))) ?></strong>
            </div>
        <?php endif; ?>

        <div class="aksi">
            <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="aksi" value="bayar">
                <button type="submit" class="tombol">Saya sudah bayar (simulasi)</button>
            </form>
            <form method="post" data-konfirmasi="Batalkan pesanan ini?">
                <?= csrf_field() ?>
                <input type="hidden" name="aksi" value="batal">
                <button type="submit" class="tombol tombol--garis">Batalkan pesanan</button>
            </form>
        </div>
        <p class="redup kecil">Belum terhubung ke payment gateway, jadi konfirmasi pembayaran dilakukan manual lewat tombol di atas.</p>
    </section>
<?php elseif ($status !== 'Dibatalkan'): ?>
    <section class="kartu">
        <h2>Status pengantaran</h2>
        <?php require __DIR__ . '/partials/stepper.php'; ?>
        <?php if ($status !== 'Selesai'): ?>
            <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="aksi" value="lanjut">
                <button type="submit" class="tombol tombol--kecil tombol--garis">
                    Lanjutkan ke <?= $status === 'Dimasak' ? 'Diantar' : 'Selesai' ?> (simulasi outlet)
                </button>
            </form>
        <?php endif; ?>
    </section>
<?php endif; ?>

<div class="dua-kolom">
    <section class="kartu">
        <h2>Item pesanan</h2>
        <ul class="daftar">
            <?php foreach ($item as $i): ?>
                <li>
                    <span><?= e($i['ikon']) ?> <?= (int) $i['jumlah'] ?>x <?= e($i['nama']) ?></span>
                    <span><?= rupiah((int) $i['harga'] * (int) $i['jumlah']) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
        <dl class="rincian">
            <div><dt>Subtotal</dt><dd><?= rupiah((int) $pesanan['subtotal']) ?></dd></div>
            <div><dt>Biaya layanan</dt><dd><?= rupiah((int) $pesanan['biaya_layanan']) ?></dd></div>
            <div><dt>Ongkir GoSend</dt><dd><?= rupiah((int) $pesanan['ongkir']) ?></dd></div>
            <?php if ((int) $pesanan['potongan'] > 0): ?>
                <div class="hemat"><dt>Voucher <?= e($pesanan['voucher_kode']) ?></dt><dd>-<?= rupiah((int) $pesanan['potongan']) ?></dd></div>
            <?php endif; ?>
            <div class="total"><dt>Total</dt><dd><?= rupiah((int) $pesanan['total']) ?></dd></div>
        </dl>
    </section>

    <section class="kartu">
        <h2>Pengiriman dan pembayaran</h2>
        <dl class="info">
            <div><dt>Alamat</dt><dd><strong><?= e($pesanan['alamat_label']) ?></strong><br><?= e($pesanan['alamat_teks']) ?> (<?= e($pesanan['jarak_km']) ?> km)</dd></div>
            <div><dt>Kurir</dt><dd>GoSend</dd></div>
            <div><dt>Pembayaran</dt><dd><?= e(METODE_BAYAR[$pesanan['metode_bayar']]) ?><?= $pesanan['dibayar_pada'] ? ' · dibayar ' . e(tanggal_waktu($pesanan['dibayar_pada'])) : '' ?></dd></div>
            <?php if ($pesanan['catatan'] !== ''): ?>
                <div><dt>Catatan</dt><dd><?= e($pesanan['catatan']) ?></dd></div>
            <?php endif; ?>
        </dl>
        <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="aksi" value="ulang">
            <button type="submit" class="tombol tombol--kuning">Pesan lagi</button>
        </form>
    </section>
</div>
