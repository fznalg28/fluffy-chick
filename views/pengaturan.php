<?php
/** Variabel: $form, $errors, $formAlamat, $errorsAlamat, $daftarAlamat. */
$notifikasi = [
    'notif_status' => ['Status pesanan',    'Kabar saat pesanan dimasak, diantar, dan tiba.'],
    'notif_promo'  => ['Promo dan voucher', 'Info diskon terbaru dari ' . APP_NAME . '.'],
    'notif_wa'     => ['Pesan WhatsApp',    'Kirim pembaruan ke nomor WhatsApp-mu.'],
    'notif_email'  => ['Email',             'Kirim struk dan ringkasan pesanan.'],
];
?>
<div class="judul-halaman">
    <h1>Pengaturan</h1>
    <p>Atur profil, notifikasi, dan alamat pengantaran.</p>
</div>

<form method="post" class="kartu form">
    <?= csrf_field() ?>
    <input type="hidden" name="aksi" value="profil">
    <h2>Profil</h2>

    <?php foreach ($errors as $err): ?><div class="notif notif--error"><?= e($err) ?></div><?php endforeach; ?>

    <label>Nama lengkap
        <input type="text" name="nama" maxlength="100" value="<?= e($form['nama']) ?>" required>
    </label>
    <label>Nomor WhatsApp
        <input type="tel" name="whatsapp" maxlength="20" value="<?= e($form['whatsapp']) ?>" placeholder="0812-3456-7890">
    </label>
    <label>Email
        <input type="email" name="email" maxlength="120" value="<?= e($form['email']) ?>" placeholder="nama@email.com">
    </label>

    <h2>Notifikasi</h2>
    <?php foreach ($notifikasi as $nama => [$judulNotif, $keterangan]): ?>
        <label class="pilihan">
            <input type="checkbox" name="<?= $nama ?>" value="1" <?= $form[$nama] ? 'checked' : '' ?>>
            <span><strong><?= e($judulNotif) ?></strong><br><span class="redup"><?= e($keterangan) ?></span></span>
        </label>
    <?php endforeach; ?>

    <button type="submit" class="tombol">Simpan perubahan</button>
</form>

<section class="kartu" id="alamat">
    <h2>Alamat tersimpan</h2>

    <?php if (!$daftarAlamat): ?>
        <p class="kosong">Belum ada alamat. Tambahkan alamat agar bisa checkout.</p>
    <?php endif; ?>

    <ul class="daftar">
        <?php foreach ($daftarAlamat as $a): ?>
            <li>
                <span>
                    <strong><?= e($a['label']) ?></strong>
                    <?php if ((int) $a['utama']): ?><span class="badge badge--masak">Utama</span><?php endif; ?><br>
                    <?= e($a['alamat']) ?> <span class="redup">(<?= e($a['jarak_km']) ?> km dari outlet)</span>
                </span>
                <span class="aksi">
                    <?php if (!(int) $a['utama']): ?>
                        <form method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="aksi" value="alamat_utama">
                            <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                            <button type="submit" class="tombol tombol--kecil tombol--garis">Jadikan utama</button>
                        </form>
                    <?php endif; ?>
                    <form method="post" data-konfirmasi="Hapus alamat <?= e($a['label']) ?>?">
                        <?= csrf_field() ?>
                        <input type="hidden" name="aksi" value="alamat_hapus">
                        <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                        <button type="submit" class="bulat" aria-label="Hapus alamat <?= e($a['label']) ?>">🗑</button>
                    </form>
                </span>
            </li>
        <?php endforeach; ?>
    </ul>

    <details class="tambah" <?= $errorsAlamat || !$daftarAlamat ? 'open' : '' ?>>
        <summary>+ Tambah alamat</summary>
        <?php foreach ($errorsAlamat as $err): ?><div class="notif notif--error"><?= e($err) ?></div><?php endforeach; ?>
        <form method="post" class="form">
            <?= csrf_field() ?>
            <input type="hidden" name="aksi" value="alamat_tambah">
            <label>Label
                <input type="text" name="label" maxlength="40" value="<?= e($formAlamat['label']) ?>" placeholder="Rumah, Kantor, ..." required>
            </label>
            <label>Alamat lengkap
                <input type="text" name="alamat" maxlength="255" value="<?= e($formAlamat['alamat']) ?>" required>
            </label>
            <label>Jarak dari outlet (km, maks. <?= GOSEND_KM_MAKS ?>)
                <input type="number" name="jarak_km" min="0.1" max="<?= GOSEND_KM_MAKS ?>" step="0.1" value="<?= e($formAlamat['jarak_km']) ?>" required>
            </label>
            <button type="submit" class="tombol">Simpan alamat</button>
        </form>
    </details>
</section>
