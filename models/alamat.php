<?php
/** Model alamat pengantaran. Alamat utama dipakai otomatis saat checkout. */

function alamat_daftar(): array
{
    return db_semua(
        'SELECT * FROM alamat WHERE pengguna_id = :p ORDER BY utama DESC, id',
        ['p' => PENGGUNA_ID]
    );
}

function alamat_utama(): ?array
{
    return db_satu(
        'SELECT * FROM alamat WHERE pengguna_id = :p AND utama = 1 LIMIT 1',
        ['p' => PENGGUNA_ID]
    );
}

function alamat_cari(int $id): ?array
{
    return db_satu('SELECT * FROM alamat WHERE id = :id AND pengguna_id = :p', ['id' => $id, 'p' => PENGGUNA_ID]);
}

function alamat_validasi(array $form): array
{
    $error = [];
    $jarak = (float) str_replace(',', '.', $form['jarak_km']);

    if ($form['label'] === '' || mb_strlen($form['label']) > 40) {
        $error[] = 'Label alamat wajib diisi, maksimal 40 karakter (contoh: Rumah).';
    }
    if ($form['alamat'] === '' || mb_strlen($form['alamat']) > 255) {
        $error[] = 'Alamat lengkap wajib diisi, maksimal 255 karakter.';
    }
    if ($jarak <= 0 || $jarak > GOSEND_KM_MAKS) {
        $error[] = 'Jarak dari outlet harus antara 0,1 dan ' . GOSEND_KM_MAKS . ' km (jangkauan GoSend).';
    }

    return $error;
}

/** Simpan alamat baru. Alamat pertama otomatis menjadi utama. */
function alamat_simpan(array $form): void
{
    $utama = alamat_utama() === null ? 1 : 0;

    db_jalankan(
        'INSERT INTO alamat (pengguna_id, label, alamat, jarak_km, utama)
         VALUES (:p, :label, :alamat, :jarak, :utama)',
        [
            'p'     => PENGGUNA_ID,
            'label' => $form['label'],
            'alamat' => $form['alamat'],
            'jarak' => round((float) str_replace(',', '.', $form['jarak_km']), 1),
            'utama' => $utama,
        ]
    );
}

function alamat_jadikan_utama(int $id): void
{
    $pdo = db();
    $pdo->beginTransaction();
    db_jalankan('UPDATE alamat SET utama = 0 WHERE pengguna_id = :p', ['p' => PENGGUNA_ID]);
    db_jalankan('UPDATE alamat SET utama = 1 WHERE id = :id AND pengguna_id = :p', ['id' => $id, 'p' => PENGGUNA_ID]);
    $pdo->commit();
}

/** Hapus alamat. Jika yang dihapus alamat utama, alamat tertua yang tersisa menjadi utama. */
function alamat_hapus(int $id): void
{
    db_jalankan('DELETE FROM alamat WHERE id = :id AND pengguna_id = :p', ['id' => $id, 'p' => PENGGUNA_ID]);

    if (alamat_utama() === null) {
        db_jalankan('UPDATE alamat SET utama = 1 WHERE pengguna_id = :p ORDER BY id LIMIT 1', ['p' => PENGGUNA_ID]);
    }
}
