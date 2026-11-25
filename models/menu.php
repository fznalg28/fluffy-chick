<?php
/** Model menu makanan dan minuman. */

/** Subquery jumlah terjual: hanya dari pesanan yang sudah dibayar. */
const SQL_TERJUAL = "SELECT pi.menu_id, SUM(pi.jumlah) AS terjual
                       FROM pesanan_item pi
                       JOIN pesanan p ON p.id = pi.pesanan_id
                      WHERE p.status IN ('Dimasak','Diantar','Selesai')
                      GROUP BY pi.menu_id";

/** Menu tersedia, bisa difilter kategori dan kata kunci, diurutkan sesuai pilihan. */
function menu_daftar(string $kategori = '', string $cari = '', string $urut = 'populer'): array
{
    $sql    = 'SELECT m.*, COALESCE(t.terjual, 0) AS terjual
                 FROM menu m
                 LEFT JOIN (' . SQL_TERJUAL . ') t ON t.menu_id = m.id
                WHERE m.tersedia = 1';
    $params = [];

    if ($kategori !== '') {
        $sql .= ' AND m.kategori = :kategori';
        $params['kategori'] = $kategori;
    }
    if ($cari !== '') {
        $sql .= ' AND (m.nama LIKE :q1 OR m.deskripsi LIKE :q2)';
        $params['q1'] = $params['q2'] = '%' . $cari . '%';
    }

    $urutan = [
        'populer'  => 'terjual DESC, m.id',
        'termurah' => 'm.harga ASC, m.id',
        'termahal' => 'm.harga DESC, m.id',
    ];
    $sql .= ' ORDER BY ' . ($urutan[$urut] ?? $urutan['populer']);

    return db_semua($sql, $params);
}

/** Jumlah menu per kategori (untuk angka di tab), ikut kata kunci pencarian. */
function menu_hitung_kategori(string $cari = ''): array
{
    $sql    = 'SELECT kategori, COUNT(*) AS jumlah FROM menu WHERE tersedia = 1';
    $params = [];

    if ($cari !== '') {
        $sql .= ' AND (nama LIKE :q1 OR deskripsi LIKE :q2)';
        $params['q1'] = $params['q2'] = '%' . $cari . '%';
    }

    $hasil = array_fill_keys(KATEGORI_MENU, 0);
    foreach (db_semua($sql . ' GROUP BY kategori', $params) as $baris) {
        $hasil[$baris['kategori']] = (int) $baris['jumlah'];
    }
    return $hasil;
}

function menu_cari_id(int $id): ?array
{
    return db_satu('SELECT * FROM menu WHERE id = :id AND tersedia = 1', ['id' => $id]);
}

/** Menu yang paling sering dipesan. Kosong selama belum ada pesanan yang dibayar. */
function menu_favorit(int $batas = 4): array
{
    return db_semua(
        'SELECT m.*, t.terjual
           FROM menu m
           JOIN (' . SQL_TERJUAL . ') t ON t.menu_id = m.id
          WHERE m.tersedia = 1
          ORDER BY t.terjual DESC, m.id
          LIMIT ' . (int) $batas
    );
}
