<?php
/** Model pesanan: ongkir GoSend, pembuatan pesanan, status, dan riwayat. */

/** Status yang berarti pesanan sudah dibayar. */
const STATUS_DIBAYAR = ['Dimasak', 'Diantar', 'Selesai'];

/* ---------- Pengiriman GoSend ---------- */

/** Ongkir GoSend: tarif dasar sampai GOSEND_KM_DASAR km, lalu tambahan per km (dibulatkan ke atas). */
function gosend_ongkir(float $km): int
{
    if ($km <= GOSEND_KM_DASAR) {
        return GOSEND_TARIF_DASAR;
    }
    return GOSEND_TARIF_DASAR + (int) ceil($km - GOSEND_KM_DASAR) * GOSEND_TARIF_PER_KM;
}

/** Perkiraan lama pengantaran (menit) dari jarak. */
function gosend_estimasi(float $km): string
{
    $dasar = 20 + (int) ceil($km * 2);
    return $dasar . '-' . ($dasar + 10) . ' menit';
}

/* ---------- Hitung total (dipakai keranjang, checkout, dan saat membuat pesanan) ---------- */

/**
 * Rincian biaya dari isi keranjang.
 * $alamat boleh null (ongkir belum diketahui), $kodeVoucher boleh kosong.
 * Mengembalikan [rincian, pesanKesalahanVoucher|null].
 */
function pesanan_rincian(array $isi, ?array $alamat, string $kodeVoucher = ''): array
{
    $subtotal = keranjang_subtotal($isi);
    $ongkir   = $alamat ? gosend_ongkir((float) $alamat['jarak_km']) : 0;
    $potongan = 0;
    $voucher  = null;
    $error    = null;

    if ($kodeVoucher !== '') {
        [$voucher, $error] = voucher_periksa($kodeVoucher, $subtotal);
        if ($voucher !== null) {
            $potongan = voucher_potongan($voucher, $subtotal, $ongkir);
        }
    }

    return [[
        'subtotal'      => $subtotal,
        'biaya_layanan' => $isi ? BIAYA_LAYANAN : 0,
        'ongkir'        => $ongkir,
        'potongan'      => $potongan,
        'voucher'       => $voucher,
        'total'         => $subtotal + ($isi ? BIAYA_LAYANAN : 0) + $ongkir - $potongan,
    ], $error];
}

/* ---------- Membuat pesanan ---------- */

function pesanan_kode_bayar(string $metode): string
{
    if ($metode === 'Transfer VA') {
        return '8808' . str_pad((string) random_int(0, 999999999999), 12, '0', STR_PAD_LEFT);
    }
    return 'QRIS-' . strtoupper(bin2hex(random_bytes(6)));
}

/**
 * Buat pesanan dari isi keranjang lalu kosongkan keranjang, semuanya dalam satu transaksi.
 * Mengembalikan [idPesanan|null, pesanKesalahan|null].
 */
