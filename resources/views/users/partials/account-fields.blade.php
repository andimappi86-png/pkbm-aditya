{{--
    Field akun yang dipakai bersama oleh Jalur A (tautkan profil) dan
    Jalur B (siswa baru + akun). Root Alpine kedua form wajib menyediakan
    state "name" dan "email" agar ringkasan konfirmasi ikut terbarui.
--}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="md:col-span-2">
        <x-input type="text" name="name" label="Nama lengkap" :value="old('name')" required
                 placeholder="Nama lengkap pengguna" x-model="name" />
    </div>
    <div>
        <x-input type="email" name="email" label="Email (dipakai untuk login)" :value="old('email')" required
                 placeholder="email@contoh.com" x-model="email" />
    </div>
    <div>
        <x-input type="tel" name="phone" label="Telepon" :value="old('phone')" placeholder="08xxxxxxxxxx" />
    </div>
    <div>
        <x-select name="status" label="Status akun" :options="['active' => 'Aktif', 'inactive' => 'Nonaktif']"
                  :selected="old('status', 'active')" required />
    </div>
    <div class="hidden md:block"></div>
    <div>
        <x-input type="password" name="password" label="Password awal" required
                 placeholder="Minimal 8 karakter" hint="Sampaikan password ini kepada pemilik akun secara aman." />
    </div>
    <div>
        <x-input type="password" name="password_confirmation" label="Ulangi password awal" required
                 placeholder="Ulangi password" />
    </div>
</div>
