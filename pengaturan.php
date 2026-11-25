<?php
/** Pengaturan: profil, notifikasi, dan alamat tersimpan. */

require __DIR__ . '/includes/bootstrap.php';

$form      = pengguna_ke_form(pengguna_profil());
$formAlamat = ['label' => '', 'alamat' => '', 'jarak_km' => ''];
$errors    = [];
$errorsAlamat = [];

if (is_post()) {
    csrf_periksa();

    switch (input('aksi')) {
        case 'profil':
            $form = [
                'nama'         => input('nama'),
                'whatsapp'     => input('whatsapp'),
                'email'        => input('email'),
                'notif_status' => isset($_POST['notif_status']) ? 1 : 0,
                'notif_promo'  => isset($_POST['notif_promo']) ? 1 : 0,
                'notif_wa'     => isset($_POST['notif_wa']) ? 1 : 0,
                'notif_email'  => isset($_POST['notif_email']) ? 1 : 0,
            ];
            $errors = pengguna_validasi($form);
            if (!$errors) {
                pengguna_simpan($form);
                flash('sukses', 'Perubahan berhasil disimpan.');
                redirect('pengaturan.php');
            }
            break;

        case 'alamat_tambah':
            $formAlamat = ['label' => input('label'), 'alamat' => input('alamat'), 'jarak_km' => input('jarak_km')];
            $errorsAlamat = alamat_validasi($formAlamat);
            if (!$errorsAlamat) {
                alamat_simpan($formAlamat);
                flash('sukses', 'Alamat ditambahkan.');
                redirect('pengaturan.php#alamat');
            }
            break;

        case 'alamat_utama':
            alamat_jadikan_utama((int) input('id'));
            flash('sukses', 'Alamat utama diganti.');
            redirect('pengaturan.php#alamat');

        case 'alamat_hapus':
            alamat_hapus((int) input('id'));
            flash('sukses', 'Alamat dihapus.');
            redirect('pengaturan.php#alamat');
    }
}

render('pengaturan', [
    'judul'        => 'Pengaturan',
    'menu'         => 'pengaturan',
    'form'         => $form,
    'errors'       => $errors,
    'formAlamat'   => $formAlamat,
    'errorsAlamat' => $errorsAlamat,
    'daftarAlamat' => alamat_daftar(),
]);
