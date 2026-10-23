<x-app-layout>
    <x-slot name="title">Role & Permission</x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        <x-breadcrumb :items="[
            ['label' => 'Sistem'],
            ['label' => 'Manajemen Pengguna', 'url' => route('users.index')],
            ['label' => 'Role & Permission']
        ]" />

        <!-- Header & Actions -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-xl font-bold text-navy">Role & Permission</h1>
                <p class="text-sm text-muted">Kelola role sistem dan pengaturan permission per role.</p>
            </div>
            @can('users.manage')
            <a href="{{ route('roles.create') }}" class="btn-primary">
                <x-icon name="plus" class="w-4 h-4" />
                Tambah Role
            </a>
            @endcan
        </div>

        <!-- Role Table -->
        <div class="card p-0 overflow-hidden">
            <div class="table-shell overflow-x-auto">
                <table class="min-w-full divide-y divide-border">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-muted uppercase">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-muted uppercase">Pengguna</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-muted uppercase">Permission</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-muted uppercase">Jenis</th>
                            <th class="px-6 py-3 text-right text-xs font-bold text-muted uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse($roles as $role)
                        <tr class="hover:bg-primary-soft/10 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-medium text-navy">{{ $role->name }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <x-badge variant="{{ $role->users_count > 0 ? 'primary' : 'warning' }}">
                                    {{ $role->users_count }} pengguna
                                </x-badge>
                            </td>
                            <td class="px-6 py-4">
                                <x-badge variant="primary">{{ $role->permissions_count }} permission</x-badge>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $systemRoles = ['operator', 'kepala-pkbm', 'ketua-yayasan', 'wali-kelas', 'tutor', 'peserta-didik', 'calon-peserta'];
                                @endphp
                                @if(in_array($role->name, $systemRoles))
                                <x-badge variant="success">Sistem</x-badge>
                                @else
                                <x-badge variant="neutral">Kustom</x-badge>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <a href="{{ route('roles.edit', $role) }}" class="text-primary hover:text-primary-dark" title="Edit Permission">
                                    <x-icon name="pencil" class="w-4 h-4" />
                                </a>
                                @can('users.manage')
                                @if(!in_array($role->name, ['operator', 'kepala-pkbm', 'ketua-yayasan', 'wali-kelas', 'tutor', 'peserta-didik', 'calon-peserta']))
                                <form method="POST" action="{{ route('roles.destroy', $role) }}" class="inline" onsubmit="return confirm('Hapus role ini? Role tidak dapat dipulihkan.');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-accent hover:text-accent/80" title="Hapus">
                                        <x-icon name="trash" class="w-4 h-4" />
                                    </button>
                                </form>
                                @else
                                <span class="text-muted/50" title="Role sistem tidak dapat dihapus">
                                    <x-icon name="lock" class="w-4 h-4" />
                                </span>
                                @endif
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-muted">Belum ada data role.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Legend -->
        <div class="card p-4 bg-slate-50/50">
            <div class="flex flex-wrap items-center gap-4 text-sm text-muted">
                <span class="font-medium text-navy">Keterangan:</span>
                <span class="inline-flex items-center gap-1"><x-badge variant="success">Sistem</x-badge> Role bawaan aplikasi, tidak bisa dihapus/diganti nama.</span>
                <span class="inline-flex items-center gap-1"><x-badge variant="neutral">Kustom</x-badge> Role yang dibuat operator, dapat dikelola penuh.</span>
                <span class="inline-flex items-center gap-1"><x-badge variant="primary">{{ $roles->sum('permissions_count') }} total permission</x-badge> di seluruh role.</span>
            </div>
        </div>
    </div>
</x-app-layout>