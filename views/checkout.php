<?php
/** Variabel: $isi, $daftarAlamat, $terpilih, $rincian, $kode. */
?>
<div class="judul-halaman">
    <h1>Checkout</h1>
    <p>Pilih alamat dan cara bayar, lalu buat pesanan.</p>
</div>

<?php if (!$daftarAlamat): ?>
    <div class="kartu kosong-besar">
        <p class="ikon-besar">📍</p>
        <p class="tebal">Belum ada alamat pengantaran</p>
        <p class="redup">Tambahkan alamat dulu supaya ongkir GoSend bisa dihitung.</p>
        <a href="<?= url('pengaturan.php#alamat') ?>" class="tombol">Tambah alamat</a>
    </div>
<?php return; endif; ?>

<div class="dua-kolom dua-kolom--lebar">
    <div>
        <section class="kartu">
            <h2>1. Alamat pengantaran</h2>
            <form method="get" class="baris-form">
                <label for="alamat" class="sr-only">Alamat</label>
                <select name="alamat" id="alamat" onchange="this.form.submit()">
                    <?php foreach ($daftarAlamat as $a): ?>
                        <option value="<?= (int) $a['id'] ?>" <?= $terpilih && (int) $terpilih['id'] === (int) $a['id'] ? 'selected' : '' ?>>
                            <?= e($a['label']) ?> · <?= e($a['jarak_km']) ?> km
                        </option>
                    <?php endforeach; ?>
                </select>
                <noscript><button type="submit" class="tombol tombol--kecil">Pilih</button></noscript>
            </form>
            <p class="redup"><?= e($terpilih['alamat']) ?></p>
        </section>

        <form method="post" id="form-checkout">
            <?= csrf_field() ?>
            <input type="hidden" name="alamat_id" value="<?= (int) $terpilih['id'] ?>">

            <section class="kartu">
                <h2>2. Pengiriman</h2>
                <p><strong>GoSend</strong> · estimasi <?= e(gosend_estimasi((float) $terpilih['jarak_km'])) ?> ·
                    ongkir <?= rupiah($rincian['ongkir']) ?></p>
            </section>

            <section class="kartu">
                <h2>3. Metode pembayaran</h2>
                <?php foreach (METODE_BAYAR as $nilai => $nama): ?>
                    <label class="pilihan">
                        <input type="radio" name="metode" value="<?= e($nilai) ?>" <?= $nilai === 'QRIS' ? 'checked' : '' ?>>
                        <span><?= e($nama) ?></span>
                    </label>
                <?php endforeach; ?>
                <p class="redup">Kode pembayaran ditampilkan setelah pesanan dibuat.</p>
            </section>

            <section class="kartu">
                <h2>4. Catatan (opsional)</h2>
                <label for="catatan" class="sr-only">Catatan untuk pesanan</label>
                <textarea name="catatan" id="catatan" rows="2" maxlength="200" placeholder="Contoh: tanpa sambal, tolong telepon dulu"></textarea>
            </section>
        </form>
    </div>

    <section class="kartu">
        <h2>Ringkasan</h2>
        <ul class="daftar">
            <?php foreach ($isi as $item): ?>
                <li>
                    <span><?= e($item['ikon']) ?> <?= (int) $item['jumlah'] ?>x <?= e($item['nama']) ?></span>
                    <span><?= rupiah((int) $item['harga'] * (int) $item['jumlah']) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
        <dl class="rincian">
            <div><dt>Subtotal</dt><dd><?= rupiah($rincian['subtotal']) ?></dd></div>
            <div><dt>Biaya layanan</dt><dd><?= rupiah($rincian['biaya_layanan']) ?></dd></div>
            <div><dt>Ongkir GoSend</dt><dd><?= rupiah($rincian['ongkir']) ?></dd></div>
            <?php if ($rincian['potongan'] > 0): ?>
                <div class="hemat"><dt>Voucher <?= e($kode) ?></dt><dd>-<?= rupiah($rincian['potongan']) ?></dd></div>
            <?php endif; ?>
            <div class="total"><dt>Total bayar</dt><dd><?= rupiah($rincian['total']) ?></dd></div>
        </dl>
        <button type="submit" form="form-checkout" class="tombol tombol--penuh">Buat pesanan</button>
        <a href="<?= url('keranjang.php') ?>" class="tombol tombol--garis tombol--penuh">Kembali ke keranjang</a>
    </section>
</div>
