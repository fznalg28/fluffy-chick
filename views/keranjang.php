<?php
/** Variabel: $isi, $rincian, $kode, $alamat, $voucherAktif. */
?>
<div class="judul-halaman">
    <h1>Keranjang</h1>
    <p><?= $isi ? array_sum(array_column($isi, 'jumlah')) . ' item siap dipesan.' : 'Pesananmu tampil di sini sebelum checkout.' ?></p>
</div>

<?php if (!$isi): ?>
    <div class="kartu kosong-besar">
        <p class="ikon-besar">🛒</p>
        <p class="tebal">Keranjangmu masih kosong</p>
        <p class="redup">Tambahkan ayam favoritmu, lalu kembali ke sini untuk checkout.</p>
        <a href="<?= url('produk.php') ?>" class="tombol">Lihat menu</a>
    </div>
<?php else: ?>
<div class="dua-kolom dua-kolom--lebar">
    <section class="kartu">
        <header class="kartu__kepala">
            <h2>Item pesanan</h2>
            <form method="post" data-konfirmasi="Kosongkan semua item di keranjang?">
                <?= csrf_field() ?>
                <input type="hidden" name="aksi" value="kosongkan">
                <button type="submit" class="tombol tombol--kecil tombol--garis">Kosongkan</button>
            </form>
        </header>

        <ul class="daftar daftar--item">
            <?php foreach ($isi as $item): ?>
                <li>
                    <span class="item__ikon"><?= e($item['ikon']) ?></span>
                    <div class="item__nama">
                        <strong><?= e($item['nama']) ?></strong>
                        <span class="redup"><?= rupiah((int) $item['harga']) ?> per porsi</span>
                    </div>
                    <div class="jumlah">
                        <?php foreach ([-1 => '−', 1 => '+'] as $selisih => $simbol): ?>
                            <?php if ($selisih === 1): ?><strong><?= (int) $item['jumlah'] ?></strong><?php endif; ?>
                            <form method="post">
                                <?= csrf_field() ?>
                                <input type="hidden" name="aksi" value="atur">
                                <input type="hidden" name="menu_id" value="<?= (int) $item['menu_id'] ?>">
                                <input type="hidden" name="jumlah" value="<?= (int) $item['jumlah'] + $selisih ?>">
                                <button type="submit" class="bulat" aria-label="<?= $selisih < 0 ? 'Kurangi' : 'Tambah' ?>"><?= $simbol ?></button>
                            </form>
                        <?php endforeach; ?>
                    </div>
                    <strong class="item__harga"><?= rupiah((int) $item['harga'] * (int) $item['jumlah']) ?></strong>
                    <form method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="aksi" value="hapus">
                        <input type="hidden" name="menu_id" value="<?= (int) $item['menu_id'] ?>">
                        <button type="submit" class="bulat" aria-label="Hapus <?= e($item['nama']) ?>">🗑</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>

    <section class="kartu">
        <h2>Ringkasan pesanan</h2>

        <?php if ($kode !== ''): ?>
            <div class="voucher-terpasang">
                <span>Voucher <strong><?= e($kode) ?></strong> dipasang</span>
                <form method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="aksi" value="voucher_lepas">
                    <button type="submit" class="tautan">Lepas</button>
                </form>
            </div>
        <?php else: ?>
            <form method="post" class="baris-form">
                <?= csrf_field() ?>
                <input type="hidden" name="aksi" value="voucher_pakai">
                <label for="kode" class="sr-only">Kode voucher</label>
                <input type="text" id="kode" name="kode" placeholder="Kode voucher" maxlength="30" autocomplete="off">
                <button type="submit" class="tombol tombol--kecil">Pakai</button>
            </form>
            <?php if ($voucherAktif): ?>
                <div class="chip-voucher">
                    <?php foreach ($voucherAktif as $v): ?>
                        <form method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="aksi" value="voucher_pakai">
                            <input type="hidden" name="kode" value="<?= e($v['kode']) ?>">
                            <button type="submit" class="chip" title="<?= e(voucher_deskripsi($v)) ?>"><?= e($v['kode']) ?></button>
                        </form>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <dl class="rincian">
            <div><dt>Subtotal</dt><dd><?= rupiah($rincian['subtotal']) ?></dd></div>
            <div><dt>Biaya layanan</dt><dd><?= rupiah($rincian['biaya_layanan']) ?></dd></div>
            <div>
                <dt>Ongkir GoSend</dt>
                <dd><?= $alamat ? rupiah($rincian['ongkir']) : '<span class="redup">Dipilih di checkout</span>' ?></dd>
            </div>
            <?php if ($rincian['potongan'] > 0): ?>
                <div class="hemat"><dt>Potongan voucher</dt><dd>-<?= rupiah($rincian['potongan']) ?></dd></div>
            <?php endif; ?>
            <div class="total"><dt>Estimasi total</dt><dd><?= rupiah($rincian['total']) ?></dd></div>
        </dl>

        <a href="<?= url('checkout.php') ?>" class="tombol tombol--penuh">Lanjut ke checkout</a>
        <a href="<?= url('produk.php') ?>" class="tombol tombol--garis tombol--penuh">Tambah menu lain</a>
    </section>
</div>
<?php endif; ?>
