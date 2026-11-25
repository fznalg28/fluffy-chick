<?php
/** Model profil pengguna: nama, kontak, dan preferensi notifikasi. */

function pengguna_profil(): array
{
    return db_satu('SELECT * FROM pengguna WHERE id = :id', ['id' => PENGGUNA_ID]);
}

/** Isian form profil dari data tersimpan. */
function pengguna_ke_form(array $p): array
{
    return [
        'nama'         => $p['nama'],
        'whatsapp'     => $p['whatsapp'],
        'email'        => $p['email'],
        'notif_status' => (int) $p['notif_status'],
        'notif_promo'  => (int) $p['notif_promo'],
        'notif_wa'     => (int) $p['notif_wa'],
        'notif_email'  => (int) $p['notif_email'],
    ];
}

/** Kembalikan daftar pesan kesalahan (kosong jika valid). */
function pengguna_validasi(array $form): array
{
    $error = [];

    if ($form['nama'] === '' || mb_strlen($form['nama']) > 100) {
        $error[] = 'Nama wajib diisi, maksimal 100 karakter.';
    }
    if ($form['whatsapp'] !== '' && !preg_match('/^[0-9+\-\s]{8,20}$/', $form['whatsapp'])) {
        $error[] = 'Nomor WhatsApp hanya boleh berisi angka, spasi, + atau -, minimal 8 digit.';
    }
    if ($form['email'] !== '' && !filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $error[] = 'Format email belum benar.';
    }

    return $error;
}

function pengguna_simpan(array $form): void
{
    db_jalankan(
        'UPDATE pengguna
            SET nama = :nama, whatsapp = :wa, email = :email,
                notif_status = :ns, notif_promo = :np, notif_wa = :nw, notif_email = :ne
          WHERE id = :id',
        [
            'nama'  => $form['nama'],
            'wa'    => $form['whatsapp'],
            'email' => $form['email'],
            'ns'    => $form['notif_status'],
            'np'    => $form['notif_promo'],
            'nw'    => $form['notif_wa'],
            'ne'    => $form['notif_email'],
            'id'    => PENGGUNA_ID,
        ]
    );
}
