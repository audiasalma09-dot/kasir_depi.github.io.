// =============================================
// 1. Tunggu seluruh halaman selesai dimuat
// =============================================
document.addEventListener('DOMContentLoaded', () => {

    // =============================================
    // 2. PESAN KONFIRMASI di semua tombol hapus
    //    Elemen dipilih dengan attribute data-konfirmasi
    // =============================================
    document.querySelectorAll('[data-konfirmasi]').forEach(tombol => {
        tombol.addEventListener('click', (e) => {
            // Ambil teks pertanyaan dari attribute
            const pesan = tombol.getAttribute('data-konfirmasi');
            // Kalau user memilih "Batal", hentikan tautan
            if (!confirm(pesan)) {
                e.preventDefault();
            }
        });
    });

    // =============================================
    // 3. PEMBANTU FORM CLOSINGAN
    //    Kotak input stok fisik: jika diisi 0 lalu user
    //    belum mengetik, halaman tetap bisa dikirim
    // =============================================
    const formClosing = document.getElementById('formClosing');
    if (formClosing) {
        formClosing.addEventListener('submit', () => {
            // Ambil semua kolom stok fisik
            document.querySelectorAll('.stok-input').forEach(kolom => {
                // Isi otomatis 0 bila kosong
                if (kolom.value === '') {
                    kolom.value = '0';
                }
            });
        });
    }
});