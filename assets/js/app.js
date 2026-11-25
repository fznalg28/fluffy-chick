/**
 * app.js - dua hal kecil:
 *  1. Konfirmasi sebelum aksi berbahaya: <form data-konfirmasi="Pertanyaan?">
 *  2. Pencarian dan filter kategori di halaman FAQ.
 */

document.addEventListener('submit', function (e) {
    var pesan = e.target.getAttribute('data-konfirmasi');
    if (pesan && !window.confirm(pesan)) {
        e.preventDefault();
    }
});

(function () {
    var kolom = document.getElementById('faq-cari');
    if (!kolom) {
        return;
    }

    var tab = document.querySelectorAll('#faq-tab a');
    var butir = document.querySelectorAll('details.faq');
    var kosong = document.getElementById('faq-kosong');
    var kategori = '';

    function terapkan() {
        var kata = kolom.value.trim().toLowerCase();
        var tampil = 0;

        butir.forEach(function (d) {
            var cocokKategori = kategori === '' || d.getAttribute('data-kategori') === kategori;
            var cocokKata = kata === '' || d.textContent.toLowerCase().indexOf(kata) !== -1;
            d.hidden = !(cocokKategori && cocokKata);
            if (!d.hidden) {
                tampil++;
            }
        });

        kosong.hidden = tampil > 0;
    }

    tab.forEach(function (a) {
        a.addEventListener('click', function (e) {
            e.preventDefault();
            kategori = a.getAttribute('data-kategori');
            tab.forEach(function (t) { t.classList.toggle('aktif', t === a); });
            terapkan();
        });
    });

    kolom.addEventListener('input', terapkan);
})();
