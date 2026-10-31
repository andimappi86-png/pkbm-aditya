<x-app-layout>
    <x-slot name="title">Pengelolaan Penugasan</x-slot>

    <div class="max-w-7xl mx-auto space-y-6" x-data="{
        activeTab: '{{ $activeTab ?? 'wali-kelas' }}',
        showWaliModal: false,
        showTeachingModal: false,
        teachingMode: 'create',
        teachingAction: '{{ route('classroom-assignments.store-teaching') }}',
        teachingData: {
            id: null,
            academic_year_id: '{{ $selectedYearId }}',
            tutor_id: '',
            subject_id: '',
            classroom_id: '',
            status_aktif: true
        },
        waliData: {
            academic_year_id: '{{ $selectedYearId }}',
            classroom_id: '',
            wali_kelas_id: ''
        },
        openCreateTeaching() {
            this.teachingMode = 'create';
            this.teachingAction = '{{ route('classroom-assignments.store-teaching') }}';
            this.teachingData = {
                id: null,
                academic_year_id: '{{ $selectedYearId }}',
                tutor_id: '',
                subject_id: '',
                classroom_id: '',
                status_aktif: true
            };
            this.showTeachingModal = true;
        },
        openEditTeaching(item) {
            this.teachingMode = 'edit';
            this.teachingAction = '/classroom-assignments/teaching/' + item.id;
            this.teachingData = {
                id: item.id,
                academic_year_id: item.academic_year_id,
                tutor_id: item.tutor_id,
                subject_id: item.subject_id,
                classroom_id: item.classroom_id,
                status_aktif: !!item.status_aktif
            };
            this.showTeachingModal = true;
        },
        openAssignWali(classroomId = '', currentWaliId = '') {
            this.waliData = {
                academic_year_id: '{{ $selectedYearId }}',
                classroom_id: classroomId,
                wali_kelas_id: currentWaliId
            };
            this.showWaliModal = true;
        }
    }">
        {{-- Header & Breadcrumb --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <x-breadcrumb :items="[['label' => 'Pengelolaan'], ['label' => 'Penugasan']]" />
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight mt-1">Pengelolaan Penugasan</h1>
                <p class="text-sm text-slate-500 mt-0.5">Kelola penetapan wali kelas dan penugasan mengajar tutor dalam satu sistem terpusat.</p>
            </div>

            {{-- Academic Year Selector --}}
            <div class="flex items-center gap-3">
                <form method="GET" action="{{ route('classroom-assignments.index') }}" class="flex items-center gap-2">
                    <input type="hidden" name="tab" :value="activeTab">
                    <label for="filter_year" class="text-xs font-medium text-slate-600 whitespace-nowrap">Tahun Ajaran:</label>
                    <select id="filter_year" name="academic_year_id" onchange="this.form.submit()"
                        class="text-sm rounded-lg border-slate-300 bg-white py-1.5 pl-3 pr-8 focus:border-emerald-600 focus:ring-emerald-600 text-slate-800 shadow-xs">
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" @selected($selectedYearId == $year->id)>
                                {{ $year->nama }} {{ $year->status_aktif ? '(Aktif)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>

        {{-- Flash Alerts --}}
        @if(session('success'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                <p class="font-medium">Terjadi kesalahan pada input:</p>
                <ul class="list-disc list-inside mt-1 space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Tab Navigation --}}
        <div class="border-b border-slate-200">
            <nav class="flex space-x-6" aria-label="Tabs">
                <button type="button"
                    @click="activeTab = 'wali-kelas'"
                    :class="activeTab === 'wali-kelas' ? 'border-emerald-600 text-emerald-700 font-semibold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium'"
                    class="py-3 px-1 border-b-2 text-sm whitespace-nowrap transition-colors flex items-center gap-2">
                    <span>1. Penetapan Wali Kelas</span>
                    <span class="text-xs py-0.5 px-2 rounded-full bg-slate-100 text-slate-600"
                        :class="activeTab === 'wali-kelas' ? 'bg-emerald-100 text-emerald-800' : ''">
                        {{ $waliClassrooms->total() }} Rombel
                    </span>
                </button>

                <button type="button"
                    @click="activeTab = 'teaching'"
                    :class="activeTab === 'teaching' ? 'border-emerald-600 text-emerald-700 font-semibold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 font-medium'"
                    class="py-3 px-1 border-b-2 text-sm whitespace-nowrap transition-colors flex items-center gap-2">
                    <span>2. Penugasan Mengajar</span>
                    <span class="text-xs py-0.5 px-2 rounded-full bg-slate-100 text-slate-600"
                        :class="activeTab === 'teaching' ? 'bg-emerald-100 text-emerald-800' : ''">
                        {{ $teachingAssignments->total() }} Penugasan
                    </span>
                </button>
            </nav>
        </div>

        {{-- SECTION 1: PENETAPAN WALI KELAS --}}
        <div x-show="activeTab === 'wali-kelas'" class="space-y-4" x-cloak>
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs space-y-4">
                {{-- Action Bar --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <form method="GET" action="{{ route('classroom-assignments.index') }}" class="flex items-center gap-2 max-w-sm w-full">
                        <input type="hidden" name="tab" value="wali-kelas">
                        <input type="hidden" name="academic_year_id" value="{{ $selectedYearId }}">
                        <div class="relative w-full">
                            <input type="text" name="wali_search" value="{{ request('wali_search') }}"
                                placeholder="Cari rombel atau nama wali kelas..."
                                class="w-full text-sm rounded-lg border-slate-300 pl-9 pr-3 py-1.5 focus:border-emerald-600 focus:ring-emerald-600 text-slate-800 placeholder-slate-400">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 pointer-events-none">
                                <x-icon name="magnifying-glass" class="w-4 h-4" />
                            </span>
                        </div>
                        <button type="submit" class="text-sm px-3 py-1.5 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 font-medium transition-colors">
                            Cari
                        </button>
                    </form>

                    @can('classrooms.manage')
                    <button type="button" @click="openAssignWali()"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-medium rounded-lg transition-colors shadow-xs">
                        <x-icon name="plus" class="w-4 h-4" />
                        Tetapkan Wali Kelas
                    </button>
                    @endcan
                </div>

                {{-- Wali Kelas Table --}}
                <div class="overflow-x-auto rounded-lg border border-slate-200">
                    <table class="w-full text-left text-sm text-slate-700 divide-y divide-slate-200">
                        <thead class="bg-slate-50 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                            <tr>
                                <th scope="col" class="px-4 py-3">Rombel</th>
                                <th scope="col" class="px-4 py-3">Program</th>
                                <th scope="col" class="px-4 py-3">Tahun Ajaran</th>
                                <th scope="col" class="px-4 py-3">Wali Kelas Aktif</th>
                                <th scope="col" class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @forelse($waliClassrooms as $classroom)
                                <tr class="hover:bg-slate-50/75 transition-colors">
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-slate-900">{{ $classroom->nama_rombel }}</div>
                                        <div class="text-xs text-slate-400">{{ $classroom->kode_rombel ?? 'Tanpa kode' }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-slate-600">
                                        {{ $classroom->program->nama ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-slate-600 whitespace-nowrap">
                                        {{ $classroom->academicYear->nama ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($classroom->waliKelas)
                                            <div class="flex items-center gap-2">
                                                <div class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center font-medium text-xs">
                                                    {{ strtoupper(substr($classroom->waliKelas->name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="font-medium text-slate-900">{{ $classroom->waliKelas->name }}</div>
                                                    <div class="text-xs text-slate-400">{{ $classroom->waliKelas->email }}</div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                                Belum Ditetapkan
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        @can('classrooms.manage')
                                        <div class="inline-flex items-center gap-2">
                                            <button type="button"
                                                @click="openAssignWali('{{ $classroom->id }}', '{{ $classroom->wali_kelas_id ?? '' }}')"
                                                class="text-xs font-medium text-emerald-700 hover:text-emerald-900 hover:underline">
                                                {{ $classroom->wali_kelas_id ? 'Ubah' : 'Tetapkan' }}
                                            </button>

                                            @if($classroom->wali_kelas_id)
                                                <form method="POST" action="{{ route('classroom-assignments.remove-wali-kelas', $classroom->id) }}"
                                                    onsubmit="return confirm('Kosongkan wali kelas dari rombel {{ $classroom->nama_rombel }}?')"
                                                    class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-xs font-medium text-rose-600 hover:text-rose-800 hover:underline">
                                                        Kosongkan
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-slate-400 text-sm">
                                        Tidak ada data rombel untuk tahun ajaran ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($waliClassrooms->hasPages())
                    <div class="pt-2">
                        {{ $waliClassrooms->links() }}
                    </div>
                @endif
            </div>
        </div>

        {{-- SECTION 2: PENUGASAN MENGAJAR --}}
        <div x-show="activeTab === 'teaching'" class="space-y-4" x-cloak>
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-xs space-y-4">
                {{-- Action Bar --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <form method="GET" action="{{ route('classroom-assignments.index') }}" class="flex flex-wrap items-center gap-2 max-w-2xl w-full">
                        <input type="hidden" name="tab" value="teaching">
                        <input type="hidden" name="academic_year_id" value="{{ $selectedYearId }}">
                        <div class="relative flex-1 min-w-[200px]">
                            <input type="text" name="teaching_search" value="{{ request('teaching_search') }}"
                                placeholder="Cari tutor, mapel, atau rombel..."
                                class="w-full text-sm rounded-lg border-slate-300 pl-9 pr-3 py-1.5 focus:border-emerald-600 focus:ring-emerald-600 text-slate-800 placeholder-slate-400">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 pointer-events-none">
                                <x-icon name="magnifying-glass" class="w-4 h-4" />
                            </span>
                        </div>

                        <select name="status_filter" class="text-sm rounded-lg border-slate-300 py-1.5 pl-3 pr-8 focus:border-emerald-600 focus:ring-emerald-600 text-slate-800">
                            <option value="">Semua Status</option>
                            <option value="1" @selected(request('status_filter') === '1')>Aktif</option>
                            <option value="0" @selected(request('status_filter') === '0')>Non-aktif</option>
                        </select>

                        <button type="submit" class="text-sm px-3 py-1.5 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 font-medium transition-colors">
                            Filter
                        </button>
                    </form>

                    @can('classrooms.manage')
                    <button type="button" @click="openCreateTeaching()"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-medium rounded-lg transition-colors shadow-xs whitespace-nowrap">
                        <x-icon name="plus" class="w-4 h-4" />
                        Tambah Penugasan Mengajar
                    </button>
                    @endcan
                </div>

                {{-- Teaching Assignments Table --}}
                <div class="overflow-x-auto rounded-lg border border-slate-200">
                    <table class="w-full text-left text-sm text-slate-700 divide-y divide-slate-200">
                        <thead class="bg-slate-50 text-xs font-semibold text-slate-600 uppercase tracking-wider">
                            <tr>
                                <th scope="col" class="px-4 py-3">Tutor</th>
                                <th scope="col" class="px-4 py-3">Mata Pelajaran</th>
                                <th scope="col" class="px-4 py-3">Rombel</th>
                                <th scope="col" class="px-4 py-3">Tahun Ajaran</th>
                                <th scope="col" class="px-4 py-3">Status</th>
                                <th scope="col" class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @forelse($teachingAssignments as $assignment)
                                <tr class="hover:bg-slate-50/75 transition-colors">
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-slate-900">{{ $assignment->tutor->nama_lengkap ?? '-' }}</div>
                                        @if($assignment->tutor?->nip)
                                            <div class="text-xs text-slate-400">NIP: {{ $assignment->tutor->nip }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-slate-800">{{ $assignment->subject->nama ?? '-' }}</div>
                                        <div class="text-xs text-slate-400">{{ $assignment->subject->kode ?? '' }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-slate-800">{{ $assignment->classroom->nama_rombel ?? '-' }}</div>
                                        <div class="text-xs text-slate-400">{{ $assignment->classroom->program->nama ?? '' }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-slate-600 whitespace-nowrap">
                                        {{ $assignment->academicYear->nama ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        @if($assignment->status_aktif)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                Aktif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                                Non-aktif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        @can('classrooms.manage')
                                        <div class="inline-flex items-center gap-2">
                                            {{-- Toggle Status --}}
                                            <form method="POST" action="{{ route('classroom-assignments.toggle-teaching-status', $assignment->id) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="text-xs font-medium {{ $assignment->status_aktif ? 'text-amber-600 hover:text-amber-800' : 'text-emerald-700 hover:text-emerald-900' }} hover:underline">
                                                    {{ $assignment->status_aktif ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </button>
                                            </form>

                                            {{-- Edit Assignment --}}
                                            <button type="button"
                                                @click="openEditTeaching(@js([
                                                    'id' => $assignment->id,
                                                    'academic_year_id' => $assignment->academic_year_id,
                                                    'tutor_id' => $assignment->tutor_id,
                                                    'subject_id' => $assignment->subject_id,
                                                    'classroom_id' => $assignment->classroom_id,
                                                    'status_aktif' => (bool) $assignment->status_aktif
                                                ]))"
                                                class="text-xs font-medium text-slate-700 hover:text-slate-900 hover:underline">
                                                Ubah
                                            </button>

                                            {{-- Delete Assignment --}}
                                            <form method="POST" action="{{ route('classroom-assignments.destroy-teaching', $assignment->id) }}"
                                                onsubmit="return confirm('Hapus penugasan mengajar ini?')"
                                                class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs font-medium text-rose-600 hover:text-rose-800 hover:underline">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-slate-400 text-sm">
                                        Belum ada data penugasan mengajar untuk kriteria yang dipilih.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($teachingAssignments->hasPages())
                    <div class="pt-2">
                        {{ $teachingAssignments->links() }}
                    </div>
                @endif
            </div>
        </div>

        {{-- MODAL 1: FORM PENETAPAN WALI KELAS --}}
        <div x-show="showWaliModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity" @click="showWaliModal = false"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-lg bg-white rounded-xl border border-slate-200 shadow-xl p-6 overflow-hidden">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <h3 class="text-lg font-semibold text-slate-900">Tetapkan Wali Kelas</h3>
                        <button type="button" @click="showWaliModal = false" class="text-slate-400 hover:text-slate-600">
                            <x-icon name="x-mark" class="w-5 h-5" />
                        </button>
                    </div>

                    <form method="POST" action="{{ route('classroom-assignments.assign-wali-kelas') }}" class="space-y-4 mt-4">
                        @csrf
                        {{-- Tahun Ajaran --}}
                        <div>
                            <label for="wali_academic_year_id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Tahun Ajaran <span class="text-rose-500">*</span>
                            </label>
                            <select id="wali_academic_year_id" name="academic_year_id" x-model="waliData.academic_year_id" required
                                class="w-full text-sm rounded-lg border-slate-300 focus:border-emerald-600 focus:ring-emerald-600">
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}">{{ $year->nama }} {{ $year->status_aktif ? '(Aktif)' : '' }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Rombel --}}
                        <div>
                            <label for="wali_classroom_id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Rombel <span class="text-rose-500">*</span>
                            </label>
                            <select id="wali_classroom_id" name="classroom_id" x-model="waliData.classroom_id" required
                                class="w-full text-sm rounded-lg border-slate-300 focus:border-emerald-600 focus:ring-emerald-600">
                                <option value="">-- Pilih Rombel --</option>
                                @foreach($classroomsForSelect as $cl)
                                    <option value="{{ $cl->id }}">{{ $cl->nama_rombel }} ({{ $cl->program->nama ?? 'Program' }})</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-slate-400 mt-1">Satu rombel hanya memiliki satu wali kelas aktif dalam satu tahun ajaran.</p>
                        </div>

                        {{-- Wali Kelas --}}
                        <div>
                            <label for="wali_user_id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Wali Kelas <span class="text-rose-500">*</span>
                            </label>
                            <select id="wali_user_id" name="wali_kelas_id" x-model="waliData.wali_kelas_id" required
                                class="w-full text-sm rounded-lg border-slate-300 focus:border-emerald-600 focus:ring-emerald-600">
                                <option value="">-- Pilih Pengguna Wali Kelas --</option>
                                @foreach($eligibleWaliKelas as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-slate-400 mt-1">Dipilih dari pengguna dengan peran Wali Kelas. Tutor juga dapat dipilih bila memiliki peran tersebut.</p>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                            <button type="button" @click="showWaliModal = false"
                                class="px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-lg transition-colors">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-emerald-700 hover:bg-emerald-800 rounded-lg transition-colors shadow-xs">
                                Simpan Penetapan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- MODAL 2: FORM PENUGASAN MENGAJAR (TAMBAH / EDIT) --}}
        <div x-show="showTeachingModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity" @click="showTeachingModal = false"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-lg bg-white rounded-xl border border-slate-200 shadow-xl p-6 overflow-hidden">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <h3 class="text-lg font-semibold text-slate-900" x-text="teachingMode === 'create' ? 'Tambah Penugasan Mengajar' : 'Ubah Penugasan Mengajar'"></h3>
                        <button type="button" @click="showTeachingModal = false" class="text-slate-400 hover:text-slate-600">
                            <x-icon name="x-mark" class="w-5 h-5" />
                        </button>
                    </div>

                    <form method="POST" :action="teachingAction" class="space-y-4 mt-4">
                        @csrf
                        <template x-if="teachingMode === 'edit'">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        {{-- Tahun Ajaran --}}
                        <div>
                            <label for="teach_academic_year_id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Tahun Ajaran <span class="text-rose-500">*</span>
                            </label>
                            <select id="teach_academic_year_id" name="academic_year_id" x-model="teachingData.academic_year_id" required
                                class="w-full text-sm rounded-lg border-slate-300 focus:border-emerald-600 focus:ring-emerald-600">
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}">{{ $year->nama }} {{ $year->status_aktif ? '(Aktif)' : '' }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Tutor --}}
                        <div>
                            <label for="teach_tutor_id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Tutor Pengampu <span class="text-rose-500">*</span>
                            </label>
                            <select id="teach_tutor_id" name="tutor_id" x-model="teachingData.tutor_id" required
                                class="w-full text-sm rounded-lg border-slate-300 focus:border-emerald-600 focus:ring-emerald-600">
                                <option value="">-- Pilih Tutor --</option>
                                @foreach($tutors as $tutor)
                                    <option value="{{ $tutor->id }}">{{ $tutor->nama_lengkap }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Mata Pelajaran --}}
                        <div>
                            <label for="teach_subject_id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Mata Pelajaran <span class="text-rose-500">*</span>
                            </label>
                            <select id="teach_subject_id" name="subject_id" x-model="teachingData.subject_id" required
                                class="w-full text-sm rounded-lg border-slate-300 focus:border-emerald-600 focus:ring-emerald-600">
                                <option value="">-- Pilih Mata Pelajaran --</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->nama }} ({{ $subject->kode }})</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Rombel --}}
                        <div>
                            <label for="teach_classroom_id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Rombel <span class="text-rose-500">*</span>
                            </label>
                            <select id="teach_classroom_id" name="classroom_id" x-model="teachingData.classroom_id" required
                                class="w-full text-sm rounded-lg border-slate-300 focus:border-emerald-600 focus:ring-emerald-600">
                                <option value="">-- Pilih Rombel --</option>
                                @foreach($classroomsForSelect as $cl)
                                    <option value="{{ $cl->id }}">{{ $cl->nama_rombel }} ({{ $cl->program->nama ?? 'Program' }})</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Status Penugasan --}}
                        <div class="pt-1">
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="status_aktif" value="1" x-model="teachingData.status_aktif"
                                    class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-600">
                                <span class="text-sm font-medium text-slate-800">Status Penugasan Aktif</span>
                            </label>
                            <p class="text-xs text-slate-400 mt-0.5">Penugasan nonaktif tidak akan dapat diakses oleh tutor pada jadwal/materi/tugas baru.</p>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                            <button type="button" @click="showTeachingModal = false"
                                class="px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-lg transition-colors">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-emerald-700 hover:bg-emerald-800 rounded-lg transition-colors shadow-xs">
                                <span x-text="teachingMode === 'create' ? 'Simpan Penugasan' : 'Perbarui Penugasan'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
