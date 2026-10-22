<x-app-layout>
    <x-slot name="title">Tambah Role</x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        <x-breadcrumb :items="[
            ['label' => 'Sistem', 'url' => route('users.index')],
            ['label' => 'Role & Permission', 'url' => route('roles.index')],
            ['label' => 'Tambah Role']
        ]" />

        <div class="card p-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                <div>
                    <h1 class="text-xl font-bold text-navy">Tambah Role Baru</h1>
                    <p class="text-sm text-muted">Buat role kustom dan pilih permission yang sesuai.</p>
                </div>
                <a href="{{ route('roles.index') }}" class="btn-secondary text-sm">Kembali</a>
            </div>

            @if ($errors->any())
            <x-alert variant="danger" title="Validasi Gagal">
                <ul class="list-disc pl-5 space-y-1 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-alert>
            @endif

            <form method="POST" action="{{ route('roles.store') }}" class="space-y-6">
                @csrf

                <!-- Role Info -->
                <div class="border-b border-border pb-6">
                    <h3 class="text-sm font-semibold text-navy uppercase tracking-wider mb-4">Info Role</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="label">Nama Role <span class="text-accent">*</span></label>
                            <x-input type="text" name="name" :value="old('name')" required placeholder="contoh: operator-laporan" />
                            @error('name')
                                <p class="mt-1 text-xs text-accent">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-muted mt-1.5">Hanya huruf kecil, angka, dan tanda hubung (-).</p>
                        </div>
                    </div>
                </div>

                <!-- Permission Matrix -->
                <div class="border-b border-border pb-6">
                    <h3 class="text-sm font-semibold text-navy uppercase tracking-wider mb-4">Permission</h3>
                    <p class="text-sm text-muted mb-4">Centang permission untuk role baru. Dapat diubah kapan saja setelah dibuat.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($permissionGroups as $module => $group)
                        <div class="border border-border rounded-xl p-4 bg-slate-50/50">
                            <h4 class="text-sm font-semibold text-navy mb-3">{{ $group['label'] }}</h4>
                            <div class="space-y-2">
                                @foreach($group['permissions'] as $perm)
                                <label class="flex items-start gap-2.5 cursor-pointer group">
                                    <input
                                        type="checkbox"
                                        name="permissions[]"
                                        value="{{ $perm->name }}"
                                        class="mt-0.5 h-4 w-4 rounded border-border text-primary focus:ring-primary focus:ring-offset-0 cursor-pointer"
                                        {{ in_array($perm->name, old('permissions', [])) ? 'checked' : '' }}
                                    >
                                    <span class="text-sm text-muted group-hover:text-navy transition-colors">
                                        {{ $perm->name }}
                                    </span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <a href="{{ route('roles.index') }}" class="btn-secondary">Batal</a>
                    <button type="submit" class="btn-primary">
                        <x-icon name="plus" class="w-4 h-4" />
                        Buat Role
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>