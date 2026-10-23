@php
    /** Program kesetaraan yang aktif beserta jumlah rombel aktifnya (satu query agregat, tanpa N+1). */
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

    /** Hanya pengumuman umum yang boleh tampil di halaman publik. */
    $informasi = \App\Models\Announcement::query()
        ->where('status_aktif', true)
        ->whereNull('target_role')
        ->whereNull('target_program')
        ->whereNull('target_rombel')
        ->where(function ($query) {
            $query->whereNull('published_at')->orWhere('published_at', '<=', now());
        })
        ->orderByDesc('published_at')
        ->orderByDesc('id')
        ->take(3)
        ->get(['judul', 'isi', 'kategori', 'published_at', 'created_at']);

    $jenjang = [
        'PA' => 'Setara SD/MI',
        'PB' => 'Setara SMP/MTs',
        'PC' => 'Setara SMA/MA',
    ];

    $icons = [
        'arrow-right' => '<path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>',
        'book' => '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/>',
        'layers' => '<path d="m12 2 9 5-9 5-9-5 9-5Z"/><path d="m3 12 9 5 9-5"/><path d="m3 17 9 5 9-5"/>',
        'clipboard' => '<rect width="8" height="4" x="8" y="2" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="m9 14 2 2 4-4"/>',
        'chart' => '<path d="M3 3v18h18"/><path d="M18 17V9M13 17V5M8 17v-3"/>',
        'bell' => '<path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/><path d="M4 17h16a8 8 0 0 0-3-6.2V8a5 5 0 0 0-10 0v2.8A8 8 0 0 0 4 17Z"/>',
        'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
        'laptop' => '<path d="M20 16V7a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v9"/><path d="M2 16h20"/><path d="M12 16v4"/>',
        'map-pin' => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
        'menu' => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'close' => '<path d="M18 6 6 18M6 6l12 12"/>',
        'check' => '<path d="M20 6 9 17l-5-5"/>',
        'lock' => '<rect width="18" height="11" x="3" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PKBM Aditya — Pendidikan Kesetaraan Kabupaten Mamuju</title>
    <meta name="description" content="PKBM Aditya menyelenggarakan program pendidikan kesetaraan Paket A, B, dan C di Kabupaten Mamuju, Sulawesi Barat, didukung sistem informasi pembelajaran digital.">
    <meta name="theme-color" content="#167C80">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('images/logo-pkbm-aditya-96.png') }}">
    <link rel="apple-touch-icon" sizes="192x192" href="{{ asset('images/logo-pkbm-aditya-192.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Penanda dini agar animasi reveal hanya aktif bila JavaScript berjalan. --}}
    <script>document.documentElement.classList.add('js-reveal');</script>
