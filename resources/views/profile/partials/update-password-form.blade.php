<section aria-labelledby="keamanan-akun-heading">
    <header class="pb-4 border-b border-border">
        <h2 id="keamanan-akun-heading" class="text-base font-semibold text-navy">Keamanan Akun</h2>
        <p class="text-sm text-muted mt-1">Ubah password akun Anda secara berkala agar tetap aman.</p>
    </header>

    @if (session('password_form_updated'))
        <div class="mt-4 flex items-start gap-2.5 rounded-xl border border-primary/20 bg-primary-soft px-4 py-3 text-sm font-medium text-primary-dark" role="status">
            <x-icon name="check-circle" class="w-4 h-4 mt-0.5 flex-shrink-0" />
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <form method="post" action="{{ route('password.update') }}" class="mt-5 space-y-5">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="label">Password Saat Ini <span class="text-accent">*</span></label>
            <input
                id="update_password_current_password"
                name="current_password"
                type="password"
                autocomplete="current-password"
                required
                class="input"
            >
            @if ($errors->updatePassword->has('current_password'))
                <p class="mt-1.5 text-xs font-medium text-accent">{{ $errors->updatePassword->first('current_password') }}</p>
            @endif
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label for="update_password_password" class="label">Password Baru <span class="text-accent">*</span></label>
                <input
                    id="update_password_password"
                    name="password"
                    type="password"
                    autocomplete="new-password"
                    required
                    class="input"
                >
                @if ($errors->updatePassword->has('password'))
                    <p class="mt-1.5 text-xs font-medium text-accent">{{ $errors->updatePassword->first('password') }}</p>
                @else
                    <p class="mt-1.5 text-xs text-muted">Minimal 8 karakter.</p>
                @endif
            </div>

            <div>
                <label for="update_password_password_confirmation" class="label">Konfirmasi Password Baru <span class="text-accent">*</span></label>
                <input
                    id="update_password_password_confirmation"
                    name="password_confirmation"
                    type="password"
                    autocomplete="new-password"
                    required
                    class="input"
                >
                @if ($errors->updatePassword->has('password_confirmation'))
                    <p class="mt-1.5 text-xs font-medium text-accent">{{ $errors->updatePassword->first('password_confirmation') }}</p>
                @endif
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 pt-4 border-t border-border">
            <p class="text-xs text-muted flex items-center gap-1.5">
                <x-icon name="lock" class="w-3.5 h-3.5 flex-shrink-0" />
                Anda tetap masuk setelah password diubah.
            </p>
            <button type="submit" class="btn-primary">Perbarui Password</button>
        </div>
    </form>
</section>
