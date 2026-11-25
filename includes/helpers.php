<?php
/** Fungsi bantu umum: escape HTML, format, URL, flash, CSRF, dan render halaman. */

function e(?string $teks): string
{
    return htmlspecialchars((string) $teks, ENT_QUOTES, 'UTF-8');
}

function rupiah(int $angka): string
{
    return 'Rp' . number_format($angka, 0, ',', '.');
}

function angka(int $angka): string
{
    return number_format($angka, 0, ',', '.');
}

/** Tanggal dan jam gaya Indonesia, contoh: "26 Nov 2026, 14.05". */
function tanggal_waktu(string $datetime): string
{
    static $bulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $t = strtotime($datetime);
    return date('j', $t) . ' ' . $bulan[(int) date('n', $t)] . ' ' . date('Y, H.i', $t);
}

function tanggal(string $date): string
{
    static $bulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $t = strtotime($date);
    return date('j', $t) . ' ' . $bulan[(int) date('n', $t)] . ' ' . date('Y', $t);
}

function inisial(string $nama): string
{
    $kata = preg_split('/\s+/', trim($nama)) ?: [];
    $hasil = '';
    foreach (array_slice($kata, 0, 2) as $k) {
        $hasil .= mb_strtoupper(mb_substr($k, 0, 1));
    }
    return $hasil !== '' ? $hasil : '?';
}

/* ---------- URL & request ---------- */

/** Alamat dasar aplikasi, mis. "/FluffyChicken" (kosong jika di root). */
function base_url(): string
{
    return rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
}

function url(string $path = ''): string
{
    return base_url() . '/' . ltrim($path, '/');
}

function aset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

function is_post(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

function input(string $nama, string $bawaan = ''): string
{
    return trim((string) ($_POST[$nama] ?? $bawaan));
}

function query(string $nama, string $bawaan = ''): string
{
    return trim((string) ($_GET[$nama] ?? $bawaan));
}

/* ---------- Flash message ---------- */

function flash(string $jenis, string $pesan): void
{
    $_SESSION['flash'][] = ['jenis' => $jenis, 'pesan' => $pesan];
}

function flash_ambil(): array
{
    $semua = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $semua;
}

/* ---------- CSRF ---------- */

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

/** Hentikan permintaan POST yang tokennya tidak cocok. */
function csrf_periksa(): void
{
    if (!hash_equals(csrf_token(), (string) ($_POST['csrf'] ?? ''))) {
        http_response_code(419);
        exit('Sesi tidak valid. Muat ulang halaman lalu coba lagi.');
    }
}

/* ---------- Render ---------- */

/**
 * Tampilkan halaman: header + views/{$view}.php + footer.
 * $data menjadi variabel di view. Kunci 'judul' dan 'menu' dipakai layout.
 */
function render(string $view, array $data = []): void
{
    $layout = [
        'jumlahKeranjang' => keranjang_jumlah(),
        'alamatUtama'     => alamat_utama(),
        'profil'          => pengguna_profil(),
        'pesanFlash'      => flash_ambil(),
        'cari'            => query('q'),
    ];

    extract($layout + $data + ['judul' => APP_NAME, 'menu' => '']);

    require __DIR__ . '/../views/layout/header.php';
    require __DIR__ . '/../views/' . $view . '.php';
    require __DIR__ . '/../views/layout/footer.php';
}

/* ---------- Tampilan ---------- */

/** Nama kelas CSS untuk badge status pesanan. */
function status_kelas(string $status): string
{
    return match ($status) {
        'Menunggu bayar' => 'badge--tunggu',
        'Dimasak'        => 'badge--masak',
        'Diantar'        => 'badge--antar',
        'Selesai'        => 'badge--selesai',
        default          => 'badge--batal',
    };
}

/** Apakah outlet sedang buka (menurut jam di config). */
function outlet_buka(): bool
{
    $jam = (int) date('G');
    return $jam >= OUTLET_BUKA && $jam < OUTLET_TUTUP;
}

/** Ringkasan item pesanan satu baris, contoh: "2x Paket Hemat Fluffy, 1x Es Teh Manis". */
function ringkas_item(array $items): string
{
    return implode(', ', array_map(fn($i) => $i['jumlah'] . 'x ' . $i['nama'], $items));
}

/**
 * Alamat tujuan kembali setelah aksi (mis. tambah ke keranjang).
 * Hanya halaman aplikasi sendiri yang diterima, selain itu dipakai $bawaan.
 */
function kembali_aman(string $nilai, string $bawaan): string
{
    return preg_match('/^[a-z\-]+\.php(\?[A-Za-z0-9_=&%+.\-]*)?$/', $nilai) ? $nilai : $bawaan;
}