</head>
<body class="lp-page bg-background text-navy antialiased"
      x-data="{ menuOpen: false, scrolled: false }"
      @scroll.window.passive="scrolled = window.pageYOffset > 16"
      @keydown.escape.window="menuOpen = false">

    <a href="#konten-utama" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[60] focus:rounded-xl focus:bg-navy focus:px-4 focus:py-2.5 focus:text-sm focus:font-semibold focus:text-white">
        Lompat ke konten utama
    </a>

    {{-- ================= NAVBAR ================= --}}
    <header class="fixed inset-x-0 top-0 z-50 transition-all duration-300"
            :class="scrolled || menuOpen ? 'border-b border-border bg-surface/95 backdrop-blur supports-[backdrop-filter]:bg-surface/80' : 'border-b border-transparent bg-surface/0'">
        <div class="mx-auto flex h-16 w-full max-w-[1200px] items-center justify-between gap-6 px-6 lg:h-[74px] lg:px-8">
            <a href="{{ route('landing') }}" class="group flex items-center gap-3 rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 focus-visible:ring-offset-2">
                <img src="{{ asset('images/logo-pkbm-aditya-96.png') }}"
                     srcset="{{ asset('images/logo-pkbm-aditya-96.png') }} 96w, {{ asset('images/logo-pkbm-aditya-192.png') }} 192w"
                     sizes="40px" alt="Logo PKBM Aditya" width="96" height="96" class="h-10 w-auto">
                <span class="flex flex-col leading-none">
                    <span class="text-[15px] font-extrabold tracking-tight text-navy">PKBM ADITYA</span>
                    <span class="mt-1 text-[11px] font-semibold uppercase tracking-[0.14em] text-muted">Kabupaten Mamuju</span>
                </span>
            </a>

            <nav class="hidden items-center gap-1 lg:flex" aria-label="Navigasi utama">
                <a href="#profil" class="rounded-lg px-3 py-2 text-sm font-medium text-muted transition-colors hover:bg-primary-soft/60 hover:text-primary-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/40">Profil</a>
                <a href="#program" class="rounded-lg px-3 py-2 text-sm font-medium text-muted transition-colors hover:bg-primary-soft/60 hover:text-primary-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/40">Program</a>
                <a href="#sistem" class="rounded-lg px-3 py-2 text-sm font-medium text-muted transition-colors hover:bg-primary-soft/60 hover:text-primary-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/40">Sistem</a>
                <a href="#informasi" class="rounded-lg px-3 py-2 text-sm font-medium text-muted transition-colors hover:bg-primary-soft/60 hover:text-primary-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/40">Informasi</a>
            </nav>

            <div class="flex items-center gap-2">
                @auth
                    <a href="{{ route('dashboard') }}" class="hidden items-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-primary-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 focus-visible:ring-offset-2 lg:inline-flex">
                        Ke Dashboard
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons['arrow-right'] !!}</svg>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="hidden items-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-primary-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 focus-visible:ring-offset-2 lg:inline-flex">
                        Masuk Sistem
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons['arrow-right'] !!}</svg>
                    </a>
                @endauth

                <button type="button"
                        class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-border bg-surface text-navy transition-colors hover:bg-primary-soft focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 lg:hidden"
                        @click="menuOpen = !menuOpen"
                        :aria-expanded="menuOpen.toString()"
                        aria-controls="menu-mobile"
                        aria-label="Buka menu navigasi">
                    <svg x-show="!menuOpen" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true">{!! $icons['menu'] !!}</svg>
                    <svg x-show="menuOpen" x-cloak class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true">{!! $icons['close'] !!}</svg>
                </button>
            </div>
        </div>

        <div id="menu-mobile" x-show="menuOpen" x-cloak x-transition.opacity.duration.150ms class="border-t border-border bg-surface lg:hidden">
            <nav class="mx-auto grid w-full max-w-[1200px] gap-1 px-6 py-4" aria-label="Navigasi seluler">
                <a href="#profil" @click="menuOpen = false" class="rounded-lg px-3 py-3 text-[15px] font-medium text-navy hover:bg-primary-soft/60">Profil</a>
                <a href="#program" @click="menuOpen = false" class="rounded-lg px-3 py-3 text-[15px] font-medium text-navy hover:bg-primary-soft/60">Program</a>
                <a href="#sistem" @click="menuOpen = false" class="rounded-lg px-3 py-3 text-[15px] font-medium text-navy hover:bg-primary-soft/60">Sistem</a>
                <a href="#informasi" @click="menuOpen = false" class="rounded-lg px-3 py-3 text-[15px] font-medium text-navy hover:bg-primary-soft/60">Informasi</a>
                <div class="mt-2 border-t border-border pt-3">
                    @auth
                        <a href="{{ route('dashboard') }}" @click="menuOpen = false" class="flex items-center justify-center gap-2 rounded-xl bg-primary px-5 py-3 text-[15px] font-semibold text-white">Ke Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" @click="menuOpen = false" class="flex items-center justify-center gap-2 rounded-xl bg-primary px-5 py-3 text-[15px] font-semibold text-white">Masuk Sistem</a>
                    @endauth
                </div>
            </nav>
        </div>
    </header>

    <main id="konten-utama">
        {{-- ================= HERO ================= --}}
        <section class="relative overflow-hidden border-b border-border bg-surface pt-28 lg:pt-32">
            <div class="lp-dot-field pointer-events-none absolute inset-0" aria-hidden="true"></div>

            <div class="relative mx-auto grid w-full max-w-[1200px] items-center gap-12 px-6 pb-16 lg:grid-cols-12 lg:gap-10 lg:px-8 lg:pb-20">
                <div class="lp-hero-copy lg:col-span-6">
                    <p class="lp-eyebrow">
                        <span class="h-1.5 w-1.5 rounded-full bg-primary" aria-hidden="true"></span>
                        Pendidikan Nonformal · Sulawesi Barat
                    </p>

                    <h1 class="mt-6 text-balance text-[2.15rem] font-extrabold leading-[1.1] tracking-[-0.02em] text-navy sm:text-[2.6rem] lg:text-[3.15rem]">
                        Pendidikan kesetaraan yang terjangkau untuk masyarakat Mamuju.
                    </h1>

                    <p class="mt-5 max-w-xl text-[15px] leading-relaxed text-muted sm:text-base">
                        PKBM Aditya menyelenggarakan program Paket A, B, dan C dengan pendampingan tutor serta sistem pembelajaran digital, sehingga warga belajar dapat menempuh pendidikan tanpa meninggalkan pekerjaan dan keluarganya.
                    </p>

                    <div class="mt-8 flex flex-col gap-4 sm:flex-row">
                        <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-6 py-3.5 text-[15px] font-semibold text-white transition-colors hover:bg-primary-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 focus-visible:ring-offset-2">
                            Masuk Sistem
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons['arrow-right'] !!}</svg>
                        </a>
                        <a href="#program" class="inline-flex items-center justify-center gap-2 rounded-xl border border-border bg-surface px-6 py-3.5 text-[15px] font-semibold text-navy transition-colors hover:border-primary/40 hover:bg-primary-soft/40 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 focus-visible:ring-offset-2">
                            Lihat program
                        </a>
                    </div>

                    <dl class="mt-10 grid max-w-lg grid-cols-3 gap-4 border-t border-border pt-6">
                        <div>
                            <dt class="text-[11px] font-semibold uppercase tracking-[0.12em] text-muted">Peserta didik</dt>
                            <dd class="mt-1 text-xl font-bold tabular-nums text-navy" data-count-to="{{ $jumlahPesertaDidik }}">{{ number_format($jumlahPesertaDidik, 0, ',', '.') }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-semibold uppercase tracking-[0.12em] text-muted">Tutor</dt>
                            <dd class="mt-1 text-xl font-bold tabular-nums text-navy" data-count-to="{{ $jumlahTutor }}">{{ number_format($jumlahTutor, 0, ',', '.') }}</dd>
                        </div>
                        <div>
                            <dt class="text-[11px] font-semibold uppercase tracking-[0.12em] text-muted">Program</dt>
                            <dd class="mt-1 text-xl font-bold tabular-nums text-navy" data-count-to="{{ $programs->count() }}">{{ $programs->count() }}</dd>
                        </div>
                    </dl>
                </div>

                <aside class="lg:col-span-6" aria-label="Identitas PKBM Aditya">
                    <div class="relative overflow-hidden rounded-2xl border border-border bg-surface p-6 sm:p-8">
                        <div class="absolute inset-x-0 top-0 h-1 bg-primary"></div>
                        <div class="flex min-h-[300px] flex-col justify-between gap-10">
                            <div class="flex items-start justify-between gap-6">
                                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl border border-primary/20 bg-primary-soft">
                                    <img src="{{ asset('images/logo-pkbm-aditya-192.png') }}"
                                         alt="Logo PKBM Aditya"
                                         width="192" height="192"
                                         fetchpriority="high" decoding="async"
                                         class="h-10 w-10 object-contain">
                                </div>
                                <span class="inline-flex items-center gap-1.5 rounded-full border border-border bg-background px-3 py-1.5 text-xs font-semibold text-primary-dark">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons['check'] !!}</svg>
                                    Sistem informasi pendidikan
                                </span>
                            </div>

                            <div>
                                <p class="text-sm font-semibold text-primary-dark">PKBM Aditya</p>
                                <p class="mt-3 max-w-md text-2xl font-bold leading-tight tracking-[-0.02em] text-navy sm:text-3xl">
                                    Pendidikan kesetaraan yang tertata, terhubung, dan terukur.
                                </p>
                            </div>

                            <dl class="grid grid-cols-2 gap-px overflow-hidden rounded-xl border border-border bg-border sm:grid-cols-3">
                                <div class="bg-surface px-4 py-3">
                                    <dt class="text-[11px] font-semibold uppercase tracking-[0.1em] text-muted">Wilayah</dt>
                                    <dd class="mt-1 text-sm font-semibold text-navy">Kab. Mamuju</dd>
                                </div>
                                <div class="bg-surface px-4 py-3">
                                    <dt class="text-[11px] font-semibold uppercase tracking-[0.1em] text-muted">Rombel</dt>
                                    <dd class="mt-1 text-sm font-semibold tabular-nums text-navy">{{ $jumlahRombel }} aktif</dd>
                                </div>
                                <div class="col-span-2 bg-surface px-4 py-3 sm:col-span-1">
                                    <dt class="text-[11px] font-semibold uppercase tracking-[0.1em] text-muted">Program</dt>
                                    <dd class="mt-1 text-sm font-semibold tabular-nums text-navy">{{ $programs->count() }} tersedia</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-muted">Informasi layanan dan pembelajaran dikelola melalui sistem PKBM Aditya.</p>
                </aside>
            </div>
        </section>

        {{-- ================= PROFIL ================= --}}
        <section id="profil" class="scroll-mt-20 border-b border-border bg-surface py-16 lg:py-24">
            <div class="mx-auto grid w-full max-w-[1200px] gap-10 px-6 lg:grid-cols-12 lg:gap-14 lg:px-8">
                <div class="lp-reveal lg:col-span-5">
                    <p class="lp-label">01 — Profil Lembaga</p>
                    <h2 class="mt-4 text-[1.7rem] font-bold leading-tight tracking-[-0.01em] text-navy lg:text-[2rem]">
                        Pusat kegiatan belajar masyarakat yang menempatkan warga belajar sebagai prioritas.
                    </h2>
                    <p class="mt-4 text-[15px] leading-relaxed text-muted">
                        PKBM Aditya berkedudukan di Kabupaten Mamuju, Sulawesi Barat. Kegiatan belajar dirancang mengikuti ritme warga belajar dewasa: jadwal yang dapat disepakati, materi yang aplikatif, dan pendampingan tutor.
                    </p>

                    <div class="mt-8 flex flex-wrap gap-2">
                        <span class="lp-chip">Paket A</span>
                        <span class="lp-chip">Paket B</span>
                        <span class="lp-chip">Paket C</span>
                        <span class="lp-chip">Pembelajaran daring</span>
                        <span class="lp-chip">Pendampingan tutor</span>
                    </div>
                </div>

                <div class="lg:col-span-7">
                    <ol class="lp-reveal divide-y divide-border border-y border-border">
                        <li class="flex gap-4 py-5">
                            <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-border bg-background text-primary-dark">
                                <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons['book'] !!}</svg>
                            </span>
                            <div>
                                <h3 class="text-[15px] font-semibold text-navy">Pendidikan kesetaraan</h3>
                                <p class="mt-1 text-sm leading-relaxed text-muted">Tiga jenjang program kesetaraan sebagai jalur menyelesaikan pendidikan dasar dan menengah.</p>
                            </div>
                        </li>
                        <li class="flex gap-4 py-5">
                            <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-border bg-background text-primary-dark">
                                <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons['laptop'] !!}</svg>
                            </span>
                            <div>
                                <h3 class="text-[15px] font-semibold text-navy">Pembelajaran berbasis sistem</h3>
                                <p class="mt-1 text-sm leading-relaxed text-muted">Materi, tugas, kuis, jadwal, dan nilai dikelola dalam satu sistem informasi, baik diakses dari komputer maupun ponsel.</p>
                            </div>
                        </li>
                        <li class="flex gap-4 py-5">
                            <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-border bg-background text-primary-dark">
                                <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons['users'] !!}</svg>
                            </span>
                            <div>
                                <h3 class="text-[15px] font-semibold text-navy">Pendampingan tutor</h3>
                                <p class="mt-1 text-sm leading-relaxed text-muted">{{ $jumlahTutor }} tutor terdaftar mendampingi rombel belajar, dari penyusunan materi hingga penilaian hasil belajar.</p>
                            </div>
                        </li>
                    </ol>
                </div>
            </div>
        </section>

        {{-- ================= PROGRAM ================= --}}
        <section id="program" class="scroll-mt-20 border-b border-border bg-background py-16 lg:py-24">
            <div class="lp-reveal mx-auto w-full max-w-[1200px] px-6 lg:px-8">
                <div class="flex flex-col gap-4 border-b border-border pb-8 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-2xl">
                        <p class="lp-label">02 — Program</p>
                        <h2 class="mt-4 text-[1.7rem] font-bold leading-tight tracking-[-0.01em] text-navy lg:text-[2rem]">Program pendidikan kesetaraan</h2>
                        <p class="mt-3 text-[15px] leading-relaxed text-muted">Jenjang program yang sedang dibuka, beserta jumlah rombel aktif pada sistem.</p>
                    </div>
                    <p class="flex items-start gap-2 text-sm text-muted lg:max-w-xs">
                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons['lock'] !!}</svg>
                        Data program dan rombel bersifat internal; perubahan hanya dapat dilakukan setelah masuk dengan akun.
                    </p>
                </div>

                @if($programs->isNotEmpty())
                    <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($programs as $program)
                            <article class="group flex flex-col rounded-2xl border border-border bg-surface p-6 transition-colors hover:border-primary/40">
                                <div class="flex items-center justify-between">
                                    <span class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg border border-primary/20 bg-primary-soft px-2 text-sm font-extrabold tracking-wide text-primary-dark">{{ $program->kode }}</span>
                                    <span class="text-xs font-medium text-muted tabular-nums">{{ $rombelPerProgram[$program->id] ?? 0 }} rombel</span>
                                </div>
                                <h3 class="mt-5 text-lg font-bold text-navy">{{ $program->nama }}</h3>
                                <p class="mt-1 text-xs font-semibold uppercase tracking-[0.1em] text-primary">{{ $jenjang[$program->kode] ?? 'Program kesetaraan' }}</p>
                                <p class="mt-3 flex-1 text-sm leading-relaxed text-muted">{{ $program->deskripsi }}</p>
                                <a href="{{ route('login') }}" class="mt-6 inline-flex items-center gap-1.5 border-t border-border pt-4 text-sm font-semibold text-primary-dark transition-colors hover:text-primary">
                                    Masuk untuk mendaftar
                                    <svg class="h-4 w-4 transition-transform group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons['arrow-right'] !!}</svg>
                                </a>
                            </article>
                        @endforeach
                    </div>
                @else
                    <p class="mt-10 rounded-2xl border border-border bg-surface p-6 text-sm text-muted">Belum ada program yang dipublikasikan.</p>
                @endif
            </div>
        </section>

        {{-- ================= SISTEM (BENTO) ================= --}}
        <section id="sistem" class="scroll-mt-20 border-b border-border bg-surface py-16 lg:py-24">
            <div class="lp-reveal mx-auto w-full max-w-[1200px] px-6 lg:px-8">
                <div class="max-w-2xl">
                    <p class="lp-label">03 — Sistem Informasi</p>
                    <h2 class="mt-4 text-[1.7rem] font-bold leading-tight tracking-[-0.01em] text-navy lg:text-[2rem]">Satu sistem untuk kegiatan belajar dan administrasi</h2>
                    <p class="mt-3 text-[15px] leading-relaxed text-muted">Peserta didik, tutor, wali kelas, dan pengelola lembaga bekerja pada data yang sama — tanpa pencatatan ganda.</p>
                </div>

                <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-6">
                    <div class="rounded-2xl border border-border bg-background p-6 sm:col-span-2 lg:col-span-4">
                        <div class="flex items-start gap-4">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-border bg-surface text-primary-dark">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons['layers'] !!}</svg>
                            </span>
                            <div>
                                <h3 class="text-base font-semibold text-navy">Akademik terpadu</h3>
                                <p class="mt-1.5 text-sm leading-relaxed text-muted">Data rombel, mata pelajaran, penugasan tutor, dan tahun ajaran tersimpan dalam satu alur kerja.</p>
                            </div>
                        </div>
                        <div class="mt-5 grid gap-2 border-t border-border pt-5 sm:grid-cols-3">
                            <p class="flex items-center gap-2 text-sm text-navy"><span class="h-1.5 w-1.5 rounded-full bg-primary" aria-hidden="true"></span>Rombel &amp; kelas</p>
                            <p class="flex items-center gap-2 text-sm text-navy"><span class="h-1.5 w-1.5 rounded-full bg-primary" aria-hidden="true"></span>Jadwal pelajaran</p>
                            <p class="flex items-center gap-2 text-sm text-navy"><span class="h-1.5 w-1.5 rounded-full bg-primary" aria-hidden="true"></span>Rekap akademik</p>
                        </div>
                    </div>

                    <div class="flex flex-col justify-between rounded-2xl border border-primary-dark/20 bg-primary p-6 text-white sm:col-span-2 lg:col-span-2">
                        <div>
                            <h3 class="text-base font-semibold">Masuk ke sistem</h3>
                            <p class="mt-1.5 text-sm leading-relaxed text-white">Gunakan akun yang diberikan lembaga untuk mengakses dashboard sesuai peran Anda.</p>
                        </div>
                        <a href="{{ route('login') }}" class="mt-6 inline-flex items-center justify-between gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-primary-dark transition-colors hover:bg-primary-soft focus:outline-none focus-visible:ring-2 focus-visible:ring-white/70 focus-visible:ring-offset-2 focus-visible:ring-offset-primary">
                            Masuk Sistem
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons['arrow-right'] !!}</svg>
                        </a>
                    </div>

                    @php
                        $fitur = [
                            ['icon' => 'clipboard', 'judul' => 'Tugas & kuis', 'teks' => 'Pengumpulan tugas dan evaluasi daring dengan tenggat yang jelas.'],
                            ['icon' => 'chart', 'judul' => 'Nilai & rapor', 'teks' => 'Rekapitulasi nilai per mata pelajaran dan rapor peserta didik.'],
                            ['icon' => 'bell', 'judul' => 'Pengumuman', 'teks' => 'Informasi lembaga tersampaikan ke peran yang tepat.'],
                        ];
                    @endphp

                    @foreach($fitur as $item)
                        <div class="rounded-2xl border border-border bg-background p-6 sm:col-span-1 lg:col-span-2">
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg border border-border bg-surface text-primary-dark">
                                <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons[$item['icon']] !!}</svg>
                            </span>
                            <h3 class="mt-4 text-[15px] font-semibold text-navy">{{ $item['judul'] }}</h3>
                            <p class="mt-1.5 text-sm leading-relaxed text-muted">{{ $item['teks'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ================= INFORMASI ================= --}}
        <section id="informasi" class="scroll-mt-20 border-b border-border bg-background py-16 lg:py-24">
            <div class="lp-reveal mx-auto w-full max-w-[1200px] px-6 lg:px-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div class="max-w-2xl">
                        <p class="lp-label">04 — Informasi</p>
                        <h2 class="mt-4 text-[1.7rem] font-bold leading-tight tracking-[-0.01em] text-navy lg:text-[2rem]">Pengumuman lembaga</h2>
                    </div>
                    <p class="text-sm text-muted">Informasi untuk publik, diperbarui oleh pengelola PKBM.</p>
                </div>

                @if($informasi->isNotEmpty())
                    <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($informasi as $item)
                            <article class="flex flex-col rounded-2xl border border-border bg-surface p-6">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="lp-chip">{{ $item->kategori ?: 'Umum' }}</span>
                                    <time class="text-xs text-muted tabular-nums" datetime="{{ ($item->published_at ?? $item->created_at)->toDateString() }}">
                                        {{ ($item->published_at ?? $item->created_at)->translatedFormat('d M Y') }}
                                    </time>
                                </div>
                                <h3 class="mt-4 text-base font-semibold leading-snug text-navy">{{ $item->judul }}</h3>
                                <p class="mt-2 line-clamp-3 flex-1 text-sm leading-relaxed text-muted">{{ Str::limit(strip_tags($item->isi), 180) }}</p>
                                <details class="group mt-4 border-t border-border pt-4">
                                    <summary class="cursor-pointer list-none text-sm font-semibold text-primary-dark marker:content-none focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 focus-visible:ring-offset-2">
                                        <span class="group-open:hidden">Baca selengkapnya</span>
                                        <span class="hidden group-open:inline">Tutup</span>
                                    </summary>
                                    <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-muted">{{ Str::limit(strip_tags($item->isi), 900) }}</p>
                                </details>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="mt-10 flex flex-col items-start gap-4 rounded-2xl border border-dashed border-border bg-surface p-8 sm:flex-row sm:items-center">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-border bg-background text-primary-dark">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons['bell'] !!}</svg>
                        </span>
                        <div class="flex-1">
                            <h3 class="text-[15px] font-semibold text-navy">Belum ada pengumuman publik</h3>
                            <p class="mt-1 text-sm leading-relaxed text-muted">Pengumuman resmi dibagikan melalui sistem kepada peserta didik, tutor, dan wali kelas. Silakan masuk untuk melihat informasi terbaru.</p>
                        </div>
                        <a href="{{ route('login') }}" class="inline-flex shrink-0 items-center gap-2 rounded-xl border border-border bg-surface px-4 py-2.5 text-sm font-semibold text-navy transition-colors hover:border-primary/40 hover:bg-primary-soft/40">Masuk</a>
                    </div>
                @endif
            </div>
        </section>

        {{-- ================= CTA ================= --}}
        <section class="relative overflow-hidden bg-primary py-16 lg:py-20">
            <div class="lp-dot-field lp-dot-field-invert pointer-events-none absolute inset-0" aria-hidden="true"></div>
            <div class="relative mx-auto flex w-full max-w-[1200px] flex-col gap-8 px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
                <div class="max-w-2xl">
                    <h2 class="text-[1.7rem] font-bold leading-tight tracking-[-0.01em] text-white lg:text-[2rem]">Siap melanjutkan pendidikan?</h2>
                    <p class="mt-3 text-[15px] leading-relaxed text-white">Masuk ke sistem untuk melihat program, jadwal, dan informasi pendaftaran yang berlaku.</p>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-6 py-3.5 text-[15px] font-semibold text-primary-dark transition-colors hover:bg-primary-soft focus:outline-none focus-visible:ring-2 focus-visible:ring-white/70 focus-visible:ring-offset-2 focus-visible:ring-offset-primary">
                        Masuk Sistem
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons['arrow-right'] !!}</svg>
                    </a>
                    <a href="#program" class="inline-flex items-center justify-center rounded-xl border border-white/80 px-6 py-3.5 text-[15px] font-semibold text-white transition-colors hover:bg-white/10 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/70 focus-visible:ring-offset-2 focus-visible:ring-offset-primary">Lihat program</a>
                </div>
            </div>
        </section>
    </main>

    {{-- ================= FOOTER ================= --}}
    <footer id="kontak" class="scroll-mt-20 bg-navy pb-8 pt-14 text-white/75">
        <div class="lp-reveal mx-auto w-full max-w-[1200px] px-6 lg:px-8">
            <div class="grid gap-10 border-b border-white/10 pb-12 md:grid-cols-2 lg:grid-cols-4">
                <div>
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('images/logo-pkbm-aditya-96.png') }}"
                             srcset="{{ asset('images/logo-pkbm-aditya-96.png') }} 96w, {{ asset('images/logo-pkbm-aditya-192.png') }} 192w"
                             sizes="40px" alt="Logo PKBM Aditya" width="96" height="96" loading="lazy" decoding="async" class="h-10 w-auto">
                        <span class="flex flex-col leading-none">
                            <span class="text-[15px] font-extrabold tracking-tight text-white">PKBM ADITYA</span>
                            <span class="mt-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-white/60">Kabupaten Mamuju</span>
                        </span>
                    </div>
                    <p class="mt-5 text-sm leading-relaxed">Pusat kegiatan belajar masyarakat yang menyelenggarakan program pendidikan kesetaraan di Kabupaten Mamuju, Sulawesi Barat.</p>
                </div>

                <div>
                    <h2 class="text-[13px] font-semibold uppercase tracking-[0.12em] text-white">Navigasi</h2>
                    <ul class="mt-4 space-y-2.5 text-sm">
                        <li><a href="#profil" class="transition-colors hover:text-white focus:outline-none focus-visible:underline">Profil Lembaga</a></li>
                        <li><a href="#program" class="transition-colors hover:text-white focus:outline-none focus-visible:underline">Program</a></li>
                        <li><a href="#sistem" class="transition-colors hover:text-white focus:outline-none focus-visible:underline">Sistem Informasi</a></li>
                        <li><a href="#informasi" class="transition-colors hover:text-white focus:outline-none focus-visible:underline">Informasi</a></li>
                    </ul>
                </div>

                <div>
                    <h2 class="text-[13px] font-semibold uppercase tracking-[0.12em] text-white">Kontak</h2>
                    <ul class="mt-4 space-y-3 text-sm">
                        <li class="flex items-start gap-2.5">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-white/40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons['map-pin'] !!}</svg>
                            <span>Kabupaten Mamuju,<br>Sulawesi Barat, Indonesia</span>
                        </li>
                        <li class="flex items-start gap-2.5">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-white/40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons['bell'] !!}</svg>
                            <span>Informasi resmi lembaga<br>disampaikan melalui sistem.</span>
                        </li>
                    </ul>
                </div>

                <div>
                    <h2 class="text-[13px] font-semibold uppercase tracking-[0.12em] text-white">Akses Sistem</h2>
                    <ul class="mt-4 space-y-2.5 text-sm">
                        <li><a href="{{ route('login') }}" class="transition-colors hover:text-white focus:outline-none focus-visible:underline">Masuk Sistem</a></li>
                        <li><a href="{{ route('operator.login.form') }}" class="transition-colors hover:text-white focus:outline-none focus-visible:underline">Login Operator</a></li>
                    </ul>
                </div>
            </div>

            <div class="flex flex-col gap-2 pt-6 text-xs text-white/55 sm:flex-row sm:items-center sm:justify-between">
                <p>&copy; {{ date('Y') }} PKBM Aditya Kabupaten Mamuju.</p>
                <p>Sistem Informasi Manajemen Pendidikan.</p>
            </div>
        </div>
    </footer>
</body>
</html>
