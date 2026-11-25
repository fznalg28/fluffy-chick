<?php
/** Model keranjang belanja (disimpan di database). */

/** Isi keranjang beserta data menunya. */
function keranjang_isi(): array
{
    return db_semua(
        'SELECT k.menu_id, k.jumlah, m.nama, m.harga, m.ikon
           FROM keranjang k
           JOIN menu m ON m.id = k.menu_id
          WHERE k.pengguna_id = :p AND m.tersedia = 1
          ORDER BY k.id',
        ['p' => PENGGUNA_ID]
    );
}

/** Total jumlah item (angka di ikon keranjang). */
function keranjang_jumlah(): int
{
    return (int) db_nilai('SELECT COALESCE(SUM(jumlah), 0) FROM keranjang WHERE pengguna_id = :p', ['p' => PENGGUNA_ID]);
}

function keranjang_subtotal(array $isi): int
{
    $total = 0;
    foreach ($isi as $item) {
        $total += (int) $item['harga'] * (int) $item['jumlah'];
    }
    return $total;
}

/** Tambah item. Jumlah dibatasi KERANJANG_MAKS_PER_ITEM. */
function keranjang_tambah(int $menuId, int $jumlah = 1): void
{
    db_jalankan(
        'INSERT INTO keranjang (pengguna_id, menu_id, jumlah) VALUES (:p, :m, :j)
         ON DUPLICATE KEY UPDATE jumlah = LEAST(jumlah + VALUES(jumlah), ' . KERANJANG_MAKS_PER_ITEM . ')',
        ['p' => PENGGUNA_ID, 'm' => $menuId, 'j' => min($jumlah, KERANJANG_MAKS_PER_ITEM)]
    );
}

/** Atur jumlah item. Jumlah 0 atau kurang menghapus item dari keranjang. */
function keranjang_atur(int $menuId, int $jumlah): void
{
    if ($jumlah <= 0) {
        keranjang_hapus($menuId);
        return;
    }
    db_jalankan(
        'UPDATE keranjang SET jumlah = :j WHERE pengguna_id = :p AND menu_id = :m',
        ['j' => min($jumlah, KERANJANG_MAKS_PER_ITEM), 'p' => PENGGUNA_ID, 'm' => $menuId]
    );
}

function keranjang_hapus(int $menuId): void
{
    db_jalankan('DELETE FROM keranjang WHERE pengguna_id = :p AND menu_id = :m', ['p' => PENGGUNA_ID, 'm' => $menuId]);
}

function keranjang_kosongkan(): void
{
    db_jalankan('DELETE FROM keranjang WHERE pengguna_id = :p', ['p' => PENGGUNA_ID]);
}
