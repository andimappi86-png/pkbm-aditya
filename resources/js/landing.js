/**
 * landing.js — PKBM Aditya Landing Page Interactivity
 * Alpine.js data functions + vanilla progressive enhancements.
 */

// ── Alpine component: Panduan Cepat Pemilihan Program ────────────────────────
window.lpPanduan = function () {
    return {
        pendidikan: '',
        aktivitas: '',
        rekomendasi: null,

        hitungRekomendasi() {
            if (!this.pendidikan) {
                this.rekomendasi = null;
                return;
            }
            if (this.pendidikan === 'belum_sd') {
                this.rekomendasi = 'PA';
            } else if (this.pendidikan === 'lulus_sd') {
                this.rekomendasi = 'PB';
            } else {
                // lulus_smp → Paket C
                this.rekomendasi = 'PC';
            }
        },
    };
};

// ── Alpine component: Program Tab (Master-Detail Ledger) ─────────────────────
window.lpProgramTab = function (counts = {}) {
    const formatRombel = (key, fallback) => {
        if (counts && counts[key] !== undefined) {
            const count = parseInt(counts[key], 10);
            return count > 0 ? `${count} Rombel Aktif` : 'Belum Ada Rombel';
        }
        return fallback;
    };

    return {
        activeTab: 'PC',

        tabs: [
            {
                id: 'PA',
                label: 'PAKET A',
                sub: 'Setara SD / MI',
                rombel: formatRombel('PA', '3 Rombel Aktif'),
                jadwal: 'Sabtu & Minggu (Pagi) + Modul Mandiri',
                mapel: [
                    'Bahasa Indonesia & Literasi Terapan',
                    'Matematika Dasar & Numerasi Harian',
                    'Pendidikan Pancasila & Kewarganegaraan',
                    'Ilmu Pengetahuan Alam & Sosial (IPAS)',
                    'Pendidikan Agama & Budi Pekerti',
                ],
                syarat: [
                    'Fotokopi KK & KTP / KIA',
                    'Akte Kelahiran',
                    'Rapor terakhir (jika ada)',
                    'Pasfoto 3×4',
                ],
            },
            {
                id: 'PB',
                label: 'PAKET B',
                sub: 'Setara SMP / MTs',
                rombel: formatRombel('PB', '4 Rombel Aktif'),
                jadwal: 'Jumat Sore & Sabtu + Akses Modul Daring',
                mapel: [
                    'Bahasa Indonesia',
                    'Bahasa Inggris & Literasi Digital',
                    'Matematika & IPA',
                    'IPS & Sejarah Lokal',
                    'Pendidikan Pancasila & Agama',
                    'Keterampilan Fungsional & Prakarya',
                ],
                syarat: [
                    'Fotokopi Ijazah SD / MI / Paket A',
                    'KK & KTP / KIA',
                    'NISN',
                    'Pasfoto 3×4',
                ],
            },
            {
                id: 'PC',
                label: 'PAKET C',
                sub: 'Setara SMA / MA',
                rombel: formatRombel('PC', '5 Rombel Aktif'),
                jadwal: 'Akhir Pekan (Sabtu–Minggu) & Kelas Malam / Daring Fleksibel',
                mapel: [
                    'Bahasa Indonesia',
                    'Bahasa Inggris',
                    'Matematika',
                    'Pendidikan Pancasila',
                    'Peminatan IPS (Ekonomi, Sosiologi, Geografi, Sejarah)',
                    'Peminatan IPA (Biologi, Fisika, Kimia Terapan)',
                    'Keterampilan Wirausaha Mandiri',
                ],
                syarat: [
                    'Fotokopi Ijazah SMP / MTs / Paket B',
                    'KTP & KK',
                    'Rapor SMA / SMK (bagi pindahan)',
                    'Pasfoto 3×4',
                ],
            },
            {
                id: 'KH',
                label: 'KECAKAPAN HIDUP',
                sub: 'Pendampingan Vokasi',
                rombel: formatRombel('KH', '2 Rombel Aktif'),
                jadwal: 'Praktik Terjadwal Bersama Instruktur',
                mapel: [
                    'Komputer Perkantoran & Administrasi Digital',
                    'Pengolahan Hasil Pertanian & Kuliner Lokal Mamuju',
                    'Manajemen Usaha Mikro & Pemasaran Digital',
                    'Literasi Keuangan Keluarga',
                ],
                syarat: [
                    'KK & KTP / KIA',
                    'Pendaftaran melalui pengelola PKBM',
                ],
            },
        ],
    };
};

// ── Alpine component: Filter Pengumuman ──────────────────────────────────────
window.lpFilter = function () {
    return {
        activeFilter: 'semua',
        filters: [
            { value: 'semua',        label: 'Semua Informasi' },
            { value: 'pendaftaran',  label: 'Pendaftaran' },
            { value: 'akademik',     label: 'Jadwal & Ujian' },
        ],
    };
};

// ── Scroll-reveal observer (vanilla — lightweight) ────────────────────────────
(function initReveal() {
    const isReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (isReduced) return;

    const style = document.createElement('style');
    style.textContent = `
        .js-reveal .lp-reveal {
            opacity: 0;
            transform: translateY(18px);
            transition: opacity 0.5s ease, transform 0.5s ease;
        }
        .js-reveal .lp-reveal.is-visible {
            opacity: 1;
            transform: none;
        }
    `;
    document.head.appendChild(style);

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.1, rootMargin: '0px 0px -40px 0px' }
    );

    document.querySelectorAll('.lp-reveal').forEach((el) => observer.observe(el));
})();
