<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PKBM Aditya - Kabupaten Mamuju</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-pkbm-aditya.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Lucide Icons via CDN for landing page -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="antialiased bg-[#F5F7F8] text-[#172B3A] font-sans selection:bg-[#167C80] selection:text-white">

    <!-- 1. NAVBAR -->
    <nav class="bg-white/90 backdrop-blur-md border-b border-[#D9E2E5] sticky top-0 z-50 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/logo-pkbm-aditya.png') }}" alt="Logo PKBM Aditya" class="h-10 w-auto">
                    <div class="flex flex-col">
                        <span class="font-bold text-lg leading-none tracking-tight text-[#0F5F63]">PKBM ADITYA</span>
                        <span class="text-xs text-[#64748B] font-medium">Kabupaten Mamuju</span>
                    </div>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center gap-8">
                    <a href="#" class="text-sm font-semibold text-[#172B3A] hover:text-[#167C80] transition-colors">Beranda</a>
                    <a href="#profil" class="text-sm font-semibold text-[#64748B] hover:text-[#167C80] transition-colors">Profil</a>
                    <a href="#program" class="text-sm font-semibold text-[#64748B] hover:text-[#167C80] transition-colors">Program</a>
                    <a href="#layanan" class="text-sm font-semibold text-[#64748B] hover:text-[#167C80] transition-colors">Fasilitas</a>
                    <a href="#kegiatan" class="text-sm font-semibold text-[#64748B] hover:text-[#167C80] transition-colors">Informasi</a>
                    <a href="#footer" class="text-sm font-semibold text-[#64748B] hover:text-[#167C80] transition-colors">Kontak</a>
                </div>

                <div class="flex items-center gap-4">
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center px-6 py-2.5 bg-[#167C80] text-white text-sm font-semibold rounded-lg hover:bg-[#0F5F63] transition-all shadow-sm">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-6 py-2.5 bg-[#167C80] text-white text-sm font-semibold rounded-lg hover:bg-[#0F5F63] transition-all shadow-sm group">
                            Masuk Sistem
                            <i data-lucide="arrow-right" class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform"></i>
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- 2. HERO -->
    <section class="relative pt-20 pb-32 lg:pt-32 lg:pb-40 overflow-hidden bg-white">
        <div class="absolute inset-0 z-0">
            <div class="absolute -top-[20%] -right-[10%] w-[70%] h-[70%] rounded-full bg-[#E8F5F4]/60 blur-3xl"></div>
            <div class="absolute -bottom-[20%] -left-[10%] w-[60%] h-[60%] rounded-full bg-[#E8F5F4]/40 blur-3xl"></div>
        </div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-4xl mx-auto">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-[#E8F5F4] text-[#0F5F63] text-xs font-semibold mb-8 uppercase tracking-wider">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#167C80] opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-[#167C80]"></span>
                    </span>
                    Pusat Kegiatan Belajar Masyarakat
                </div>
                
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold tracking-tight mb-6 leading-tight text-[#172B3A]">
                    Belajar, Berkembang, dan <br class="hidden md:block" />
                    <span class="text-[#167C80]">Berkarya Bersama PKBM Aditya</span>
                </h1>
                
                <p class="text-lg md:text-xl text-[#64748B] mb-10 max-w-2xl mx-auto leading-relaxed">
                    Pusat pendidikan nonformal unggulan di Kabupaten Mamuju yang mendukung peserta didik mengembangkan kompetensi, keterampilan, dan kemandirian untuk melanjutkan pendidikan ke jenjang yang lebih tinggi.
                </p>
                
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="#program" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3.5 bg-white text-[#172B3A] text-base font-semibold rounded-lg border border-[#D9E2E5] hover:bg-[#F5F7F8] hover:border-[#64748B] transition-all shadow-sm">
                        Lihat Program
                    </a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3.5 bg-[#167C80] text-white text-base font-semibold rounded-lg hover:bg-[#0F5F63] transition-all shadow-sm hover:shadow-md">
                            Dashboard
                            <i data-lucide="layout-dashboard" class="w-5 h-5 ml-2"></i>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3.5 bg-[#167C80] text-white text-base font-semibold rounded-lg hover:bg-[#0F5F63] transition-all shadow-sm hover:shadow-md">
                            Masuk Sistem
                            <i data-lucide="log-in" class="w-5 h-5 ml-2"></i>
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </section>

    <!-- 3. TRUST / QUICK INFO -->
    <section class="py-12 bg-[#0F5F63] border-y border-[#0F5F63]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 divide-x divide-white/20">
                <div class="text-center px-4">
                    <div class="text-3xl lg:text-4xl font-bold text-white mb-1">Paket A</div>
                    <div class="text-[#E8F5F4] text-sm font-medium opacity-90">Setara SD/MI</div>
                </div>
                <div class="text-center px-4">
                    <div class="text-3xl lg:text-4xl font-bold text-white mb-1">Paket B</div>
                    <div class="text-[#E8F5F4] text-sm font-medium opacity-90">Setara SMP/MTs</div>
                </div>
                <div class="text-center px-4">
                    <div class="text-3xl lg:text-4xl font-bold text-white mb-1">Paket C</div>
                    <div class="text-[#E8F5F4] text-sm font-medium opacity-90">Setara SMA/MA</div>
                </div>
                <div class="text-center px-4">
                    <div class="text-3xl lg:text-4xl font-bold text-white mb-1">100%</div>
                    <div class="text-[#E8F5F4] text-sm font-medium opacity-90">Sistem Digital</div>
                </div>
            </div>
        </div>
    </section>

    <!-- 5. TENTANG PKBM ADITYA -->
    <section id="profil" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <div class="order-2 lg:order-1 relative">
                    <div class="absolute inset-0 bg-[#E8F5F4] rounded-2xl transform -rotate-3 scale-105 z-0"></div>
                    <div class="relative z-10 bg-white p-8 rounded-2xl border border-[#D9E2E5] shadow-sm flex flex-col items-center justify-center text-center h-full min-h-[400px]">
                        <img src="{{ asset('images/logo-pkbm-aditya.png') }}" alt="Logo PKBM Aditya" class="w-48 h-48 mb-6 drop-shadow-lg">
                        <h3 class="text-2xl font-bold text-[#172B3A] mb-2">PKBM Aditya</h3>
                        <p class="text-[#64748B] font-medium">Lembaga Pendidikan Nonformal</p>
                    </div>
                </div>
                <div class="order-1 lg:order-2">
                    <h2 class="text-sm font-bold text-[#167C80] uppercase tracking-widest mb-3">Tentang Kami</h2>
                    <h3 class="text-3xl md:text-4xl font-bold text-[#172B3A] mb-6 leading-tight">Membangun Generasi Cerdas dan Mandiri di Mamuju</h3>
                    <p class="text-[#64748B] text-lg mb-6 leading-relaxed">
                        PKBM Aditya hadir sebagai solusi pendidikan inklusif bagi masyarakat yang membutuhkan akses pendidikan kesetaraan. Kami berkomitmen menyelenggarakan pembelajaran yang bermutu, relevan dengan kebutuhan, dan didukung oleh tenaga pendidik profesional.
                    </p>
                    <p class="text-[#64748B] text-lg mb-8 leading-relaxed">
                        Dengan sistem pembelajaran modern berbasis digital, kami memfasilitasi peserta didik agar dapat belajar secara fleksibel, mandiri, dan terstruktur untuk mempersiapkan masa depan yang lebih cerah.
                    </p>
                    <ul class="space-y-4">
                        <li class="flex items-start">
                            <div class="flex-shrink-0 w-6 h-6 rounded-full bg-[#E8F5F4] flex items-center justify-center mt-1 mr-3">
                                <i data-lucide="check" class="w-4 h-4 text-[#167C80]"></i>
                            </div>
                            <span class="text-[#172B3A] font-medium">Kurikulum berstandar nasional</span>
                        </li>
                        <li class="flex items-start">
                            <div class="flex-shrink-0 w-6 h-6 rounded-full bg-[#E8F5F4] flex items-center justify-center mt-1 mr-3">
                                <i data-lucide="check" class="w-4 h-4 text-[#167C80]"></i>
                            </div>
                            <span class="text-[#172B3A] font-medium">Tutor profesional dan berpengalaman</span>
                        </li>
                        <li class="flex items-start">
                            <div class="flex-shrink-0 w-6 h-6 rounded-full bg-[#E8F5F4] flex items-center justify-center mt-1 mr-3">
                                <i data-lucide="check" class="w-4 h-4 text-[#167C80]"></i>
                            </div>
                            <span class="text-[#172B3A] font-medium">Fasilitas pembelajaran terintegrasi digital</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. PROGRAM PEMBELAJARAN -->
    <section id="program" class="py-24 bg-[#F5F7F8]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-sm font-bold text-[#167C80] uppercase tracking-widest mb-3">Program Kami</h2>
                <h3 class="text-3xl md:text-4xl font-bold text-[#172B3A] mb-4">Program Pendidikan Kesetaraan</h3>
                <p class="text-[#64748B] text-lg">Pilihan program pendidikan yang dirancang untuk memenuhi standar kompetensi lulusan setara dengan pendidikan formal.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Paket A -->
                <div class="bg-white rounded-2xl p-8 border border-[#D9E2E5] hover:border-[#167C80] hover:shadow-lg transition-all duration-300 group">
                    <div class="w-14 h-14 rounded-xl bg-[#E8F5F4] flex items-center justify-center mb-6 group-hover:bg-[#167C80] transition-colors">
                        <i data-lucide="book-open" class="w-7 h-7 text-[#167C80] group-hover:text-white transition-colors"></i>
                    </div>
                    <div class="inline-block px-3 py-1 rounded-md bg-[#F5F7F8] text-[#64748B] text-xs font-bold mb-4">Setara SD/MI</div>
                    <h4 class="text-2xl font-bold text-[#172B3A] mb-3">Paket A</h4>
                    <p class="text-[#64748B] mb-6 leading-relaxed">Program pendidikan dasar yang memberikan pengetahuan dan keterampilan dasar bagi peserta didik.</p>
                    <a href="#footer" class="inline-flex items-center text-[#167C80] font-semibold hover:text-[#0F5F63] transition-colors">
                        Informasi Pendaftaran <i data-lucide="arrow-right" class="w-4 h-4 ml-1"></i>
                    </a>
                </div>

                <!-- Paket B -->
                <div class="bg-white rounded-2xl p-8 border border-[#D9E2E5] hover:border-[#167C80] hover:shadow-lg transition-all duration-300 group relative">
                    <div class="absolute -top-3 right-6 bg-[#C62828] text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm">Populer</div>
                    <div class="w-14 h-14 rounded-xl bg-[#E8F5F4] flex items-center justify-center mb-6 group-hover:bg-[#167C80] transition-colors">
                        <i data-lucide="graduation-cap" class="w-7 h-7 text-[#167C80] group-hover:text-white transition-colors"></i>
                    </div>
                    <div class="inline-block px-3 py-1 rounded-md bg-[#F5F7F8] text-[#64748B] text-xs font-bold mb-4">Setara SMP/MTs</div>
                    <h4 class="text-2xl font-bold text-[#172B3A] mb-3">Paket B</h4>
                    <p class="text-[#64748B] mb-6 leading-relaxed">Program pendidikan menengah pertama untuk mengembangkan kompetensi akademik dan life skill dasar.</p>
                    <a href="#footer" class="inline-flex items-center text-[#167C80] font-semibold hover:text-[#0F5F63] transition-colors">
                        Informasi Pendaftaran <i data-lucide="arrow-right" class="w-4 h-4 ml-1"></i>
                    </a>
                </div>

                <!-- Paket C -->
                <div class="bg-white rounded-2xl p-8 border border-[#D9E2E5] hover:border-[#167C80] hover:shadow-lg transition-all duration-300 group">
                    <div class="w-14 h-14 rounded-xl bg-[#E8F5F4] flex items-center justify-center mb-6 group-hover:bg-[#167C80] transition-colors">
                        <i data-lucide="award" class="w-7 h-7 text-[#167C80] group-hover:text-white transition-colors"></i>
                    </div>
                    <div class="inline-block px-3 py-1 rounded-md bg-[#F5F7F8] text-[#64748B] text-xs font-bold mb-4">Setara SMA/MA</div>
                    <h4 class="text-2xl font-bold text-[#172B3A] mb-3">Paket C</h4>
                    <p class="text-[#64748B] mb-6 leading-relaxed">Program pendidikan menengah atas persiapan melanjutkan ke perguruan tinggi atau memasuki dunia kerja.</p>
                    <a href="#footer" class="inline-flex items-center text-[#167C80] font-semibold hover:text-[#0F5F63] transition-colors">
                        Informasi Pendaftaran <i data-lucide="arrow-right" class="w-4 h-4 ml-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- 6. LAYANAN DIGITAL -->
    <section id="layanan" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row justify-between items-end mb-12 gap-6">
                <div class="max-w-2xl">
                    <h2 class="text-sm font-bold text-[#167C80] uppercase tracking-widest mb-3">Fasilitas Digital</h2>
                    <h3 class="text-3xl md:text-4xl font-bold text-[#172B3A]">Sistem Informasi & Pembelajaran Terpadu</h3>
                </div>
                @auth
                    <a href="{{ route('dashboard') }}" class="hidden lg:inline-flex items-center justify-center px-6 py-3 bg-[#E8F5F4] text-[#0F5F63] font-semibold rounded-lg hover:bg-[#167C80] hover:text-white transition-colors">
                        Buka Dashboard <i data-lucide="layout-dashboard" class="w-4 h-4 ml-2"></i>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="hidden lg:inline-flex items-center justify-center px-6 py-3 bg-[#E8F5F4] text-[#0F5F63] font-semibold rounded-lg hover:bg-[#167C80] hover:text-white transition-colors">
                        Coba Sekarang <i data-lucide="arrow-right" class="w-4 h-4 ml-2"></i>
                    </a>
                @endauth
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-12">
                <!-- Feature 1: Akademik -->
                <div class="flex flex-col">
                    <div class="w-12 h-12 rounded-lg bg-[#E8F5F4] flex items-center justify-center mb-5 text-[#167C80]">
                        <i data-lucide="layout-dashboard" class="w-6 h-6"></i>
                    </div>
                    <h4 class="text-xl font-bold text-[#172B3A] mb-2">Akademik</h4>
                    <p class="text-[#64748B] leading-relaxed">Manajemen data peserta didik, tutor, rombongan belajar, dan administrasi sekolah yang terpusat.</p>
                </div>

                <!-- Feature 2: Jadwal & Absensi -->
                <div class="flex flex-col">
                    <div class="w-12 h-12 rounded-lg bg-[#E8F5F4] flex items-center justify-center mb-5 text-[#167C80]">
                        <i data-lucide="calendar-clock" class="w-6 h-6"></i>
                    </div>
                    <h4 class="text-xl font-bold text-[#172B3A] mb-2">Jadwal & Absensi</h4>
                    <p class="text-[#64748B] leading-relaxed">Sistem penjadwalan kelas yang terstruktur dilengkapi fitur presensi kehadiran digital.</p>
                </div>

                <!-- Feature 3: Materi Pembelajaran -->
                <div class="flex flex-col">
                    <div class="w-12 h-12 rounded-lg bg-[#E8F5F4] flex items-center justify-center mb-5 text-[#167C80]">
                        <i data-lucide="file-text" class="w-6 h-6"></i>
                    </div>
                    <h4 class="text-xl font-bold text-[#172B3A] mb-2">Materi Pembelajaran</h4>
                    <p class="text-[#64748B] leading-relaxed">Akses modul, bahan ajar, dan materi belajar secara online kapan saja dan di mana saja.</p>
                </div>

                <!-- Feature 4: Tugas & Quiz -->
                <div class="flex flex-col">
                    <div class="w-12 h-12 rounded-lg bg-[#E8F5F4] flex items-center justify-center mb-5 text-[#167C80]">
                        <i data-lucide="clipboard-list" class="w-6 h-6"></i>
                    </div>
                    <h4 class="text-xl font-bold text-[#172B3A] mb-2">Tugas & Quiz</h4>
                    <p class="text-[#64748B] leading-relaxed">Pemberian dan pengumpulan tugas secara digital, serta pelaksanaan ujian/kuis terintegrasi.</p>
                </div>

                <!-- Feature 5: Nilai & Rapor -->
                <div class="flex flex-col">
                    <div class="w-12 h-12 rounded-lg bg-[#E8F5F4] flex items-center justify-center mb-5 text-[#167C80]">
                        <i data-lucide="line-chart" class="w-6 h-6"></i>
                    </div>
                    <h4 class="text-xl font-bold text-[#172B3A] mb-2">Nilai & Rapor</h4>
                    <p class="text-[#64748B] leading-relaxed">Pengelolaan nilai, evaluasi akademik, dan pencetakan e-Rapor secara otomatis.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 7. KEGIATAN / INFORMASI -->
    <section id="kegiatan" class="py-24 bg-[#F5F7F8]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-sm font-bold text-[#167C80] uppercase tracking-widest mb-3">Kegiatan & Informasi</h2>
                <h3 class="text-3xl md:text-4xl font-bold text-[#172B3A] mb-4">Aktivitas Terbaru PKBM Aditya</h3>
                <p class="text-[#64748B] text-lg">Informasi kegiatan, pengumuman, dan berita terbaru dari PKBM Aditya Kabupaten Mamuju.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Kegiatan 1 -->
                <article class="bg-white rounded-2xl overflow-hidden border border-[#D9E2E5] hover:border-[#167C80] hover:shadow-lg transition-all duration-300">
                    <div class="aspect-video bg-[#E8F5F4] flex items-center justify-center">
                        <i data-lucide="calendar" class="w-16 h-16 text-[#167C80]/50"></i>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center gap-2 text-xs text-[#64748B] mb-3">
                            <span class="px-2 py-0.5 rounded bg-[#E8F5F4] text-[#167C80] font-medium">Pengumuman</span>
                            <span>•</span>
                            <time datetime="2025-01-15">15 Januari 2025</time>
                        </div>
                        <h4 class="text-xl font-bold text-[#172B3A] mb-2 line-clamp-2">Pendaftaran Peserta Didik Baru Tahun Ajaran 2025/2026</h4>
                        <p class="text-[#64748B] mb-4 leading-relaxed">Dibuka pendaftaran untuk Paket A, B, dan C. Proses pendaftaran dapat dilakukan secara online melalui sistem.</p>
                        <a href="#footer" class="inline-flex items-center text-[#167C80] font-semibold hover:text-[#0F5F63] transition-colors">
                            Selengkapnya <i data-lucide="arrow-right" class="w-4 h-4 ml-1"></i>
                        </a>
                    </div>
                </article>

                <!-- Kegiatan 2 -->
                <article class="bg-white rounded-2xl overflow-hidden border border-[#D9E2E5] hover:border-[#167C80] hover:shadow-lg transition-all duration-300">
                    <div class="aspect-video bg-[#E8F5F4] flex items-center justify-center">
                        <i data-lucide="award" class="w-16 h-16 text-[#167C80]/50"></i>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center gap-2 text-xs text-[#64748B] mb-3">
                            <span class="px-2 py-0.5 rounded bg-[#E8F5F4] text-[#167C80] font-medium">Kegiatan</span>
                            <span>•</span>
                            <time datetime="2025-02-20">20 Februari 2025</time>
                        </div>
                        <h4 class="text-xl font-bold text-[#172B3A] mb-2 line-clamp-2">Pelatihan Keterampilan Digital bagi Tutor</h4>
                        <p class="text-[#64748B] mb-4 leading-relaxed">Workshop meningkatkan kompetensi digital tutor untuk mendukung pembelajaran berbasis teknologi.</p>
                        <a href="#footer" class="inline-flex items-center text-[#167C80] font-semibold hover:text-[#0F5F63] transition-colors">
                            Selengkapnya <i data-lucide="arrow-right" class="w-4 h-4 ml-1"></i>
                        </a>
                    </div>
                </article>

                <!-- Kegiatan 3 -->
                <article class="bg-white rounded-2xl overflow-hidden border border-[#D9E2E5] hover:border-[#167C80] hover:shadow-lg transition-all duration-300">
                    <div class="aspect-video bg-[#E8F5F4] flex items-center justify-center">
                        <i data-lucide="users" class="w-16 h-16 text-[#167C80]/50"></i>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center gap-2 text-xs text-[#64748B] mb-3">
                            <span class="px-2 py-0.5 rounded bg-[#E8F5F4] text-[#167C80] font-medium">Berita</span>
                            <span>•</span>
                            <time datetime="2025-03-10">10 Maret 2025</time>
                        </div>
                        <h4 class="text-xl font-bold text-[#172B3A] mb-2 line-clamp-2">Kunjungan Kerja Dinas Pendidikan Kabupaten Mamuju</h4>
                        <p class="text-[#64748B] mb-4 leading-relaxed">Tim Dinas Pendidikan mengunjungi PKBM Aditya untuk meninjau pelaksanaan program kesetaraan.</p>
                        <a href="#footer" class="inline-flex items-center text-[#167C80] font-semibold hover:text-[#0F5F63] transition-colors">
                            Selengkapnya <i data-lucide="arrow-right" class="w-4 h-4 ml-1"></i>
                        </a>
                    </div>
                </article>
            </div>

            <div class="text-center mt-10">
                <a href="#footer" class="inline-flex items-center justify-center px-8 py-3.5 bg-white text-[#172B3A] text-base font-semibold rounded-lg border border-[#D9E2E5] hover:bg-[#F5F7F8] hover:border-[#64748B] transition-all shadow-sm">
                    Lihat Semua Kegiatan
                </a>
            </div>
        </div>
    </section>

    <!-- 8. CALL TO ACTION -->
    <section class="py-20 bg-[#167C80] relative overflow-hidden">
        <div class="absolute inset-0 z-0 opacity-10">
            <svg class="absolute w-full h-full" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                        <path d="M 40 0 L 0 0 0 40" fill="none" stroke="white" stroke-width="1"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#grid)" />
            </svg>
        </div>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
            <h2 class="text-3xl md:text-5xl font-bold text-white mb-6">Mulai Perjalanan Belajarmu Bersama PKBM Aditya</h2>
            <p class="text-lg text-[#E8F5F4] mb-10 max-w-2xl mx-auto opacity-90">Akses seluruh fasilitas pembelajaran digital dan administrasi dengan masuk ke dalam sistem.</p>
            @auth
                <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center px-10 py-4 bg-white text-[#0F5F63] text-lg font-bold rounded-lg hover:bg-[#F5F7F8] hover:shadow-xl transition-all group">
                    Buka Dashboard
                    <i data-lucide="layout-dashboard" class="w-5 h-5 ml-2 group-hover:scale-110 transition-transform"></i>
                </a>
            @else
                <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-10 py-4 bg-white text-[#0F5F63] text-lg font-bold rounded-lg hover:bg-[#F5F7F8] hover:shadow-xl transition-all group">
                    Masuk Sistem Sekarang
                    <i data-lucide="arrow-right" class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform"></i>
                </a>
            @endauth
        </div>
    </section>

    <!-- 9. FOOTER -->
    <footer id="footer" class="bg-[#F5F7F8] pt-16 pb-8 border-t border-[#D9E2E5]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
                <div class="md:col-span-2">
                    <div class="flex items-center gap-3 mb-6">
                        <img src="{{ asset('images/logo-pkbm-aditya.png') }}" alt="Logo" class="h-12 w-auto">
                        <div class="flex flex-col">
                            <span class="font-bold text-xl leading-none text-[#172B3A]">PKBM ADITYA</span>
                            <span class="text-sm text-[#64748B] font-medium">Kabupaten Mamuju</span>
                        </div>
                    </div>
                    <p class="text-[#64748B] leading-relaxed mb-6 max-w-md">
                        Lembaga pendidikan nonformal berdedikasi untuk mencerdaskan kehidupan bangsa melalui program pendidikan kesetaraan yang berkualitas.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="w-10 h-10 rounded-full bg-white border border-[#D9E2E5] flex items-center justify-center text-[#64748B] hover:text-[#167C80] hover:border-[#167C80] transition-colors">
                            <i data-lucide="facebook" class="w-5 h-5"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full bg-white border border-[#D9E2E5] flex items-center justify-center text-[#64748B] hover:text-[#167C80] hover:border-[#167C80] transition-colors">
                            <i data-lucide="instagram" class="w-5 h-5"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full bg-white border border-[#D9E2E5] flex items-center justify-center text-[#64748B] hover:text-[#167C80] hover:border-[#167C80] transition-colors">
                            <i data-lucide="mail" class="w-5 h-5"></i>
                        </a>
                    </div>
                </div>

                <div>
                    <h4 class="text-[#172B3A] font-bold mb-4">Navigasi</h4>
                    <ul class="space-y-3 text-[#64748B]">
                        <li><a href="#" class="hover:text-[#167C80] transition-colors">Beranda</a></li>
                        <li><a href="#profil" class="hover:text-[#167C80] transition-colors">Profil Lembaga</a></li>
                        <li><a href="#program" class="hover:text-[#167C80] transition-colors">Program Pendidikan</a></li>
                        <li><a href="#layanan" class="hover:text-[#167C80] transition-colors">Fasilitas Digital</a></li>
                        <li><a href="#kegiatan" class="hover:text-[#167C80] transition-colors">Informasi & Kegiatan</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-[#172B3A] font-bold mb-4">Kontak</h4>
                    <ul class="space-y-4 text-[#64748B]">
                        <li class="flex items-start">
                            <i data-lucide="map-pin" class="w-5 h-5 text-[#167C80] mr-3 mt-0.5 flex-shrink-0"></i>
                            <span>Kabupaten Mamuju, Provinsi Sulawesi Barat, Indonesia</span>
                        </li>
                        <li class="flex items-center">
                            <i data-lucide="phone" class="w-5 h-5 text-[#167C80] mr-3 flex-shrink-0"></i>
                            <span>(0426) XXXXXX</span>
                        </li>
                        <li class="flex items-center">
                            <i data-lucide="mail" class="w-5 h-5 text-[#167C80] mr-3 flex-shrink-0"></i>
                            <span>info@pkbmaditya.com</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="pt-8 border-t border-[#D9E2E5] flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-[#64748B] text-sm text-center md:text-left">
                    &copy; {{ date('Y') }} PKBM Aditya. Hak Cipta Dilindungi Undang-Undang.
                </p>
                <div class="text-[#64748B] text-sm font-medium">
                    Sistem Informasi Manajemen & Pembelajaran v1.0
                </div>
            </div>
        </div>
    </footer>

    <!-- Initialize Lucide Icons -->
    <script>
        lucide.createIcons();
    </script>
</body>
</html>