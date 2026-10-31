@php
    /**
     * Statistik lembaga dari DB — satu query per dimensi, tanpa N+1.
     */
    $programs = \App\Models\Program::query()
        ->where('status_aktif', true)
        ->orderBy('id')
        ->get(['id', 'kode', 'nama', 'deskripsi']);

    $rombelPerProgram = \App\Models\Classroom::query()
        ->where('status_aktif', true)
        ->selectRaw('program_id, count(*) as total')
        ->groupBy('program_id')
        ->pluck('total', 'program_id');

    $jumlahPesertaDidik = \App\Models\Student::query()->where('status', 'aktif')->count();
    $jumlahTutor = \App\Models\Tutor::query()->count();
    $jumlahRombel = \App\Models\Classroom::query()->where('status_aktif', true)->count();

    $rombelByKode = [];
    foreach ($programs as $prog) {
        $rombelByKode[$prog->kode] = (int) ($rombelPerProgram[$prog->id] ?? 0);
    }
    $vokasiCount = 0;
    foreach (['PKT-08', 'PKT-32', 'PKT-74', 'KH'] as $vCode) {
        if (isset($rombelByKode[$vCode])) {
            $vokasiCount += $rombelByKode[$vCode];
        }
    }
    $rombelByKode['KH'] = $vokasiCount;

    /**
     * Hanya pengumuman umum yang boleh tampil di halaman publik.
     * Filter: status_aktif = true, tanpa target peran/program/rombel,
     * published_at sudah lewat atau null.
     */
    $informasi = \App\Models\Announcement::query()
        ->where('status_aktif', true)
        ->whereNull('target_role')
        ->whereNull('target_program')
        ->whereNull('target_rombel')
        ->where(function ($q) {
            $q->whereNull('published_at')->orWhere('published_at', '<=', now());
        })
        ->orderByDesc('published_at')
        ->orderByDesc('id')
        ->take(3)
        ->get(['judul', 'isi', 'kategori', 'published_at', 'created_at']);

    $jenjang = [
        'PA' => 'Setara SD / MI',
        'PB' => 'Setara SMP / MTs',
        'PC' => 'Setara SMA / MA',
    ];

    /** SVG path data untuk inline icon */
    $icons = [
        'arrow-right'   => '<path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>',
        'book-open'     => '<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>',
        'layers'        => '<path d="m12 2 9 5-9 5-9-5 9-5Z"/><path d="m3 12 9 5 9-5"/><path d="m3 17 9 5 9-5"/>',
        'clipboard'     => '<rect width="8" height="4" x="8" y="2" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="m9 14 2 2 4-4"/>',
        'chart'         => '<path d="M3 3v18h18"/><path d="M18 17V9M13 17V5M8 17v-3"/>',
        'bell'          => '<path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/><path d="M4 17h16a8 8 0 0 0-3-6.2V8a5 5 0 0 0-10 0v2.8A8 8 0 0 0 4 17Z"/>',
        'users'         => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
        'laptop'        => '<path d="M20 16V7a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v9"/><path d="M2 16h20"/><path d="M12 16v4"/>',
        'map-pin'       => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
        'menu'          => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'close'         => '<path d="M18 6 6 18M6 6l12 12"/>',
        'check'         => '<path d="M20 6 9 17l-5-5"/>',
        'check-circle'  => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/>',
        'lock'          => '<rect width="18" height="11" x="3" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
        'calendar'      => '<rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/>',
        'user'          => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        'shield'        => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
        'eye'           => '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>',
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PKBM Aditya — Pendidikan Kesetaraan Kabupaten Mamuju</title>
    <meta name="description" content="PKBM Aditya menyelenggarakan program pendidikan kesetaraan Paket A, B, dan C di Kabupaten Mamuju, Sulawesi Barat, didukung sistem informasi pembelajaran digital.">
    <meta name="theme-color" content="#0E706A">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('images/logo-pkbm-aditya-96.png') }}">
    <link rel="apple-touch-icon" sizes="192x192" href="{{ asset('images/logo-pkbm-aditya-192.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>document.documentElement.classList.add('js-reveal');</script>
