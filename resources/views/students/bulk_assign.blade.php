<x-app-layout>
    <x-slot name="title">Penempatan Siswa Massal ke Rombel</x-slot>

    <div class="max-w-7xl mx-auto space-y-6" x-data="{
        selectedIds: [],
        allIds: {{ Js::from($students->pluck('id')->toArray()) }},
        targetRombel: {{ Js::from((string) (request('rombel_id') ?? '')) }},
        rombelList: {{ Js::from($classrooms->map(fn($c) => [
            'id' => $c->id,
            'nama' => $c->nama_rombel,
            'program_id' => $c->program_id,
            'program_nama' => $c->program?->nama ?? '-',
            'tahun_ajaran_id' => $c->tahun_ajaran_id,
            'tahun_ajaran_nama' => $c->academicYear?->nama ?? '-'
        ])) }},
        showModal: false,
        toggleAll() {
            if (this.selectedIds.length === this.allIds.length) {
                this.selectedIds = [];
            } else {
                this.selectedIds = [...this.allIds];
            }
        },
        get selectedRombelData() {
            return this.rombelList.find(r => r.id == this.targetRombel) || null;
        },
        confirmSubmit() {
            if (this.selectedIds.length === 0) {
                alert('Pilih setidaknya satu siswa terlebih dahulu.');
                return;
            }
            if (!this.targetRombel) {
                alert('Silakan pilih rombel tujuan.');
                return;
            }
            this.showModal = true;
        }
    }">
        <x-breadcrumb :items="[
            ['label' => 'Peserta Didik', 'url' => route('peserta-didik.index')],
            ['label' => 'Penempatan Massal ke Rombel']
        ]" />

        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-xl font-bold text-navy">Penempatan Siswa Massal ke Rombel</h1>
                <p class="text-sm text-muted">Tempatkan siswa yang belum memiliki rombel ke rombel tujuan yang sesuai dengan program dan tahun ajaran.</p>
            </div>
            <a href="{{ route('peserta-didik.index') }}" class="btn-secondary text-sm">Kembali ke Daftar</a>
        </div>

        @if(session('error'))
            <div class="p-4 rounded-xl bg-accent-50 border border-accent-200 text-accent-800 text-sm">
                <div class="font-semibold mb-1">Error:</div>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        @if(session('warning'))
            <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-sm">
                <div class="font-semibold mb-1">Peringatan:</div>
                <div>{!! session('warning') !!}</div>
            </div>
        @endif

        <!-- Filter Card -->
        <div class="card p-5">
            <h2 class="text-sm font-bold text-navy uppercase tracking-wider mb-4">Filter Siswa</h2>
            <form method="GET" action="{{ route('peserta-didik.bulk-assign') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div>
                    <label class="block text-xs font-bold text-muted uppercase mb-1">Pencarian</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama / NIS / NISN..." class="input w-full">
                </div>
                <div>
                    <label class="block text-xs font-bold text-muted uppercase mb-1">Program</label>
                    <select name="program_id" class="input w-full">
                        <option value="">Semua Program</option>
                        @foreach($programs as $prog)
                            <option value="{{ $prog->id }}" {{ request('program_id') == $prog->id ? 'selected' : '' }}>{{ $prog->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-muted uppercase mb-1">Tahun Ajaran</label>
                    <select name="tahun_ajaran_id" class="input w-full">
                        <option value="">Semua Tahun Ajaran</option>
                        @foreach($academicYears as $ay)
                            <option value="{{ $ay->id }}" {{ request('tahun_ajaran_id') == $ay->id ? 'selected' : '' }}>{{ $ay->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn-primary flex-1">Filter</button>
                    <a href="{{ route('peserta-didik.bulk-assign') }}" class="btn-secondary">Reset</a>
                </div>
            </form>
        </div>

        <!-- Assignment Form -->
        <form method="POST" action="{{ route('peserta-didik.bulk-assign.process') }}" id="bulkAssignForm">
            @csrf

            <!-- Target Rombel Selection Bar -->
            <div class="card p-5 mb-4 border-2 border-primary/20 bg-primary-soft/10">
                <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
                    <div class="flex-1 max-w-lg">
                        <label class="block text-xs font-bold text-navy uppercase mb-1">
                            Pilih Rombel Tujuan <span class="text-accent">*</span>
                        </label>
                        <select name="rombel_id" x-model="targetRombel" class="input w-full font-medium" required>
                            <option value="">-- Pilih Rombel Tujuan --</option>
                            @foreach($classrooms as $c)
                                <option value="{{ $c->id }}">
                                    {{ $c->nama_rombel }} ({{ $c->program?->nama ?? '-' }} — {{ $c->academicYear?->nama ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                        <div x-show="selectedRombelData" class="mt-2 text-xs text-muted" x-cloak>
                            <span class="font-semibold text-navy">Program:</span> <span x-text="selectedRombelData?.program_nama"></span> |
                            <span class="font-semibold text-navy">Tahun Ajaran:</span> <span x-text="selectedRombelData?.tahun_ajaran_nama"></span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="text-right">
                            <div class="text-xs text-muted uppercase font-semibold">Siswa Terpilih</div>
                            <div class="text-xl font-bold text-navy">
                                <span x-text="selectedIds.length"></span> / {{ $students->total() }}
                            </div>
                        </div>
                        <button type="button" @click="confirmSubmit()" class="btn-primary" :disabled="selectedIds.length === 0 || !targetRombel">
                            Tempatkan ke Rombel
                        </button>
                    </div>
                </div>
            </div>

            <!-- Student List Table -->
            <div class="card p-0 overflow-hidden">
                <div class="p-4 border-b border-border flex items-center justify-between bg-slate-50/50">
                    <div class="flex items-center gap-2">
                        <button type="button" @click="toggleAll()" class="btn-secondary text-xs">
                            <span x-text="selectedIds.length === allIds.length && allIds.length > 0 ? 'Batal Pilih Semua' : 'Pilih Semua di Halaman Ini'"></span>
                        </button>
                    </div>
                    <div class="text-xs text-muted">
                        Total belum punya rombel: <span class="font-bold text-navy">{{ $unassignedCount }}</span> siswa
                    </div>
                </div>

                <div class="table-shell overflow-x-auto">
                    <table class="min-w-full divide-y divide-border">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="w-12 px-4 py-3 text-center">
                                    <input type="checkbox" :checked="selectedIds.length === allIds.length && allIds.length > 0" @change="toggleAll()" class="rounded border-border text-primary focus:ring-primary/20">
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-muted uppercase">NIS / NISN</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-muted uppercase">Nama Peserta Didik</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-muted uppercase">Program</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-muted uppercase">Tahun Ajaran</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-muted uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            @forelse($students as $st)
                            <tr class="hover:bg-primary-soft/10 transition-colors cursor-pointer" @click="if ($event.target.type !== 'checkbox') { if (selectedIds.includes({{ $st->id }})) { selectedIds = selectedIds.filter(id => id !== {{ $st->id }}) } else { selectedIds.push({{ $st->id }}) } }">
                                <td class="px-4 py-3 text-center">
                                    <input type="checkbox" name="student_ids[]" value="{{ $st->id }}" x-model.number="selectedIds" class="rounded border-border text-primary focus:ring-primary/20">
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <div class="font-medium text-navy">{{ $st->nis ?? '-' }}</div>
                                    <div class="text-xs text-muted">{{ $st->nisn ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <div class="font-medium text-navy">{{ $st->nama_lengkap }}</div>
                                    <div class="text-xs text-muted">{{ $st->jenis_kelamin }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm text-navy">
                                    {{ $st->program?->nama ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-navy">
                                    {{ $st->academicYear?->nama ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <x-badge variant="{{ $st->status == 'aktif' ? 'success' : 'warning' }}">
                                        {{ ucfirst($st->status) }}
                                    </x-badge>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-muted">
                                    <p class="font-medium">Tidak ada siswa yang belum memiliki rombel sesuai filter.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($students->hasPages())
                <div class="p-4 border-t border-border">
                    {{ $students->links() }}
                </div>
                @endif
            </div>

            <!-- Confirmation Modal -->
            <div x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                    <div class="fixed inset-0 transition-opacity bg-navy/60" @click="showModal = false"></div>
                    <div class="relative inline-block w-full max-w-md p-6 overflow-hidden text-left align-middle transition-all transform bg-white rounded-2xl shadow-xl">
                        <h3 class="text-lg font-bold text-navy mb-2">Konfirmasi Penempatan Siswa</h3>
                        <p class="text-sm text-muted mb-4">
                            Anda akan menempatkan <span class="font-bold text-navy" x-text="selectedIds.length"></span> siswa ke rombel:
                        </p>
                        <div class="p-3 bg-slate-50 rounded-xl mb-4 text-sm">
                            <div class="font-bold text-navy" x-text="selectedRombelData?.nama"></div>
                            <div class="text-xs text-muted" x-text="selectedRombelData?.program_nama + ' — ' + selectedRombelData?.tahun_ajaran_nama"></div>
                        </div>
                        <p class="text-xs text-muted mb-6">
                            Pastikan data siswa sesuai. Siswa dengan program atau tahun ajaran yang tidak cocok akan otomatis dilewati untuk menjaga integritas data.
                        </p>
                        <div class="flex justify-end gap-3">
                            <button type="button" @click="showModal = false" class="btn-secondary">Batal</button>
                            <button type="submit" class="btn-primary">Ya, Tempatkan Sekarang</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>