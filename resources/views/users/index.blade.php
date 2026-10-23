<x-app-layout>
    <x-slot name="title">Manajemen Pengguna</x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        <x-breadcrumb :items="[
            ['label' => 'Sistem'],
            ['label' => 'Manajemen Pengguna']
        ]" />

        <!-- Header & Actions -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-xl font-bold text-navy">Manajemen Pengguna</h1>
                <p class="text-sm text-muted">Kelola akun pengguna, role, dan status.</p>
            </div>
            @can('users.manage')
            <a href="{{ route('users.create') }}" class="btn-primary">
                <x-icon name="plus" class="w-4 h-4" />
                Tambah Pengguna
            </a>
            @endcan
        </div>

        <!-- Statistics -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <x-stat-card title="Total Pengguna" :value="$stats['total']" variant="primary" icon="users" />
            <x-stat-card title="Aktif" :value="$stats['active']" variant="success" icon="user-check" />
            <x-stat-card title="Nonaktif" :value="$stats['inactive']" variant="warning" icon="x-circle" />
            <x-stat-card title="Siswa Belum Tertaut" :value="$stats['unlinked_students']" variant="primary" icon="link" />
        </div>

        <!-- Filter & Search -->
        <div class="card p-4">
            <form method="GET" action="{{ route('users.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div class="md:col-span-2">
                    <label class="label">Pencarian</label>
                    <x-input type="text" name="search" :value="request('search')" placeholder="Nama / Email / Telepon..." />
                </div>
                <div>
                    <label class="label">Role</label>
                    <x-select name="role" :options="$roles->pluck('name', 'name')->toArray()" :selected="request('role')" placeholder="Semua Role" />
                </div>
                <div>
                    <label class="label">Status</label>
                    <x-select name="status" :options="['active' => 'Aktif', 'inactive' => 'Nonaktif']" :selected="request('status')" placeholder="Semua Status" />
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn-primary">Filter</button>
                    <a href="{{ route('users.index') }}" class="btn-secondary">Reset</a>
                </div>
            </form>
        </div>

        <!-- Users Table -->
        <div class="card p-0 overflow-hidden">
            <div class="table-shell overflow-x-auto">
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-muted uppercase">Pengguna</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-muted uppercase">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-muted uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-muted uppercase">Profil Terhubung</th>
                            <th class="px-6 py-3 text-right text-xs font-bold text-muted uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse($users as $user)
                        <tr class="hover:bg-primary-soft/10 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-medium text-navy">{{ $user->name }}</div>
                                <div class="text-xs text-muted">{{ $user->email }}</div>
                                @if($user->phone)
                                <div class="text-xs text-muted">{{ $user->phone }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @foreach($user->roles as $role)
                                <x-badge variant="{{ $role->name === 'operator' ? 'danger' : ($role->name === 'kepala-pkbm' ? 'warning' : ($role->name === 'peserta-didik' ? 'success' : 'primary')) }}">
                                    {{ $role->name }}
                                </x-badge>
                                @endforeach
                            </td>
                            <td class="px-6 py-4">
                                @if($user->status === 'active')
                                <x-badge variant="success">Aktif</x-badge>
                                @else
                                <x-badge variant="warning">Nonaktif</x-badge>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-navy">
                                @if($user->student)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-blue-100 text-blue-800">
                                    Siswa: {{ $user->student->nama_lengkap }}
                                </span>
                                @elseif($user->tutor)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800">
                                    Tutor: {{ $user->tutor->nama_lengkap }}
                                </span>
                                @else
                                <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <a href="{{ route('users.show', $user) }}" class="text-primary hover:text-primary-dark" title="Detail">
                                    <x-icon name="eye" class="w-4 h-4" />
                                </a>
                                @can('users.manage')
                                <a href="{{ route('users.edit', $user) }}" class="text-primary hover:text-primary-dark" title="Edit">
                                    <x-icon name="pencil" class="w-4 h-4" />
                                </a>
                                <form method="POST" action="{{ route('users.toggle-status', $user) }}" class="inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="text-primary hover:text-primary-dark" title="{{ $user->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}">
                                        <x-icon name="{{ $user->status === 'active' ? 'user-check' : 'x-circle' }}" class="w-4 h-4" />
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('users.destroy', $user) }}" class="inline" onsubmit="return confirm('Hapus akun ini? Data tidak dapat dipulihkan.');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-accent hover:text-accent/80" title="Hapus">
                                        <x-icon name="trash" class="w-4 h-4" />
                                    </button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-muted">Belum ada data pengguna.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
            <div class="p-4 border-t border-border bg-slate-50/50">
                {{ $users->links() }}
            </div>
            @endif
        </div>
    </div>
</x-app-layout>