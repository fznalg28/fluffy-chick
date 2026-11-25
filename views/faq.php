<?php
/** Variabel: $kategori, $tanya. */
?>
<div class="judul-halaman">
    <h1>Pertanyaan yang sering diajukan</h1>
    <p>Jawaban cepat seputar pemesanan, pembayaran, dan pengiriman.</p>
</div>

<div class="dua-kolom dua-kolom--lebar">
    <section>
        <label for="faq-cari" class="tebal">Cari pertanyaan</label>
        <input type="search" id="faq-cari" placeholder="Contoh: voucher, ongkir, batal">

        <nav class="tab" id="faq-tab">
            <a href="#" class="aktif" data-kategori="">Semua</a>
            <?php foreach ($kategori as $k): ?>
                <a href="#" data-kategori="<?= e($k) ?>"><?= e($k) ?></a>
            <?php endforeach; ?>
        </nav>

        <?php foreach ($tanya as [$kat, $pertanyaan, $jawaban]): ?>
            <details class="faq" data-kategori="<?= e($kat) ?>">
                <summary><?= e($pertanyaan) ?></summary>
                <p><?= e($jawaban) ?></p>
            </details>
        <?php endforeach; ?>
        <p class="kosong" id="faq-kosong" hidden>Tidak ada pertanyaan yang cocok.</p>
    </section>

    <aside class="kartu kartu--bantuan">
        <img src="<?= aset('img/logo.png') ?>" alt="" class="logo-kecil">
        <h2>Masih butuh bantuan?</h2>
        <p>Tim kami membalas setiap hari pukul <?= sprintf('%02d.00-%02d.00', OUTLET_BUKA, OUTLET_TUTUP) ?>.</p>
        <a href="https://wa.me/<?= KONTAK_WA ?>" class="tombol tombol--kuning tombol--penuh" target="_blank" rel="noopener">Chat via WhatsApp</a>
        <p class="kecil">Telepon: <?= e(KONTAK_TELEPON) ?><br>Email: <?= e(KONTAK_EMAIL) ?></p>
    </aside>
</div>