function pesanan_buat(array $alamat, string $metode, string $catatan, string $kodeVoucher): array
{
    $isi = keranjang_isi();
    if (!$isi) {
        return [null, 'Keranjang masih kosong.'];
    }
    if (!isset(METODE_BAYAR[$metode])) {
        return [null, 'Pilih metode pembayaran terlebih dahulu.'];
    }

    [$r, $errorVoucher] = pesanan_rincian($isi, $alamat, $kodeVoucher);
    if ($errorVoucher !== null) {
        return [null, $errorVoucher];
    }

    $pdo = db();
    $pdo->beginTransaction();

    try {
        $awal = 'FC-' . date('ymd') . '-';
        $urut = (int) db_nilai(
            'SELECT COUNT(*) FROM pesanan WHERE no_pesanan LIKE :awal FOR UPDATE',
            ['awal' => $awal . '%']
        ) + 1;

        db_jalankan(
            'INSERT INTO pesanan
                (no_pesanan, pengguna_id, metode_bayar, kode_bayar, alamat_label, alamat_teks, jarak_km,
                 catatan, subtotal, biaya_layanan, ongkir, potongan, voucher_kode, total)
             VALUES
                (:no, :p, :metode, :kode, :label, :teks, :jarak,
                 :catatan, :subtotal, :layanan, :ongkir, :potongan, :voucher, :total)',
            [
                'no'       => $awal . str_pad((string) $urut, 4, '0', STR_PAD_LEFT),
                'p'        => PENGGUNA_ID,
                'metode'   => $metode,
                'kode'     => pesanan_kode_bayar($metode),
                'label'    => $alamat['label'],
                'teks'     => $alamat['alamat'],
                'jarak'    => $alamat['jarak_km'],
                'catatan'  => $catatan,
                'subtotal' => $r['subtotal'],
                'layanan'  => $r['biaya_layanan'],
                'ongkir'   => $r['ongkir'],
                'potongan' => $r['potongan'],
                'voucher'  => $r['voucher'] ? $r['voucher']['kode'] : null,
                'total'    => $r['total'],
            ]
        );
        $pesananId = (int) $pdo->lastInsertId();

        foreach ($isi as $item) {
            db_jalankan(
                'INSERT INTO pesanan_item (pesanan_id, menu_id, nama, ikon, harga, jumlah)
                 VALUES (:pesanan, :menu, :nama, :ikon, :harga, :jumlah)',
                [
                    'pesanan' => $pesananId,
                    'menu'    => $item['menu_id'],
                    'nama'    => $item['nama'],
                    'ikon'    => $item['ikon'],
                    'harga'   => $item['harga'],
                    'jumlah'  => $item['jumlah'],
                ]
            );
        }

        keranjang_kosongkan();
        $pdo->commit();
        return [$pesananId, null];
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

/* ---------- Membaca pesanan ---------- */

function pesanan_cari(int $id): ?array
{
    return db_satu('SELECT * FROM pesanan WHERE id = :id AND pengguna_id = :p', ['id' => $id, 'p' => PENGGUNA_ID]);
}

function pesanan_item(int $pesananId): array
{
    return db_semua('SELECT * FROM pesanan_item WHERE pesanan_id = :id ORDER BY id', ['id' => $pesananId]);
}

/** Daftar pesanan terbaru dulu, boleh difilter status. Item ikut dimuat. */
function pesanan_daftar(string $status = '', int $batas = 50): array
{
    $sql    = 'SELECT * FROM pesanan WHERE pengguna_id = :p';
    $params = ['p' => PENGGUNA_ID];

    if ($status !== '') {
        $sql .= ' AND status = :status';
        $params['status'] = $status;
    }

    $daftar = db_semua($sql . ' ORDER BY dibuat_pada DESC, id DESC LIMIT ' . (int) $batas, $params);
    foreach ($daftar as &$p) {
        $p['item'] = pesanan_item((int) $p['id']);
    }
    return $daftar;
}

/** Jumlah pesanan per status, termasuk kunci 'Semua'. */
function pesanan_hitung_status(): array
{
    $hasil = array_fill_keys(STATUS_PESANAN, 0);
    $hasil['Semua'] = 0;

    $baris = db_semua(
        'SELECT status, COUNT(*) AS jumlah FROM pesanan WHERE pengguna_id = :p GROUP BY status',
        ['p' => PENGGUNA_ID]
    );
    foreach ($baris as $b) {
        $hasil[$b['status']]  = (int) $b['jumlah'];
        $hasil['Semua']      += (int) $b['jumlah'];
    }
    return $hasil;
}

/** Pesanan yang sedang berjalan (sudah dibayar, belum selesai), yang terbaru. */
function pesanan_aktif(): ?array
{
    return db_satu(
        "SELECT * FROM pesanan
          WHERE pengguna_id = :p AND status IN ('Dimasak','Diantar')
          ORDER BY dibuat_pada DESC, id DESC LIMIT 1",
        ['p' => PENGGUNA_ID]
    );
}

function pesanan_terakhir(int $batas = 3): array
{
    return pesanan_daftar('', $batas);
}

/** Jumlah pesanan (tanpa yang dibatalkan) dan total belanja (yang sudah dibayar) bulan ini. */
function pesanan_ringkasan_bulan(): array
{
    $baris = db_satu(
        "SELECT COUNT(CASE WHEN status <> 'Dibatalkan' THEN 1 END) AS jumlah,
                COALESCE(SUM(CASE WHEN status IN ('Dimasak','Diantar','Selesai') THEN total END), 0) AS belanja
           FROM pesanan
          WHERE pengguna_id = :p AND DATE_FORMAT(dibuat_pada, '%Y-%m') = :bulan",
        ['p' => PENGGUNA_ID, 'bulan' => date('Y-m')]
    );
    return ['jumlah' => (int) $baris['jumlah'], 'belanja' => (int) $baris['belanja']];
}

/** Total belanja per bulan untuk grafik, $n bulan terakhir (termasuk bulan ini). */
function pesanan_belanja_bulanan(int $n = 6): array
{
    static $nama = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

    $hasil = [];
    for ($i = $n - 1; $i >= 0; $i--) {
        $t = strtotime(date('Y-m-01') . " -$i month");
        $hasil[date('Y-m', $t)] = ['label' => $nama[(int) date('n', $t)], 'total' => 0];
    }

    $baris = db_semua(
        "SELECT DATE_FORMAT(dibuat_pada, '%Y-%m') AS bulan, SUM(total) AS total
           FROM pesanan
          WHERE pengguna_id = :p AND status IN ('Dimasak','Diantar','Selesai')
            AND dibuat_pada >= :awal
          GROUP BY bulan",
        ['p' => PENGGUNA_ID, 'awal' => array_key_first($hasil) . '-01']
    );
    foreach ($baris as $b) {
        if (isset($hasil[$b['bulan']])) {
            $hasil[$b['bulan']]['total'] = (int) $b['total'];
        }
    }
    return array_values($hasil);
}

/* ---------- Mengubah status ---------- */

/** Konfirmasi pembayaran: Menunggu bayar -> Dimasak. Mengembalikan true jika berhasil. */
function pesanan_bayar(int $id): bool
{
    return db_jalankan(
        "UPDATE pesanan SET status = 'Dimasak', dibayar_pada = NOW()
          WHERE id = :id AND pengguna_id = :p AND status = 'Menunggu bayar'",
        ['id' => $id, 'p' => PENGGUNA_ID]
    ) > 0;
}

/** Majukan status: Dimasak -> Diantar -> Selesai. */
function pesanan_lanjut(int $id): bool
{
    return db_jalankan(
        "UPDATE pesanan
            SET status = CASE status WHEN 'Dimasak' THEN 'Diantar' WHEN 'Diantar' THEN 'Selesai' END
          WHERE id = :id AND pengguna_id = :p AND status IN ('Dimasak','Diantar')",
        ['id' => $id, 'p' => PENGGUNA_ID]
    ) > 0;
}

/** Batalkan pesanan yang belum dibayar. */
function pesanan_batal(int $id): bool
{
    return db_jalankan(
        "UPDATE pesanan SET status = 'Dibatalkan'
          WHERE id = :id AND pengguna_id = :p AND status = 'Menunggu bayar'",
        ['id' => $id, 'p' => PENGGUNA_ID]
    ) > 0;
}

/** Masukkan kembali item pesanan lama ke keranjang. Mengembalikan jumlah menu yang berhasil ditambahkan. */
function pesanan_ulang(int $id): int
{
    $ditambah = 0;
    foreach (pesanan_item($id) as $item) {
        if ($item['menu_id'] !== null && menu_cari_id((int) $item['menu_id']) !== null) {
            keranjang_tambah((int) $item['menu_id'], (int) $item['jumlah']);
            $ditambah++;
        }
    }
    return $ditambah;
}
