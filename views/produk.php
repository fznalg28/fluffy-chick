<?php
/** Variabel: $daftar, $kategori, $urut, $hitung, $semua, $cari. */

/** Alamat halaman Produk dengan filter tertentu, pencarian dan urutan tetap terbawa. */
$tautan = function (string $kat) use ($cari, $urut): string {
    return url('produk.php') . '?' . http_build_query(array_filter(['kategori' => $kat, 'q' => $cari, 'urut' => $urut]));
};
?>
<div class="judul-halaman">
    <h1>Menu <?= APP_NAME ?></h1>
    <p>Pilih favoritmu lalu masukkan ke keranjang.</p>
</div>

<div class="alat">
    <nav class="tab">
        <a href="<?= $tautan('') ?>" class="<?= $kategori === '' ? 'aktif' : '' ?>">Semua <span><?= $semua ?></span></a>
        <?php foreach (KATEGORI_MENU as $k): ?>
            <a href="<?= $tautan($k) ?>" class="<?= $kategori === $k ? 'aktif' : '' ?>"><?= e($k) ?> <span><?= $hitung[$k] ?></span></a>
        <?php endforeach; ?>
    </nav>

    <form method="get" class="urut">
        <?php if ($kategori !== ''): ?><input type="hidden" name="kategori" value="<?= e($kategori) ?>"><?php endif; ?>
        <?php if ($cari !== ''): ?><input type="hidden" name="q" value="<?= e($cari) ?>"><?php endif; ?>
        <label for="urut">Urutkan</label>
        <select name="urut" id="urut" onchange="this.form.submit()">
            <option value="populer"  <?= $urut === 'populer'  ? 'selected' : '' ?>>Terpopuler</option>
            <option value="termurah" <?= $urut === 'termurah' ? 'selected' : '' ?>>Harga terendah</option>
            <option value="termahal" <?= $urut === 'termahal' ? 'selected' : '' ?>>Harga tertinggi</option>
        </select>
    </form>
</div>

<?php if ($cari !== ''): ?>
    <p class="redup">Hasil pencarian "<?= e($cari) ?>" · <a href="<?= url('produk.php') ?>">hapus pencarian</a></p>
<?php endif; ?>

<?php if ($daftar): ?>
    <div class="grid-menu">
        <?php foreach ($daftar as $m): ?>
            <article class="kartu kartu-menu">
                <div class="kartu-menu__gambar">
                    <span><?= e($m['ikon']) ?></span>
                    <?php if ($m['label']): ?><span class="penanda"><?= e($m['label']) ?></span><?php endif; ?>
                </div>
                <h3><?= e($m['nama']) ?></h3>
                <p class="redup"><?= e($m['deskripsi']) ?></p>
                <div class="kartu-menu__bawah">
                    <div>
                        <strong><?= rupiah((int) $m['harga']) ?></strong>
                        <?php if ((int) $m['terjual'] > 0): ?><small class="redup"><?= angka((int) $m['terjual']) ?> terjual</small><?php endif; ?>
                    </div>
                    <form method="post" action="<?= url('keranjang.php') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="aksi" value="tambah">
                        <input type="hidden" name="menu_id" value="<?= (int) $m['id'] ?>">
                        <input type="hidden" name="kembali" value="produk.php?<?= e(http_build_query($_GET)) ?>">
                        <button type="submit" class="tombol tombol--kecil" aria-label="Tambah <?= e($m['nama']) ?> ke keranjang">+ Tambah</button>
                    </form>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="kartu kosong-besar">
        <p class="tebal">Menu tidak ditemukan.</p>
        <p class="redup">Coba kata kunci atau kategori lain.</p>
        <a href="<?= url('produk.php') ?>" class="tombol">Lihat semua menu</a>
    </div>
<?php endif; ?>
