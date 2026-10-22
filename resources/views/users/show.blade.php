<x-app-layout>
    <x-slot name="title">Detail Pengguna: {{ $user->name }}</x-slot>

    <div class="max-w-3xl mx-auto space-y-6">
        <x-breadcrumb :items="[
            ['label' => 'Sistem', 'url' => route('users.index')],
            ['label' => 'Manajemen Pengguna', 'url' => route('users.index')],
            ['label' => '{{ $user->name }}']
        ]" />

        <div class="card p-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl bg-primary-soft flex items-center justify-center">
                        <x-icon name="{{ $user->hasRole('operator') ? 'user-check' : ($user->hasRole('peserta-didik') ? 'users' : 'user-group') }}" class="w-8 h-8 text-primary-dark" />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-navy">{{ $user->name }}</h1>
                        <p class="text-sm text-muted">{{ $user->email }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @can('users.manage')
                    <a href="{{ route('users.edit', $user) }}" class="btn-primary text-sm">
                        <x-icon name="pencil" class="w-4 h-4" />
                        Edit
                    </a>
                    @endcan
                    <a href="{{ route('users.index') }}" class="btn-secondary text-sm">Kembali</a>
                </div>
            </div>

            <!-- Status & Role Badges -->
            <div class="flex flex-wrap items-center gap-2 mb-6">
                @foreach($user->roles as $role)
                    <x-badge variant="{{ $role->name === 'operator' ? 'danger' : ($role->name === 'kepala-pkbm' ? 'warning' : ($role->name === 'peserta-didik' ? 'success' : 'primary')) }}">
                        {{ $role->name }}
                    </x-badge>
                @endforeach
                @if($user->status === 'active')
                    <x-badge variant="success">Aktif</x-badge>
                @else
                    <x-badge variant="warning">Nonaktif</x-badge>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <h4 class="text-sm font-semibold text-navy uppercase tracking-wider">Info Akun</h4>
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-muted">Nama</dt>
                            <dd class="font-medium text-navy">{{ $user->name }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-muted">Email</dt>
                            <dd class="font-medium text-navy text-right truncate max-w-xs">{{ $user->email }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-muted">Telepon</dt>
                            <dd class="font-medium text-navy text-right">{{ $user->phone ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-muted">Status</dt>
                            <dd class="font-medium text-navy text-right">
                                @if($user->status === 'active')
                                <x-badge variant="success">Aktif</x-badge>
                                @else
                                <x-badge variant="warning">Nonaktif</x-badge>
                                @endif
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-muted">Dibuat</dt>
                            <dd class="font-medium text-navy text-right">{{ $user->created_at->format('d M Y H:i') }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-muted">Diperbarui</dt>
                            <dd class="font-medium text-navy text-right">{{ $user->updated_at->format('d M Y H:i') }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="space-y-4">
                    <h4 class="text-sm font-semibold text-navy uppercase tracking-wider">Profil Terhubung</h4>
                    @if($user->student)
                    <div class="bg-blue-50 border border-blue-100 rounded-xl p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <x-icon name="users" class="w-5 h-5 text-blue-600" />
                            <span class="font-semibold text-blue-800">Profil Siswa</span>
                        </div>
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between"><dt class="text-muted">Nama</dt><dd class="font-medium">{{ $user->student->nama_lengkap }}</dd></div>
                            <div class="flex justify-between"><dt class="text-muted">NIS</dt><dd class="font-medium">{{ $user->student->nis }}</dd></div>
                            <div class="flex justify-between"><dt class="text-muted">NISN</dt><dd class="font-medium">{{ $user->student->nisn ?? '-' }}</dd></div>
                            <div class="flex justify-between"><dt class="text-muted">Rombel</dt><dd class="font-medium">{{ $user->student->classroom?->nama_rombel ?? '-' }}</dd></div>
                            <div class="flex justify-between"><dt class="text-muted">Status</dt><dd class="font-medium"><x-badge variant="{{ $user->student->status === 'aktif' ? 'success' : 'warning' }}">{{ ucfirst($user->student->status) }}</x-badge></dd></div>
                        </dl>
                        <a href="{{ route('peserta-didik.show', $user->student) }}" class="inline-flex items-center gap-1 mt-3 text-primary hover:text-primary-dark text-sm">
                            <x-icon name="eye" class="w-3 h-3" />
                            Lihat Detail Siswa
                        </a>
                    </div>
                    @elseif($user->tutor)
                    <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <x-icon name="user-group" class="w-5 h-5 text-emerald-600" />
                            <span class="font-semibold text-emerald-800">Profil Tutor</span>
                        </div>
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between"><dt class="text-muted">Nama</dt><dd class="font-medium">{{ $user->tutor->nama_lengkap }}</dd></div>
                            <div class="flex justify-between"><dt class="text-muted">Nomor Induk</dt><dd class="font-medium">{{ $user->tutor->nomor_induk }}</dd></div>
                            <div class="flex justify-between"><dt class="text-muted">Status</dt><dd class="font-medium"><x-badge variant="{{ $user->tutor->status_tutor === 'aktif' ? 'success' : 'warning' }}">{{ ucfirst($user->tutor->status_tutor) }}</x-badge></dd></div>
                            <div class="flex justify-between"><dt class="text-muted">Bidang Keahlian</dt><dd class="font-medium">{{ $user->tutor->bidang_keahlian }}</dd></div>
                        </dl>
                        <a href="{{ route('tutors.show', $user->tutor) }}" class="inline-flex items-center gap-1 mt-3 text-primary hover:text-primary-dark text-sm">
                            <x-icon name="eye" class="w-3 h-3" />
                            Lihat Detail Tutor
                        </a>
                    </div>
                    @else
                    <div class="text-center py-8 text-muted border-2 border-dashed border-border rounded-xl">
                        <x-icon name="user-group" class="w-10 h-10 mx-auto mb-2 text-muted/50" />
                        <p>Akun ini belum terhubung ke profil Siswa atau Tutor.</p>
                        @can('users.manage')
                        <p class="text-xs text-muted mt-2">Gunakan fitur <a href="{{ route('peserta-didik.link-accounts') }}" class="text-primary hover:underline">Penautan Akun Siswa</a> untuk menghubungkan.</p>
                        @endcan
                    </div>
                    @endif
                </div>
            </div>

            @can('users.manage')
            <div class="mt-6 pt-6 border-t border-border">
                <div class="flex flex-col sm:flex-row gap-3">
                    <form method="POST" action="{{ route('users.reset-password', $user) }}" onsubmit="return confirm('Yakin reset password akun ini?');" class="flex-1">
                        @csrf
                        <button type="submit" class="btn-secondary w-full">
                            <x-icon name="lock" class="w-4 h-4" />
                            Reset Password
                        </button>
                    </form>
                    <form method="POST" action="{{ route('users.toggle-status', $user) }}" class="flex-1">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn-secondary w-full" title="{{ $user->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}">
                            <x-icon name="{{ $user->status === 'active' ? 'x-circle' : 'user-check' }}" class="w-4 h-4" />
                            {{ $user->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('PERINGATAN: Menghapus akun tidak bisa dibatalkan. Lanjutkan?');" class="flex-1">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-secondary w-full text-accent hover:bg-accent-soft">
                            <x-icon name="trash" class="w-4 h-4" />
                            Hapus
                        </button>
                    </form>
                </div>
            </div>
            @endcan
        </div>
    </div>
</x-app-layout>