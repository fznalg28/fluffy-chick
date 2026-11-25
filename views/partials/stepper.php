<?php
/**
 * Tahapan pesanan. Variabel: $status.
 * Menunggu bayar = belum ada tahap aktif, Dimasak = tahap 2, Diantar = tahap 3, Selesai = semua.
 */
$tahap  = ['Pesanan diterima', 'Sedang dimasak', 'Diantar kurir', 'Tiba di tujuan'];
$posisi = ['Menunggu bayar' => 0, 'Dimasak' => 2, 'Diantar' => 3, 'Selesai' => 4, 'Dibatalkan' => 0][$status] ?? 0;
?>
<ol class="stepper">
    <?php foreach ($tahap as $i => $nama): ?>
        <?php $nomor = $i + 1; ?>
        <li class="<?= $nomor < $posisi || $posisi === 4 ? 'selesai' : ($nomor === $posisi ? 'sekarang' : '') ?>">
            <span class="stepper__titik"><?= $nomor ?></span>
            <span><?= e($nama) ?></span>
        </li>
    <?php endforeach; ?>
</ol>
