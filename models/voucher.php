<?php
/** Model voucher: daftar, validasi kode, dan perhitungan potongan. */

const JENIS_VOUCHER = [
    'persen'  => 'Diskon persen',
    'nominal' => 'Potongan rupiah',
    'ongkir'  => 'Potongan ongkir',
];

/** Voucher yang masih berlaku (aktif dan belum lewat tanggalnya). */
function voucher_aktif_daftar(): array
{
    return db_semua(
        'SELECT * FROM voucher
          WHERE aktif = 1 AND (berlaku_sampai IS NULL OR berlaku_sampai >= CURDATE())
          ORDER BY id DESC'
    );
}

function voucher_hitung_aktif(): int
{
    return (int) db_nilai(
        'SELECT COUNT(*) FROM voucher
          WHERE aktif = 1 AND (berlaku_sampai IS NULL OR berlaku_sampai >= CURDATE())'
    );
}

function voucher_cari_kode(string $kode): ?array
{
    return db_satu('SELECT * FROM voucher WHERE kode = :k', ['k' => strtoupper(trim($kode))]);
}

/** Kalimat singkat syarat dan manfaat voucher. */
function voucher_deskripsi(array $v): string
{
    $manfaat = match ($v['jenis']) {
        'persen'  => 'Diskon ' . (int) $v['nilai'] . '%' . ($v['maks_potongan'] ? ', maks. ' . rupiah((int) $v['maks_potongan']) : ''),
        'nominal' => 'Potongan ' . rupiah((int) $v['nilai']),
        'ongkir'  => $v['maks_potongan'] ? 'Potongan ongkir maks. ' . rupiah((int) $v['maks_potongan']) : 'Gratis ongkir',
    };

    $syarat = [];
    if ((int) $v['min_belanja'] > 0) {
        $syarat[] = 'min. belanja ' . rupiah((int) $v['min_belanja']);
    }
    if ($v['berlaku_sampai']) {
        $syarat[] = 'sampai ' . tanggal($v['berlaku_sampai']);
    }

    return $manfaat . ($syarat ? ' · ' . implode(' · ', $syarat) : '');
}

/**
 * Cek apakah kode bisa dipakai untuk subtotal tertentu.
 * Mengembalikan [voucher|null, pesanKesalahan|null].
 */
function voucher_periksa(string $kode, int $subtotal): array
{
    $v = voucher_cari_kode($kode);

    if ($v === null || !(int) $v['aktif']) {
        return [null, 'Kode voucher tidak ditemukan.'];
    }
    if ($v['berlaku_sampai'] && $v['berlaku_sampai'] < date('Y-m-d')) {
        return [null, 'Voucher ' . $v['kode'] . ' sudah kedaluwarsa.'];
    }
    if ($subtotal < (int) $v['min_belanja']) {
        return [null, 'Belanja minimal ' . rupiah((int) $v['min_belanja']) . ' untuk memakai voucher ' . $v['kode'] . '.'];
    }

    return [$v, null];
}

/**
 * Besar potongan dalam rupiah.
 * Voucher persen dan nominal memotong subtotal, voucher ongkir memotong ongkir.
 */
function voucher_potongan(array $v, int $subtotal, int $ongkir): int
{
    $potongan = match ($v['jenis']) {
        'persen'  => intdiv($subtotal * (int) $v['nilai'], 100),
        'nominal' => (int) $v['nilai'],
        'ongkir'  => $ongkir,
    };

    if ($v['maks_potongan'] !== null && $v['jenis'] !== 'nominal') {
        $potongan = min($potongan, (int) $v['maks_potongan']);
    }

    $batas = $v['jenis'] === 'ongkir' ? $ongkir : $subtotal;
    return max(0, min($potongan, $batas));
}

/* ---------- Tambah voucher ---------- */

function voucher_form_kosong(): array
{
    return ['kode' => '', 'jenis' => 'persen', 'nilai' => '', 'maks_potongan' => '', 'min_belanja' => '', 'berlaku_sampai' => ''];
}

function voucher_validasi(array $form): array
{
    $error = [];

    if (!preg_match('/^[A-Za-z0-9]{3,30}$/', $form['kode'])) {
        $error[] = 'Kode voucher 3-30 karakter, hanya huruf dan angka.';
    } elseif (voucher_cari_kode($form['kode']) !== null) {
        $error[] = 'Kode ' . strtoupper($form['kode']) . ' sudah ada.';
    }
    if (!isset(JENIS_VOUCHER[$form['jenis']])) {
        $error[] = 'Jenis voucher tidak dikenal.';
    }
    if ($form['jenis'] !== 'ongkir' && (int) $form['nilai'] <= 0) {
        $error[] = 'Nilai voucher harus lebih dari 0.';
    }
    if ($form['jenis'] === 'persen' && (int) $form['nilai'] > 100) {
        $error[] = 'Diskon persen maksimal 100.';
    }
    if ($form['berlaku_sampai'] !== '' && !strtotime($form['berlaku_sampai'])) {
        $error[] = 'Tanggal berlaku tidak valid.';
    }

    return $error;
}

function voucher_simpan(array $form): void
{
    db_jalankan(
        'INSERT INTO voucher (kode, jenis, nilai, maks_potongan, min_belanja, berlaku_sampai)
         VALUES (:kode, :jenis, :nilai, :maks, :min, :sampai)',
        [
            'kode'   => strtoupper($form['kode']),
            'jenis'  => $form['jenis'],
            'nilai'  => $form['jenis'] === 'ongkir' ? 0 : (int) $form['nilai'],
            'maks'   => $form['maks_potongan'] === '' ? null : (int) $form['maks_potongan'],
            'min'    => (int) $form['min_belanja'],
            'sampai' => $form['berlaku_sampai'] === '' ? null : $form['berlaku_sampai'],
        ]
    );
}

function voucher_hapus(int $id): void
{
    db_jalankan('DELETE FROM voucher WHERE id = :id', ['id' => $id]);
}
