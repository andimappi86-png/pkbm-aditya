<x-app-layout>
    <x-slot name="title">Buat Quiz Baru</x-slot>

    <div class="max-w-7xl mx-auto" x-data="quizBuilder()" x-init="init()">
        <x-breadcrumb :items="[['label' => 'Pembelajaran'], ['label' => 'Kuis & Ujian', 'href' => route('quiz.index')], ['label' => 'Buat Baru']]" />

        {{-- Tab Navigation --}}
        <div class="flex items-center gap-1 mb-6 bg-slate-100 rounded-xl p-1 border border-slate-200">
            <button type="button"
                @click="activeTab = 'settings'"
                :class="activeTab === 'settings' ? 'bg-white text-teal-700 shadow-sm font-semibold border border-slate-200' : 'text-slate-500 hover:text-slate-700'"
                class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg text-sm transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span class="hidden sm:inline">1. Pengaturan Kuis</span>
                <span class="sm:hidden">Pengaturan</span>
            </button>
            <button type="button"
                @click="activeTab = 'questions'"
                :class="activeTab === 'questions' ? 'bg-white text-teal-700 shadow-sm font-semibold border border-slate-200' : 'text-slate-500 hover:text-slate-700'"
                class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg text-sm transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="hidden sm:inline">2. Daftar Pertanyaan</span>
                <span class="sm:hidden">Soal</span>
                <span x-show="questions.length > 0"
                    class="ml-1 bg-teal-100 text-teal-700 text-xs font-bold px-2 py-0.5 rounded-full"
                    x-text="questions.length"></span>
            </button>
            <button type="button"
                @click="activeTab = 'preview'"
                :class="activeTab === 'preview' ? 'bg-white text-teal-700 shadow-sm font-semibold border border-slate-200' : 'text-slate-500 hover:text-slate-700'"
                class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg text-sm transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                <span class="hidden sm:inline">3. Pratinjau</span>
                <span class="sm:hidden">Pratinjau</span>
            </button>
        </div>

        {{-- FORM WRAPPER --}}
        <form method="POST" action="{{ route('quiz.store') }}" id="quizForm" @submit.prevent="submitForm">
            @csrf
            @if(request('penugasan_mengajar_id'))
                <input type="hidden" name="penugasan_mengajar_id" value="{{ request('penugasan_mengajar_id') }}">
            @endif
            <input type="hidden" name="questions_data" :value="JSON.stringify(questions)">

            {{-- ═══════════════════════════════ TAB 1: PENGATURAN ═══════════════════════════════ --}}
            <div x-show="activeTab === 'settings'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    {{-- Kolom Utama --}}
                    <div class="lg:col-span-2 space-y-5">

                        {{-- ── Section 1: Informasi Dasar Kuis ── --}}
                        <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden">
                            <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100 bg-slate-50/60">
                                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-teal-100 text-teal-700 shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </span>
                                <div>
                                    <h2 class="text-sm font-semibold text-slate-900 leading-tight">Informasi Dasar Kuis</h2>
                                    <p class="text-xs text-slate-500 mt-0.5">Judul dan instruksi yang akan dilihat peserta didik</p>
                                </div>
                            </div>
                            <div class="px-6 py-5 space-y-4">
                                <div>
                                    <label for="judul" class="block text-sm font-medium text-slate-700 mb-1.5">
                                        Judul Quiz <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="judul" name="judul"
                                        class="w-full px-3.5 py-2.5 text-slate-900 bg-white border border-slate-300 rounded-xl text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-600 @error('judul') border-red-400 bg-red-50/30 @enderror"
                                        required value="{{ old('judul') }}"
                                        placeholder="Contoh: Kuis Matematika Bab 3 — Persamaan Linear">
                                    @error('judul')
                                        <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="deskripsi" class="block text-sm font-medium text-slate-700 mb-1.5">Deskripsi / Petunjuk</label>
                                    <textarea id="deskripsi" name="deskripsi" rows="4"
                                        class="w-full px-3.5 py-2.5 text-slate-900 bg-white border border-slate-300 rounded-xl text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-600 resize-none @error('deskripsi') border-red-400 @enderror"
                                        placeholder="Instruksi, panduan, atau catatan penting untuk peserta didik sebelum mengerjakan kuis...">{{ old('deskripsi') }}</textarea>
                                    @error('deskripsi')
                                        <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- ── Section 2: Waktu, Kuota & Publikasi ── --}}
                        <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden">
                            <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100 bg-slate-50/60">
                                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-blue-100 text-blue-700 shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </span>
                                <div>
                                    <h2 class="text-sm font-semibold text-slate-900 leading-tight">Waktu, Kuota & Publikasi</h2>
                                    <p class="text-xs text-slate-500 mt-0.5">Durasi, batas percobaan, jadwal, dan pengaturan akses</p>
                                </div>
                            </div>
                            <div class="px-6 py-5 space-y-5">
                                {{-- Durasi & Percobaan --}}
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label for="durasi_menit" class="block text-sm font-medium text-slate-700 mb-1.5">
                                            Durasi <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <input type="number" id="durasi_menit" name="durasi_menit"
                                                class="w-full pl-3.5 pr-16 py-2.5 text-slate-900 bg-white border border-slate-300 rounded-xl text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-600 @error('durasi_menit') border-red-400 @enderror"
                                                required min="1" max="300" value="{{ old('durasi_menit', 30) }}">
                                            <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none">
                                                <span class="text-xs font-semibold text-slate-400 bg-slate-100 px-2 py-0.5 rounded-md">Menit</span>
                                            </span>
                                        </div>
                                        @error('durasi_menit')
                                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="max_attempts" class="block text-sm font-medium text-slate-700 mb-1.5">
                                            Maksimal Percobaan <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <input type="number" id="max_attempts" name="max_attempts"
                                                class="w-full pl-3.5 pr-14 py-2.5 text-slate-900 bg-white border border-slate-300 rounded-xl text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-600 @error('max_attempts') border-red-400 @enderror"
                                                required min="1" max="10" value="{{ old('max_attempts', 1) }}">
                                            <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none">
                                                <span class="text-xs font-semibold text-slate-400 bg-slate-100 px-2 py-0.5 rounded-md">Kali</span>
                                            </span>
                                        </div>
                                        @error('max_attempts')
                                            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Waktu Mulai & Selesai --}}
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label for="buka_waktu" class="block text-sm font-medium text-slate-700 mb-1.5">
                                            Waktu Mulai <span class="text-slate-400 font-normal text-xs">(Opsional)</span>
                                        </label>
                                        <input type="datetime-local" id="buka_waktu" name="buka_waktu"
                                            class="w-full px-3.5 py-2.5 text-slate-900 bg-white border border-slate-300 rounded-xl text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-600 @error('buka_waktu') border-red-400 @enderror"
                                            value="{{ old('buka_waktu') ? \Carbon\Carbon::parse(old('buka_waktu'))->format('Y-m-d\TH:i') : '' }}">
                                        <p class="mt-1.5 text-xs text-slate-400 flex items-start gap-1">
                                            <svg class="w-3 h-3 shrink-0 mt-px text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Kosongkan untuk langsung bisa dikerjakan saat diterbitkan.
                                        </p>
                                        @error('buka_waktu')
                                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="tutup_waktu" class="block text-sm font-medium text-slate-700 mb-1.5">
                                            Waktu Selesai <span class="text-slate-400 font-normal text-xs">(Opsional)</span>
                                        </label>
                                        <input type="datetime-local" id="tutup_waktu" name="tutup_waktu"
                                            class="w-full px-3.5 py-2.5 text-slate-900 bg-white border border-slate-300 rounded-xl text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-600 @error('tutup_waktu') border-red-400 @enderror"
                                            value="{{ old('tutup_waktu') ? \Carbon\Carbon::parse(old('tutup_waktu'))->format('Y-m-d\TH:i') : '' }}">
                                        <p class="mt-1.5 text-xs text-slate-400 flex items-start gap-1">
                                            <svg class="w-3 h-3 shrink-0 mt-px text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Kosongkan untuk tidak ada batas waktu akhir.
                                        </p>
                                        @error('tutup_waktu')
                                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Acak & Status --}}
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label for="acak_soal" class="block text-sm font-medium text-slate-700 mb-1.5">Pengacakan Soal</label>
                                        <select id="acak_soal" name="acak_soal"
                                            class="w-full px-3.5 py-2.5 text-slate-900 bg-white border border-slate-300 rounded-xl text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-600">
                                            <option value="0" {{ old('acak_soal', false) ? '' : 'selected' }}>Tidak Acak</option>
                                            <option value="1" {{ old('acak_soal') === '1' ? 'selected' : '' }}>Acak Urutan Soal</option>
                                            <option value="2" {{ old('acak_soal') === '2' ? 'selected' : '' }}>Acak Soal & Pilihan Jawaban</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="status_aktif" class="block text-sm font-medium text-slate-700 mb-1.5">Status Publikasi</label>
                                        <div class="relative">
                                            <select id="status_aktif" name="status_aktif"
                                                class="w-full pl-8 pr-3.5 py-2.5 text-slate-900 bg-white border border-slate-300 rounded-xl text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-600 appearance-none"
                                                x-model="statusPublish">
                                                <option value="1">Aktif / Diterbitkan</option>
                                                <option value="0" {{ old('status_aktif') === '0' ? 'selected' : '' }}>Draft / Disembunyikan</option>
                                            </select>
                                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                                <span class="w-2 h-2 rounded-full transition-colors"
                                                    :class="statusPublish === '1' || statusPublish === 1 ? 'bg-emerald-500' : 'bg-slate-400'"></span>
                                            </span>
                                            <span class="absolute inset-y-0 right-2.5 flex items-center pointer-events-none">
                                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ── Section 3: Pengaturan Tampilan Hasil ── --}}
                        <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden">
                            <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100 bg-slate-50/60">
                                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-violet-100 text-violet-700 shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </span>
                                <div>
                                    <h2 class="text-sm font-semibold text-slate-900 leading-tight">Pengaturan Tampilan Hasil</h2>
                                    <p class="text-xs text-slate-500 mt-0.5">Atur kapan peserta didik dapat melihat jawaban dan nilai mereka</p>
                                </div>
                            </div>
                            <div class="px-6 py-5">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="bg-slate-50/70 border border-slate-200 rounded-xl p-4 space-y-3">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                            <label for="tampilkan_jawaban" class="text-sm font-medium text-slate-700">Tampilkan Jawaban</label>
                                        </div>
                                        <select id="tampilkan_jawaban" name="tampilkan_jawaban" required
                                            class="w-full px-3 py-2 text-slate-900 bg-white border border-slate-300 rounded-lg text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-600">
                                            <option value="never" {{ old('tampilkan_jawaban', 'never') === 'never' ? 'selected' : '' }}>Tidak Pernah</option>
                                            <option value="after_submit" {{ old('tampilkan_jawaban') === 'after_submit' ? 'selected' : '' }}>Setelah Kuis Disubmit</option>
                                            <option value="after_deadline" {{ old('tampilkan_jawaban') === 'after_deadline' ? 'selected' : '' }}>Setelah Waktu Kuis Berakhir</option>
                                        </select>
                                        <p class="text-xs text-slate-500 leading-relaxed">Kapan peserta didik dapat melihat jawaban yang benar beserta pembahasan soal.</p>
                                    </div>

                                    <div class="bg-slate-50/70 border border-slate-200 rounded-xl p-4 space-y-3">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                            <label for="tampilkan_nilai" class="text-sm font-medium text-slate-700">Tampilkan Nilai</label>
                                        </div>
                                        <select id="tampilkan_nilai" name="tampilkan_nilai" required
                                            class="w-full px-3 py-2 text-slate-900 bg-white border border-slate-300 rounded-lg text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-600">
                                            <option value="never" {{ old('tampilkan_nilai', 'never') === 'never' ? 'selected' : '' }}>Tidak Pernah</option>
                                            <option value="after_submit" {{ old('tampilkan_nilai') === 'after_submit' ? 'selected' : '' }}>Langsung Setelah Submit</option>
                                            <option value="after_grading" {{ old('tampilkan_nilai') === 'after_grading' ? 'selected' : '' }}>Setelah Waktu Kuis Berakhir</option>
                                        </select>
                                        <p class="text-xs text-slate-500 leading-relaxed">Kapan peserta didik dapat melihat skor dan nilai akhir hasil pengerjaan kuis.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- Kolom Sidebar Pengaturan --}}
                    <div class="space-y-5">
                        {{-- Ringkasan soal --}}
                        <div class="bg-teal-50 border border-teal-200 rounded-2xl p-5 space-y-3">
                            <div class="flex items-center gap-2 mb-1">
                                <svg class="w-4 h-4 text-teal-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                <h3 class="text-sm font-semibold text-teal-800">Ringkasan Soal</h3>
                            </div>
                            <div class="space-y-2.5 text-sm text-teal-700">
                                <div class="flex justify-between items-center">
                                    <span class="text-teal-600">Total Soal</span>
                                    <span class="font-bold text-teal-900" x-text="questions.length + ' soal'">0 soal</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-teal-600">Total Poin</span>
                                    <span class="font-bold text-teal-900" x-text="totalPoints + ' poin'">0 poin</span>
                                </div>
                                <div class="h-px bg-teal-200/60"></div>
                                <div class="flex justify-between items-center">
                                    <span class="text-teal-600">Soal Lengkap</span>
                                    <span class="font-bold" :class="completeCount === questions.length && questions.length > 0 ? 'text-emerald-700' : 'text-amber-600'"
                                        x-text="completeCount + '/' + questions.length + ' soal'">0/0 soal</span>
                                </div>
                            </div>
                            <button type="button" @click="activeTab = 'questions'"
                                class="w-full mt-1 text-xs font-semibold text-teal-700 border border-teal-300 rounded-xl px-3 py-2.5 bg-white hover:bg-teal-100 transition-colors flex items-center justify-center gap-1.5">
                                Kelola Soal
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>

                        {{-- Tips --}}
                        <div class="bg-white border border-slate-200 rounded-2xl p-5 space-y-3">
                            <h3 class="text-xs font-semibold text-slate-600 uppercase tracking-wide">Tips Pengaturan</h3>
                            <ul class="space-y-2.5 text-xs text-slate-500 leading-relaxed">
                                <li class="flex gap-2">
                                    <span class="text-teal-500 font-bold shrink-0">→</span>
                                    <span>Atur <strong class="text-slate-700">Status</strong> ke "Draft" dahulu agar peserta belum bisa mengerjakan saat kamu masih menyusun soal.</span>
                                </li>
                                <li class="flex gap-2">
                                    <span class="text-teal-500 font-bold shrink-0">→</span>
                                    <span>Gunakan <strong class="text-slate-700">Acak Soal & Pilihan Jawaban</strong> untuk meminimalisir kecurangan.</span>
                                </li>
                                <li class="flex gap-2">
                                    <span class="text-teal-500 font-bold shrink-0">→</span>
                                    <span>Setelah pengaturan selesai, lanjut ke tab <strong class="text-slate-700">Daftar Pertanyaan</strong> untuk membuat soal.</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ═══════════════════════════════ TAB 2: PERTANYAAN ═══════════════════════════════ --}}
            <div x-show="activeTab === 'questions'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="grid grid-cols-1 lg:grid-cols-5 gap-5">

                    {{-- Sidebar Daftar Soal --}}
                    <div class="lg:col-span-2 space-y-3">
                        {{-- Toolbar Tambah --}}
                        <div class="bg-white border border-slate-200 rounded-xl p-4 space-y-3">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm font-semibold text-slate-700">Daftar Soal</span>
                                <span class="text-xs text-slate-400" x-text="questions.length + ' soal · ' + totalPoints + ' poin'"></span>
                            </div>
                            {{-- Generate AI Button --}}
                            <button type="button" @click="showAiModal = true"
                                class="w-full flex items-center justify-center gap-2 px-3 py-2.5 bg-gradient-to-r from-teal-600 to-indigo-600 hover:from-teal-700 hover:to-indigo-700 text-white rounded-lg text-xs font-semibold transition-all shadow-sm">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                Buat Soal Otomatis dengan AI
                            </button>
                            <div class="grid grid-cols-2 gap-2">
                                <button type="button" @click="addQuestion('pilihan_ganda')"
                                    class="flex items-center gap-2 px-3 py-2 bg-slate-50 hover:bg-teal-50 border border-slate-200 hover:border-teal-300 text-slate-700 hover:text-teal-700 rounded-lg text-xs font-medium transition-all">
                                    <span class="w-4 h-4 rounded-full border-2 border-current flex items-center justify-center text-[9px] font-bold">A</span>
                                    + Pilihan Ganda
                                </button>
                                <button type="button" @click="addQuestion('benar_salah')"
                                    class="flex items-center gap-2 px-3 py-2 bg-slate-50 hover:bg-teal-50 border border-slate-200 hover:border-teal-300 text-slate-700 hover:text-teal-700 rounded-lg text-xs font-medium transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    + Benar / Salah
                                </button>
                                <button type="button" @click="addQuestion('isian_singkat')"
                                    class="flex items-center gap-2 px-3 py-2 bg-slate-50 hover:bg-teal-50 border border-slate-200 hover:border-teal-300 text-slate-700 hover:text-teal-700 rounded-lg text-xs font-medium transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16"/></svg>
                                    + Isian Singkat
                                </button>
                                <button type="button" @click="addQuestion('esai')"
                                    class="flex items-center gap-2 px-3 py-2 bg-slate-50 hover:bg-teal-50 border border-slate-200 hover:border-teal-300 text-slate-700 hover:text-teal-700 rounded-lg text-xs font-medium transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    + Esai
                                </button>
                            </div>
                        </div>

                        {{-- List soal --}}
                        <div class="space-y-2 max-h-[calc(100vh-360px)] overflow-y-auto pr-1">
                            <template x-if="questions.length === 0">
                                <div class="bg-white border border-dashed border-slate-300 rounded-xl p-8 text-center">
                                    <svg class="w-8 h-8 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    <p class="text-sm text-slate-400">Belum ada soal. Tambahkan dari tombol di atas.</p>
                                </div>
                            </template>
                            <template x-for="(q, idx) in questions" :key="q._id">
                                <div @click="activeQuestion = idx"
                                    :class="activeQuestion === idx ? 'border-teal-500 bg-teal-50/50 ring-1 ring-teal-500' : 'border-slate-200 bg-white hover:border-slate-300'"
                                    class="border rounded-xl p-3 cursor-pointer transition-all">
                                    <div class="flex items-start gap-2">
                                        <span class="mt-0.5 shrink-0 w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold"
                                            :class="activeQuestion === idx ? 'bg-teal-700 text-white' : 'bg-slate-200 text-slate-600'"
                                            x-text="idx + 1"></span>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs text-slate-700 truncate" x-text="q.pertanyaan || '(Belum ada teks soal)'"></p>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="text-[10px] px-1.5 py-0.5 rounded font-medium"
                                                    :class="{
                                                        'bg-blue-100 text-blue-700': q.tipe === 'pilihan_ganda',
                                                        'bg-purple-100 text-purple-700': q.tipe === 'benar_salah',
                                                        'bg-orange-100 text-orange-700': q.tipe === 'isian_singkat',
                                                        'bg-rose-100 text-rose-700': q.tipe === 'esai',
                                                    }"
                                                    x-text="labelTipe(q.tipe)"></span>
                                                <span class="text-[10px] text-slate-400" x-text="q.bobot + ' poin'"></span>
                                                <span class="ml-auto w-2 h-2 rounded-full shrink-0"
                                                    :class="isQuestionComplete(q) ? 'bg-emerald-500' : 'bg-amber-400'"
                                                    :title="isQuestionComplete(q) ? 'Lengkap' : 'Belum lengkap'"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex gap-1 mt-2 border-t border-slate-100 pt-2">
                                        <button type="button" @click.stop="moveUp(idx)" :disabled="idx === 0" title="Naik"
                                            class="flex-1 py-1 text-slate-400 hover:text-teal-600 disabled:opacity-30 text-xs hover:bg-teal-50 rounded transition-colors">&uarr;</button>
                                        <button type="button" @click.stop="moveDown(idx)" :disabled="idx === questions.length - 1" title="Turun"
                                            class="flex-1 py-1 text-slate-400 hover:text-teal-600 disabled:opacity-30 text-xs hover:bg-teal-50 rounded transition-colors">&darr;</button>
                                        <button type="button" @click.stop="duplicateQuestion(idx)" title="Duplikat"
                                            class="flex-1 py-1 text-slate-400 hover:text-teal-600 text-xs hover:bg-teal-50 rounded transition-colors">Duplikat</button>
                                        <button type="button" @click.stop="deleteQuestion(idx)" title="Hapus"
                                            class="flex-1 py-1 text-slate-400 hover:text-red-500 text-xs hover:bg-red-50 rounded transition-colors">Hapus</button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Panel Editor Soal Aktif --}}
                    <div class="lg:col-span-3">
                        <template x-if="questions.length === 0 || activeQuestion === null">
                            <div class="bg-white border border-dashed border-slate-300 rounded-xl p-12 text-center">
                                <svg class="w-12 h-12 text-slate-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/></svg>
                                <p class="text-slate-400 text-sm">Pilih soal di panel kiri untuk mulai mengedit,<br>atau tambahkan soal baru.</p>
                            </div>
                        </template>

                        <template x-if="questions.length > 0 && activeQuestion !== null">
                            <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
                                {{-- Header editor --}}
                                <div class="flex items-center justify-between px-5 py-3 bg-slate-50 border-b border-slate-200">
                                    <span class="text-sm font-semibold text-slate-700" x-text="'Edit Soal #' + (activeQuestion + 1)"></span>
                                    <div class="flex items-center gap-3">
                                        <span class="text-xs text-slate-500" x-text="'Total Soal: ' + questions.length + ' · Total: ' + totalPoints + ' Poin'"></span>
                                        <span class="w-2.5 h-2.5 rounded-full"
                                            :class="questions[activeQuestion] && isQuestionComplete(questions[activeQuestion]) ? 'bg-emerald-500' : 'bg-amber-400'"
                                            :title="questions[activeQuestion] && isQuestionComplete(questions[activeQuestion]) ? 'Lengkap' : 'Belum lengkap'"></span>
                                    </div>
                                </div>

                                <div class="p-5 space-y-5" x-show="questions[activeQuestion]">
                                    {{-- Tipe & Bobot --}}
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-medium text-slate-600 mb-1.5">Tipe Soal</label>
                                            <select x-model="questions[activeQuestion].tipe"
                                                @change="onTipeChange(activeQuestion)"
                                                class="w-full px-3 py-2 text-slate-800 bg-white border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500">
                                                <option value="pilihan_ganda">Pilihan Ganda (Multiple Choice)</option>
                                                <option value="benar_salah">Benar / Salah (True/False)</option>
                                                <option value="isian_singkat">Isian Singkat (Short Answer)</option>
                                                <option value="esai">Esai</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-slate-600 mb-1.5">Bobot Poin Soal</label>
                                            <input type="number" x-model.number="questions[activeQuestion].bobot" min="1" max="100"
                                                class="w-full px-3 py-2 text-slate-800 bg-white border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500">
                                        </div>
                                    </div>

                                    {{-- Teks Pertanyaan --}}
                                    <div>
                                        <label class="block text-xs font-medium text-slate-600 mb-1.5">Teks Pertanyaan <span class="text-red-500">*</span></label>
                                        <textarea x-model="questions[activeQuestion].pertanyaan" rows="3"
                                            placeholder="Tuliskan pertanyaan di sini..."
                                            class="w-full px-3 py-2 text-slate-800 bg-white border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 resize-none"></textarea>
                                    </div>

                                    {{-- Opsi Jawaban: Pilihan Ganda --}}
                                    <div x-show="questions[activeQuestion].tipe === 'pilihan_ganda'">
                                        <div class="flex items-center justify-between mb-2">
                                            <label class="text-xs font-medium text-slate-600">Opsi Jawaban <span class="text-slate-400 font-normal">(klik huruf untuk tandai Kunci Jawaban Benar)</span></label>
                                        </div>
                                        <div class="space-y-2">
                                            <template x-for="(opt, oi) in (questions[activeQuestion].options || [])" :key="oi">
                                                <div class="flex items-center gap-2">
                                                    <button type="button"
                                                        @click="setCorrectOption(activeQuestion, oi)"
                                                        :class="opt.is_benar ? 'bg-emerald-600 text-white border-emerald-600 shadow-xs' : 'bg-white text-slate-500 border-slate-300 hover:border-emerald-400'"
                                                        class="shrink-0 w-8 h-8 rounded-full border-2 font-bold text-xs transition-all flex items-center justify-center"
                                                        x-text="String.fromCharCode(65 + oi)"></button>
                                                    <input type="text" x-model="opt.teks" placeholder="Teks pilihan..."
                                                        :class="opt.is_benar ? 'border-emerald-500 bg-emerald-50/40 text-slate-900' : 'border-slate-300 bg-white text-slate-800'"
                                                        class="flex-1 px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 transition-colors">
                                                    <button type="button" @click="removeOption(activeQuestion, oi)"
                                                        :disabled="(questions[activeQuestion].options || []).length <= 2"
                                                        class="shrink-0 p-1 text-slate-300 hover:text-red-500 disabled:opacity-20 transition-colors"
                                                        title="Hapus opsi">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    </button>
                                                </div>
                                            </template>
                                        </div>
                                        <button type="button" @click="addOption(activeQuestion)"
                                            :disabled="(questions[activeQuestion].options || []).length >= 6"
                                            class="mt-3 inline-flex items-center gap-1.5 text-xs text-teal-700 hover:text-teal-800 font-semibold disabled:opacity-40 disabled:cursor-not-allowed">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                            + Tambah Opsi Jawaban
                                        </button>
                                    </div>

                                    {{-- Opsi Jawaban: Benar/Salah --}}
                                    <div x-show="questions[activeQuestion].tipe === 'benar_salah'">
                                        <label class="block text-xs font-medium text-slate-600 mb-2">Pilih Kunci Jawaban Benar</label>
                                        <div class="grid grid-cols-2 gap-3">
                                            <button type="button"
                                                @click="questions[activeQuestion].benar_salah_key = 'benar'"
                                                :class="questions[activeQuestion].benar_salah_key === 'benar' ? 'bg-emerald-600 text-white border-emerald-600 ring-2 ring-emerald-200 shadow-xs' : 'bg-white text-slate-700 border-slate-300 hover:border-emerald-400'"
                                                class="py-3 rounded-xl border-2 text-sm font-semibold transition-all flex items-center justify-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                Benar
                                            </button>
                                            <button type="button"
                                                @click="questions[activeQuestion].benar_salah_key = 'salah'"
                                                :class="questions[activeQuestion].benar_salah_key === 'salah' ? 'bg-emerald-600 text-white border-emerald-600 ring-2 ring-emerald-200 shadow-xs' : 'bg-white text-slate-700 border-slate-300 hover:border-emerald-400'"
                                                class="py-3 rounded-xl border-2 text-sm font-semibold transition-all flex items-center justify-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                Salah
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Isian Singkat --}}
                                    <div x-show="questions[activeQuestion].tipe === 'isian_singkat'">
                                        <label class="block text-xs font-medium text-slate-600 mb-1.5">Kunci Jawaban yang Tepat</label>
                                        <input type="text" x-model="questions[activeQuestion].kunci_jawaban"
                                            placeholder="Tuliskan kunci jawaban yang tepat..."
                                            class="w-full px-3 py-2 text-slate-800 bg-white border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500">
                                        <p class="mt-1 text-[11px] text-slate-400">Pencocokan jawaban peserta didik bersifat case-insensitive.</p>
                                    </div>

                                    {{-- Esai --}}
                                    <div x-show="questions[activeQuestion].tipe === 'esai'">
                                        <div class="bg-slate-50 border border-slate-200 rounded-lg px-4 py-3">
                                            <p class="text-xs text-slate-700 font-medium">Soal Esai — Penilaian Manual Tutor</p>
                                            <p class="text-xs text-slate-500 mt-0.5">Jawaban esai peserta didik akan dinilai secara manual oleh tutor melalui menu Penilaian Esai.</p>
                                        </div>
                                        <div class="mt-3">
                                            <label class="block text-xs font-medium text-slate-600 mb-1.5">Rubrik / Kriteria Penilaian <span class="text-slate-400 font-normal">(Opsional)</span></label>
                                            <textarea x-model="questions[activeQuestion].rubrik" rows="2"
                                                placeholder="Kriteria penilaian dan poin penting yang diharapkan..."
                                                class="w-full px-3 py-2 text-slate-800 bg-white border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 resize-none"></textarea>
                                        </div>
                                    </div>

                                    {{-- Pembahasan --}}
                                    <div class="border-t border-slate-100 pt-4">
                                        <label class="block text-xs font-medium text-slate-600 mb-1.5">
                                            Pembahasan / Penjelasan Jawaban
                                            <span class="text-slate-400 font-normal">(Opsional — muncul jika "Tampilkan Jawaban" diaktifkan)</span>
                                        </label>
                                        <textarea x-model="questions[activeQuestion].pembahasan" rows="2"
                                            placeholder="Tuliskan pembahasan atau alasan jawaban yang benar di sini..."
                                            class="w-full px-3 py-2 text-slate-800 bg-white border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 resize-none"></textarea>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- ═══════════════════════════════ TAB 3: PRATINJAU ═══════════════════════════════ --}}
            <div x-show="activeTab === 'preview'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="max-w-3xl mx-auto space-y-4">
                    <div class="bg-white border border-slate-200 rounded-xl p-6">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold uppercase tracking-wider text-teal-700 bg-teal-50 px-2.5 py-1 rounded-md border border-teal-200">Pratinjau Kuis</span>
                            <span class="text-xs text-slate-400" x-text="questions.length + ' Soal · ' + totalPoints + ' Poin'"></span>
                        </div>
                        <h2 class="text-lg font-bold text-slate-900 mt-3" x-text="document.getElementById('judul')?.value || '(Judul kuis belum diisi)'"></h2>
                        <div class="flex flex-wrap gap-4 mt-2 text-sm text-slate-500">
                            <span x-text="'Durasi: ' + (document.getElementById('durasi_menit')?.value || '30') + ' menit'"></span>
                            <span>·</span>
                            <span x-text="'Percobaan: ' + (document.getElementById('max_attempts')?.value || '1') + 'x'"></span>
                        </div>
                        <p class="mt-3 text-sm text-slate-600 whitespace-pre-wrap" x-text="document.getElementById('deskripsi')?.value || '(Tidak ada petunjuk tambahan)'"></p>
                    </div>

                    <template x-if="questions.length === 0">
                        <div class="bg-white border border-dashed border-slate-300 rounded-xl p-10 text-center text-slate-400 text-sm">
                            Belum ada pertanyaan. Tambahkan pertanyaan pada tab "2. Daftar Pertanyaan & Kunci Jawaban".
                        </div>
                    </template>

                    <template x-for="(q, idx) in questions" :key="q._id">
                        <div class="bg-white border border-slate-200 rounded-xl p-5 space-y-3">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-start gap-3">
                                    <span class="shrink-0 w-7 h-7 rounded-full bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-700" x-text="idx + 1"></span>
                                    <p class="text-sm text-slate-800 font-medium leading-relaxed" x-text="q.pertanyaan || '(Teks pertanyaan belum diisi)'"></p>
                                </div>
                                <span class="text-xs text-slate-400 shrink-0 font-medium" x-text="q.bobot + ' Poin'"></span>
                            </div>

                            <div x-show="q.tipe === 'pilihan_ganda'" class="ml-10 space-y-2">
                                <template x-for="(opt, oi) in (q.options || [])" :key="oi">
                                    <div class="flex items-center gap-2.5 p-2 rounded-lg border"
                                        :class="opt.is_benar ? 'border-emerald-300 bg-emerald-50/50' : 'border-slate-200 bg-white'">
                                        <span class="w-6 h-6 rounded-full border-2 flex items-center justify-center text-[10px] font-bold"
                                            :class="opt.is_benar ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-slate-300 text-slate-500'"
                                            x-text="String.fromCharCode(65 + oi)"></span>
                                        <span class="text-sm flex-1" :class="opt.is_benar ? 'text-emerald-800 font-semibold' : 'text-slate-700'" x-text="opt.teks || '(Opsi kosong)'"></span>
                                        <span x-show="opt.is_benar" class="text-xs text-emerald-700 font-semibold bg-emerald-100 px-2 py-0.5 rounded">Kunci Benar</span>
                                    </div>
                                </template>
                            </div>

                            <div x-show="q.tipe === 'benar_salah'" class="ml-10">
                                <div class="flex gap-3">
                                    <span class="px-4 py-2 rounded-lg text-sm font-semibold border flex items-center gap-2"
                                        :class="q.benar_salah_key === 'benar' ? 'bg-emerald-600 text-white border-emerald-600' : 'border-slate-300 text-slate-600 bg-white'">
                                        ✓ Benar <span x-show="q.benar_salah_key === 'benar'" class="text-xs font-normal">(Kunci)</span>
                                    </span>
                                    <span class="px-4 py-2 rounded-lg text-sm font-semibold border flex items-center gap-2"
                                        :class="q.benar_salah_key === 'salah' ? 'bg-emerald-600 text-white border-emerald-600' : 'border-slate-300 text-slate-600 bg-white'">
                                        ✗ Salah <span x-show="q.benar_salah_key === 'salah'" class="text-xs font-normal">(Kunci)</span>
                                    </span>
                                </div>
                            </div>

                            <div x-show="q.tipe === 'isian_singkat'" class="ml-10">
                                <div class="bg-slate-50 border border-slate-200 rounded-lg p-3 text-sm">
                                    <span class="text-xs text-slate-500 block">Kunci Jawaban Tepat:</span>
                                    <span class="font-semibold text-emerald-700" x-text="q.kunci_jawaban || '(Belum ditentukan)'"></span>
                                </div>
                            </div>

                            <div x-show="q.tipe === 'esai'" class="ml-10">
                                <div class="bg-slate-50 border border-slate-200 rounded-lg p-3 text-sm">
                                    <span class="text-xs text-slate-500 block">Jenis Soal: Esai (Uraian Bebas)</span>
                                    <p x-show="q.rubrik" class="text-xs text-slate-600 mt-1" x-text="'Rubrik: ' + q.rubrik"></p>
                                </div>
                            </div>

                            <div x-show="q.pembahasan" class="ml-10 bg-teal-50/50 border border-teal-200 rounded-lg p-3 text-xs text-teal-800">
                                <span class="font-semibold block mb-0.5">Pembahasan:</span>
                                <span x-text="q.pembahasan"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

        </form>

        {{-- ══════════════════ STICKY FOOTER BAR ══════════════════ --}}
        <div class="fixed bottom-0 left-0 right-0 z-40 bg-white border-t border-slate-200 px-6 py-3.5 shadow-xs">
            <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
                <div class="text-sm text-slate-600 flex items-center gap-2">
                    <span class="font-semibold text-slate-900" x-text="questions.length + ' Soal'"></span>
                    <span class="text-slate-300">•</span>
                    <span class="font-medium text-slate-700" x-text="totalPoints + ' Poin Total'"></span>
                    <span class="text-slate-300">•</span>
                    <span :class="questions.length > 0 && completeCount === questions.length ? 'text-emerald-700 font-medium' : 'text-amber-600'"
                        x-text="completeCount + '/' + questions.length + ' Soal Siap'"></span>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('quiz.index') }}"
                        class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition-colors">
                        Batal
                    </a>
                    <button type="button" @click="submitForm"
                        class="inline-flex items-center gap-2 px-5 py-2 text-sm font-semibold text-white bg-teal-700 hover:bg-teal-800 border border-teal-700 rounded-xl shadow-xs transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Simpan Quiz
                    </button>
                </div>
            </div>
        </div>
        {{-- Modal AI Generator --}}
        <div x-show="showAiModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
            style="display: none;"
            @keydown.escape.window="if (!aiLoading) showAiModal = false">

            <div class="bg-white rounded-2xl max-w-xl w-full border border-slate-200 shadow-xl overflow-hidden"
                @click.outside="if (!aiLoading) showAiModal = false">

                {{-- Modal Header --}}
                <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-teal-50 to-indigo-50 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-teal-600 text-white shadow-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </span>
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900 leading-tight">Buat Soal Otomatis dengan AI</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Generate soal berkualitas dalam hitungan detik</p>
                        </div>
                    </div>
                    <button type="button" @click="if (!aiLoading) showAiModal = false" :disabled="aiLoading"
                        class="text-slate-400 hover:text-slate-600 transition-colors p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="px-6 py-5 space-y-4">
                    {{-- Alert Error --}}
                    <div x-show="aiError" class="p-3 bg-red-50 border border-red-200 rounded-xl flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="text-xs text-red-600 leading-relaxed" x-text="aiError"></p>
                    </div>

                    {{-- Topik / Materi Sumber --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wide mb-1.5">
                            Topik atau Materi Sumber <span class="text-red-500">*</span>
                        </label>
                        <textarea x-model="aiForm.topic" rows="5" :disabled="aiLoading"
                            placeholder="Tempel ringkasan materi, paragraf buku, bab tertentu, atau tulis topik mendalam di sini...&#10;&#10;Contoh: Sistem peredaran darah manusia terdiri atas jantung, pembuluh darah, dan darah. Jantung memiliki 4 ruang..."
                            class="w-full px-3.5 py-2.5 text-slate-900 bg-white border border-slate-300 rounded-xl text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-600 resize-none disabled:bg-slate-50 disabled:text-slate-400"></textarea>
                        <p class="mt-1 text-[11px] text-slate-400">Minimal 10 karakter. Semakin detail materi yang kamu masukkan, semakin akurat soal yang dihasilkan.</p>
                    </div>

                    {{-- Baris Pengaturan: Jumlah, Tipe, Kesulitan --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wide mb-1.5">Jumlah Soal</label>
                            <select x-model.number="aiForm.count" :disabled="aiLoading"
                                class="w-full px-3 py-2 text-slate-900 bg-white border border-slate-300 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-600 disabled:bg-slate-50">
                                <option :value="5">5 Soal</option>
                                <option :value="10">10 Soal</option>
                                <option :value="15">15 Soal</option>
                                <option :value="20">20 Soal</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wide mb-1.5">Tipe Soal</label>
                            <select x-model="aiForm.type" :disabled="aiLoading"
                                class="w-full px-3 py-2 text-slate-900 bg-white border border-slate-300 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-600 disabled:bg-slate-50">
                                <option value="multiple_choice">Pilihan Ganda</option>
                                <option value="true_false">Benar / Salah</option>
                                <option value="essay">Esai</option>
                                <option value="mixed">Campuran (PG, B/S, Esai)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wide mb-1.5">Kesulitan</label>
                            <select x-model="aiForm.difficulty" :disabled="aiLoading"
                                class="w-full px-3 py-2 text-slate-900 bg-white border border-slate-300 rounded-xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-teal-500/20 focus:border-teal-600 disabled:bg-slate-50">
                                <option value="easy">Mudah</option>
                                <option value="medium">Sedang</option>
                                <option value="hard">Sulit / HOTS</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-xs text-slate-400">Soal yang di-generate dapat diedit setelah dimasukkan.</span>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="showAiModal = false" :disabled="aiLoading"
                            class="px-3.5 py-2 text-xs font-medium text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition-colors disabled:opacity-50">
                            Batal
                        </button>
                        <button type="button" @click="generateViaAi" :disabled="aiLoading || !aiForm.topic.trim()"
                            class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-white bg-gradient-to-r from-teal-600 to-indigo-600 hover:from-teal-700 hover:to-indigo-700 rounded-xl transition-all shadow-xs disabled:opacity-50 disabled:cursor-not-allowed">
                            <template x-if="!aiLoading">
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    Buat Soal
                                </span>
                            </template>
                            <template x-if="aiLoading">
                                <span class="flex items-center gap-1.5">
                                    <svg class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                                    Membuat Soal...
                                </span>
                            </template>
                        </button>
                    </div>
                </div>

            </div>
        </div>
        <div class="h-20"></div>
    </div>

    <script>
    function quizBuilder() {
        return {
            activeTab: 'settings',
            activeQuestion: 0,
            statusPublish: '1',
            showAiModal: false,
            aiLoading: false,
            aiError: '',
            aiForm: {
                topic: '',
                count: 5,
                type: 'multiple_choice',
                difficulty: 'medium',
            },
            questions: [
                {
                    _id: 'q_' + Date.now(),
                    tipe: 'pilihan_ganda',
                    pertanyaan: '',
                    bobot: 10,
                    pembahasan: '',
                    options: [
                        { teks: '', is_benar: true },
                        { teks: '', is_benar: false },
                        { teks: '', is_benar: false },
                        { teks: '', is_benar: false }
                    ],
                    benar_salah_key: 'benar',
                    kunci_jawaban: '',
                    rubrik: ''
                }
            ],

            get totalPoints() {
                return this.questions.reduce((sum, q) => sum + (parseInt(q.bobot) || 0), 0);
            },
            get completeCount() {
                return this.questions.filter(q => this.isQuestionComplete(q)).length;
            },

            init() {
                if (this.questions.length > 0) {
                    this.activeQuestion = 0;
                }
            },

            addQuestion(tipe = 'pilihan_ganda') {
                const id = 'q_' + Date.now() + Math.random().toString(36).substr(2, 4);
                const q = {
                    _id: id,
                    tipe: tipe,
                    pertanyaan: '',
                    bobot: 10,
                    pembahasan: '',
                    options: tipe === 'pilihan_ganda' ? [
                        { teks: '', is_benar: true },
                        { teks: '', is_benar: false },
                        { teks: '', is_benar: false },
                        { teks: '', is_benar: false }
                    ] : [],
                    benar_salah_key: tipe === 'benar_salah' ? 'benar' : null,
                    kunci_jawaban: '',
                    rubrik: ''
                };
                this.questions.push(q);
                this.activeQuestion = this.questions.length - 1;
                this.activeTab = 'questions';
            },

            deleteQuestion(idx) {
                if (!confirm('Hapus soal #' + (idx + 1) + '?')) return;
                this.questions.splice(idx, 1);
                if (this.questions.length === 0) {
                    this.activeQuestion = null;
                } else if (this.activeQuestion >= this.questions.length) {
                    this.activeQuestion = this.questions.length - 1;
                }
            },

            duplicateQuestion(idx) {
                const original = this.questions[idx];
                const clone = JSON.parse(JSON.stringify(original));
                clone._id = 'q_' + Date.now() + Math.random().toString(36).substr(2, 4);
                this.questions.splice(idx + 1, 0, clone);
                this.activeQuestion = idx + 1;
            },

            moveUp(idx) {
                if (idx <= 0) return;
                const temp = this.questions[idx];
                this.questions.splice(idx, 1);
                this.questions.splice(idx - 1, 0, temp);
                this.activeQuestion = idx - 1;
            },

            moveDown(idx) {
                if (idx >= this.questions.length - 1) return;
                const temp = this.questions[idx];
                this.questions.splice(idx, 1);
                this.questions.splice(idx + 1, 0, temp);
                this.activeQuestion = idx + 1;
            },

            onTipeChange(idx) {
                const q = this.questions[idx];
                if (q.tipe === 'pilihan_ganda') {
                    if (!q.options || q.options.length < 2) {
                        q.options = [
                            { teks: '', is_benar: true },
                            { teks: '', is_benar: false },
                            { teks: '', is_benar: false },
                            { teks: '', is_benar: false }
                        ];
                    }
                } else if (q.tipe === 'benar_salah') {
                    q.benar_salah_key = q.benar_salah_key || 'benar';
                }
            },

            setCorrectOption(qIdx, optIdx) {
                const q = this.questions[qIdx];
                if (!q.options) return;
                q.options.forEach((opt, i) => {
                    opt.is_benar = (i === optIdx);
                });
            },

            addOption(qIdx) {
                const q = this.questions[qIdx];
                if (!q.options) q.options = [];
                if (q.options.length < 6) {
                    q.options.push({ teks: '', is_benar: false });
                }
            },

            removeOption(qIdx, optIdx) {
                const q = this.questions[qIdx];
                if (!q.options || q.options.length <= 2) return;
                const wasCorrect = q.options[optIdx].is_benar;
                q.options.splice(optIdx, 1);
                if (wasCorrect && q.options.length > 0) {
                    q.options[0].is_benar = true;
                }
            },

            isQuestionComplete(q) {
                if (!q || !q.pertanyaan || q.pertanyaan.trim() === '') return false;
                if (q.tipe === 'pilihan_ganda') {
                    if (!q.options || q.options.length < 2) return false;
                    const hasEmpty = q.options.some(o => !o.teks || o.teks.trim() === '');
                    const hasCorrect = q.options.some(o => o.is_benar);
                    return !hasEmpty && hasCorrect;
                }
                if (q.tipe === 'benar_salah') {
                    return q.benar_salah_key === 'benar' || q.benar_salah_key === 'salah';
                }
                if (q.tipe === 'isian_singkat') {
                    return Boolean(q.kunci_jawaban && q.kunci_jawaban.trim() !== '');
                }
                if (q.tipe === 'esai') {
                    return true;
                }
                return false;
            },

            labelTipe(tipe) {
                const labels = {
                    pilihan_ganda: 'Pilihan Ganda',
                    benar_salah: 'Benar / Salah',
                    isian_singkat: 'Isian Singkat',
                    esai: 'Esai'
                };
                return labels[tipe] || tipe;
            },

            submitForm() {
                const form = document.getElementById('quizForm');
                const titleInput = document.getElementById('judul');
                if (!titleInput.value.trim()) {
                    this.activeTab = 'settings';
                    titleInput.focus();
                    alert('Mohon isi Judul Kuis terlebih dahulu.');
                    return;
                }
                const hiddenInput = form.querySelector('[name="questions_data"]');
                if (hiddenInput) {
                    hiddenInput.value = JSON.stringify(this.questions);
                }
                form.submit();
            },

            async generateViaAi() {
                if (!this.aiForm.topic.trim() || this.aiForm.topic.trim().length < 10) {
                    this.aiError = 'Topik / materi terlalu pendek. Isi minimal 10 karakter.';
                    return;
                }
                this.aiLoading = true;
                this.aiError = '';

                try {
                    const res = await fetch('{{ route('quiz.ai.generate') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            topic:      this.aiForm.topic,
                            count:      Number(this.aiForm.count),
                            type:       this.aiForm.type,
                            difficulty: this.aiForm.difficulty,
                        }),
                    });

                    const data = await res.json();

                    if (!res.ok || !data.success) {
                        this.aiError = data.message || 'Terjadi kesalahan saat menghubungi AI. Coba lagi.';
                        return;
                    }

                    this.importAiQuestions(data.questions);
                    this.showAiModal = false;
                    this.aiForm.topic = '';
                    this.activeTab = 'questions';
                } catch (err) {
                    this.aiError = 'Gagal terhubung ke server. Periksa koneksi internet Anda.';
                } finally {
                    this.aiLoading = false;
                }
            },

            importAiQuestions(aiQuestions) {
                const typeMap = {
                    multiple_choice: 'pilihan_ganda',
                    true_false:      'benar_salah',
                    short_answer:    'isian_singkat',
                    essay:           'esai',
                };

                for (const q of aiQuestions) {
                    const tipe = typeMap[q.type] || 'pilihan_ganda';
                    const base = {
                        _id:           'q_ai_' + Date.now() + '_' + Math.random().toString(36).slice(2, 7),
                        tipe:          tipe,
                        pertanyaan:    q.text || '',
                        bobot:         q.points || 10,
                        pembahasan:    q.explanation || '',
                        opsi:          [],
                        kunci_jawaban: '',
                        benar_salah_key: null,
                    };

                    if (tipe === 'pilihan_ganda') {
                        const huruf = ['A', 'B', 'C', 'D', 'E'];
                        base.opsi = (q.options || ['', '', '', '']).map((txt, i) => ({
                            _id:    'o_' + Date.now() + '_' + i,
                            huruf:  huruf[i] || String.fromCharCode(65 + i),
                            teks:   txt,
                            benar:  i === q.correctIndex,
                        }));
                    } else if (tipe === 'benar_salah') {
                        const ci = q.correctIndex; // 0=Benar, 1=Salah
                        base.benar_salah_key = ci === 0 ? 'benar' : 'salah';
                    } else if (tipe === 'isian_singkat') {
                        base.kunci_jawaban = q.correctAnswer || '';
                    } else if (tipe === 'esai') {
                        base.rubrik = q.rubric || q.correctAnswer || '';
                    }

                    this.questions.push(base);
                }

                this.activeQuestion = Math.max(0, this.questions.length - aiQuestions.length);
            }
        };
    }
    </script>
</x-app-layout>
