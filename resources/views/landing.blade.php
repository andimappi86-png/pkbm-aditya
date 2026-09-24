@php
    $kegiatans = \App\Models\Announcement::where('status_aktif', true)->latest('published_at')->take(3)->get();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PKBM Aditya - Kabupaten Mamuju</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-pkbm-aditya.png') }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Alpine & Lucide -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        .hero-pattern {
            background-color: #F5F7F8;
            background-image: radial-gradient(#D9E2E5 1px, transparent 1px);
            background-size: 32px 32px;
        }
        .text-balance { text-wrap: balance; }
    </style>
</head>
<body class="antialiased bg-background text-navy selection:bg-primary selection:text-white" x-data="{ mobileMenuOpen: false, scrolled: false }" @scroll.window="scrolled = (window.pageYOffset > 20)">

    <!-- NAVBAR -->
    <nav 
        class="fixed top-0 inset-x-0 z-50 transition-all duration-300 border-b"
        :class="scrolled ? 'bg-surface/90 backdrop-blur-lg border-border shadow-sm py-3' : 'bg-surface border-transparent py-5'"
    >
        <div class="max-w-[1280px] mx-auto px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <!-- Logo -->
                <a href="#" class="flex items-center gap-3.5 group">
                    <img src="{{ asset('images/logo-pkbm-aditya.png') }}" alt="Logo PKBM Aditya" class="h-10 w-auto group-hover:opacity-90 transition-opacity">
                    <div class="flex flex-col">
                        <span class="font-extrabold text-lg leading-none tracking-tight text-primary-dark">PKBM ADITYA</span>
                        <span class="text-[11px] font-semibold text-muted tracking-wider uppercase mt-0.5">Kabupaten Mamuju</span>
                    </div>
                </a>

                <!-- Desktop Menu -->
                <div class="hidden lg:flex items-center gap-8">
                    <a href="#" class="text-sm font-medium text-navy hover:text-primary transition-colors">Beranda</a>
                    <a href="#tentang" class="text-sm font-medium text-muted hover:text-primary transition-colors">Profil</a>
                    <a href="#program" class="text-sm font-medium text-muted hover:text-primary transition-colors">Program</a>
                    <a href="#lms" class="text-sm font-medium text-muted hover:text-primary transition-colors">Pembelajaran</a>
                    <a href="#kegiatan" class="text-sm font-medium text-muted hover:text-primary transition-colors">Informasi</a>
                </div>

                <!-- CTA & Hamburger -->
                <div class="flex items-center gap-4">
                    @auth
                        <a href="{{ route('dashboard') }}" class="hidden lg:inline-flex items-center justify-center px-6 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary-dark transition-all shadow-soft hover:shadow-soft-md">
                            Ke Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="hidden lg:inline-flex items-center justify-center px-6 py-2.5 bg-primary text-white text-sm font-semibold rounded-xl hover:bg-primary-dark transition-all shadow-soft hover:shadow-soft-md group">
                            Masuk Sistem
                        </a>
                    @endauth
                    
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden p-2 -mr-2 text-navy hover:bg-primary-soft rounded-lg transition-colors">
                        <i data-lucide="menu" class="w-6 h-6" x-show="!mobileMenuOpen"></i>
                        <i data-lucide="x" class="w-6 h-6" x-show="mobileMenuOpen" x-cloak></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div x-show="mobileMenuOpen" x-collapse class="lg:hidden bg-surface border-t border-border absolute top-full left-0 w-full shadow-lg">
            <div class="flex flex-col px-6 py-4 space-y-4">
                <a href="#" class="text-base font-medium text-navy">Beranda</a>
                <a href="#tentang" class="text-base font-medium text-muted">Profil</a>
                <a href="#program" class="text-base font-medium text-muted">Program</a>
                <a href="#lms" class="text-base font-medium text-muted">Pembelajaran</a>
                <a href="#kegiatan" class="text-base font-medium text-muted">Informasi</a>
                <div class="pt-4 border-t border-border">
                    <a href="{{ route('login') }}" class="w-full inline-flex items-center justify-center px-6 py-3 bg-primary text-white text-base font-semibold rounded-xl text-center">Masuk Sistem</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- HERO SECTION -->
    <section class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden hero-pattern">
        <div class="max-w-[1280px] mx-auto px-6 lg:px-8 relative z-10">
            <div class="grid lg:grid-cols-2 gap-12 lg:gap-8 items-center">
                <!-- Left: Typography -->
                <div class="max-w-2xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-primary-soft border border-primary/20 text-primary-dark text-xs font-bold uppercase tracking-wider mb-6">
                        <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
                        PKBM Aditya • Kab. Mamuju
                    </div>
                    
                    <h1 class="text-4xl lg:text-6xl font-extrabold text-navy leading-[1.1] mb-6 tracking-tight text-balance">
                        Belajar, Berkembang, dan Berkarya Bersama <span class="text-primary relative inline-block">PKBM Aditya<svg class="absolute -bottom-2 left-0 w-full text-accent/30" viewBox="0 0 100 20" preserveAspectRatio="none"><path d="M0 10 Q 50 20 100 10" stroke="currentColor" stroke-width="4" fill="none"/></svg></span>
                    </h1>
                    
                    <p class="text-lg text-muted leading-relaxed mb-8 max-w-xl">
                        PKBM Aditya hadir sebagai lembaga pendidikan nonformal yang mendukung masyarakat memperoleh kesempatan belajar melalui program pendidikan kesetaraan dan pembelajaran digital yang inovatif.
                    </p>
                    
                    <div class="flex flex-col sm:flex-row items-center gap-4">
                        <a href="{{ route('login') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3.5 bg-primary text-white text-base font-semibold rounded-xl hover:bg-primary-dark transition-all shadow-soft hover:shadow-soft-lg group">
                            Masuk Sistem
                            <i data-lucide="arrow-right" class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform"></i>
                        </a>
                        <a href="#tentang" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3.5 bg-surface text-navy border border-border text-base font-semibold rounded-xl hover:bg-background transition-all">
                            Kenal PKBM
                        </a>
                    </div>
                </div>
                
                <!-- Right: Visual -->
                <div class="relative lg:ml-auto w-full max-w-lg aspect-[4/3] rounded-3xl overflow-hidden shadow-soft-lg border border-white/50 group">
                    <div class="absolute inset-0 bg-navy/10 group-hover:bg-transparent transition-colors z-10"></div>
                    <img src="https://images.unsplash.com/photo-1524178232363-1fb2b075b655?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80" alt="Suasana Belajar Edukatif" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                    
                    <!-- Floating Badge -->
                    <div class="absolute bottom-6 left-6 z-20 bg-surface/95 backdrop-blur px-5 py-3 rounded-2xl shadow-soft border border-border flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-primary-soft text-primary flex items-center justify-center">
                            <i data-lucide="monitor-play" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-muted uppercase tracking-wider">Metode Modern</p>
                            <p class="font-bold text-navy">Pembelajaran Digital</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- QUICK STATISTICS -->
    <section class="border-y border-border bg-surface relative z-20">
        <div class="max-w-[1280px] mx-auto px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 divide-x divide-border py-8 lg:py-10">
                <div class="px-4 text-center">
                    <h3 class="text-xl lg:text-2xl font-extrabold text-navy">Paket A</h3>
                    <p class="text-sm font-medium text-muted mt-1">Setara SD/MI</p>
                </div>
                <div class="px-4 text-center">
                    <h3 class="text-xl lg:text-2xl font-extrabold text-navy">Paket B</h3>
                    <p class="text-sm font-medium text-muted mt-1">Setara SMP/MTs</p>
                </div>
                <div class="px-4 text-center">
                    <h3 class="text-xl lg:text-2xl font-extrabold text-navy">Paket C</h3>
                    <p class="text-sm font-medium text-muted mt-1">Setara SMA/MA</p>
                </div>
                <div class="px-4 text-center">
                    <h3 class="text-xl lg:text-2xl font-extrabold text-primary">Sistem LMS</h3>
                    <p class="text-sm font-medium text-muted mt-1">Terintegrasi Digital</p>
                </div>
            </div>
        </div>
    </section>

    <!-- TENTANG PKBM -->
    <section id="tentang" class="py-24 bg-background">
        <div class="max-w-[1280px] mx-auto px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <div class="order-2 lg:order-1 relative rounded-3xl overflow-hidden aspect-square max-w-md mx-auto lg:mx-0 shadow-soft-lg">
                    <img src="https://images.unsplash.com/photo-1577896851231-70ef18881754?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Tentang PKBM Aditya" class="w-full h-full object-cover">
                    <div class="absolute inset-0 border-4 border-white/20 rounded-3xl pointer-events-none"></div>
                </div>
                
                <div class="order-1 lg:order-2">
                    <div class="inline-block px-3 py-1 bg-primary-soft text-primary-dark text-xs font-bold uppercase tracking-wider rounded-lg mb-4">Tentang PKBM Aditya</div>
                    <h2 class="text-3xl lg:text-4xl font-bold text-navy mb-6 leading-tight">Membangun Generasi Cerdas dan Mandiri di Mamuju</h2>
                    <p class="text-muted leading-relaxed mb-8 text-lg">
                        Lembaga kami berkomitmen menyelenggarakan program pendidikan masyarakat yang berkualitas. Kami memadukan pendekatan humanis dan pemanfaatan teknologi untuk memastikan setiap individu memiliki akses belajar yang layak dan terstruktur.
                    </p>
                    
                    <div class="space-y-6 mb-10">
                        <div class="flex gap-4">
                            <div class="w-12 h-12 rounded-xl bg-surface border border-border flex items-center justify-center shrink-0 shadow-sm">
                                <i data-lucide="book-open" class="w-6 h-6 text-primary"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-navy text-lg">Pendidikan Kesetaraan</h4>
                                <p class="text-sm text-muted mt-1">Layanan program Paket A, B, dan C bersertifikat resmi berstandar nasional.</p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div class="w-12 h-12 rounded-xl bg-surface border border-border flex items-center justify-center shrink-0 shadow-sm">
                                <i data-lucide="laptop" class="w-6 h-6 text-primary"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-navy text-lg">Pembelajaran Berbasis Teknologi</h4>
                                <p class="text-sm text-muted mt-1">Menggunakan platform e-learning modern yang mudah diakses di mana saja.</p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div class="w-12 h-12 rounded-xl bg-surface border border-border flex items-center justify-center shrink-0 shadow-sm">
                                <i data-lucide="users" class="w-6 h-6 text-primary"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-navy text-lg">Pendampingan Tutor</h4>
                                <p class="text-sm text-muted mt-1">Dibimbing oleh tutor berpengalaman yang berdedikasi tinggi secara luring maupun daring.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION UTAMA LMS -->
    <section id="lms" class="py-24 bg-surface border-y border-border overflow-hidden">
        <div class="max-w-[1280px] mx-auto px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-3xl lg:text-4xl font-bold text-navy mb-4">Pembelajaran Digital yang Terintegrasi</h2>
                <p class="text-lg text-muted">Belajar tidak berhenti di ruang kelas. PKBM Aditya menghadirkan sistem pembelajaran digital untuk membantu peserta didik dan tutor menjalankan proses pembelajaran secara lebih terarah.</p>
            </div>

            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <!-- Left: Abstract LMS Visual -->
                <div class="relative bg-background rounded-[2rem] p-8 border border-border/60 shadow-inner">
                    <!-- Decorative mock UI elements -->
                    <div class="space-y-4">
                        <div class="h-12 bg-white rounded-xl shadow-sm border border-border/50 flex items-center px-4 w-3/4">
                            <div class="w-6 h-6 rounded bg-primary/10 mr-3"></div>
                            <div class="h-3 bg-gray-200 rounded w-1/2"></div>
                        </div>
                        <div class="flex gap-4">
                            <div class="flex-1 h-32 bg-white rounded-xl shadow-sm border border-border/50 p-4 flex flex-col justify-between">
                                <div class="w-8 h-8 rounded-full bg-emerald-50"></div>
                                <div class="space-y-2">
                                    <div class="h-2 bg-gray-200 rounded w-3/4"></div>
                                    <div class="h-2 bg-gray-100 rounded w-1/2"></div>
                                </div>
                            </div>
                            <div class="flex-1 h-32 bg-white rounded-xl shadow-sm border border-border/50 p-4 flex flex-col justify-between">
                                <div class="w-8 h-8 rounded-full bg-blue-50"></div>
                                <div class="space-y-2">
                                    <div class="h-2 bg-gray-200 rounded w-3/4"></div>
                                    <div class="h-2 bg-gray-100 rounded w-1/2"></div>
                                </div>
                            </div>
                        </div>
                        <div class="h-24 bg-white rounded-xl shadow-sm border border-border/50 p-4">
                            <div class="h-3 bg-gray-200 rounded w-1/4 mb-4"></div>
                            <div class="w-full h-2 bg-background rounded-full overflow-hidden">
                                <div class="w-2/3 h-full bg-primary rounded-full"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Editorial Features -->
                <div>
                    <div class="grid sm:grid-cols-2 gap-x-8 gap-y-10">
                        <!-- Feature 1 -->
                        <div>
                            <div class="text-sm font-extrabold text-primary/30 mb-1">01</div>
                            <h4 class="text-lg font-bold text-navy mb-2 flex items-center gap-2">
                                <i data-lucide="book-open" class="w-4 h-4 text-primary"></i> Materi Belajar
                            </h4>
                            <p class="text-sm text-muted">Akses modul pembelajaran dan literatur kapan pun dibutuhkan.</p>
                        </div>
                        <!-- Feature 2 -->
                        <div>
                            <div class="text-sm font-extrabold text-primary/30 mb-1">02</div>
                            <h4 class="text-lg font-bold text-navy mb-2 flex items-center gap-2">
                                <i data-lucide="clipboard-list" class="w-4 h-4 text-primary"></i> Tugas Evaluasi
                            </h4>
                            <p class="text-sm text-muted">Pengumpulan tugas terpusat dengan sistem tenggat waktu.</p>
                        </div>
                        <!-- Feature 3 -->
                        <div>
                            <div class="text-sm font-extrabold text-primary/30 mb-1">03</div>
                            <h4 class="text-lg font-bold text-navy mb-2 flex items-center gap-2">
                                <i data-lucide="help-circle" class="w-4 h-4 text-primary"></i> Kuis & Ujian
                            </h4>
                            <p class="text-sm text-muted">Evaluasi pemahaman secara interaktif langsung dari platform.</p>
                        </div>
                        <!-- Feature 4 -->
                        <div>
                            <div class="text-sm font-extrabold text-primary/30 mb-1">04</div>
                            <h4 class="text-lg font-bold text-navy mb-2 flex items-center gap-2">
                                <i data-lucide="calendar" class="w-4 h-4 text-primary"></i> Jadwal & Absensi
                            </h4>
                            <p class="text-sm text-muted">Pemantauan kehadiran dan transparansi jadwal kelas online/offline.</p>
                        </div>
                        <!-- Feature 5 -->
                        <div>
                            <div class="text-sm font-extrabold text-primary/30 mb-1">05</div>
                            <h4 class="text-lg font-bold text-navy mb-2 flex items-center gap-2">
                                <i data-lucide="award" class="w-4 h-4 text-primary"></i> Nilai & Rapor
                            </h4>
                            <p class="text-sm text-muted">Rekapitulasi pencapaian akademik secara transparan.</p>
                        </div>
                        <!-- Feature 6 -->
                        <div>
                            <div class="text-sm font-extrabold text-primary/30 mb-1">06</div>
                            <h4 class="text-lg font-bold text-navy mb-2 flex items-center gap-2">
                                <i data-lucide="trending-up" class="w-4 h-4 text-primary"></i> Progress Belajar
                            </h4>
                            <p class="text-sm text-muted">Pantau perkembangan kompetensi peserta didik tiap semester.</p>
                        </div>
                    </div>
                    
                    <div class="mt-10 pt-8 border-t border-border">
                        <a href="{{ route('login') }}" class="inline-flex items-center text-primary font-bold hover:text-primary-dark group">
                            Jelajahi Sistem 
                            <i data-lucide="arrow-right" class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- PROGRAM KESETARAAN -->
    <section id="program" class="py-24 bg-background">
        <div class="max-w-[1280px] mx-auto px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <h2 class="text-3xl lg:text-4xl font-bold text-navy mb-4">Program Pendidikan Kesetaraan</h2>
                <p class="text-lg text-muted">Pilihan program pendidikan formal untuk mendukung kesempatan belajar masyarakat sesuai dengan jenjang masing-masing.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Paket A -->
                <div class="bg-surface border border-border rounded-2xl p-8 hover:shadow-soft transition-all duration-300">
                    <div class="w-14 h-14 rounded-2xl bg-primary-soft text-primary flex items-center justify-center mb-6">
                        <span class="text-2xl font-extrabold">A</span>
                    </div>
                    <h3 class="text-xl font-bold text-navy mb-2">Program Paket A</h3>
                    <div class="inline-block px-2.5 py-1 bg-gray-100 text-gray-700 text-xs font-semibold rounded-md mb-4">Setara SD/MI</div>
                    <p class="text-muted text-sm leading-relaxed mb-8">
                        Program pendidikan kesetaraan untuk jenjang sekolah dasar. Memfokuskan pada penguasaan dasar membaca, menulis, berhitung, dan budi pekerti.
                    </p>
                </div>

                <!-- Paket B -->
                <div class="bg-surface border border-border rounded-2xl p-8 hover:shadow-soft transition-all duration-300">
                    <div class="w-14 h-14 rounded-2xl bg-primary-soft text-primary flex items-center justify-center mb-6">
                        <span class="text-2xl font-extrabold">B</span>
                    </div>
                    <h3 class="text-xl font-bold text-navy mb-2">Program Paket B</h3>
                    <div class="inline-block px-2.5 py-1 bg-gray-100 text-gray-700 text-xs font-semibold rounded-md mb-4">Setara SMP/MTs</div>
                    <p class="text-muted text-sm leading-relaxed mb-8">
                        Program kelanjutan untuk jenjang sekolah menengah pertama. Mengembangkan kompetensi akademik lanjutan dan keterampilan pra-vokasional.
                    </p>
                </div>

                <!-- Paket C -->
                <div class="bg-surface border border-border rounded-2xl p-8 hover:shadow-soft transition-all duration-300">
                    <div class="w-14 h-14 rounded-2xl bg-primary-soft text-primary flex items-center justify-center mb-6">
                        <span class="text-2xl font-extrabold">C</span>
                    </div>
                    <h3 class="text-xl font-bold text-navy mb-2">Program Paket C</h3>
                    <div class="inline-block px-2.5 py-1 bg-gray-100 text-gray-700 text-xs font-semibold rounded-md mb-4">Setara SMA/MA</div>
                    <p class="text-muted text-sm leading-relaxed mb-8">
                        Program setingkat sekolah menengah atas. Menyiapkan peserta didik dengan kompetensi akademik matang untuk memasuki dunia kerja atau perguruan tinggi.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- EKOSISTEM SISTEM -->
    <section class="py-24 bg-surface border-y border-border">
        <div class="max-w-[1280px] mx-auto px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-8 mb-16">
                <div class="max-w-xl">
                    <h2 class="text-3xl lg:text-4xl font-bold text-navy mb-4">Ekosistem Informasi & Pembelajaran</h2>
                    <p class="text-lg text-muted">Satu pintu untuk semua kebutuhan akademik dan administrasi peserta didik dan pendidik.</p>
                </div>
                <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-6 py-3 bg-white border border-border text-navy text-sm font-semibold rounded-xl hover:bg-background transition-colors shadow-sm">
                    Coba Sistem
                </a>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
                <div class="p-6 border border-border rounded-2xl text-center hover:border-primary/50 transition-colors">
                    <i data-lucide="layout-dashboard" class="w-8 h-8 text-primary mx-auto mb-4"></i>
                    <h4 class="font-bold text-navy text-sm">Akademik</h4>
                </div>
                <div class="p-6 border border-border rounded-2xl text-center hover:border-primary/50 transition-colors">
                    <i data-lucide="calendar-check" class="w-8 h-8 text-primary mx-auto mb-4"></i>
                    <h4 class="font-bold text-navy text-sm">Jadwal & Absensi</h4>
                </div>
                <div class="p-6 border border-border rounded-2xl text-center hover:border-primary/50 transition-colors">
                    <i data-lucide="book-open" class="w-8 h-8 text-primary mx-auto mb-4"></i>
                    <h4 class="font-bold text-navy text-sm">Materi</h4>
                </div>
                <div class="p-6 border border-border rounded-2xl text-center hover:border-primary/50 transition-colors">
                    <i data-lucide="clipboard-edit" class="w-8 h-8 text-primary mx-auto mb-4"></i>
                    <h4 class="font-bold text-navy text-sm">Tugas & Kuis</h4>
                </div>
                <div class="p-6 border border-border rounded-2xl text-center hover:border-primary/50 transition-colors">
                    <i data-lucide="bar-chart-2" class="w-8 h-8 text-primary mx-auto mb-4"></i>
                    <h4 class="font-bold text-navy text-sm">Nilai & Rapor</h4>
                </div>
                <div class="p-6 border border-border rounded-2xl text-center hover:border-primary/50 transition-colors">
                    <i data-lucide="bell" class="w-8 h-8 text-primary mx-auto mb-4"></i>
                    <h4 class="font-bold text-navy text-sm">Pengumuman</h4>
                </div>
            </div>
        </div>
    </section>

    <!-- KEGIATAN & INFORMASI -->
    <section id="kegiatan" class="py-24 bg-background">
        <div class="max-w-[1280px] mx-auto px-6 lg:px-8">
            <div class="flex items-center justify-between mb-12">
                <h2 class="text-3xl font-bold text-navy">Kegiatan & Informasi Terbaru</h2>
            </div>

            @if($kegiatans->count() > 0)
                <div class="grid md:grid-cols-3 gap-8">
                    @foreach($kegiatans as $kegiatan)
                        <article class="bg-surface rounded-2xl overflow-hidden border border-border flex flex-col hover:shadow-soft transition-all">
                            <div class="p-6 flex-1 flex flex-col">
                                <div class="flex items-center justify-between mb-4">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-primary">{{ $kegiatan->kategori ?? 'Informasi' }}</span>
                                    <span class="text-[11px] font-medium text-muted">{{ $kegiatan->published_at ? $kegiatan->published_at->format('d M Y') : $kegiatan->created_at->format('d M Y') }}</span>
                                </div>
                                <h3 class="text-lg font-bold text-navy line-clamp-2 mb-3">{{ $kegiatan->judul }}</h3>
                                <p class="text-sm text-muted line-clamp-3 mb-6 flex-1">{{ strip_tags($kegiatan->konten) }}</p>
                                
                                <div class="mt-auto border-t border-border pt-4">
                                    <span class="text-sm font-semibold text-primary hover:text-primary-dark transition-colors inline-flex items-center">
                                        Selengkapnya <i data-lucide="arrow-right" class="w-4 h-4 ml-1"></i>
                                    </span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12 bg-surface rounded-2xl border border-border">
                    <i data-lucide="newspaper" class="mx-auto w-12 h-12 text-muted/50 mb-4"></i>
                    <p class="text-muted font-medium">Belum ada informasi terbaru.</p>
                </div>
            @endif
        </div>
    </section>

    <!-- FINAL CTA -->
    <section class="py-24 relative overflow-hidden bg-primary">
        <!-- Subtle Pattern -->
        <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(#ffffff 1.5px, transparent 1.5px); background-size: 24px 24px;"></div>
        
        <div class="max-w-[1280px] mx-auto px-6 lg:px-8 relative z-10 text-center">
            <h2 class="text-3xl lg:text-5xl font-extrabold text-white mb-6 leading-tight">
                Mulai Perjalanan Belajarmu<br/>Bersama PKBM Aditya
            </h2>
            <p class="text-primary-soft text-lg mb-10 max-w-2xl mx-auto">
                Akses fasilitas pembelajaran digital dan sistem informasi PKBM terpadu melalui satu platform pintar.
            </p>
            <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-10 py-4 bg-white text-primary-dark text-lg font-bold rounded-2xl hover:bg-surface transition-all shadow-lg hover:shadow-xl hover:-translate-y-0.5">
                Masuk Sistem
            </a>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="bg-navy pt-20 pb-10 text-white/80">
        <div class="max-w-[1280px] mx-auto px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-16">
                <!-- Col 1 -->
                <div>
                    <div class="flex items-center gap-3 mb-6">
                        <img src="{{ asset('images/logo-pkbm-aditya.png') }}" alt="Logo PKBM Aditya" class="h-10 w-auto brightness-0 invert">
                        <div class="flex flex-col">
                            <span class="font-extrabold text-lg leading-none tracking-tight text-white">PKBM ADITYA</span>
                            <span class="text-[11px] font-semibold text-white/60 tracking-wider uppercase mt-0.5">Kabupaten Mamuju</span>
                        </div>
                    </div>
                    <p class="text-sm leading-relaxed mb-6">
                        Lembaga Pendidikan Nonformal yang berdedikasi membangun generasi cerdas, mandiri, dan berkarakter melalui pendidikan kesetaraan berstandar nasional.
                    </p>
                </div>

                <!-- Col 2 -->
                <div>
                    <h4 class="text-white font-bold mb-6 tracking-wide">Navigasi</h4>
                    <ul class="space-y-3 text-sm">
                        <li><a href="#" class="hover:text-white transition-colors">Beranda</a></li>
                        <li><a href="#tentang" class="hover:text-white transition-colors">Profil Lembaga</a></li>
                        <li><a href="#program" class="hover:text-white transition-colors">Program Studi</a></li>
                        <li><a href="#lms" class="hover:text-white transition-colors">Pembelajaran</a></li>
                        <li><a href="#kegiatan" class="hover:text-white transition-colors">Informasi</a></li>
                    </ul>
                </div>

                <!-- Col 3 -->
                <div>
                    <h4 class="text-white font-bold mb-6 tracking-wide">Kontak Kami</h4>
                    <ul class="space-y-4 text-sm">
                        <li class="flex items-start gap-3">
                            <i data-lucide="map-pin" class="w-5 h-5 shrink-0 text-white/50"></i>
                            <span>Kabupaten Mamuju,<br/>Sulawesi Barat, Indonesia</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i data-lucide="mail" class="w-5 h-5 shrink-0 text-white/50"></i>
                            <span>info@pkbmaditya.id</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i data-lucide="phone" class="w-5 h-5 shrink-0 text-white/50"></i>
                            <span>(0426) 123456</span>
                        </li>
                    </ul>
                </div>

                <!-- Col 4 -->
                <div>
                    <h4 class="text-white font-bold mb-6 tracking-wide">Akses Sistem</h4>
                    <ul class="space-y-3 text-sm">
                        <li><a href="{{ route('login') }}" class="hover:text-white transition-colors">Masuk Sistem</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-white transition-colors">LMS PKBM Aditya</a></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-white/10 pt-8 flex flex-col md:flex-row items-center justify-between gap-4 text-xs font-medium">
                <p>&copy; 2026 PKBM Aditya Kabupaten Mamuju.</p>
                <p>Sistem Informasi Manajemen Pendidikan.</p>
            </div>
        </div>
    </footer>

    <!-- Initialize Lucide Icons -->
    <script>
        lucide.createIcons();
    </script>
</body>
</html>