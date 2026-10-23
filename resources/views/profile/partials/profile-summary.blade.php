@php
    $user = auth()->user();

    $roleLabels = [
        'operator' => 'Operator',
        'kepala-pkbm' => 'Kepala PKBM',
        'ketua-yayasan' => 'Ketua Yayasan',
        'wali-kelas' => 'Wali Kelas',
        'tutor' => 'Tutor',
        'peserta-didik' => 'Peserta Didik',
        'calon-peserta' => 'Calon Peserta',
    ];

    $avatarUrl = $user->avatar && file_exists(public_path('storage/'.$user->avatar))
        ? asset('storage/'.$user->avatar)
        : null;

    $isActive = $user->status === 'active';

    $nomorInduk = null;
    $nomorIndukLabel = null;

    if ($user->tutor) {
        $nomorInduk = $user->tutor->nomor_induk;
        $nomorIndukLabel = 'Nomor Induk Tutor';
    } elseif ($user->student) {
        $nomorInduk = $user->student->nis;
        $nomorIndukLabel = 'NIS';
    }
@endphp

<div class="card p-5 sm:p-6">
    <div class="flex items-start gap-4">
        <x-avatar :src="$avatarUrl" :name="$user->name" size="xl" />

        <div class="min-w-0 flex-1">
            <h2 class="text-base sm:text-lg font-semibold text-navy leading-snug break-words">
                {{ $user->name }}
            </h2>
            <p class="text-sm text-muted break-all mt-0.5">{{ $user->email }}</p>

            <div class="flex flex-wrap items-center gap-1.5 mt-2.5">
                @forelse ($user->getRoleNames() as $role)
                    <x-badge variant="primary" size="sm">{{ $roleLabels[$role] ?? ucfirst($role) }}</x-badge>
                @empty
                    <x-badge variant="neutral" size="sm">Tanpa Role</x-badge>
                @endforelse

                <x-badge :variant="$isActive ? 'success' : 'neutral'" size="sm" dot>
                    {{ $isActive ? 'Akun Aktif' : 'Akun Tidak Aktif' }}
                </x-badge>
            </div>
        </div>
    </div>

    <dl class="mt-5 pt-4 border-t border-border space-y-2.5 text-sm">
        @if ($nomorInduk)
            <div class="flex items-center justify-between gap-3">
                <dt class="text-muted">{{ $nomorIndukLabel }}</dt>
                <dd class="font-medium text-navy tabular-nums">{{ $nomorInduk }}</dd>
            </div>
        @endif

        <div class="flex items-center justify-between gap-3">
            <dt class="text-muted">Nomor Telepon</dt>
            <dd class="font-medium tabular-nums {{ $user->phone ? 'text-navy' : 'text-muted font-normal' }}">
                {{ $user->phone ?: 'Belum diisi' }}
            </dd>
        </div>

        <div class="flex items-center justify-between gap-3">
            <dt class="text-muted">Terdaftar Sejak</dt>
            <dd class="font-medium text-navy">{{ $user->created_at?->format('d/m/Y') ?? '-' }}</dd>
        </div>
    </dl>

    <p class="mt-4 pt-4 border-t border-border text-xs text-muted leading-relaxed">
        Role dan status akun hanya dapat diubah oleh administrator sistem.
    </p>
</div>
