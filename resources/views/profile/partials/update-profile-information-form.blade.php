@php
    $user = auth()->user();
@endphp

<section aria-labelledby="informasi-profil-heading">
    <header class="pb-4 border-b border-border">
        <h2 id="informasi-profil-heading" class="text-base font-semibold text-navy">Informasi Profil</h2>
        <p class="text-sm text-muted mt-1">Perbarui informasi profil dan alamat email Anda.</p>
    </header>

    @if (session('profile_form_updated'))
        <div class="mt-4 flex items-start gap-2.5 rounded-xl border border-primary/20 bg-primary-soft px-4 py-3 text-sm font-medium text-primary-dark" role="status">
            <x-icon name="check-circle" class="w-4 h-4 mt-0.5 flex-shrink-0" />
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <form method="post" action="{{ route('profile.update') }}" class="mt-5 space-y-5">
        @csrf
        @method('patch')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label for="name" class="label">Nama Lengkap <span class="text-accent">*</span></label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    value="{{ old('name', $user->name) }}"
                    required
                    autofocus
                    autocomplete="name"
                    class="input"
                >
                @error('name')
                    <p class="mt-1.5 text-xs font-medium text-accent">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="phone" class="label">Nomor Telepon</label>
                <input
                    id="phone"
                    name="phone"
                    type="tel"
                    value="{{ old('phone', $user->phone) }}"
                    autocomplete="tel"
                    placeholder="08xxxxxxxxxx"
                    class="input tabular-nums"
                >
                @error('phone')
                    <p class="mt-1.5 text-xs font-medium text-accent">{{ $message }}</p>
                @else
                    <p class="mt-1.5 text-xs text-muted">Opsional, untuk kebutuhan komunikasi.</p>
                @enderror
            </div>
        </div>

        <div>
            <label for="email" class="label">Email <span class="text-accent">*</span></label>
            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email', $user->email) }}"
                required
                autocomplete="username"
                class="input"
            >
            @error('email')
                <p class="mt-1.5 text-xs font-medium text-accent">{{ $message }}</p>
            @else
                <p class="mt-1.5 text-xs text-muted">Email ini digunakan untuk masuk ke sistem dan harus unik.</p>
            @enderror
        </div>

        <div class="flex flex-wrap items-center justify-end gap-3 pt-4 border-t border-border">
            <button type="submit" class="btn-primary">Simpan Perubahan</button>
        </div>
    </form>
</section>
