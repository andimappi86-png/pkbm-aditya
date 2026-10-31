<x-app-layout>
    <x-slot name="title">Penautan Akun Peserta Didik</x-slot>

    <div class="max-w-7xl mx-auto space-y-6" x-data="{
        showModal: false,
        pendingForm: null,
        studentName: '',
        accountName: '',
        pendingDelete: null,
        submitLink(form, studentName) {
            const select = form.querySelector('select[name="user_id"]');
            if (!select || !select.value) { alert('Pilih akun terlebih dahulu.'); return; }
            this.studentName = studentName;
            this.accountName = select.options[select.selectedIndex].text;
            this.pendingForm = form;
            this.pendingDelete = null;
            this.showModal = true;
        },
        submitUnlink(form, studentName, accountName) {
            this.studentName = studentName;
            this.accountName = accountName;
            this.pendingForm = null;
            this.pendingDelete = form;
            this.showModal = true;
        },
        confirmDialog() {
            if (this.pendingForm) { this.pendingForm.submit(); this.showModal = false; return; }
            if (this.pendingDelete) { this.pendingDelete.submit(); this.showModal = false; return; }
        },
    }">
        <x-breadcrumb :items="[
            ['label' => 'Peserta Didik', 'url' => route('peserta-didik.index')],
            ['label' => 'Penautan Akun Siswa']
        ]" />

        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-xl font-bold text-navy">Penautan Akun Peserta Didik</h1>
                <p class="text-sm text-muted">Pilih pasangan akun login dan profil siswa secara eksplisit, lalu konfirmasi sebelum disimpan.</p>
            </div>
            <a href="{{ route('peserta-didik.index') }}" class="btn-secondary text-sm">Kembali ke Daftar</a>
        </div>

        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="card p-4 border-l-4 border-amber-500">
                <div class="text-xs font-bold text-muted uppercase">Siswa Tanpa Akun</div>
                <div class="text-xl font-bold text-navy">{{ $stats['students_without_account'] }}</div>
            </div>
            <div class="card p-4 border-l-4 border-primary">
                <div class="text-xs font-bold text-muted uppercase">Akun Tanpa Profil</div>
                <div class="text-xl font-bold text-navy">{{ $stats['accounts_without_student'] }}</div>
            </div>
            <div class="card p-4 border-l-4 border-emerald-500">
                <div class="text-xs font-bold text-muted uppercase">Sudah Tertaut</div>
                <div class="text-xl font-bold text-navy">{{ $stats['students_linked'] }}</div>
            </div>
        </div>

        <!-- Filter -->
        <div class="card p-4">
            <form method="GET" action="{{ route('peserta-didik.link-accounts') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-muted uppercase mb-1">Pencarian</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama/NIS/NISN (siswa) atau Nama/Email (akun)..." class="input w-full">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn-primary flex-1">Filter</button>
                    <a href="{{ route('peserta-didik.link-accounts') }}" class="btn-secondary">Reset</a>
                </div>
            </form>
        </div>

        <!-- Siswa Tanpa Akun -->
        <div class="card p-0 overflow-hidden">
            <div class="p-4 border-b border-border flex items-center justify-between bg-slate-50/50">
                <h2 class="text-sm font-bold text-navy uppercase tracking-wider">Siswa Belum Memiliki Akun Login</h2>
                <div class="text-xs text-muted">Total: <span class="font-bold text-navy">{{ $students->total() }}</span> siswa</div>
            </div>

            @if($linkableAccounts->isEmpty())
            <div class="p-4">
                <x-alert variant="info" title="Belum ada akun">
                    Belum ada akun ber-role Peserta Didik yang bebas untuk ditautkan. Buat akun terlebih dahulu, lalu kembali ke halaman ini.
                </x-alert>
            </div>
            @endif

            <div class="table-shell overflow-x-auto">
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-bold text-muted uppercase w-72">Tautkan ke Akun</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-muted uppercase">NIS / NISN</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-muted uppercase">Nama Peserta Didik</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-muted uppercase">Program</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-muted uppercase">Tahun Ajaran</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-muted uppercase">Rombel</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-muted uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse($students as $st)
                        <tr class="hover:bg-primary-soft/10 transition-colors">
                            <td class="px-4 py-3">
                                <form method="POST" action="{{ route('peserta-didik.link-accounts.store') }}" @submit.prevent="submitLink($event.target, @js($st->nama_lengkap))">
                                    @csrf
                                    <input type="hidden" name="student_id" value="{{ $st->id }}">
                                    <select name="user_id" class="input w-full text-sm mb-2" required>
                                        <option value="">-- Pilih Akun --</option>
                                        @foreach($linkableAccounts as $acc)
                                            <option value="{{ $acc->id }}">{{ $acc->name }} ({{ $acc->email }})</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn-primary text-xs w-full">Tautkan</button>
                                </form>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <div class="font-medium text-navy">{{ $st->nis }}</div>
                                <div class="text-xs text-muted">{{ $st->nisn ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <div class="font-medium text-navy">{{ $st->nama_lengkap }}</div>
                                <div class="text-xs text-muted">{{ $st->jenis_kelamin }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-navy">{{ $st->program->nama ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-navy">{{ $st->academicYear->nama ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-navy">{{ $st->classroom->nama_rombel ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm">
                                <x-badge variant="{{ $st->status == 'aktif' ? 'success' : 'warning' }}">
                                    {{ ucfirst($st->status) }}
                                </x-badge>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-muted">
                                <p class="font-medium">Tidak ada siswa tanpa akun sesuai filter.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($students->hasPages())
            <div class="p-4 border-t border-border bg-slate-50/50">
                {{ $students->links() }}
            </div>
            @endif
        </div>

        <!-- Akun Tanpa Profil -->
        <div class="card p-0 overflow-hidden">
            <div class="p-4 border-b border-border flex items-center justify-between bg-slate-50/50">
                <h2 class="text-sm font-bold text-navy uppercase tracking-wider">Akun Peserta Didik Belum Terhubung ke Profil</h2>
                <div class="text-xs text-muted">Total: <span class="font-bold text-navy">{{ $accounts->total() }}</span> akun</div>
            </div>
            <div class="table-shell overflow-x-auto">
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-bold text-muted uppercase">Nama</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-muted uppercase">Email</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-muted uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse($accounts as $acc)
                        <tr class="hover:bg-primary-soft/10 transition-colors">
                            <td class="px-4 py-3 text-sm font-medium text-navy">{{ $acc->name }}</td>
                            <td class="px-4 py-3 text-sm text-navy">{{ $acc->email }}</td>
                            <td class="px-4 py-3 text-sm">
                                <x-badge variant="warning">Belum Terhubung</x-badge>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-12 text-center text-muted">
                                <p class="font-medium">Tidak ada akun peserta didik tanpa profil sesuai filter.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($accounts->hasPages())
            <div class="p-4 border-t border-border bg-slate-50/50">
                {{ $accounts->links() }}
            </div>
            @endif
        </div>

        <!-- Sudah Tertaut -->
        <div class="card p-0 overflow-hidden">
            <div class="p-4 border-b border-border flex items-center justify-between bg-slate-50/50">
                <h2 class="text-sm font-bold text-navy uppercase tracking-wider">Siswa yang Sudah Tertaut ke Akun</h2>
                <div class="text-xs text-muted">Total: <span class="font-bold text-navy">{{ $linkedStudents->total() }}</span> pasangan</div>
            </div>
            <div class="table-shell overflow-x-auto">
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-bold text-muted uppercase">NIS / NISN</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-muted uppercase">Nama Peserta Didik</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-muted uppercase">Akun Terhubung</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-muted uppercase">Rombel</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-muted uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse($linkedStudents as $ls)
                        <tr class="hover:bg-primary-soft/10 transition-colors">
                            <td class="px-4 py-3 text-sm">
                                <div class="font-medium text-navy">{{ $ls->nis }}</div>
                                <div class="text-xs text-muted">{{ $ls->nisn ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm font-medium text-navy">{{ $ls->nama_lengkap }}</td>
                            <td class="px-4 py-3 text-sm">
                                <div class="font-medium text-navy">{{ $ls->user->name ?? '-' }}</div>
                                <div class="text-xs text-muted">{{ $ls->user->email ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-navy">{{ $ls->classroom->nama_rombel ?? '-' }}</td>
                            <td class="px-4 py-3 text-right">
                                <form method="POST" action="{{ route('peserta-didik.link-accounts.destroy', $ls->id) }}" @submit.prevent="submitUnlink($event.target, @js($ls->nama_lengkap), @js($ls->user->name ?? '-'))">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-secondary text-xs">Lepas Tautan</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-muted">
                                <p class="font-medium">Tidak ada siswa yang sudah tertaut sesuai filter.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($linkedStudents->hasPages())
            <div class="p-4 border-t border-border bg-slate-50/50">
                {{ $linkedStudents->links() }}
            </div>
            @endif
        </div>

        <!-- Confirmation Modal -->
        <div x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-navy/60" @click="showModal = false"></div>
                <div class="relative inline-block w-full max-w-md p-6 overflow-hidden text-left align-middle transition-all transform bg-white rounded-2xl shadow-xl">
                    <h3 class="text-lg font-bold text-navy mb-2" x-text="pendingDelete ? 'Konfirmasi Lepas Tautan' : 'Konfirmasi Tautkan Akun'"></h3>
                    <p class="text-sm text-muted mb-4" x-text="pendingDelete ? ('Lepas tautan akun '+accountName+' dari siswa '+studentName+' ?') : ('Tautkan akun '+accountName+' ke siswa '+studentName+' ?')"></p>
                    <p class="text-xs text-muted mb-6" x-text="pendingDelete ? 'Siswa akan kembali dinilai tidak memiliki akun login.' : 'Pastikan pasangan akun–siswa sudah benar. Sistem menolak penautan ganda pada satu akun atau profil siswa.'"></p>
                    <div class="flex justify-end gap-3">
                        <button type="button" @click="showModal = false; pendingForm = null; pendingDelete = null;" class="btn-secondary">Batal</button>
                        <button type="button" @click="confirmDialog()" class="btn-primary" x-text="pendingDelete ? 'Ya, Lepas Tautan' : 'Ya, Tautkan Sekarang'"></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
