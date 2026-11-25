<?php
/** Variabel: $voucher, $form, $errors, $transaksi. */
?>
<div class="judul-halaman">
    <h1>Pembayaran</h1>
    <p>Metode pembayaran, voucher, dan transaksi terakhirmu.</p>
</div>

<div class="dua-kolom">
    <section class="kartu">
        <h2>Metode pembayaran</h2>
        <ul class="daftar">
            <li><span><strong>QRIS</strong><br><span class="redup">Scan dengan aplikasi pembayaran apa saja.</span></span></li>
            <li><span><strong>Transfer Virtual Account</strong><br><span class="redup">Nomor VA dibuat otomatis setiap pesanan.</span></span></li>
        </ul>
        <p class="redup kecil">Dipilih saat checkout.</p>
    </section>

    <section class="kartu">
        <header class="kartu__kepala">
            <h2>Voucher saya</h2>
            <span class="badge badge--masak"><?= count($voucher) ?> aktif</span>
        </header>

        <?php if (!$voucher): ?>
            <p class="kosong">Belum ada voucher aktif.</p>
        <?php endif; ?>

        <?php foreach ($voucher as $v): ?>
            <div class="voucher">
                <div>
                    <strong class="voucher__kode"><?= e($v['kode']) ?></strong>
                    <span><?= e(voucher_deskripsi($v)) ?></span>
                </div>
                <div class="aksi">
                    <form method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="aksi" value="pakai">
                        <input type="hidden" name="kode" value="<?= e($v['kode']) ?>">
                        <button type="submit" class="tombol tombol--kecil tombol--kuning">Pakai</button>
                    </form>
                    <form method="post" data-konfirmasi="Hapus voucher <?= e($v['kode']) ?>?">
                        <?= csrf_field() ?>
                        <input type="hidden" name="aksi" value="hapus">
                        <input type="hidden" name="id" value="<?= (int) $v['id'] ?>">
                        <button type="submit" class="bulat" aria-label="Hapus voucher <?= e($v['kode']) ?>">🗑</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>

        <details class="tambah" <?= $errors ? 'open' : '' ?>>
            <summary>+ Tambah voucher</summary>
            <?php foreach ($errors as $err): ?><div class="notif notif--error"><?= e($err) ?></div><?php endforeach; ?>
            <form method="post" class="form">
                <?= csrf_field() ?>
                <input type="hidden" name="aksi" value="tambah">
                <label>Kode voucher
                    <input type="text" name="kode" maxlength="30" value="<?= e($form['kode']) ?>" required>
                </label>
                <label>Jenis
                    <select name="jenis">
                        <?php foreach (JENIS_VOUCHER as $nilai => $nama): ?>
                            <option value="<?= $nilai ?>" <?= $form['jenis'] === $nilai ? 'selected' : '' ?>><?= e($nama) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Nilai (persen atau rupiah, kosongkan untuk gratis ongkir)
                    <input type="number" name="nilai" min="0" value="<?= e($form['nilai']) ?>">
                </label>
                <label>Maksimal potongan (Rp, opsional)
                    <input type="number" name="maks_potongan" min="0" value="<?= e($form['maks_potongan']) ?>">
                </label>
                <label>Minimal belanja (Rp)
                    <input type="number" name="min_belanja" min="0" value="<?= e($form['min_belanja']) ?>">
                </label>
                <label>Berlaku sampai (opsional)
                    <input type="date" name="berlaku_sampai" value="<?= e($form['berlaku_sampai']) ?>">
                </label>
                <button type="submit" class="tombol">Simpan voucher</button>
            </form>
        </details>
    </section>
</div>

<section class="kartu">
    <header class="kartu__kepala">
        <h2>Transaksi terakhir</h2>
        <a href="<?= url('pesanan.php') ?>">Lihat riwayat</a>
    </header>
    <?php if ($transaksi): ?>
        <ul class="daftar">
            <?php foreach ($transaksi as $t): ?>
                <li>
                    <a href="<?= url('pesanan-detail.php?id=' . (int) $t['id']) ?>">
                        <strong><?= e($t['no_pesanan']) ?></strong>
                        <span class="redup"><?= e(tanggal_waktu($t['dibuat_pada'])) ?> · <?= e(METODE_BAYAR[$t['metode_bayar']]) ?></span>
                    </a>
                    <span><?= rupiah((int) $t['total']) ?> <span class="badge <?= status_kelas($t['status']) ?>"><?= e($t['status']) ?></span></span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p class="kosong">Belum ada transaksi.</p>
    <?php endif; ?>
</section>