</head>
<body class="lp-page bg-warm-ivory antialiased overflow-x-hidden w-full text-navy"
      x-data="{
          menuOpen: false,
          scrolled: false,
          modalOpen: false,
          programTab: 'PC',
          roleTab: 'wargabelajar',
          filterPengumuman: 'semua',
          panduan: { pendidikan: '', aktivitas: '' },
          rekomendasiProgram: null,
          hitungRekomendasi() {
              const p = this.panduan.pendidikan;
              if (!p) { this.rekomendasiProgram = null; return; }
              if (p === 'belum_sd') this.rekomendasiProgram = 'PA';
              else if (p === 'lulus_sd') this.rekomendasiProgram = 'PB';
              else this.rekomendasiProgram = 'PC';
          }
      }"
      @scroll.window.passive="scrolled = window.pageYOffset > 16"
      @keydown.escape.window="menuOpen = false; modalOpen = false">

    <a href="#konten-utama" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[60] focus:rounded-xl focus:bg-pine-teal focus:px-4 focus:py-2.5 focus:text-sm focus:font-semibold focus:text-white">
        Lompat ke konten utama
    </a>

    {{-- ============================================================
         1. TOP INSTITUTIONAL BAR + STICKY HEADER
         ============================================================ --}}
    <div class="bg-brand-dark py-2 px-4 hidden sm:block border-b border-white/10" role="banner" aria-label="Status lembaga">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-2.5 text-[11px] font-medium text-white/70 tracking-wide">
                <span class="inline-block h-2 w-2 rounded-full bg-brand-accent-light" aria-hidden="true"></span>
                <span>Lembaga Pendidikan Nonformal Resmi</span>
                <span class="text-white/30" aria-hidden="true">•</span>
                <span>Kabupaten Mamuju, Provinsi Sulawesi Barat</span>
            </div>
            <div class="text-[11px] font-medium text-white/60">
                Tahun Ajaran Aktif: <span class="text-white/90 font-semibold">2026/2027</span>
            </div>
        </div>
    </div>

    <header class="sticky top-0 z-50 transition-all duration-300 bg-warm-ivory/95 backdrop-blur-md border-b border-warm-border"
            aria-label="Navigasi PKBM Aditya">
        <div class="mx-auto flex h-20 w-full max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">

            {{-- Logo Emblem --}}
            <a href="{{ route('landing') }}" class="group flex items-center gap-3 rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-pine-teal/40 focus-visible:ring-offset-2" aria-label="PKBM Aditya — halaman utama">
                <img src="{{ asset('images/logo-pkbm-aditya-96.png') }}"
                     alt="Logo Resmi PKBM Aditya Kabupaten Mamuju"
                     width="40"
                     height="40"
                     class="h-10 w-10 shrink-0 object-contain">
                <span class="flex flex-col leading-none">
                    <span class="font-display text-[15px] font-extrabold tracking-tight text-navy">PKBM ADITYA</span>
                    <span class="mt-0.5 text-[10px] font-bold uppercase tracking-[0.16em] text-brand/80">KABUPATEN MAMUJU</span>
                </span>
            </a>

            {{-- Navigasi Utama --}}
            <nav class="hidden items-center gap-1 lg:flex" aria-label="Navigasi utama">
                <a href="#tentang" class="rounded-lg px-3 py-2 text-sm font-medium text-muted whitespace-nowrap transition-colors hover:bg-warm-sand hover:text-navy focus:outline-none focus-visible:ring-2 focus-visible:ring-pine-teal/40">Tentang Lembaga</a>
                <a href="#program" class="rounded-lg px-3 py-2 text-sm font-medium text-muted whitespace-nowrap transition-colors hover:bg-warm-sand hover:text-navy focus:outline-none focus-visible:ring-2 focus-visible:ring-pine-teal/40">Jalur Kesetaraan</a>
                <a href="#sistem" class="rounded-lg px-3 py-2 text-sm font-medium text-muted whitespace-nowrap transition-colors hover:bg-warm-sand hover:text-navy focus:outline-none focus-visible:ring-2 focus-visible:ring-pine-teal/40">Ruang Belajar Digital</a>
                <a href="#informasi" class="rounded-lg px-3 py-2 text-sm font-medium text-muted whitespace-nowrap transition-colors hover:bg-warm-sand hover:text-navy focus:outline-none focus-visible:ring-2 focus-visible:ring-pine-teal/40">Papan Maklumat</a>
            </nav>

            {{-- CTA Header --}}
            <div class="flex items-center gap-2">
                <a href="#program" class="hidden items-center gap-1.5 rounded-xl border border-warm-border bg-warm-sand px-4 py-2 text-sm font-semibold text-navy whitespace-nowrap shrink-0 transition-colors hover:border-pine-teal/40 hover:bg-warm-sand focus:outline-none focus-visible:ring-2 focus-visible:ring-pine-teal/40 lg:inline-flex">
                    Cek Jenjang Paket
                </a>
                @auth
                    <a href="{{ route('dashboard') }}" class="hidden items-center gap-1.5 rounded-xl bg-pine-teal px-4 py-2 text-sm font-semibold text-white whitespace-nowrap shrink-0 transition-colors hover:bg-pine-teal-hover focus:outline-none focus-visible:ring-2 focus-visible:ring-pine-teal/40 focus-visible:ring-offset-2 lg:inline-flex">
                        Dashboard →
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="hidden items-center gap-1.5 rounded-xl bg-pine-teal px-4 py-2 text-sm font-semibold text-white whitespace-nowrap shrink-0 transition-colors hover:bg-pine-teal-hover focus:outline-none focus-visible:ring-2 focus-visible:ring-pine-teal/40 focus-visible:ring-offset-2 lg:inline-flex">
                        Masuk Sistem →
                    </a>
                @endauth

                {{-- Hamburger Mobile --}}
                <button type="button"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-warm-border bg-white text-navy transition-colors hover:bg-warm-sand focus:outline-none focus-visible:ring-2 focus-visible:ring-pine-teal/40 lg:hidden"
                        @click="menuOpen = !menuOpen"
                        :aria-expanded="menuOpen.toString()"
                        aria-controls="menu-mobile"
                        aria-label="Buka menu navigasi">
                    <svg x-show="!menuOpen" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true">{!! $icons['menu'] !!}</svg>
                    <svg x-show="menuOpen" x-cloak class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true">{!! $icons['close'] !!}</svg>
                </button>
            </div>
        </div>

        {{-- Menu Mobile --}}
        <div id="menu-mobile" x-show="menuOpen" x-cloak x-transition.opacity.duration.150ms
             class="border-t border-warm-border bg-warm-ivory lg:hidden">
            <nav class="mx-auto grid w-full max-w-7xl gap-1 px-4 sm:px-6 lg:px-8 py-4" aria-label="Navigasi seluler">
                <a href="#tentang" @click="menuOpen = false" class="rounded-lg px-3 py-3 text-[15px] font-medium text-navy hover:bg-warm-sand">Tentang Lembaga</a>
                <a href="#program" @click="menuOpen = false" class="rounded-lg px-3 py-3 text-[15px] font-medium text-navy hover:bg-warm-sand">Jalur Kesetaraan</a>
                <a href="#sistem" @click="menuOpen = false" class="rounded-lg px-3 py-3 text-[15px] font-medium text-navy hover:bg-warm-sand">Ruang Belajar Digital</a>
                <a href="#informasi" @click="menuOpen = false" class="rounded-lg px-3 py-3 text-[15px] font-medium text-navy hover:bg-warm-sand">Papan Maklumat</a>
                <div class="mt-2 border-t border-warm-border pt-3">
                    @auth
                        <a href="{{ route('dashboard') }}" @click="menuOpen = false" class="flex items-center justify-center gap-2 rounded-xl bg-pine-teal px-5 py-3 text-[15px] font-semibold text-white">Ke Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="w-full flex items-center justify-center gap-2 rounded-xl bg-pine-teal px-5 py-3 text-[15px] font-semibold text-white">Masuk Sistem →</a>
                    @endauth
                </div>
            </nav>
        </div>
    </header>

    <main id="konten-utama">

        {{-- ============================================================
             2. HERO — ASYMMETRIC EDITORIAL SPLIT (7 : 5 COLUMN)
             ============================================================ --}}
        <section class="relative overflow-hidden bg-warm-ivory pt-10 pb-0 lg:pt-14">
            <div class="lp-dot-field pointer-events-none absolute inset-0" aria-hidden="true"></div>

            <div class="relative mx-auto grid w-full max-w-7xl items-start gap-12 px-4 sm:px-6 lg:px-8 lg:grid-cols-12 lg:gap-10">

                {{-- Kolom Kiri (7 Kolom) --}}
                <div class="lp-hero-copy lg:col-span-7 pb-16">

                    {{-- Eyebrow --}}
                    <div class="flex items-center gap-3 border-l-4 border-terracotta pl-4">
                        <span class="text-[11px] font-bold uppercase tracking-[0.14em] text-terracotta">
                            PUSAT KEGIATAN BELAJAR MASYARAKAT • SULAWESI BARAT
                        </span>
                    </div>

                    {{-- Judul Utama --}}
                    <h1 class="mt-6 font-display text-[2.2rem] font-extrabold leading-[1.1] tracking-[-0.025em] text-navy sm:text-[2.8rem] lg:text-[3.4rem]">
                        Pendidikan kesetaraan yang <em class="font-serif-editorial font-normal not-italic italic text-pine-teal">membumi</em><br class="hidden sm:block"> dan terjangkau<br class="hidden lg:block"> bagi warga Mamuju.
                    </h1>

                    {{-- Paragraf Penjelas --}}
                    <p class="mt-5 max-w-xl text-[15px] leading-relaxed text-muted sm:text-base">
                        PKBM Aditya menyelenggarakan <strong class="font-semibold text-navy">Paket A (Setara SD)</strong>, <strong class="font-semibold text-navy">Paket B (Setara SMP)</strong>, dan <strong class="font-semibold text-navy">Paket C (Setara SMA)</strong> dengan bimbingan tutor tatap muka serta sistem pembelajaran terpadu — dirancang bagi warga belajar yang bekerja atau mengurus keluarga.
                    </p>

                    {{-- Panduan Cepat Pemilihan Program --}}
                    <div class="mt-8 rounded-xl bg-warm-sand border border-warm-border p-5"
                         x-data="lpPanduan()"
                         role="form"
                         aria-label="Panduan Cepat Pemilihan Program">
                        <p class="text-[12px] font-bold uppercase tracking-[0.16em] text-terracotta mb-4">
                            Panduan Cepat Pemilihan Program
                        </p>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <div>
                                <label for="panduan-pendidikan" class="block mb-1.5 text-[12px] font-semibold text-navy">Pendidikan Terakhir</label>
                                <select id="panduan-pendidikan" class="lp-select py-2.5 px-3" x-model="pendidikan" @change="hitungRekomendasi()">
                                    <option value="">— Pilih pendidikan terakhir —</option>
                                    <option value="lulus_smp">Lulus SMP / MTs / Putus SMA</option>
                                    <option value="lulus_sd">Lulus SD / MI / Putus SMP</option>
                                    <option value="belum_sd">Belum Tamat SD / Baru Belajar</option>
                                </select>
                            </div>
                            <div>
                                <label for="panduan-aktivitas" class="block mb-1.5 text-[12px] font-semibold text-navy">Aktivitas Harian</label>
                                <select id="panduan-aktivitas" class="lp-select py-2.5 px-3" x-model="aktivitas">
                                    <option value="">— Pilih aktivitas harian —</option>
                                    <option value="kerja">Sambil Bekerja / Usaha</option>
                                    <option value="reguler">Usia Sekolah / Reguler</option>
                                    <option value="keluarga">Mengurus Keluarga</option>
                                </select>
                            </div>
                        </div>

                        <button type="button"
                                @click="hitungRekomendasi()"
                                class="mt-3.5 inline-flex items-center gap-1.5 rounded-xl bg-pine-teal px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-pine-teal-hover focus:outline-none focus-visible:ring-2 focus-visible:ring-pine-teal/40 focus-visible:ring-offset-2">
                            Lihat Jalur →
                        </button>

                        {{-- Hasil Rekomendasi --}}
                        <div x-show="rekomendasi !== null" x-cloak x-transition.opacity.duration.200ms
                             class="mt-4 border-t border-warm-border pt-4">
                            <template x-if="rekomendasi === 'PC'">
                                <div>
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="inline-flex items-center rounded-full bg-pine-teal/10 border border-pine-teal/20 px-3 py-0.5 text-[11px] font-bold uppercase tracking-wider text-pine-teal">Paket C Disarankan</span>
                                        <span class="text-[11px] text-muted">Setara SMA / MA</span>
                                    </div>
                                    <p class="text-sm text-muted leading-relaxed">{{ ($rombelByKode['PC'] ?? 0) > 0 ? ($rombelByKode['PC'] . ' Rombel aktif · ') : 'Jalur aktif · ' }}Kelas akhir pekan + malam fleksibel · Peminatan IPS &amp; IPA tersedia.</p>
                                    <a href="#program" class="mt-2 inline-flex items-center gap-1 text-sm font-semibold text-pine-teal hover:text-pine-teal-hover transition-colors">Pelajari Kurikulum →</a>
                                </div>
                            </template>
                            <template x-if="rekomendasi === 'PB'">
                                <div>
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="inline-flex items-center rounded-full bg-pine-teal/10 border border-pine-teal/20 px-3 py-0.5 text-[11px] font-bold uppercase tracking-wider text-pine-teal">Paket B Disarankan</span>
                                        <span class="text-[11px] text-muted">Setara SMP / MTs</span>
                                    </div>
                                    <p class="text-sm text-muted leading-relaxed">{{ ($rombelByKode['PB'] ?? 0) > 0 ? ($rombelByKode['PB'] . ' Rombel aktif · ') : 'Jalur aktif · ' }}Jumat sore &amp; Sabtu + akses modul daring · Cocok sambil bekerja.</p>
                                    <a href="#program" class="mt-2 inline-flex items-center gap-1 text-sm font-semibold text-pine-teal hover:text-pine-teal-hover transition-colors">Pelajari Kurikulum →</a>
                                </div>
                            </template>
                            <template x-if="rekomendasi === 'PA'">
                                <div>
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="inline-flex items-center rounded-full bg-pine-teal/10 border border-pine-teal/20 px-3 py-0.5 text-[11px] font-bold uppercase tracking-wider text-pine-teal">Paket A Disarankan</span>
                                        <span class="text-[11px] text-muted">Setara SD / MI</span>
                                    </div>
                                    <p class="text-sm text-muted leading-relaxed">{{ ($rombelByKode['PA'] ?? 0) > 0 ? ($rombelByKode['PA'] . ' Rombel aktif · ') : 'Jalur aktif · ' }}Sabtu &amp; Minggu (pagi) + modul mandiri · Literasi &amp; numerasi dasar.</p>
                                    <a href="#program" class="mt-2 inline-flex items-center gap-1 text-sm font-semibold text-pine-teal hover:text-pine-teal-hover transition-colors">Pelajari Kurikulum →</a>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Data Faktual --}}
                    <dl class="mt-8 grid max-w-lg grid-cols-3">
                        <div class="pr-6">
                            <dd class="font-display text-[2rem] font-extrabold tabular-nums text-navy" data-count-to="{{ $jumlahPesertaDidik }}">{{ number_format($jumlahPesertaDidik, 0, ',', '.') }}</dd>
                            <dt class="mt-1 text-[12px] font-semibold text-muted leading-tight">Warga Belajar Aktif</dt>
                        </div>
                        <div class="border-l border-warm-border pl-6 pr-6">
                            <dd class="font-display text-[2rem] font-extrabold tabular-nums text-navy" data-count-to="{{ $jumlahTutor }}">{{ number_format($jumlahTutor, 0, ',', '.') }}</dd>
                            <dt class="mt-1 text-[12px] font-semibold text-muted leading-tight">Tutor Pendamping</dt>
                        </div>
                        <div class="border-l border-warm-border pl-6">
                            <dd class="font-display text-[2rem] font-extrabold tabular-nums text-navy">{{ $jumlahRombel }}</dd>
                            <dt class="mt-1 text-[12px] font-semibold text-muted leading-tight">Rombel dalam<br>{{ $programs->count() }} Program</dt>
                        </div>
                    </dl>
                </div>

                {{-- Kolom Kanan (5 Kolom) --}}
                <aside class="lg:col-span-5 flex flex-col gap-4 pb-8 lg:pb-16 lg:pt-4" aria-label="Dokumentasi dan identitas lembaga">

                    {{-- Foto Dokumentasi --}}
                    <div class="relative overflow-hidden rounded-2xl border border-warm-border bg-warm-sand">
                        <img src="{{ asset('images/foto-formal-pkbm-aditya-1100.jpeg') }}"
                             alt="Dokumentasi kegiatan pembelajaran tatap muka warga belajar PKBM Aditya bersama tutor"
                             width="1100" height="700"
                             class="w-full h-64 lg:h-72 object-cover"
                             fetchpriority="high"
                             decoding="async">
                        <div class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-brand-dark/80 to-transparent px-4 pb-3 pt-8">
                            <p class="text-[11px] font-medium text-white/90 tracking-wide">
                                Dokumentasi Pembelajaran &bull; Kab. Mamuju
                            </p>
                        </div>
                    </div>

                    {{-- Tabel Identitas Satuan Pendidikan --}}
                    <div class="rounded-2xl border border-warm-border bg-white p-5">
                        <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-terracotta mb-3">Identitas Satuan Pendidikan</p>
                        <dl class="grid grid-cols-2 gap-y-3 gap-x-4">
                            <div>
                                <dt class="text-[11px] font-semibold uppercase tracking-[0.1em] text-subtle">Kedudukan Wilayah</dt>
                                <dd class="mt-0.5 text-sm font-semibold text-navy">Kab. Mamuju, Sulawesi Barat</dd>
                            </div>
                            <div>
                                <dt class="text-[11px] font-semibold uppercase tracking-[0.1em] text-subtle">Rombongan Belajar</dt>
                                <dd class="mt-0.5 text-sm font-semibold tabular-nums text-navy">{{ $jumlahRombel }} Rombel Aktif</dd>
                            </div>
                            <div>
                                <dt class="text-[11px] font-semibold uppercase tracking-[0.1em] text-subtle">Metode Belajar</dt>
                                <dd class="mt-0.5 text-sm font-semibold text-navy">Tatap Muka, Tutorial &amp; Mandiri</dd>
                            </div>
                            <div>
                                <dt class="text-[11px] font-semibold uppercase tracking-[0.1em] text-subtle">Layanan Akademik</dt>
                                <dd class="mt-0.5 flex items-center gap-2">
                                    <span class="text-sm font-semibold text-navy">Portal SIM PKBM</span>
                                    <span class="inline-flex items-center gap-1 rounded-full bg-green-50 border border-green-200 px-2 py-0.5 text-[10px] font-bold text-green-700">
                                        <span class="inline-block h-1.5 w-1.5 rounded-full bg-green-500"></span>
                                        Terhubung
                                    </span>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </aside>
            </div>
        </section>

        {{-- Garis pembatas bersih --}}
        <div class="border-b border-warm-border"></div>

        {{-- ============================================================
             3. TENTANG LEMBAGA — EDITORIAL MAGAZINE SPREAD
             ============================================================ --}}
        <section id="tentang" class="scroll-mt-24 bg-white border-b border-warm-border py-16 lg:py-24">
            <div class="mx-auto grid w-full max-w-7xl gap-12 px-4 sm:px-6 lg:px-8 lg:grid-cols-12 lg:gap-16">

                {{-- 5 Kolom Kiri --}}
                <div class="lp-reveal lg:col-span-5">
                    <p class="lp-label">Tentang Lembaga</p>
                    <h2 class="mt-4 font-display text-[1.8rem] font-extrabold leading-tight tracking-[-0.02em] text-navy lg:text-[2.2rem]">
                        Dirancang mengikuti ritme hidup warga belajar, <em class="font-serif-editorial font-normal not-italic italic text-pine-teal">bukan sebaliknya</em>.
                    </h2>
                    <p class="mt-5 text-[15px] leading-relaxed text-muted">
                        Kabupaten Mamuju menyimpan ribuan warga dewasa yang putus sekolah karena keterbatasan akses, jarak, dan tanggung jawab keluarga. PKBM Aditya hadir sebagai jalur resmi negara — program kesetaraan yang berijazah, dapat digunakan untuk melamar kerja, dan diakui untuk melanjutkan pendidikan ke jenjang lebih tinggi.
                    </p>
                    <blockquote class="mt-6 border-l-4 border-terracotta pl-5 py-1">
                        <p class="font-serif-editorial text-[1.2rem] italic leading-snug text-navy">
                            "Pendidikan bukan kemewahan — ia adalah hak setiap warga, termasuk yang bekerja dan mengurus keluarga."
                        </p>
                        <footer class="mt-2 text-[12px] font-semibold uppercase tracking-[0.12em] text-subtle">— Pengelola PKBM Aditya</footer>
                    </blockquote>
                </div>

                {{-- 7 Kolom Kanan — 3 Pilar --}}
                <div class="lp-reveal lg:col-span-7">
                    <ol class="divide-y divide-warm-border border-y border-warm-border" aria-label="Tiga pilar lembaga">
                        <li class="flex gap-5 py-7">
                            <span class="font-serif-editorial text-[2.5rem] font-bold italic text-pine-teal/30 leading-none mt-1 w-10 shrink-0 select-none" aria-label="Pilar pertama">I.</span>
                            <div>
                                <h3 class="font-display text-[15px] font-bold text-navy">Tiga Jenjang Kesetaraan Berijazah Negara</h3>
                                <p class="mt-2 text-sm leading-relaxed text-muted">
                                    Paket A, B, dan C menghasilkan ijazah resmi yang sah digunakan untuk melamar pekerjaan, mendaftar kuliah, dan mengurus administrasi kependudukan. Kesetaraan bukan jalan pintas — ia adalah jembatan formal bagi warga yang memerlukannya.
                                </p>
                                <div class="mt-3 flex flex-wrap gap-1.5">
                                    <span class="rounded-full border border-warm-border bg-warm-ivory px-2.5 py-1 text-[11px] font-semibold text-muted">Paket A — SD/MI</span>
                                    <span class="rounded-full border border-warm-border bg-warm-ivory px-2.5 py-1 text-[11px] font-semibold text-muted">Paket B — SMP/MTs</span>
                                    <span class="rounded-full border border-warm-border bg-warm-ivory px-2.5 py-1 text-[11px] font-semibold text-muted">Paket C — SMA/MA</span>
                                </div>
                            </div>
                        </li>
                        <li class="flex gap-5 py-7">
                            <span class="font-serif-editorial text-[2.5rem] font-bold italic text-pine-teal/30 leading-none mt-1 w-10 shrink-0 select-none" aria-label="Pilar kedua">II.</span>
                            <div>
                                <h3 class="font-display text-[15px] font-bold text-navy">Pembelajaran &amp; Administrasi dalam Satu Sistem</h3>
                                <p class="mt-2 text-sm leading-relaxed text-muted">
                                    Modul belajar, tugas, kuis, presensi, dan rapor dikelola melalui SIM PKBM Aditya. Tutor, wali kelas, dan pengelola bekerja pada data yang sama — tanpa duplikasi catatan atau formulir kertas yang berulang.
                                </p>
                                <p class="mt-2 flex items-center gap-2 text-[12px] font-semibold text-pine-teal">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons['check-circle'] !!}</svg>
                                    Portal SIM PKBM Terpadu — Sistem Terhubung
                                </p>
                            </div>
                        </li>
                        <li class="flex gap-5 py-7">
                            <span class="font-serif-editorial text-[2.5rem] font-bold italic text-pine-teal/30 leading-none mt-1 w-10 shrink-0 select-none" aria-label="Pilar ketiga">III.</span>
                            <div>
                                <h3 class="font-display text-[15px] font-bold text-navy">Pendampingan Langsung oleh {{ $jumlahTutor }} Tutor Terdaftar</h3>
                                <p class="mt-2 text-sm leading-relaxed text-muted">
                                    Setiap rombel belajar didampingi tutor mata pelajaran dan wali kelas yang bertanggung jawab penuh terhadap kemajuan warga belajar — dari penyusunan materi, penilaian, hingga bimbingan tatap muka langsung.
                                </p>
                            </div>
                        </li>
                    </ol>
                </div>
            </div>
        </section>

        {{-- ============================================================
             4. JALUR PROGRAM KESETARAAN — INTERACTIVE MASTER-DETAIL LEDGER
             ============================================================ --}}
        <section id="program" class="scroll-mt-24 bg-warm-ivory border-b border-warm-border py-16 lg:py-24">
            <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">

                <div class="lp-reveal mb-10">
                    <p class="lp-label">Jalur Program Kesetaraan &amp; Kecakapan</p>
                    <h2 class="mt-3 font-display text-[1.8rem] font-extrabold leading-tight tracking-[-0.02em] text-navy lg:text-[2.2rem]">
                        Program yang bisa Anda pilih sesuai jenjang dan jadwal.
                    </h2>
                    <p class="mt-3 text-[15px] leading-relaxed text-muted max-w-2xl">
                        Pilih tab program di bawah untuk melihat jadwal, mata pelajaran, dan syarat pendaftaran secara lengkap.
                    </p>
                </div>

                {{-- Konten Program (Alpine-driven tabs) --}}
                <div class="lp-reveal grid grid-cols-1 lg:grid-cols-12 gap-8 items-start" x-data="lpProgramTab(@js($rombelByKode))">

                    {{-- Tab Pemilih (4 Kolom Kiri) --}}
                    <div class="lg:col-span-4 flex flex-col gap-3" role="tablist" aria-label="Pilih program kesetaraan">
                        <template x-for="tab in tabs" :key="tab.id">
                            <button type="button"
                                    role="tab"
                                    :id="'tab-' + tab.id"
                                    :aria-selected="activeTab === tab.id"
                                    :aria-controls="'panel-' + tab.id"
                                    :class="activeTab === tab.id ? 'bg-pine-teal text-white border-pine-teal shadow-soft' : 'bg-white text-muted border-warm-border hover:bg-warm-sand hover:text-navy'"
                                    class="w-full rounded-xl border p-5 text-left transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-pine-teal/40"
                                    @click="activeTab = tab.id">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <span class="block font-display text-base font-bold leading-tight" x-text="tab.label"></span>
                                        <span class="block text-sm mt-1 opacity-80 font-medium" x-text="tab.sub"></span>
                                    </div>
                                    <span class="shrink-0 inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-bold"
                                          :class="activeTab === tab.id ? 'bg-white/20 text-white' : 'bg-pine-teal/10 text-pine-teal border border-pine-teal/20'"
                                          x-text="tab.rombel"></span>
                                </div>
                            </button>
                        </template>
                    </div>

                    {{-- Panel Detail (8 Kolom Kanan) --}}
                    <div class="lg:col-span-8 min-w-0">
                        <template x-for="tab in tabs" :key="tab.id">
                            <div :id="'panel-' + tab.id"
                                 role="tabpanel"
                                 :aria-labelledby="'tab-' + tab.id"
                                 x-show="activeTab === tab.id"
                                 x-transition.opacity.duration.200ms
                                 class="bg-white rounded-2xl border border-warm-border p-6 sm:p-8 shadow-sm">

                                <div class="flex items-start justify-between gap-4 flex-wrap pb-6 border-b border-warm-border">
                                    <div>
                                        <span class="font-display text-[11px] font-bold uppercase tracking-[0.16em] text-terracotta" x-text="tab.label"></span>
                                        <h3 class="mt-1 font-display text-2xl lg:text-3xl font-extrabold text-navy" x-text="tab.sub"></h3>
                                    </div>
                                    <span class="inline-flex items-center rounded-full bg-pine-teal/10 border border-pine-teal/20 px-3.5 py-1 text-xs font-bold text-pine-teal" x-text="tab.rombel"></span>
                                </div>

                                <div class="mt-6 grid gap-6 sm:grid-cols-3">
                                    {{-- Jadwal --}}
                                    <div>
                                        <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-subtle mb-2">Jadwal Belajar</p>
                                        <p class="text-sm leading-relaxed text-navy" x-text="tab.jadwal"></p>
                                    </div>

                                    {{-- Mata Pelajaran --}}
                                    <div class="sm:col-span-2">
                                        <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-subtle mb-2">Mata Pelajaran / Muatan</p>
                                        <ul class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            <template x-for="mapel in tab.mapel" :key="mapel">
                                                <li class="flex items-start gap-2 text-sm text-navy">
                                                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-pine-teal" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                                                    <span x-text="mapel"></span>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                </div>

                                {{-- Syarat Pendaftaran --}}
                                <div class="mt-6 rounded-xl bg-warm-sand border border-warm-border p-5">
                                    <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-terracotta mb-2.5">Syarat Pendaftaran</p>
                                    <ul class="flex flex-wrap gap-2">
                                        <template x-for="syarat in tab.syarat" :key="syarat">
                                            <li class="inline-flex items-center rounded-full border border-warm-border bg-white px-3 py-1 text-xs font-medium text-navy" x-text="syarat"></li>
                                        </template>
                                    </ul>
                                </div>

                                {{-- CTA dalam tab --}}
                                <div class="mt-6 flex items-center gap-3 flex-wrap border-t border-warm-border pt-5">
                                    @auth
                                        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-pine-teal px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-pine-teal-hover">
                                            Akses Dashboard →
                                        </a>
                                    @else
                                        <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-pine-teal px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-pine-teal-hover focus:outline-none focus-visible:ring-2 focus-visible:ring-pine-teal/40">
                                            Masuk Sistem →
                                        </a>
                                    @endauth
                                    <a href="#informasi" class="text-sm font-semibold text-pine-teal hover:text-pine-teal-hover transition-colors">Lihat Pengumuman Pendaftaran</a>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                @if($programs->isNotEmpty())
                    <div class="mt-12 border-t border-warm-border pt-8">
                        <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
                            <div>
                                <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-terracotta">Status Kurikulum Lembaga</p>
                                <h3 class="text-base font-bold text-navy">Program Terdaftar Aktif di PKBM Aditya</h3>
                            </div>
                            <span class="text-xs text-subtle">{{ $programs->count() }} program terdaftar</span>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach($programs as $prog)
                                <div class="rounded-xl border border-warm-border bg-white p-4 shadow-2xs">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="inline-flex items-center rounded-lg bg-pine-teal/10 px-2 py-0.5 text-xs font-bold text-pine-teal">{{ $prog->kode }}</span>
                                        <span class="text-xs text-subtle tabular-nums">{{ $rombelPerProgram[$prog->id] ?? 0 }} rombel</span>
                                    </div>
                                    <h4 class="mt-2 text-sm font-bold text-navy">{{ $prog->nama }}</h4>
                                    @if($prog->deskripsi)
                                        <p class="mt-1 text-xs text-muted leading-relaxed line-clamp-2">{{ $prog->deskripsi }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </section>

        {{-- ============================================================
             5. RUANG BELAJAR DIGITAL / SIM PKBM — ROLE WORKSPACE PREVIEW
             ============================================================ --}}
        <section id="sistem" class="scroll-mt-24 w-full bg-brand-darkest text-white border-b border-white/10 py-20 lg:py-24 overflow-hidden">
            <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8 flex flex-col gap-12"
                 x-data="{ role: 'wargabelajar' }">

                {{-- Baris 1: Header kiri + Tab Peran kanan --}}
                <div class="lp-reveal flex flex-col lg:flex-row lg:items-end justify-between gap-6 pb-8 border-b border-white/10">
                    <div class="max-w-2xl">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-terracotta">Ruang Belajar Digital</p>
                        <h2 class="mt-3 font-display text-3xl lg:text-4xl font-extrabold leading-tight tracking-[-0.02em] text-white">
                            SIM PKBM Aditya — satu sistem untuk semua peran.
                        </h2>
                        <p class="mt-3 text-[15px] leading-relaxed text-white/60">
                            Warga belajar, tutor, wali kelas, dan operator bekerja pada data yang sama tanpa pencatatan ganda.
                        </p>
                    </div>

                    {{-- Tab Peran Pills --}}
                    <div class="flex flex-wrap gap-2 shrink-0" role="tablist" aria-label="Pilih tampilan peran">
                        <button type="button" role="tab"
                                :aria-selected="role === 'wargabelajar'"
                                :class="role === 'wargabelajar' ? 'bg-pine-teal text-white' : 'bg-white/10 text-white/60 hover:bg-white/20 hover:text-white'"
                                class="whitespace-nowrap rounded-xl px-4 py-2.5 text-sm font-semibold transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-white/50"
                                @click="role = 'wargabelajar'">
                            Warga Belajar
                        </button>
                        <button type="button" role="tab"
                                :aria-selected="role === 'tutor'"
                                :class="role === 'tutor' ? 'bg-pine-teal text-white' : 'bg-white/10 text-white/60 hover:bg-white/20 hover:text-white'"
                                class="whitespace-nowrap rounded-xl px-4 py-2.5 text-sm font-semibold transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-white/50"
                                @click="role = 'tutor'">
                            Tutor Pengajar
                        </button>
                        <button type="button" role="tab"
                                :aria-selected="role === 'admin'"
                                :class="role === 'admin' ? 'bg-pine-teal text-white' : 'bg-white/10 text-white/60 hover:bg-white/20 hover:text-white'"
                                class="whitespace-nowrap rounded-xl px-4 py-2.5 text-sm font-semibold transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-white/50"
                                @click="role = 'admin'">
                            Wali Kelas &amp; Admin
                        </button>
                    </div>
                </div>

                {{-- Baris 2: Konten per-peran — Grid 12 kolom --}}

                {{-- Panel Warga Belajar --}}
                <div x-show="role === 'wargabelajar'" x-transition.opacity.duration.200ms
                     class="lp-reveal grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-center"
                     role="tabpanel" aria-label="Tampilan Warga Belajar">
                    <div class="lg:col-span-5 min-w-0">
                        <h3 class="font-display text-[1.2rem] font-bold text-white mb-3">Akses belajar di mana saja</h3>
                        <p class="text-[15px] leading-relaxed text-white/80 mb-6">Warga belajar dapat melihat jadwal tatap muka, mengunduh modul, mengumpulkan tugas, mengikuti kuis, dan memantau kehadiran serta rapor semester langsung dari portal.</p>
                        <ul class="space-y-2.5">
                            @foreach(['Jadwal pertemuan & modul belajar', 'Pengumpulan tugas & kuis daring', 'Pantau kehadiran — target 80%+', 'Unduh rapor semester digital'] as $f)
                            <li class="flex items-center gap-2.5 text-sm text-white/70">
                                <svg class="h-4 w-4 shrink-0 text-terracotta" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons['check'] !!}</svg>
                                {{ $f }}
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="lg:col-span-7 min-w-0 w-full">
                        <div class="w-full max-w-full rounded-2xl bg-brand-dark border border-white/15 p-6 sm:p-8 shadow-2xl overflow-hidden"
                             role="img" aria-label="Mockup dashboard warga belajar">
                            <div class="flex items-center justify-between mb-4 pb-3 border-b border-white/10">
                                <div>
                                    <p class="text-[11px] font-bold uppercase tracking-wider text-white/40">Pratinjau Antarmuka Siswa</p>
                                    <p class="text-sm font-semibold text-white mt-0.5">Halo, Siti Aminah 👋</p>
                                </div>
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-green-900/50 border border-green-700/40 px-3 py-1 text-[11px] font-bold text-green-400">
                                    <span class="h-1.5 w-1.5 rounded-full bg-green-400"></span>
                                    Status Aktif
                                </span>
                            </div>
                            <div class="grid grid-cols-2 gap-3 mb-4">
                                <div class="rounded-xl border border-white/10 bg-brand-surface px-3 py-3">
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-1">Jadwal Terdekat</p>
                                    <p class="text-sm font-semibold text-white">Sabtu, 07:30</p>
                                    <p class="text-[11px] text-white/50">Matematika — Kelas VII-B</p>
                                </div>
                                <div class="rounded-xl border border-white/10 bg-brand-surface px-3 py-3">
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-white/40 mb-1">Kuis Aktif</p>
                                    <p class="text-sm font-semibold text-white">2 Kuis</p>
                                    <p class="text-[11px] text-white/50">Tenggat: 3 hari lagi</p>
                                </div>
                            </div>
                            <div class="rounded-xl border border-pine-teal/30 bg-pine-teal/10 px-3 py-3 flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-pine-teal mb-0.5">Rapor Semester Ganjil</p>
                                    <p class="text-sm font-semibold text-white">Tersedia untuk diunduh</p>
                                </div>
                                <svg class="h-5 w-5 shrink-0 text-pine-teal" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Panel Tutor --}}
                <div x-show="role === 'tutor'" x-cloak x-transition.opacity.duration.200ms
                     class="lp-reveal grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-center"
                     role="tabpanel" aria-label="Tampilan Tutor Pengajar">
                    <div class="lg:col-span-5 min-w-0">
                        <h3 class="font-display text-[1.2rem] font-bold text-white mb-3">Kelola kelas &amp; penilaian dengan mudah</h3>
                        <p class="text-[15px] leading-relaxed text-white/80 mb-6">{{ $jumlahTutor }} tutor terdaftar mengelola presensi harian, unggah materi, dan penilaian tugas serta kuis untuk rombel yang diampu — semuanya dalam satu workspace terpadu.</p>
                        <ul class="space-y-2.5">
                            @foreach(['Input presensi harian warga belajar', 'Unggah modul & materi belajar', 'Kelola tugas & nilai kuis', 'Pantau progres rombel aktif'] as $f)
                            <li class="flex items-center gap-2.5 text-sm text-white/70">
                                <svg class="h-4 w-4 shrink-0 text-terracotta" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons['check'] !!}</svg>
                                {{ $f }}
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="lg:col-span-7 min-w-0 w-full">
                        <div class="w-full max-w-full rounded-2xl bg-brand-dark border border-white/15 p-6 sm:p-8 shadow-2xl overflow-hidden">
                            <div class="mb-4 pb-3 border-b border-white/10">
                                <p class="text-[11px] font-bold uppercase tracking-wider text-white/40">Presensi Hari Ini</p>
                                <p class="text-sm font-semibold text-white mt-0.5">Kelas Paket C — Sabtu, 07:30</p>
                            </div>
                            <ul class="space-y-2.5">
                                @foreach([['Ahmad Fauzan', 'H'], ['Dewi Maharani', 'H'], ['Siti Rahayu', 'S'], ['Arif Budiman', 'H'], ['Rina Wati', 'H']] as $row)
                                <li class="flex items-center justify-between">
                                    <span class="text-sm text-white/70">{{ $row[0] }}</span>
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-bold {{ $row[1] === 'H' ? 'bg-green-900/40 text-green-400 border border-green-800/40' : 'bg-amber-900/40 text-amber-400 border border-amber-800/40' }}">
                                        {{ $row[1] }} • {{ $row[1] === 'H' ? 'Hadir' : 'Sakit (Surat)' }}
                                    </span>
                                </li>
                                @endforeach
                            </ul>
                            <div class="mt-4 pt-3 border-t border-white/10 flex items-center justify-between">
                                <p class="text-[11px] text-white/40">{{ $jumlahRombel }} Rombel terdaftar</p>
                                <span class="text-[11px] font-semibold text-pine-teal">Lihat semua rombel →</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Panel Admin --}}
                <div x-show="role === 'admin'" x-cloak x-transition.opacity.duration.200ms
                     class="lp-reveal grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-center"
                     role="tabpanel" aria-label="Tampilan Wali Kelas dan Admin">
                    <div class="lg:col-span-5 min-w-0">
                        <h3 class="font-display text-[1.2rem] font-bold text-white mb-3">Pemantauan rombel &amp; operasional</h3>
                        <p class="text-[15px] leading-relaxed text-white/80 mb-6">Wali kelas dan operator lembaga dapat memantau seluruh aktivitas rombel, mengelola data warga belajar, mengunggah dokumen resmi, dan mengatur jadwal dan pengumuman lembaga.</p>
                        <ul class="space-y-2.5">
                            @foreach(['Pantau kehadiran seluruh rombel', 'Kelola data warga belajar & tutor', 'Jadwalkan kelas & pengumuman', 'Unduh laporan akademik & rapor'] as $f)
                            <li class="flex items-center gap-2.5 text-sm text-white/70">
                                <svg class="h-4 w-4 shrink-0 text-terracotta" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons['check'] !!}</svg>
                                {{ $f }}
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="lg:col-span-7 min-w-0 w-full">
                        <div class="w-full max-w-full rounded-2xl bg-brand-dark border border-white/15 p-6 sm:p-8 shadow-2xl overflow-hidden">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-white/40 mb-4">Panel Wali Kelas &amp; Operator</p>
                            <div class="grid grid-cols-3 gap-3 mb-4">
                                <div class="rounded-xl border border-white/10 bg-brand-surface px-3 py-3 text-center">
                                    <p class="font-display text-[1.5rem] font-extrabold tabular-nums text-white" data-count-to="{{ $jumlahPesertaDidik }}">{{ $jumlahPesertaDidik }}</p>
                                    <p class="text-[10px] font-semibold text-white/40 mt-0.5">Warga Belajar</p>
                                </div>
                                <div class="rounded-xl border border-white/10 bg-brand-surface px-3 py-3 text-center">
                                    <p class="font-display text-[1.5rem] font-extrabold tabular-nums text-white" data-count-to="{{ $jumlahTutor }}">{{ $jumlahTutor }}</p>
                                    <p class="text-[10px] font-semibold text-white/40 mt-0.5">Tutor Pengampu</p>
                                </div>
                                <div class="rounded-xl border border-pine-teal/30 bg-pine-teal/10 px-3 py-3 text-center">
                                    <p class="font-display text-[1.5rem] font-extrabold tabular-nums text-pine-teal">{{ $jumlahRombel }}</p>
                                    <p class="text-[10px] font-semibold text-pine-teal/60 mt-0.5">Rombel Aktif</p>
                                </div>
                            </div>
                            <div class="rounded-xl border border-white/10 bg-brand-surface px-3 py-2.5 flex items-center gap-3">
                                <span class="h-2 w-2 rounded-full bg-green-400 shrink-0"></span>
                                <p class="text-sm text-white/60">Semua rombel aktif dan terjadwal</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        {{-- ============================================================
             6. PAPAN MAKLUMAT & KALENDER KEGIATAN — FILTERABLE PUBLIC LEDGER
             ============================================================ --}}
        <section id="informasi" class="scroll-mt-24 bg-warm-ivory border-b border-warm-border py-16 lg:py-24">
            <div class="lp-reveal mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">

                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-8">
                    <div>
                        <p class="lp-label">Papan Maklumat &amp; Kalender Kegiatan</p>
                        <h2 class="mt-3 font-display text-[1.8rem] font-extrabold leading-tight tracking-[-0.02em] text-navy lg:text-[2.2rem]">
                            Informasi resmi lembaga.
                        </h2>
                    </div>
                    <p class="text-sm text-muted max-w-xs">Diperbarui oleh pengelola PKBM Aditya. Informasi khusus peran tersedia setelah masuk sistem.</p>
                </div>

                {{-- Filter Kategori --}}
                <div class="flex gap-2 mb-6 flex-wrap" x-data="lpFilter()" id="filter-pengumuman" role="group" aria-label="Filter kategori pengumuman">
                    <template x-for="f in filters" :key="f.value">
                        <button type="button"
                                :class="activeFilter === f.value ? 'bg-pine-teal text-white border-pine-teal' : 'bg-white text-muted border-warm-border hover:bg-warm-sand hover:text-navy'"
                                class="rounded-xl border px-4 py-2 text-sm font-semibold transition-all duration-150 focus:outline-none focus-visible:ring-2 focus-visible:ring-pine-teal/40"
                                @click="activeFilter = f.value"
                                :aria-pressed="activeFilter === f.value"
                                x-text="f.label">
                        </button>
                    </template>

                    {{-- Kartu Pengumuman --}}
                    <div class="w-full mt-2">
                        @if($informasi->isNotEmpty())
                            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach($informasi as $item)
                                    @php $kat = $item->kategori ?: 'Umum'; @endphp
                                    <article class="lp-card flex flex-col p-5 bg-white"
                                             :class="activeFilter === 'semua' || activeFilter === '{{ Str::lower($kat) }}' ? 'block' : 'hidden'">
                                        <div class="flex items-center justify-between gap-3 mb-4">
                                            <span class="inline-flex items-center rounded-full border border-warm-border bg-warm-ivory px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wider text-muted">{{ $kat }}</span>
                                            <time class="text-[11px] text-subtle tabular-nums" datetime="{{ ($item->published_at ?? $item->created_at)->toDateString() }}">
                                                {{ ($item->published_at ?? $item->created_at)->translatedFormat('d M Y') }}
                                            </time>
                                        </div>
                                        <h3 class="font-display text-[15px] font-bold leading-snug text-navy flex-1">{{ $item->judul }}</h3>
                                        <p class="mt-2 line-clamp-3 text-sm leading-relaxed text-muted">{{ Str::limit(strip_tags($item->isi), 160) }}</p>
                                        <details class="group mt-4 border-t border-warm-border pt-4">
                                            <summary class="cursor-pointer list-none text-sm font-semibold text-pine-teal hover:text-pine-teal-hover transition-colors marker:content-none focus:outline-none focus-visible:ring-2 focus-visible:ring-pine-teal/40 focus-visible:ring-offset-2">
                                                <span class="group-open:hidden">Baca Maklumat →</span>
                                                <span class="hidden group-open:inline text-muted">Tutup ↑</span>
                                            </summary>
                                            <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-muted">{{ Str::limit(strip_tags($item->isi), 900) }}</p>
                                        </details>
                                    </article>
                                @endforeach
                            </div>
                        @else
                            {{-- Fallback default 3 kartu pengumuman resmi jika belum ada di database --}}
                            <div class="mb-4 flex items-center gap-2 text-xs text-subtle">
                                <span class="inline-block h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                <span>Belum ada pengumuman publik baru. Menampilkan agenda dan maklumat resmi lembaga:</span>
                            </div>
                            <div class="grid gap-6 grid-cols-1 md:grid-cols-3">
                                <article class="lp-card flex flex-col p-6 bg-white rounded-2xl border border-warm-border shadow-sm">
                                    <div class="flex items-center justify-between gap-3 mb-4">
                                        <span class="inline-flex items-center rounded-full border border-pine-teal/20 bg-pine-teal/10 px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wider text-pine-teal">Pendaftaran</span>
                                        <time class="text-[11px] text-subtle tabular-nums" datetime="{{ date('Y-m-d') }}">
                                            T.A. 2026/2027
                                        </time>
                                    </div>
                                    <h3 class="font-display text-base font-bold leading-snug text-navy flex-1">Pembukaan Pendaftaran Warga Belajar Baru TA 2026/2027</h3>
                                    <p class="mt-2 text-sm leading-relaxed text-muted">Penerimaan warga belajar baru untuk Paket A (Setara SD), Paket B (Setara SMP), dan Paket C (Setara SMA) resmi dibuka. Pelayanan pendaftaran dilakukan secara daring dan tatap muka.</p>
                                    <div class="mt-4 border-t border-warm-border pt-4">
                                        <a href="{{ route('login') }}" class="text-sm font-semibold text-pine-teal hover:text-pine-teal-hover transition-colors inline-flex items-center gap-1">
                                            Masuk ke Portal Lembaga →
                                        </a>
                                    </div>
                                </article>

                                <article class="lp-card flex flex-col p-6 bg-white rounded-2xl border border-warm-border shadow-sm">
                                    <div class="flex items-center justify-between gap-3 mb-4">
                                        <span class="inline-flex items-center rounded-full border border-amber-500/20 bg-amber-500/10 px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wider text-amber-700">Jadwal &amp; Ujian</span>
                                        <time class="text-[11px] text-subtle tabular-nums" datetime="{{ date('Y-m-d') }}">
                                            Semester Genap
                                        </time>
                                    </div>
                                    <h3 class="font-display text-base font-bold leading-snug text-navy flex-1">Jadwal Pelaksanaan Ujian Pendidikan Kesetaraan (UPK)</h3>
                                    <p class="mt-2 text-sm leading-relaxed text-muted">Warga belajar tingkat akhir diimbau melengkapi berkas administrasi dan mengikuti simulasi ujian berbasis komputer di sekretariat PKBM Aditya Mamuju.</p>
                                    <div class="mt-4 border-t border-warm-border pt-4">
                                        <a href="#sistem" class="text-sm font-semibold text-pine-teal hover:text-pine-teal-hover transition-colors inline-flex items-center gap-1">
                                            Cek Jadwal di Portal →
                                        </a>
                                    </div>
                                </article>

                                <article class="lp-card flex flex-col p-6 bg-white rounded-2xl border border-warm-border shadow-sm">
                                    <div class="flex items-center justify-between gap-3 mb-4">
                                        <span class="inline-flex items-center rounded-full border border-terracotta/20 bg-terracotta/10 px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wider text-terracotta">Kegiatan</span>
                                        <time class="text-[11px] text-subtle tabular-nums" datetime="{{ date('Y-m-d') }}">
                                            Vokasi Terjadwal
                                        </time>
                                    </div>
                                    <h3 class="font-display text-base font-bold leading-snug text-navy flex-1">Pelatihan Keterampilan Komputer &amp; Wirausaha Mandiri</h3>
                                    <p class="mt-2 text-sm leading-relaxed text-muted">Program kecakapan hidup (life skill) berupa literasi digital, pengoperasian komputer kantor, dan strategi pemasaran online untuk meningkatkan kemandirian warga.</p>
                                    <div class="mt-4 border-t border-warm-border pt-4">
                                        <a href="#program" class="text-sm font-semibold text-pine-teal hover:text-pine-teal-hover transition-colors inline-flex items-center gap-1">
                                            Info Program Vokasi →
                                        </a>
                                    </div>
                                </article>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </section>

        {{-- ============================================================
             7. CTA BANNER EDITORIAL HANGAT
             ============================================================ --}}
        <section class="bg-warm-sand border-b border-warm-border py-16 lg:py-20">
            <div class="lp-reveal mx-auto flex w-full max-w-7xl flex-col gap-8 px-4 sm:px-6 lg:px-8 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-2xl">
                    <p class="lp-label mb-3">Siap Bergabung?</p>
                    <h2 class="font-display text-[1.8rem] font-extrabold leading-tight tracking-[-0.02em] text-navy lg:text-[2.2rem]">
                        Mulai perjalanan pendidikanmu<br class="hidden lg:block"> bersama PKBM Aditya.
                    </h2>
                    <p class="mt-3 text-[15px] leading-relaxed text-muted max-w-xl">Masuk ke sistem informasi untuk melihat program, jadwal tatap muka, dan persyaratan pendaftaran yang berlaku pada Tahun Ajaran 2026/2027.</p>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row">
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-pine-teal px-6 py-3.5 text-[15px] font-semibold text-white transition-colors hover:bg-pine-teal-hover focus:outline-none focus-visible:ring-2 focus-visible:ring-pine-teal/40 focus-visible:ring-offset-2">
                            Masuk Dashboard →
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                           class="inline-flex items-center justify-center gap-2 rounded-xl bg-pine-teal px-6 py-3.5 text-[15px] font-semibold text-white transition-colors hover:bg-pine-teal-hover focus:outline-none focus-visible:ring-2 focus-visible:ring-pine-teal/40 focus-visible:ring-offset-2">
                            Masuk Sistem Informasi →
                        </a>
                    @endauth
                    <a href="#program" class="inline-flex items-center justify-center gap-2 rounded-xl border border-warm-border bg-white px-6 py-3.5 text-[15px] font-semibold text-navy transition-colors hover:border-pine-teal/40 hover:bg-warm-sand focus:outline-none focus-visible:ring-2 focus-visible:ring-pine-teal/40">
                        Lihat Persyaratan Paket
                    </a>
                </div>
            </div>
        </section>
    </main>

    {{-- ============================================================
         FOOTER
         ============================================================ --}}
    <footer class="bg-brand-darkest pb-8 pt-14 text-white/70" aria-label="Footer PKBM Aditya">
        <div class="lp-reveal mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-10 border-b border-white/10 pb-12 md:grid-cols-2 lg:grid-cols-4">
                {{-- Identitas --}}
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <img src="{{ asset('images/logo-pkbm-aditya-96.png') }}"
                             alt="Logo Resmi PKBM Aditya Kabupaten Mamuju"
                             width="40"
                             height="40"
                             class="h-10 w-10 shrink-0 object-contain">
                        <span class="flex flex-col leading-none">
                            <span class="font-display text-[15px] font-extrabold tracking-tight text-white">PKBM ADITYA</span>
                            <span class="mt-0.5 text-[10px] font-bold uppercase tracking-[0.16em] text-white/50">KABUPATEN MAMUJU</span>
                        </span>
                    </div>
                    <p class="text-sm leading-relaxed">Pusat Kegiatan Belajar Masyarakat yang menyelenggarakan pendidikan kesetaraan Paket A, B, dan C di Kabupaten Mamuju, Sulawesi Barat.</p>
                    <p class="mt-3 text-[11px] font-semibold uppercase tracking-wider text-white/30">Tahun Ajaran 2026/2027</p>
                </div>

                {{-- Navigasi --}}
                <div>
                    <h2 class="font-display text-[13px] font-bold uppercase tracking-[0.16em] text-white mb-4">Navigasi</h2>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="#tentang" class="transition-colors hover:text-white focus:outline-none focus-visible:underline">Tentang Lembaga</a></li>
                        <li><a href="#program" class="transition-colors hover:text-white focus:outline-none focus-visible:underline">Jalur Kesetaraan</a></li>
                        <li><a href="#sistem" class="transition-colors hover:text-white focus:outline-none focus-visible:underline">Ruang Belajar Digital</a></li>
                        <li><a href="#informasi" class="transition-colors hover:text-white focus:outline-none focus-visible:underline">Papan Maklumat</a></li>
                    </ul>
                </div>

                {{-- Layanan --}}
                <div>
                    <h2 class="font-display text-[13px] font-bold uppercase tracking-[0.16em] text-white mb-4">Layanan di Sulawesi Barat</h2>
                    <ul class="space-y-2.5 text-sm">
                        <li class="flex items-start gap-2.5">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-white/30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons['map-pin'] !!}</svg>
                            <span>Kabupaten Mamuju,<br>Sulawesi Barat, Indonesia</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-white/30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons['shield'] !!}</svg>
                            <span>Lembaga Pendidikan Nonformal<br>Resmi Terdaftar</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-white/30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons['bell'] !!}</svg>
                            <span>Informasi resmi disampaikan<br>melalui portal sistem.</span>
                        </li>
                    </ul>
                </div>

                {{-- Akses Portal --}}
                <div>
                    <h2 class="font-display text-[13px] font-bold uppercase tracking-[0.16em] text-white mb-4">Pintasan Akses Portal</h2>
                    <ul class="space-y-2.5 text-sm">
                        @auth
                            <li><a href="{{ route('dashboard') }}" class="transition-colors hover:text-white focus:outline-none focus-visible:underline">Dashboard Utama</a></li>
                        @else
                            <li>
                                <a href="{{ route('login') }}" class="transition-colors hover:text-white focus:outline-none focus-visible:underline text-left text-white/70">
                                    Masuk Sistem Informasi
                                </a>
                            </li>
                        @endauth
                        <li><a href="{{ route('operator.login.form') }}" class="transition-colors hover:text-white focus:outline-none focus-visible:underline">Portal Operator Lembaga</a></li>
                        <li><a href="#program" class="transition-colors hover:text-white focus:outline-none focus-visible:underline">Panduan Program Paket</a></li>
                        <li><a href="#informasi" class="transition-colors hover:text-white focus:outline-none focus-visible:underline">Papan Maklumat Publik</a></li>
                    </ul>
                </div>
            </div>

            <div class="flex flex-col gap-2 pt-6 text-xs text-white/40 sm:flex-row sm:items-center sm:justify-between">
                <p>&copy; {{ date('Y') }} PKBM Aditya Kabupaten Mamuju. Hak cipta dilindungi.</p>
                <p>Sistem Informasi Manajemen Pendidikan Nonformal.</p>
            </div>
        </div>
    </footer>

    {{-- ============================================================
         MODAL LOGIN PORTAL AKADEMIK
         ============================================================ --}}
    @guest
    <div x-show="modalOpen"
         x-cloak
         class="fixed inset-0 z-50"
         x-transition.opacity.duration.200ms
         role="dialog"
         aria-modal="true"
         aria-labelledby="modal-login-title"
         @open-modal.window="modalOpen = true">

        {{-- Overlay --}}
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="modalOpen = false"></div>

        {{-- Dialog --}}
        <div class="relative flex min-h-screen items-center justify-center p-4">
            <div class="w-full max-w-md rounded-2xl border border-warm-border bg-white shadow-soft-lg"
                 @click.stop
                 x-trap.noscroll="modalOpen">

                {{-- Header Modal --}}
                <div class="flex items-center justify-between border-b border-warm-border px-6 py-5">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('images/logo-pkbm-aditya-96.png') }}"
                             alt="Logo Resmi PKBM Aditya"
                             width="32"
                             height="32"
                             class="h-8 w-8 shrink-0 object-contain">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-terracotta">Portal Akademik</p>
                            <h2 id="modal-login-title" class="font-display text-[1.1rem] font-extrabold text-navy mt-0.5">Masuk ke Sistem PKBM Aditya</h2>
                        </div>
                    </div>
                    <button type="button"
                            class="flex h-8 w-8 items-center justify-center rounded-lg border border-warm-border bg-warm-ivory text-muted transition-colors hover:bg-warm-sand focus:outline-none focus-visible:ring-2 focus-visible:ring-pine-teal/40"
                            @click="modalOpen = false"
                            aria-label="Tutup dialog">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">{!! $icons['close'] !!}</svg>
                    </button>
                </div>

                {{-- Body Modal --}}
                <div class="px-6 py-5">
                    <form method="POST" action="{{ route('login') }}" class="space-y-4">
                        @csrf

                        <div class="rounded-xl border border-warm-border bg-warm-sand/60 p-3.5 text-xs text-muted leading-relaxed">
                            Gunakan akun terdaftar (Email atau NISN) untuk mengakses portal warga belajar, tutor pendamping, atau staf PKBM Aditya.
                        </div>

                        {{-- Email / NISN --}}
                        <div>
                            <label for="modal-email" class="label text-navy">Email / Nomor Induk (NISN)</label>
                            <input id="modal-email" type="text" name="email" autocomplete="username"
                                   class="input" placeholder="Masukkan email atau NISN" required>
                        </div>

                        {{-- Kata Sandi --}}
                        <div>
                            <label for="modal-password" class="label text-navy">Kata Sandi</label>
                            <input id="modal-password" type="password" name="password" autocomplete="current-password"
                                   class="input" placeholder="••••••••" required>
                        </div>

                        {{-- Submit --}}
                        <div class="flex items-center gap-3 pt-1">
                            <button type="submit" class="flex-1 inline-flex items-center justify-center gap-2 rounded-xl bg-pine-teal px-6 py-3 text-[15px] font-semibold text-white transition-colors hover:bg-pine-teal-hover focus:outline-none focus-visible:ring-2 focus-visible:ring-pine-teal/40">
                                Masuk Sistem →
                            </button>
                        </div>

                        <div class="pt-2 text-center border-t border-warm-border">
                            <a href="{{ route('operator.login.form') }}" class="text-xs font-semibold text-pine-teal hover:underline inline-flex items-center gap-1">
                                Masuk melalui Portal Khusus Operator Lembaga →
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endguest

</body>
</html>