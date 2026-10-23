<x-app-layout>
    <x-slot name="title">Data Penugasan Mengajar</x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        <div class="flex flex-col sm:flex-row gap-4">
            <x-breadcrumb :items="[['label' => 'Pengelolaan'], ['label' => 'Penugasan Mengajar']]" />
        </div>

        <div class="card p-6">
            <h3 class="text-lg font-semibold text-navy mb-4">Daftar Penugasan Mengajar</h3>

            <!-- Filter Form -->
                    <form method="GET" action="{{ route('penugasan-mengajar.index') }}" class="mb-6 space-y-4">
                        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                            <div class="md:col-span-2">
                                <label class="label">Pencarian</label>
                                <div class="relative">
                                    <input
                                        type="text"
                                        name="search"
                                        value="{{ request('search') }}"
                                        placeholder="Cari tutor, mata pelajaran, rombel, tahun ajaran..."
                                        class="input pl-10 @error('search') input-error @enderror"
                                    />
                                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                @error('search')
                                <p class="input-error text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="label">Tutor</label>
                        <select name="tutor_id" class="input @error('tutor_id') input-error @enderror">
                            <option value="">-- Semua Tutor --</option>
                            <option value="">-- Pilih Tutor --</option>
                            @foreach($tutors as $tutor)
                                <option value="{{ $tutor->id }}" {{ request('tutor_id') == $tutor->id ? 'selected' : '' }}>
                                    {{ $tutor->nama_lengkap }}
                                </option>
                            @endforeach
                        </select>
                        @error('tutor_id')
                        <p class="input-error text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="label">Mata Pelajaran</label>
                        <select name="subject_id" class="input @error('subject_id') input-error @enderror">
                            <option value="">-- Semua Mata Pelajaran --</option>
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                    {{ $subject->kode . ': ' . $subject->nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('subject_id')
                        <p class="input-error text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="label">Rombel</label>
                        <select name="classroom_id" class="input @error('classroom_id') input-error @enderror">
                            <option value="">-- Semua Rombel --</option>
                            <option value="">-- Pilih Rombel --</option>
                            @foreach($classrooms as $classroom)
                                <option value="{{ $classroom->id }}" {{ request('classroom_id') == $classroom->id ? 'selected' : '' }}>
                                    {{ $classroom->nama_rombel }} ({{ $classroom->kode_rombel }})
                                </option>
                            @endforeach
                        </select>
                        @error('classroom_id')
                        <p class="input-error text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="label">Tahun Ajaran</label>
                        <select name="academic_year_id" class="input @error('academic_year_id') input-error @enderror">
                            <option value="">-- Semua Tahun Ajaran --</option>
                            <option value="">-- Pilih Tahun Ajaran --</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
                                    {{ $year->nama_ajaran }}
                                </option>
                            @endforeach
                        </select>
                        @error('academic_year_id')
                        <p class="input-error text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="label">Semester</label>
                        <select name="semester_id" class="input @error('semester_id') input-error @enderror">
                            <option value="">-- Semester --</option>
                            @foreach($semesters as $semester)
                                <option value="{{ $semester->id }}" {{ request('semester_id') == $semester->id ? 'selected' : '' }}>
                                    {{ $semester->nama_semester }} ({{ $semester->kode }})
                                </option>
                            @endforeach
                        </select>
                        @error('semester_id')
                        <p class="input-error text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="label">Status</label>
                        <select name="status_aktif" class="input @error('status_aktif') input-error @enderror">
                            <option value="" {{ request('status_aktif') === '' || request('status_aktif') === null ? 'selected' : '' }}>-- Semua Status --</option>
                            <option value="1" {{ request('status_aktif') == '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ request('status_aktif') == '0' ? 'selected' : '' }}>Tidak Aktif</option>
                        </select>
                        @error('status_aktif')
                        <p class="input-error text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <button type="submit" class="btn-primary">Filter</button>
                        <a href="{{ route('penugasan-mengajar.index') }}" class="btn-secondary">Batal</a>
                    </div>
                </div>
            </form>

            <!-- Assignments Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-border">
                    <thead>
                        <tr>
                            <th scope="col" class="text-left">Tutor</th>
                            <th scope="col" class="text-left">Mata Pelajaran</th>
                            <th scope="col" class="text-left">Rombel</th>
                            <th scope="col" class="text-left">Tahun Ajaran</th>
                            <th scope="col" class="text-left">Semester</th>
                            <th scope="col" class="text-center">Status</th>
                            <th scope="col" class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse($penugasanans as $pm)
                        <tr class="border-b hover:bg-navy/5">
                            <td class="font-medium text-navy">{{ $pm->tutor->nama_lengkap }}</td>
                            <td class="text-sm text-muted">{{ $pm->subject->kode . ': ' . $pm->subject->nama }}</td>
                            <td class="text-sm text-muted">{{ $pm->classroom->nama_rombel }} ({{ $pm->classroom->kode_rombel }})</td>
                            <td class="text-sm text-muted">{{ $pm->academicYear->nama_ajaran }}</td>
                            <td class="text-sm text-muted">{{ $pm->semester ? $pm->semester->nama_semester : '-' }}</td>
                            <td>
                                <span class="badge {{ $pm->status_aktif ? 'badge-success' : 'badge-warning' }}">
                                    {{ $pm->status_aktif ? 'Aktif' : 'Tidak Aktif' }}
                                </span>
                            </td>
                            <td class="text-right">
                                @can('classrooms.manage')
                                <a href="{{ route('penugasan-mengajar.show', $pm->id) }}" class="text-primary hover:underline">Detail</a>
                                <a href="{{ route('penugasan-mengajar.edit', $pm->id) }}" class="text-primary hover:underline ml-2">Edit</a>
                                <form action="{{ route('penugasan-mengajar.destroy', $pm->id) }}" method="POST" style="display:inline">
                                    @csrf
                                    @method('DELETE')
                                    <button onclick="return confirm('Yakin menghapus penugasan ini?')" class="text-danger hover:underline ml-2">Hapus</button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-sm text-muted">
                                Belum ada penugasan mengajar. Tambahkan penugasan melalui tombol di atas.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($penugasanans->hasPages())
            <div class="mt-4 flex justify-end">
                {{ $penugasanans->links() }}
            </div>
            @endif
        </div>
    </div>
</x-app-layout>