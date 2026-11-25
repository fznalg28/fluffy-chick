<?php
/** Variabel: $daftar, $status, $hitung. */
?>
<div class="judul-halaman">
    <h1>Riwayat pesanan</h1>
    <p>Semua pesananmu, lengkap dengan status dan opsi pesan ulang.</p>
</div>

<nav class="tab">
    <a href="<?= url('pesanan.php') ?>" class="<?= $status === '' ? 'aktif' : '' ?>">Semua <span><?= $hitung['Semua'] ?></span></a>
    <?php foreach (STATUS_PESANAN as $s): ?>
        <a href="<?= url('pesanan.php?status=' . urlencode($s)) ?>" class="<?= $status === $s ? 'aktif' : '' ?>">
            <?= e($s) ?> <span><?= $hitung[$s] ?></span>
        </a>
    <?php endforeach; ?>
</nav>

<?php if (!$daftar): ?>
    <div class="kartu kosong-besar">
        <p class="ikon-besar">🧾</p>
        <p class="tebal"><?= $status === '' ? 'Belum ada riwayat pesanan' : 'Tidak ada pesanan dengan status ' . e($status) ?></p>
        <p class="redup">Pesanan yang kamu buat akan tampil di sini.</p>
        <a href="<?= url('produk.php') ?>" class="tombol">Lihat menu</a>
    </div>
<?php endif; ?>

<?php foreach ($daftar as $p): ?>
    <article class="kartu">
        <header class="kartu__kepala">
            <div>
                <h2><?= e($p['no_pesanan']) ?></h2>
                <p class="redup"><?= e(tanggal_waktu($p['dibuat_pada'])) ?> · <?= e(METODE_BAYAR[$p['metode_bayar']]) ?> · GoSend</p>
            </div>
            <span class="badge <?= status_kelas($p['status']) ?>"><?= e($p['status']) ?></span>
        </header>

        <ul class="daftar">
            <?php foreach ($p['item'] as $i): ?>
                <li>
                    <span><?= e($i['ikon']) ?> <?= (int) $i['jumlah'] ?>x <?= e($i['nama']) ?></span>
                    <span><?= rupiah((int) $i['harga'] * (int) $i['jumlah']) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>

        <footer class="kartu__kaki">
            <div>
                <span class="redup">Total</span>
                <strong class="besar"><?= rupiah((int) $p['total']) ?></strong>
            </div>
            <div class="aksi">
                <a href="<?= url('pesanan-detail.php?id=' . (int) $p['id']) ?>" class="tombol tombol--kecil tombol--garis">Lihat detail</a>
                <form method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="aksi" value="ulang">
                    <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                    <button type="submit" class="tombol tombol--kecil tombol--kuning">Pesan lagi</button>
                </form>
            </div>
        </footer>
    </article>
<?php endforeach; ?>
