# Fluffy Chicken

Aplikasi pemesanan fried chicken (PHP + MySQL). Dimulai dalam keadaan baru: tidak ada pesanan,
riwayat, voucher, maupun alamat. Hanya ada 6 menu dan satu profil.

## Cara menjalankan (XAMPP)

1. Salin folder ini ke `C:\xampp\htdocs\FluffyChicken`.
2. Nyalakan **Apache** dan **MySQL** di XAMPP.
3. Buka phpMyAdmin, tab **Import**, pilih `database/fluffy_chicken.sql`, klik **Import**.
   (Database `fluffy_chicken` dibuat otomatis. Mengimpor ulang menghapus semua data.)
4. Buka <http://localhost/FluffyChicken/>.

Kalau MySQL-mu memakai user atau password lain, ubah `config/database.php`.

## Alur pemakaian

1. **Pengaturan**: isi profil dan tambah alamat (jarak dari outlet dipakai menghitung ongkir GoSend).
2. **Produk**: tekan *Tambah* pada menu, lalu buka **Keranjang**.
3. Di Keranjang, atur jumlah dan pasang voucher (opsional), lalu **Lanjut ke checkout**.
4. **Checkout**: pilih alamat dan metode bayar (QRIS atau Transfer VA), lalu **Buat pesanan**.
5. Di detail pesanan, kode QRIS / nomor VA tampil. Tekan *Saya sudah bayar* lalu pantau statusnya
   (Menunggu bayar > Dimasak > Diantar > Selesai). Pesanan yang belum dibayar bisa dibatalkan.
6. **Riwayat pesanan**, **Dashboard**, dan **Pengiriman** terisi dari pesanan itu. *Pesan lagi* memasukkan
   item pesanan lama ke keranjang.

Voucher dimulai kosong. Tambahkan lewat menu **Pembayaran > + Tambah voucher**
(jenis: diskon persen, potongan rupiah, atau potongan ongkir).

## Catatan penting

- **Belum ada login.** Semua data milik satu pengguna (`PENGGUNA_ID` di `config/app.php`).
- **Pembayaran dan status adalah simulasi.** Belum terhubung ke payment gateway maupun panel outlet:
  tombol *Saya sudah bayar* dan *Lanjutkan status* di `pesanan-detail.php` adalah tempat
  menyambungkannya nanti. Kode QRIS/VA yang tampil hanya contoh, bukan kode asli.
- Tarif GoSend, biaya layanan, jam buka, dan kontak bantuan diatur di `config/app.php`.
  Alamat outlet dan kontak di FAQ masih contoh, silakan diganti.
- Menu diubah lewat tabel `menu` di phpMyAdmin (belum ada halaman admin).

## Struktur

```
FluffyChicken/
├── index.php  produk.php  keranjang.php  checkout.php  pesanan.php
├── pesanan-detail.php  pembayaran.php  pengiriman.php  faq.php  pengaturan.php
├── config/      app.php (tarif, jam, kontak), database.php
├── includes/    bootstrap, database (PDO), helpers (format, CSRF, render)
├── models/      pengguna, alamat, menu, keranjang, voucher, pesanan  (semua query SQL)
├── views/       layout/ (header, footer), partials/stepper.php, satu file tampilan per halaman
├── assets/      css/style.css, js/app.js, img/ (logo)
└── database/    fluffy_chicken.sql
```

Setiap halaman di root bertugas sebagai controller: baca input, panggil model, lalu `render()` view.
Tabel: `pengguna`, `alamat`, `menu`, `keranjang`, `voucher`, `pesanan`, `pesanan_item`.
