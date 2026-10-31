<x-app-layout>
    <x-slot name="title">Buku Presensi Peserta Didik</x-slot>

    <div class="max-w-7xl mx-auto space-y-5"
         x-data="{
            activeTab: '{{ request('tab', request()->has('bulan') ? 'matrix' : 'daily') }}',
            students: {{ Js::from($students->map(function($s, $idx) use ($existingAttendances) {
                $existing = $existingAttendances->get($s->id);
                $st = ($existing instanceof \App\Models\Attendance) ? $existing->status : 'hadir';
                if ($st === 'alpa') { $st = 'alfa'; }
                return [
                    'index' => $idx,
                    'id' => $s->id,
                    'status' => $st,
                    'keterangan' => ($existing instanceof \App\Models\Attendance) ? ($existing->keterangan ?? '') : ''
                ];
            })) }},
            get countTotal() {
                return this.students.length;
            },
            get countHadir() {
                return this.students.filter(s => s.status === 'hadir').length;
            },
            get countSakit() {
                return this.students.filter(s => s.status === 'sakit').length;
            },
            get countIzin() {
                return this.students.filter(s => s.status === 'izin').length;
            },
            get countAlfa() {
                return this.students.filter(s => s.status === 'alfa' || s.status === 'alpa').length;
            },
            markAllHadir() {
                this.students.forEach(s => s.status = 'hadir');
            }
         }">

        <!-- Header & Breadcrumb -->
        <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between pb-1 border-b border-slate-200">
            <div>
                <x-breadcrumb :items="[['label' => 'Akademik'], ['label' => 'Presensi Peserta Didik']]" />
                <div class="flex items-center gap-2 mt-1">
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">Buku Presensi Kelas</h1>
                    @if($selectedClassroom)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                            {{ $selectedClassroom->nama_rombel }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <!-- Tab Switcher -->
                <div class="inline-flex p-1 bg-slate-100 rounded-lg border border-slate-200 text-xs font-medium">
                    <button type="button"
                            @click="activeTab = 'daily'"
                            :class="activeTab === 'daily' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                            class="px-3.5 py-1.5 rounded-md transition-colors flex items-center gap-1.5 font-semibold whitespace-nowrap shrink-0">
                        <span>Input Presensi Harian</span>
                    </button>
                    <button type="button"
                            @click="activeTab = 'matrix'"
                            :class="activeTab === 'matrix' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                            class="px-3.5 py-1.5 rounded-md transition-colors flex items-center gap-1.5 font-semibold whitespace-nowrap shrink-0">
                        <span>Buku Rekap Bulanan (H/S/I/A)</span>
                    </button>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="p-3.5 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200 text-sm flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-emerald-200 text-emerald-800 text-xs font-bold">✓</span>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="p-3.5 rounded-lg bg-rose-50 text-rose-800 border border-rose-200 text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- BAR KONTEKS KELAS (3 Input Utama) -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-xs">
            <form method="GET" action="{{ route('absensi.index') }}" id="contextForm" class="grid grid-cols-1 md:grid-cols-12 gap-3.5 items-end">
                <!-- Rombel -->
                <div class="md:col-span-4">
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Kelas / Rombel
                    </label>
                    <select name="classroom_id"
                            onchange="document.getElementById('contextForm').submit()"
                            class="w-full text-sm font-medium border border-slate-300 rounded-lg bg-slate-50 focus:bg-white focus:border-teal-600 focus:ring-1 focus:ring-teal-600 px-3 py-2 text-slate-900">
                        <option value="">-- Pilih Rombel --</option>
                        @foreach($classrooms as $cls)
                            <option value="{{ $cls->id }}" {{ (string)$selectedClassroomId === (string)$cls->id ? 'selected' : '' }}>
                                {{ $cls->nama_rombel }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Mata Pelajaran -->
                <div class="md:col-span-4">
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Mata Pelajaran
                    </label>
                    <select name="subject_id"
                            onchange="document.getElementById('contextForm').submit()"
                            class="w-full text-sm font-medium border border-slate-300 rounded-lg bg-slate-50 focus:bg-white focus:border-teal-600 focus:ring-1 focus:ring-teal-600 px-3 py-2 text-slate-900">
                        <option value="">-- Pilih Mata Pelajaran --</option>
                        @foreach($subjects as $sub)
                            <option value="{{ $sub->id }}" {{ (string)$selectedSubjectId === (string)$sub->id ? 'selected' : '' }}>
                                {{ $sub->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Tanggal Pertemuan -->
                <div class="md:col-span-3">
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Tanggal Pertemuan
                    </label>
                    <input type="date"
                           name="tanggal"
                           value="{{ $selectedTanggal }}"
                           onchange="document.getElementById('contextForm').submit()"
                           class="w-full text-sm font-medium border border-slate-300 rounded-lg bg-slate-50 focus:bg-white focus:border-teal-600 focus:ring-1 focus:ring-teal-600 px-3 py-2 text-slate-900" />
                </div>

                <!-- Submit / Refresh -->
                <div class="md:col-span-1">
                    <button type="submit"
                            title="Segarkan Data"
                            class="w-full py-2 px-3 border border-slate-300 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold transition-colors flex items-center justify-center">
                        Terapkan
                    </button>
                </div>
            </form>
        </div>

        <!-- 5 KARTU COUNTER OTOMATIS -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
            <!-- Total Siswa -->
            <div class="bg-white border border-slate-200 rounded-xl p-3.5 shadow-xs">
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Siswa</div>
                <div class="mt-1 flex items-baseline justify-between">
                    <span class="text-2xl font-bold text-slate-900 tabular-nums" x-text="countTotal">{{ $summary['total'] }}</span>
                    <span class="text-xs font-medium text-slate-500">Siswa Aktif</span>
                </div>
            </div>

            <!-- Hadir (H) -->
            <div class="bg-emerald-50/60 border border-emerald-200 rounded-xl p-3.5 shadow-xs">
                <div class="text-xs font-semibold text-emerald-800 uppercase tracking-wider flex items-center justify-between">
                    <span>[H] Hadir</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                </div>
                <div class="mt-1 flex items-baseline justify-between">
                    <span class="text-2xl font-bold text-emerald-700 tabular-nums" x-text="countHadir">{{ $summary['hadir'] }}</span>
                    <span class="text-xs font-medium text-emerald-700/80">Siswa</span>
                </div>
            </div>

            <!-- Sakit (S) -->
            <div class="bg-amber-50/60 border border-amber-200 rounded-xl p-3.5 shadow-xs">
                <div class="text-xs font-semibold text-amber-800 uppercase tracking-wider flex items-center justify-between">
                    <span>[S] Sakit</span>
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                </div>
                <div class="mt-1 flex items-baseline justify-between">
                    <span class="text-2xl font-bold text-amber-700 tabular-nums" x-text="countSakit">{{ $summary['sakit'] }}</span>
                    <span class="text-xs font-medium text-amber-700/80">Siswa</span>
                </div>
            </div>

            <!-- Izin (I) -->
            <div class="bg-sky-50/60 border border-sky-200 rounded-xl p-3.5 shadow-xs">
                <div class="text-xs font-semibold text-sky-800 uppercase tracking-wider flex items-center justify-between">
                    <span>[I] Izin</span>
                    <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                </div>
                <div class="mt-1 flex items-baseline justify-between">
                    <span class="text-2xl font-bold text-sky-700 tabular-nums" x-text="countIzin">{{ $summary['izin'] }}</span>
                    <span class="text-xs font-medium text-sky-700/80">Siswa</span>
                </div>
            </div>

            <!-- Alfa (A) -->
            <div class="bg-rose-50/60 border border-rose-200 rounded-xl p-3.5 shadow-xs col-span-2 sm:col-span-1">
                <div class="text-xs font-semibold text-rose-800 uppercase tracking-wider flex items-center justify-between">
                    <span>[A] Alfa</span>
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                </div>
                <div class="mt-1 flex items-baseline justify-between">
                    <span class="text-2xl font-bold text-rose-700 tabular-nums" x-text="countAlfa">{{ $summary['alpa'] }}</span>
                    <span class="text-xs font-medium text-rose-700/80">Tanpa Ket.</span>
                </div>
            </div>
        </div>

        <!-- TAB 1: INPUT PRESENSI HARIAN -->
        <div x-show="activeTab === 'daily'" class="space-y-4">
            <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
                <!-- Bar Tindakan Cepat Atas Tabel -->
                <div class="p-4 border-b border-slate-200 bg-slate-50/60 flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">
                            Daftar Kehadiran Siswa
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Pertemuan: <span class="font-semibold text-slate-800">{{ \Carbon\Carbon::parse($selectedTanggal)->locale('id')->isoFormat('dddd, D MMMM Y') }}</span>
                            @if($selectedSchedule)
                                &bull; Jadwal: <span class="font-medium text-slate-700">{{ $selectedSchedule->jam_mulai }} - {{ $selectedSchedule->jam_selesai }}</span>
                            @endif
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        @can('attendance.manage')
                            @if($students->isNotEmpty())
                                <button type="button"
                                        @click="markAllHadir()"
                                        class="inline-flex items-center gap-1.5 px-3 py-2 text-xs sm:text-sm font-semibold text-emerald-800 bg-emerald-100 hover:bg-emerald-200 border border-emerald-300 rounded-xl transition-colors whitespace-nowrap shrink-0">
                                    <span class="font-bold">✓</span>
                                    <span>Tandai Semua Hadir (H)</span>
                                </button>
                            @endif
                        @endcan
                    </div>
                </div>

                @if(!$selectedClassroomId)
                    <div class="p-12 text-center text-slate-500 text-sm">
                        Silakan pilih <strong>Kelas / Rombel</strong> pada bar konteks di atas untuk memuat daftar peserta didik.
                    </div>
                @elseif($students->isEmpty())
                    <div class="p-12 text-center text-slate-500 text-sm">
                        Tidak ada data peserta didik aktif di rombel ini.
                    </div>
                @else
                    <form method="POST" action="{{ route('absensi.store') }}">
                        @csrf
                        @if($selectedSchedule)
                            <input type="hidden" name="schedule_id" value="{{ $selectedSchedule->id }}">
                        @endif
                        <input type="hidden" name="classroom_id" value="{{ $selectedClassroomId }}">
                        <input type="hidden" name="subject_id" value="{{ $selectedSubjectId }}">
                        <input type="hidden" name="tanggal" value="{{ $selectedTanggal }}">

                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                                <thead>
                                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold uppercase text-2xs tracking-wider">
                                        <th class="py-2.5 px-3 w-12 text-center">No</th>
                                        <th class="py-2.5 px-3 w-28">NISN</th>
                                        <th class="py-2.5 px-4 min-w-[200px]">Nama Lengkap Siswa</th>
                                        <th class="py-2.5 px-3 text-center min-w-[240px]">Status Presensi</th>
                                        <th class="py-2.5 px-4 min-w-[220px]">Keterangan / Catatan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 text-slate-800">
                                    @foreach($students as $idx => $student)
                                        <tr class="hover:bg-slate-50/70 transition-colors">
                                            <td class="py-2.5 px-3 text-center font-medium text-slate-500 tabular-nums">
                                                {{ $loop->iteration }}
                                            </td>
                                            <td class="py-2.5 px-3 font-mono text-xs text-slate-600">
                                                {{ $student->nisn ?? '-' }}
                                            </td>
                                            <td class="py-2.5 px-4">
                                                <div class="font-semibold text-slate-900">{{ $student->nama_lengkap }}</div>
                                                <div class="text-2xs text-slate-500">ID: #{{ $student->id }}</div>
                                            </td>

                                            <!-- Tombol Pill Radio H S I A -->
                                            <td class="py-2.5 px-3 text-center">
                                                <input type="hidden"
                                                       name="attendances[{{ $idx }}][student_id]"
                                                       value="{{ $student->id }}">

                                                <div class="inline-flex items-center gap-1.5 bg-slate-100/90 p-1 rounded-lg border border-slate-200">
                                                    <!-- [H] Hadir -->
                                                    <label class="cursor-pointer select-none">
                                                        <input type="radio"
                                                               name="attendances[{{ $idx }}][status]"
                                                               value="hadir"
                                                               x-model="students[{{ $idx }}].status"
                                                               class="sr-only">
                                                        <span :class="students[{{ $idx }}].status === 'hadir'
                                                                    ? 'bg-emerald-600 text-white font-bold ring-2 ring-emerald-500/30'
                                                                    : 'bg-white text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 border border-slate-200'"
                                                              class="inline-flex items-center justify-center w-8 h-8 rounded-md text-xs font-semibold transition-all">
                                                            H
                                                        </span>
                                                    </label>

                                                    <!-- [S] Sakit -->
                                                    <label class="cursor-pointer select-none">
                                                        <input type="radio"
                                                               name="attendances[{{ $idx }}][status]"
                                                               value="sakit"
                                                               x-model="students[{{ $idx }}].status"
                                                               class="sr-only">
                                                        <span :class="students[{{ $idx }}].status === 'sakit'
                                                                    ? 'bg-amber-500 text-white font-bold ring-2 ring-amber-400/30'
                                                                    : 'bg-white text-slate-600 hover:bg-amber-50 hover:text-amber-700 border border-slate-200'"
                                                              class="inline-flex items-center justify-center w-8 h-8 rounded-md text-xs font-semibold transition-all">
                                                            S
                                                        </span>
                                                    </label>

                                                    <!-- [I] Izin -->
                                                    <label class="cursor-pointer select-none">
                                                        <input type="radio"
                                                               name="attendances[{{ $idx }}][status]"
                                                               value="izin"
                                                               x-model="students[{{ $idx }}].status"
                                                               class="sr-only">
                                                        <span :class="students[{{ $idx }}].status === 'izin'
                                                                    ? 'bg-sky-600 text-white font-bold ring-2 ring-sky-500/30'
                                                                    : 'bg-white text-slate-600 hover:bg-sky-50 hover:text-sky-700 border border-slate-200'"
                                                              class="inline-flex items-center justify-center w-8 h-8 rounded-md text-xs font-semibold transition-all">
                                                            I
                                                        </span>
                                                    </label>

                                                    <!-- [A] Alfa -->
                                                    <label class="cursor-pointer select-none">
                                                        <input type="radio"
                                                               name="attendances[{{ $idx }}][status]"
                                                               value="alfa"
                                                               x-model="students[{{ $idx }}].status"
                                                               class="sr-only">
                                                        <span :class="(students[{{ $idx }}].status === 'alfa' || students[{{ $idx }}].status === 'alpa')
                                                                    ? 'bg-rose-600 text-white font-bold ring-2 ring-rose-500/30'
                                                                    : 'bg-white text-slate-600 hover:bg-rose-50 hover:text-rose-700 border border-slate-200'"
                                                              class="inline-flex items-center justify-center w-8 h-8 rounded-md text-xs font-semibold transition-all">
                                                            A
                                                        </span>
                                                    </label>
                                                </div>
                                            </td>

                                            <!-- Kolom Keterangan / Catatan -->
                                            <td class="py-2.5 px-4">
                                                <input type="text"
                                                       name="attendances[{{ $idx }}][keterangan]"
                                                       x-model="students[{{ $idx }}].keterangan"
                                                       placeholder="Alasan izin, sakit, atau catatan..."
                                                       class="w-full text-xs border border-slate-200 rounded-md px-2.5 py-1.5 focus:border-teal-600 focus:ring-1 focus:ring-teal-600 text-slate-800 placeholder-slate-400 bg-white" />
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Bar Tombol Simpan Bawah -->
                        <div class="p-4 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3">
                            <div class="text-xs text-slate-600">
                                Pastikan seluruh baris telah terisi sesuai kehadiran fisik sebelum menyimpan.
                            </div>

                            @can('attendance.manage')
                                <button type="submit"
                                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-[#0f766e] hover:bg-[#115e59] active:bg-[#134e4a] rounded-lg shadow-xs transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>Simpan Absensi Kelas</span>
                                </button>
                            @else
                                <span class="text-xs font-medium text-slate-500 italic">
                                    Mode lihat saja (Hanya Tutor/Operator yang dapat menyimpan absensi)
                                </span>
                            @endcan
                        </div>
                    </form>
                @endif
            </div>
        </div>

        <!-- TAB 2: BUKU REKAP BULANAN (MATRIKS SEKOLAH) -->
        <div x-show="activeTab === 'matrix'" class="space-y-4" style="display: none;">
            <div class="bg-white border border-slate-200 rounded-xl shadow-xs p-4">
                <form method="GET" action="{{ route('absensi.index') }}" class="flex flex-col sm:flex-row items-end gap-3">
                    <input type="hidden" name="classroom_id" value="{{ $selectedClassroomId }}">
                    <input type="hidden" name="subject_id" value="{{ $selectedSubjectId }}">
                    <input type="hidden" name="tanggal" value="{{ $selectedTanggal }}">

                    <div class="w-full sm:w-64">
                        <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            Pilih Bulan Rekap
                        </label>
                        <input type="month"
                               name="bulan"
                               value="{{ $selectedBulan }}"
                               class="w-full text-sm font-medium border border-slate-300 rounded-lg bg-slate-50 focus:bg-white focus:border-teal-600 focus:ring-1 focus:ring-teal-600 px-3 py-2 text-slate-900" />
                    </div>

                    <button type="submit"
                            class="px-4 py-2 border border-slate-300 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold transition-colors">
                        Tampilkan Matriks Rekap
                    </button>
                </form>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl shadow-xs overflow-hidden">
                <div class="p-4 border-b border-slate-200 bg-slate-50/60 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">
                            Matriks Rekap Absensi Bulanan
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Bulan: <span class="font-semibold text-slate-800">{{ \Carbon\Carbon::parse($selectedBulan . '-01')->locale('id')->isoFormat('MMMM Y') }}</span>
                            @if(count($matrixData['dates']) > 0)
                                &bull; Total: <span class="font-semibold text-slate-800">{{ count($matrixData['dates']) }} Pertemuan</span>
                            @endif
                        </p>
                    </div>

                    <!-- Legenda Singkat -->
                    <div class="hidden md:flex items-center gap-3 text-2xs font-semibold">
                        <span class="inline-flex items-center gap-1 text-emerald-700">
                            <span class="w-4 h-4 rounded bg-emerald-100 border border-emerald-300 inline-flex items-center justify-center font-bold">H</span> Hadir
                        </span>
                        <span class="inline-flex items-center gap-1 text-amber-700">
                            <span class="w-4 h-4 rounded bg-amber-100 border border-amber-300 inline-flex items-center justify-center font-bold">S</span> Sakit
                        </span>
                        <span class="inline-flex items-center gap-1 text-sky-700">
                            <span class="w-4 h-4 rounded bg-sky-100 border border-sky-300 inline-flex items-center justify-center font-bold">I</span> Izin
                        </span>
                        <span class="inline-flex items-center gap-1 text-rose-700">
                            <span class="w-4 h-4 rounded bg-rose-100 border border-rose-300 inline-flex items-center justify-center font-bold">A</span> Alfa
                        </span>
                    </div>
                </div>

                @if(empty($matrixData['students']))
                    <div class="p-12 text-center text-slate-500 text-sm">
                        Belum ada rekaman presensi pada bulan ini untuk rombel dan mata pelajaran yang dipilih.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-semibold uppercase text-2xs tracking-wider">
                                    <th class="py-2.5 px-3 w-10 text-center sticky left-0 bg-slate-50 z-10 border-r border-slate-200">No</th>
                                    <th class="py-2.5 px-3 w-24">NISN</th>
                                    <th class="py-2.5 px-3 min-w-[180px] sticky left-10 bg-slate-50 z-10 border-r border-slate-200">Nama Siswa</th>

                                    <!-- Kolom Pertemuan P1..Pn -->
                                    @forelse($matrixData['dates'] as $idx => $dateStr)
                                        <th class="py-2 px-2 text-center border-r border-slate-200 min-w-[44px]">
                                            <div class="font-bold text-slate-800">P{{ $idx + 1 }}</div>
                                            <div class="text-3xs text-slate-500 font-normal">{{ \Carbon\Carbon::parse($dateStr)->format('d/m') }}</div>
                                        </th>
                                    @empty
                                        <th class="py-2.5 px-3 text-center text-slate-500">Pertemuan</th>
                                    @endforelse

                                    <!-- Kolom Total -->
                                    <th class="py-2.5 px-2 text-center bg-emerald-50/70 text-emerald-800 font-bold border-r border-slate-200 w-10">H</th>
                                    <th class="py-2.5 px-2 text-center bg-amber-50/70 text-amber-800 font-bold border-r border-slate-200 w-10">S</th>
                                    <th class="py-2.5 px-2 text-center bg-sky-50/70 text-sky-800 font-bold border-r border-slate-200 w-10">I</th>
                                    <th class="py-2.5 px-2 text-center bg-rose-50/70 text-rose-800 font-bold border-r border-slate-200 w-10">A</th>
                                    <th class="py-2.5 px-3 text-center bg-slate-100 text-slate-800 font-bold w-16">% Hadir</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 text-slate-800">
                                @foreach($matrixData['students'] as $row)
                                    <tr class="hover:bg-slate-50/70 transition-colors">
                                        <td class="py-2 px-3 text-center font-medium text-slate-500 tabular-nums sticky left-0 bg-white border-r border-slate-200">
                                            {{ $loop->iteration }}
                                        </td>
                                        <td class="py-2 px-3 font-mono text-2xs text-slate-600">
                                            {{ $row['student']->nisn ?? '-' }}
                                        </td>
                                        <td class="py-2 px-3 font-medium text-slate-900 sticky left-10 bg-white border-r border-slate-200">
                                            {{ $row['student']->nama_lengkap }}
                                        </td>

                                        <!-- Nilai Tiap Pertemuan -->
                                        @foreach($matrixData['dates'] as $dateStr)
                                            @php
                                                $st = $row['records'][$dateStr] ?? '-';
                                                $cellCls = match($st) {
                                                    'hadir' => 'bg-emerald-100 text-emerald-800 font-bold',
                                                    'sakit' => 'bg-amber-100 text-amber-800 font-bold',
                                                    'izin'  => 'bg-sky-100 text-sky-800 font-bold',
                                                    'alpa'  => 'bg-rose-100 text-rose-800 font-bold',
                                                    default => 'text-slate-300',
                                                };
                                                $initial = match($st) {
                                                    'hadir' => 'H',
                                                    'sakit' => 'S',
                                                    'izin'  => 'I',
                                                    'alpa'  => 'A',
                                                    default => '-',
                                                };
                                            @endphp
                                            <td class="py-1.5 px-1.5 text-center border-r border-slate-200">
                                                <span class="inline-flex items-center justify-center w-6 h-6 rounded text-2xs {{ $cellCls }}">
                                                    {{ $initial }}
                                                </span>
                                            </td>
                                        @endforeach

                                        <!-- Kolom Ringkasan Per Siswa -->
                                        <td class="py-2 px-2 text-center font-semibold text-emerald-700 bg-emerald-50/30 tabular-nums border-r border-slate-200">
                                            {{ $row['hadir'] }}
                                        </td>
                                        <td class="py-2 px-2 text-center font-semibold text-amber-700 bg-amber-50/30 tabular-nums border-r border-slate-200">
                                            {{ $row['sakit'] }}
                                        </td>
                                        <td class="py-2 px-2 text-center font-semibold text-sky-700 bg-sky-50/30 tabular-nums border-r border-slate-200">
                                            {{ $row['izin'] }}
                                        </td>
                                        <td class="py-2 px-2 text-center font-semibold text-rose-700 bg-rose-50/30 tabular-nums border-r border-slate-200">
                                            {{ $row['alpa'] }}
                                        </td>
                                        <td class="py-2 px-3 text-center font-bold text-slate-900 bg-slate-50 tabular-nums">
                                            {{ $row['persentase'] }}%
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>