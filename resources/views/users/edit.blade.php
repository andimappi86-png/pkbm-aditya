<x-app-layout>
    <x-slot name="title">Edit Pengguna: {{ $user->name }}</x-slot>

    <script>
        function userEditForm() {
            return {
                role: '{{ old('role', $user->roles->first()?->name ?? '') }}',
                studentId: '{{ old('student_id', $user->student?->id ?? '') }}',
                tutorId: '{{ old('tutor_id', $user->tutor?->id ?? '') }}',
            };
        }
    </script>

    <div class="max-w-3xl mx-auto space-y-6">
        <x-breadcrumb :items="[
            ['label' => 'Sistem', 'url' => route('users.index')],
            ['label' => 'Manajemen Pengguna', 'url' => route('users.index')],
            ['label' => 'Edit: {{ $user->name }}']
        ]" />

        <div class="card p-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                <div>
                    <h1 class="text-xl font-bold text-navy">Edit Pengguna</h1>
                    <p class="text-sm text-muted">{{ $user->email }}</p>
                </div>
                <a href="{{ route('users.show', $user) }}" class="btn-secondary text-sm">Lihat Detail</a>
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

            <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-6" x-data="userEditForm()">
                @csrf @method('PUT')

                <!-- Info Akun -->
                <div class="border-b border-border pb-6">
                    <h3 class="text-sm font-semibold text-navy uppercase tracking-wider mb-4">Info Akun</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="label">Nama Lengkap <span class="text-accent">*</span></label>
                            <x-input type="text" name="name" :value="old('name', $user->name)" required />
                        </div>
                        <div>
                            <label class="label">Email <span class="text-accent">*</span></label>
                            <x-input type="email" name="email" :value="old('email', $user->email)" required />
                        </div>
                        <div>
                            <label class="label">Telepon</label>
                            <x-input type="tel" name="phone" :value="old('phone', $user->phone)" />
                        </div>
                        <div>
                            <label class="label">Status <span class="text-accent">*</span></label>
                            <x-select name="status" :options="['active' => 'Aktif', 'inactive' => 'Nonaktif']" :selected="old('status', $user->status)" required />
                        </div>
                    </div>
                </div>

                <!-- Role & Profil -->
                <div class="border-b border-border pb-6">
                    <h3 class="text-sm font-semibold text-navy uppercase tracking-wider mb-4">Role & Profil</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="label">Role <span class="text-accent">*</span></label>
                            <select name="role" class="input w-full" required x-model="role">
                                <option value="">-- Pilih Role --</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}" {{ $user->hasRole($role->name) ? 'selected' : '' }}>{{ $roleLabels[$role->name] ?? $role->name }}</option>
                                @endforeach
                            </select>
                            @error('role')
                                <p class="mt-1 text-xs text-accent">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-muted mt-1.5">Role menentukan modul yang dapat diakses akun.</p>
                        </div>

                        <div>
                            <label class="label">Profil Terhubung Saat Ini</label>
                            @if($user->student)
                                <div class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-blue-100 text-blue-800 mb-2">
                                    Siswa: {{ $user->student->nama_lengkap }} ({{ $user->student->nis }})
                                </div>
                                <p class="text-xs text-muted">Penautan ke siswa sudah ada.</p>
                            @elseif($user->tutor)
                                <div class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800 mb-2">
                                    Tutor: {{ $user->tutor->nama_lengkap }} ({{ $user->tutor->nomor_induk }})
                                </div>
                                <p class="text-xs text-muted">Penautan ke tutor sudah ada.</p>
                            @else
                                <p class="text-muted">Belum terhubung ke profil siswa/tutor.</p>
                            @endif
                        </div>
                    </div>

                    {{-- Profil siswa --}}
                    <div x-show="role === 'peserta-didik'" x-cloak class="mt-4 pt-4 border-t border-border">
                        <label class="label" for="student_id">
                            Profil Siswa
                            <span class="inline-flex items-center rounded-full border border-accent/20 bg-accent-soft px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-accent align-middle">Wajib</span>
                        </label>

                        @if ($unlinkedStudents->isEmpty())
                            <div class="rounded-xl border border-amber-200 bg-amber-50 p-3">
                                <p class="text-sm font-medium text-amber-800">Belum ada profil siswa yang bisa ditautkan.</p>
                                <p class="mt-1 text-xs text-amber-700">
                                    Semua data siswa sudah memiliki akun, atau belum ada data siswa sama sekali.
                                    Tambahkan data siswa terlebih dahulu, lalu kembali ke halaman ini.
                                </p>
                                <a href="{{ route('peserta-didik.create') }}" class="mt-2 inline-flex items-center gap-1 text-xs font-semibold text-primary-dark hover:underline">
                                    <x-icon name="plus" class="w-3 h-3" />
                                    Tambah Peserta Didik
                                </a>
                            </div>
                        @else
                            <select id="student_id" name="student_id" class="input w-full" x-model="studentId"
                                    x-bind:required="role === 'peserta-didik'"
                                    x-bind:disabled="role !== 'peserta-didik'">
                                <option value="">-- Pilih Siswa --</option>
                                @foreach ($unlinkedStudents as $student)
                                    <option value="{{ $student->id }}" {{ (string) old('student_id', $user->student?->id) === (string) $student->id ? 'selected' : '' }}>
                                        {{ $student->nama_lengkap }} — NIS {{ $student->nis }}
                                        @if ($student->classroom) — {{ $student->classroom->nama_rombel }} @endif
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-muted mt-1.5">
                                Wajib untuk role Peserta Didik: akun memakai profil ini untuk mengakses tugas, nilai, dan absensi.
                                Hanya profil yang belum tertaut ke akun lain yang ditampilkan ({{ $unlinkedStudents->count() }} tersedia).
                            </p>
                        @endif

                        @error('student_id')
                            <p class="mt-1 text-xs text-accent font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Profil tutor --}}
                    <div x-show="role === 'tutor' || role === 'wali-kelas'" x-cloak class="mt-4 pt-4 border-t border-border">
                        <label class="label" for="tutor_id">
                            Profil Tutor
                            <span class="inline-flex items-center rounded-full border border-accent/20 bg-accent-soft px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-accent align-middle">Wajib</span>
                        </label>

                        @if ($unlinkedTutors->isEmpty())
                            <div class="rounded-xl border border-amber-200 bg-amber-50 p-3">
                                <p class="text-sm font-medium text-amber-800">Belum ada profil tutor yang bisa ditautkan.</p>
                                <p class="mt-1 text-xs text-amber-700">
                                    Semua data tutor sudah memiliki akun, atau belum ada data tutor sama sekali.
                                    Tambahkan data tutor terlebih dahulu, lalu kembali ke halaman ini.
                                </p>
                                <a href="{{ route('tutors.create') }}" class="mt-2 inline-flex items-center gap-1 text-xs font-semibold text-primary-dark hover:underline">
                                    <x-icon name="plus" class="w-3 h-3" />
                                    Tambah Tutor
                                </a>
                            </div>
                        @else
                            <select id="tutor_id" name="tutor_id" class="input w-full" x-model="tutorId"
                                    x-bind:required="role === 'tutor' || role === 'wali-kelas'"
                                    x-bind:disabled="role !== 'tutor' && role !== 'wali-kelas'">
                                <option value="">-- Pilih Tutor --</option>
                                @foreach ($unlinkedTutors as $tutor)
                                    <option value="{{ $tutor->id }}" {{ (string) old('tutor_id', $user->tutor?->id) === (string) $tutor->id ? 'selected' : '' }}>
                                        {{ $tutor->nama_lengkap }} — NIP {{ $tutor->nomor_induk }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-muted mt-1.5">
                                Wajib untuk role Tutor dan Wali Kelas: akun memakai profil ini untuk mengakses kelas dan penugasan mengajar.
                                Hanya profil yang belum tertaut ke akun lain yang ditampilkan ({{ $unlinkedTutors->count() }} tersedia).
                            </p>
                        @endif

                        @error('tutor_id')
                            <p class="mt-1 text-xs text-accent font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Roles tanpa profil --}}
                    <div x-show="role !== '' && role !== 'peserta-didik' && role !== 'tutor' && role !== 'wali-kelas'" x-cloak class="mt-4 pt-4 border-t border-border">
                        <p class="text-sm text-muted">Role ini tidak memerlukan profil siswa atau tutor.</p>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <a href="{{ route('users.show', $user) }}" class="btn-secondary">Batal</a>
                    <button type="submit" class="btn-primary">
                        <x-icon name="pencil" class="w-4 h-4" />
                        Simpan Perubahan
                    </button>
                </div>
            </form>

            <!-- Reset Password & Hapus -->
            <div class="mt-8 pt-6 border-t border-border">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="border border-amber-200 rounded-xl p-4 bg-amber-50">
                        <h4 class="font-semibold text-amber-800 mb-3 flex items-center gap-2">
                            <x-icon name="lock" class="w-4 h-4" />
                            Reset Password
                        </h4>
                        <p class="text-sm text-amber-700 mb-3">Mengubah password akan memaksa pengguna login ulang. Sampaikan password baru secara aman.</p>
                        <form method="POST" action="{{ route('users.reset-password', $user) }}" class="space-y-3" onsubmit="return confirm('Yakin reset password akun ini?');">
                            @csrf
                            <div class="space-y-2">
                                <label class="label">Password Baru</label>
                                <x-input type="password" name="password" required placeholder="Minimal 8 karakter" />
                                @error('password')
                                    <p class="text-xs text-accent">{{ $message }}</p>
                                @enderror
                                <label class="label">Konfirmasi Password</label>
                                <x-input type="password" name="password_confirmation" required placeholder="Ulangi password" />
                            </div>
                            <button type="submit" class="btn-secondary w-full">
                                <x-icon name="lock" class="w-4 h-4" />
                                Reset Password
                            </button>
                        </form>
                    </div>

                    <div class="border border-accent/20 rounded-xl p-4 bg-accent-soft/50">
                        <h4 class="font-semibold text-accent mb-3 flex items-center gap-2">
                            <x-icon name="trash" class="w-4 h-4" />
                            Hapus Akun
                        </h4>
                        <p class="text-sm text-accent/80 mb-3">Menghapus akun ini permanen. Pastikan akun tidak memiliki relasi ke data akademik.</p>
                        <form method="POST" action="{{ route('users.destroy', $user) }}" class="inline" onsubmit="return confirm('PERINGATAN: Menghapus akun tidak bisa dibatalkan. Lanjutkan?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-secondary w-full text-accent hover:bg-accent-soft">
                                <x-icon name="trash" class="w-4 h-4" />
                                Hapus Akun Permanen
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>