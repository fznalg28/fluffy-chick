<?php
/** FAQ: pertanyaan umum, bisa dicari dan difilter per kategori (filter dijalankan app.js). */

require __DIR__ . '/includes/bootstrap.php';

$kategori = ['Pemesanan', 'Pembayaran', 'Pengiriman', 'Akun & Voucher'];

$tanya = [
    ['Pemesanan', 'Bagaimana cara memesan?',
        'Pilih menu di halaman Produk lalu tekan Tambah. Buka Keranjang, tekan Lanjut ke checkout, pilih alamat dan metode pembayaran, lalu buat pesanan.'],
    ['Pemesanan', 'Apakah pesanan bisa dibatalkan?',
        'Bisa, selama statusnya masih Menunggu bayar. Buka detail pesanan lalu tekan Batalkan pesanan. Pesanan yang sudah dibayar langsung diproses dapur sehingga tidak bisa dibatalkan.'],
    ['Pemesanan', 'Bagaimana cara memesan ulang?',
        'Buka Riwayat pesanan lalu tekan Pesan lagi. Semua item pesanan itu otomatis masuk ke keranjang.'],
    ['Pembayaran', 'Metode pembayaran apa saja yang tersedia?',
        'QRIS dan Transfer Virtual Account.'],
    ['Pembayaran', 'Bagaimana cara membayar?',
        'Setelah pesanan dibuat, kode QRIS atau nomor Virtual Account tampil di halaman detail pesanan. Bayar sesuai total pesanan, lalu konfirmasi pembayaran.'],
    ['Pengiriman', 'Pesanan diantar pakai apa?',
        'Semua pesanan diantar lewat GoSend. Kamu bisa melacak kurirnya lewat aplikasi Gojek.'],
    ['Pengiriman', 'Berapa ongkos kirimnya?',
        'Rp' . number_format(GOSEND_TARIF_DASAR, 0, ',', '.') . ' sampai ' . GOSEND_KM_DASAR . ' km, lalu tambah Rp' . number_format(GOSEND_TARIF_PER_KM, 0, ',', '.') . ' setiap km berikutnya. Jangkauan sampai ' . GOSEND_KM_MAKS . ' km dari outlet. Cek di menu Pengiriman.'],
    ['Pengiriman', 'Jam berapa pesanan bisa diantar?',
        'Setiap hari pukul ' . sprintf('%02d.00-%02d.00', OUTLET_BUKA, OUTLET_TUTUP) . '.'],
    ['Akun & Voucher', 'Bagaimana memakai voucher?',
        'Di halaman Keranjang, ketik kode voucher lalu tekan Pakai, atau pilih voucher aktif yang tampil. Potongan langsung terlihat di ringkasan pesanan.'],
    ['Akun & Voucher', 'Kenapa voucher tidak bisa dipakai?',
        'Biasanya karena belanja belum mencapai minimal, voucher sudah kedaluwarsa, atau kodenya salah.'],
    ['Akun & Voucher', 'Bagaimana menambah alamat pengantaran?',
        'Buka Pengaturan, cari kartu Alamat tersimpan, lalu isi label, alamat lengkap, dan jarak dari outlet.'],
];

render('faq', [
    'judul'    => 'FAQ',
    'menu'     => 'faq',
    'kategori' => $kategori,
    'tanya'    => $tanya,
]);
