<x-app-layout>
    <x-slot name="title">Buat Akun Pengguna</x-slot>

    @php
        $studentOptions = $unlinkedStudents->map(fn ($student) => [
            'id' => $student->id,
            'label' => $student->nama_lengkap,
            'meta' => 'NIS ' . $student->nis . ($student->classroom?->nama_rombel ? ' — Rombel ' . $student->classroom->nama_rombel : ''),
        ])->values();

        $tutorOptions = $unlinkedTutors->map(fn ($tutor) => [
            'id' => $tutor->id,
            'label' => $tutor->nama_lengkap,
            'meta' => 'NIP ' . $tutor->nomor_induk,
        ])->values();

        $initialPathway = old('_pathway') === 'new' && $canRegisterStudent ? 'new' : 'link';

        $formPayload = [
            'students' => $studentOptions,
            'tutors' => $tutorOptions,
            'roleLabels' => $roleLabels,
            'programs' => $programs->pluck('nama', 'id'),
            'academicYears' => $academicYears->pluck('nama', 'id'),
            'old' => [
                'name' => old('name'),
                'email' => old('email'),
                'phone' => old('phone'),
                'role' => old('role'),
                'student_id' => old('student_id', $preselectedStudentId),
                'tutor_id' => old('tutor_id'),
                'nis' => old('nis'),
                'program_id' => old('program_id'),
            ],
        ];
    @endphp

    <script type="application/json" id="user-account-form-data">{!! json_encode($formPayload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>

    <script>
        (function () {
            const source = document.getElementById('user-account-form-data');
            const data = source ? JSON.parse(source.textContent) : {};
            const students = data.students || [];
            const tutors = data.tutors || [];
            const roleLabels = data.roleLabels || {};
            const old = data.old || {};

            function findLabel(list, id) {
                const found = list.find((item) => String(item.id) === String(id));

                return found ? found.label : '';
            }

            window.userAccountProfileForm = function () {
                return {
                    name: old.name || '',
                    email: old.email || '',
                    role: old.role || (old.student_id ? 'peserta-didik' : ''),
                    query: '',
                    studentId: old.student_id ? String(old.student_id) : '',
                    tutorId: old.tutor_id ? String(old.tutor_id) : '',
                    students: students,
                    tutors: tutors,
                    roleLabels: roleLabels,
                    get filteredStudents() {
                        const keyword = this.query.trim().toLowerCase();

                        if (keyword === '') {
                            return this.students.slice(0, 15);
                        }

                        return this.students.filter((student) => {
                            return student.label.toLowerCase().includes(keyword)
                                || String(student.meta).toLowerCase().includes(keyword);
                        }).slice(0, 15);
                    },
                    get studentLabel() {
                        return findLabel(this.students, this.studentId);
                    },
                    get tutorLabel() {
                        return findLabel(this.tutors, this.tutorId);
                    },
                    get roleLabel() {
                        if (!this.role) {
                            return 'Belum dipilih';
                        }

                        return this.roleLabels[this.role] || this.role;
                    },
                    get profileLabel() {
                        if (this.role === 'peserta-didik') {
                            return this.studentLabel || 'Belum dipilih';
                        }

                        if (this.role === 'tutor') {
                            return this.tutorLabel || 'Belum dipilih';
                        }

                        return this.role === '' ? 'Menunggu pilihan role' : 'Tidak diperlukan';
                    },
                    pickStudent(id) {
                        this.studentId = String(id);
                    },
                };
            };

            window.userAccountNewStudentForm = function () {
                return {
                    name: old.name || '',
                    email: old.email || '',
                    nis: old.nis || '',
                    programId: old.program_id ? String(old.program_id) : '',
                    get programLabel() {
                        return (data.programs || {})[this.programId] || 'Belum dipilih';
                    },
                };
            };
        })();
    </script>

    <div class="max-w-4xl mx-auto space-y-6"
         x-data="{ pathway: '{{ $initialPathway }}' }"
         x-on:switch-pathway.window="pathway = $event.detail.pathway">

        <x-breadcrumb :items="[
            ['label' => 'Sistem', 'url' => route('users.index')],
            ['label' => 'Manajemen Pengguna', 'url' => route('users.index')],
            ['label' => 'Buat Akun']
        ]" />

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-xl font-bold text-navy">Buat Akun Pengguna</h1>
                <p class="text-sm text-muted">Pilih cara pembuatan akun sesuai kondisi data di sekolah.</p>
            </div>
            <a href="{{ route('users.index') }}" class="btn-secondary text-sm">Kembali</a>
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

        {{-- Pemilih jalur --}}
        <div class="card p-4">
            <p class="text-xs font-semibold uppercase tracking-wider text-muted mb-3">Langkah 1 — Pilih cara pembuatan akun</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <button type="button"
                        x-on:click="pathway = 'link'"
                        x-bind:class="pathway === 'link' ? 'border-primary bg-primary-soft' : 'border-border bg-surface hover:border-primary/40'"
                        class="text-left rounded-xl border p-4 transition-colors">
                    <span class="block text-sm font-semibold text-navy">Data siswa/tutor sudah ada</span>
                    <span class="mt-1 block text-xs text-muted">
                        Buat akun login, lalu hubungkan ke profil siswa atau tutor yang sudah terdaftar.
                    </span>
                </button>

                @if ($canRegisterStudent)
                    <button type="button"
                            x-on:click="pathway = 'new'"
                            x-bind:class="pathway === 'new' ? 'border-primary bg-primary-soft' : 'border-border bg-surface hover:border-primary/40'"
                            class="text-left rounded-xl border p-4 transition-colors">
                        <span class="block text-sm font-semibold text-navy">Siswa baru</span>
                        <span class="mt-1 block text-xs text-muted">
                            Isi data siswa sekaligus membuat akun login. Keduanya disimpan bersama.
                        </span>
                    </button>
                @else
                    <div class="rounded-xl border border-dashed border-border bg-background p-4">
                        <span class="block text-sm font-semibold text-muted">Siswa baru</span>
                        <span class="mt-1 block text-xs text-muted">
                            Akun Anda tidak memiliki izin menambah data peserta didik. Hubungi operator lain.
                        </span>
                    </div>
                @endif
            </div>
        </div>

        {{-- JALUR A — tautkan ke profil yang sudah ada --}}
        <form method="POST" action="{{ route('users.store') }}" class="space-y-6" x-show="pathway === 'link'" x-data="userAccountProfileForm()">
            @csrf
            <input type="hidden" name="_pathway" value="link">

            <div class="card p-6 space-y-6">
                <div>
                    <h2 class="text-sm font-semibold text-navy uppercase tracking-wider">A. Informasi akun</h2>
                    <p class="text-xs text-muted mt-1">Kredensial yang dipakai pengguna untuk masuk ke sistem.</p>
                    <div class="mt-4">
                        @include('users.partials.account-fields')
                    </div>
                </div>

                <div class="border-t border-border pt-6">
                    <h2 class="text-sm font-semibold text-navy uppercase tracking-wider">B. Hubungkan profil</h2>
                    <p class="text-xs text-muted mt-1">
                        Penautan membuat akun dapat membaca data siswa/tutor yang bersangkutan. Satu profil hanya boleh tertaut ke satu akun.
                    </p>

                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="label" for="role">Role <span class="text-accent">*</span></label>
                            <select id="role" name="role" x-model="role" class="input w-full" required>
                                <option value="">-- Pilih Role --</option>
                                @foreach ($roles as $roleOption)
                                    <option value="{{ $roleOption->name }}" {{ old('role') === $roleOption->name ? 'selected' : '' }}>
                                        {{ $roleLabels[$roleOption->name] ?? ucwords(str_replace('-', ' ', $roleOption->name)) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role')
                                <p class="mt-1 text-xs text-accent">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-muted mt-1.5">Role menentukan modul yang dapat diakses akun.</p>
                        </div>

                        {{-- Profil peserta didik: pencarian nama / NIS --}}
                        <div x-show="role === 'peserta-didik'" x-cloak>
                            <label class="label" for="student-search">
                                Pilih Siswa
                                <span class="inline-flex items-center rounded-full border border-accent/20 bg-accent-soft px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-accent align-middle">Wajib</span>
                            </label>

                            @if ($unlinkedStudents->isEmpty())
                                <div class="rounded-xl border border-amber-200 bg-amber-50 p-3">
                                    <p class="text-sm font-medium text-amber-800">Belum ada profil siswa yang bisa ditautkan.</p>
                                    <p class="mt-1 text-xs text-amber-700">
                                        Semua data peserta didik sudah memiliki akun, atau belum ada data peserta didik sama sekali.
                                        Tambahkan data peserta didik terlebih dahulu, lalu kembali ke halaman ini.
                                    </p>
                                    <div class="mt-2 flex flex-wrap items-center gap-3">
                                        @if ($canRegisterStudent)
                                            <a href="{{ route('peserta-didik.create', ['from' => 'akun']) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-primary-dark hover:underline">
                                                <x-icon name="plus" class="w-3 h-3" />
                                                Tambah Peserta Didik
                                            </a>
                                            <button type="button" class="text-xs font-semibold text-primary-dark hover:underline"
                                                    x-on:click="$dispatch('switch-pathway', { pathway: 'new' })">
                                                Atau daftarkan siswa baru sekaligus akunnya
                                            </button>
                                        @else
                                            <p class="text-xs text-amber-700">
                                                Akun Anda belum memiliki izin menambah peserta didik. Hubungi operator yang berwenang untuk menambahkan data siswa.
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <input id="student-search" type="text" class="input w-full"
                                       placeholder="Cari nama atau NIS siswa..." autocomplete="off"
                                       x-model="query">

                                <div class="mt-2 max-h-56 overflow-y-auto rounded-xl border border-border bg-surface divide-y divide-border">
                                    <template x-for="student in filteredStudents" :key="student.id">
                                        <button type="button"
                                                class="w-full text-left px-3 py-2 transition-colors hover:bg-primary-soft"
                                                x-bind:class="String(student.id) === String(studentId) ? 'bg-primary-soft' : ''"
                                                x-on:click="pickStudent(student.id)">
                                            <span class="block text-sm font-medium text-navy" x-text="student.label"></span>
                                            <span class="block text-xs text-muted" x-text="student.meta"></span>
                                        </button>
                                    </template>
                                    <p class="px-3 py-3 text-sm text-muted" x-show="filteredStudents.length === 0">
                                        Tidak ada siswa yang cocok dengan pencarian.
                                    </p>
                                </div>

                                <input type="hidden" name="student_id" x-bind:value="studentId"
                                       x-bind:required="role === 'peserta-didik'"
                                       x-bind:disabled="role !== 'peserta-didik'">

                                <p class="mt-2 text-xs text-muted" x-show="!studentLabel">
                                    Belum ada siswa dipilih. {{ $unlinkedStudents->count() }} profil belum tertaut tersedia.
                                </p>
                                <p class="mt-2 text-sm font-medium text-primary-dark" x-show="studentLabel">
                                    Dipilih: <span x-text="studentLabel"></span>
                                </p>
                                <p class="text-xs text-muted mt-1">
                                    Hanya profil yang belum tertaut ke akun lain yang ditampilkan. Siswa yang sudah punya akun tidak muncul di daftar ini.
                                </p>
                            @endif

                            @error('student_id')
                                <p class="mt-1 text-xs text-accent font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Profil tutor --}}
                        <div x-show="role === 'tutor' || role === 'wali-kelas'" x-cloak>
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
                                        <option value="{{ $tutor->id }}" {{ (string) old('tutor_id') === (string) $tutor->id ? 'selected' : '' }}>
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

                        <div x-show="role !== '' && role !== 'peserta-didik' && role !== 'tutor' && role !== 'wali-kelas'" x-cloak>
                            <span class="label">Profil Terhubung</span>
                            <div class="rounded-xl border border-border bg-background p-3">
                                <p class="text-sm text-muted">Role ini tidak memerlukan profil siswa atau tutor.</p>
                                <p class="mt-1 text-xs text-muted">Akun langsung dapat digunakan tanpa penautan profil.</p>
                            </div>
                        </div>

                        <div x-show="role === ''" x-cloak>
                            <span class="label">Profil Terhubung</span>
                            <div class="rounded-xl border border-dashed border-border bg-background p-3">
                                <p class="text-sm text-muted">Pilih role terlebih dahulu.</p>
                                <p class="mt-1 text-xs text-muted">Persyaratan profil mengikuti role yang dipilih.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-t border-border pt-6">
                    <h2 class="text-sm font-semibold text-navy uppercase tracking-wider">C. Konfirmasi</h2>
                    <div class="mt-4 rounded-xl border border-border bg-background p-4">
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                            <div>
                                <dt class="text-xs text-muted">Nama akun</dt>
                                <dd class="font-medium text-navy" x-text="name || 'Belum diisi'"></dd>
                            </div>
                            <div>
                                <dt class="text-xs text-muted">Email login</dt>
                                <dd class="font-medium text-navy" x-text="email || 'Belum diisi'"></dd>
                            </div>
                            <div>
                                <dt class="text-xs text-muted">Role</dt>
                                <dd class="font-medium text-navy" x-text="roleLabel"></dd>
                            </div>
                            <div>
                                <dt class="text-xs text-muted">Profil terhubung</dt>
                                <dd class="font-medium text-navy" x-text="profileLabel"></dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <div class="flex justify-end gap-3 border-t border-border pt-4">
                    <a href="{{ route('users.index') }}" class="btn-secondary">Batal</a>
                    <button type="submit" class="btn-primary">
                        <x-icon name="plus" class="w-4 h-4" />
                        Simpan akun
                    </button>
                </div>
            </div>
        </form>

        {{-- JALUR B — siswa baru sekaligus akun --}}
        @if ($canRegisterStudent)
            @if ($programs->isEmpty() || $academicYears->isEmpty())
                <div class="card p-6" x-show="pathway === 'new'" x-cloak>
                    <x-alert variant="warning" title="Data dasar belum lengkap">
                        Jalur ini membutuhkan minimal satu program dan satu tahun ajaran yang aktif.
                        Lengkapi data berikut lebih dulu, lalu kembali ke halaman ini.
                    </x-alert>
                    <div class="mt-4 flex flex-wrap gap-3">
                        @if ($programs->isEmpty())
                            <a href="{{ route('programs.index') }}" class="btn-secondary text-sm">Kelola Program</a>
                        @endif
                        @if ($academicYears->isEmpty())
                            <a href="{{ route('academic-years.index') }}" class="btn-secondary text-sm">Kelola Tahun Ajaran</a>
                        @endif
                    </div>
                </div>
            @else
            <form method="POST" action="{{ route('users.store-with-student') }}" class="space-y-6" x-show="pathway === 'new'" x-data="userAccountNewStudentForm()">
                @csrf
                <input type="hidden" name="_pathway" value="new">

                <div class="card p-6 space-y-6">
                    <div>
                        <h2 class="text-sm font-semibold text-navy uppercase tracking-wider">A. Informasi akun</h2>
                        <p class="text-xs text-muted mt-1">
                            Nama lengkap di bawah dipakai sekaligus sebagai nama pada data siswa, jadi cukup diisi sekali.
                        </p>
                        <div class="mt-4">
                            @include('users.partials.account-fields')
                        </div>
                    </div>

                    <div class="border-t border-border pt-6">
                        <h2 class="text-sm font-semibold text-navy uppercase tracking-wider">B. Data siswa</h2>
                        <p class="text-xs text-muted mt-1">
                            Data minimum agar siswa tercatat di Buku Induk. Lengkapi data lain (orang tua, kesehatan, dokumen) setelah akun dibuat.
                        </p>

                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-input name="nis" label="NIS" :value="old('nis')" required placeholder="Nomor induk siswa" x-model="nis" />
                            <x-input name="nisn" label="NISN" :value="old('nisn')" placeholder="Opsional" />
                            <x-input name="nik" label="NIK" :value="old('nik')" required />
                            <x-select name="jenis_kelamin" label="Jenis kelamin" :options="['Laki-laki' => 'Laki-laki', 'Perempuan' => 'Perempuan']" :selected="old('jenis_kelamin')" required />
                            <x-input type="date" name="tanggal_lahir" label="Tanggal lahir" :value="old('tanggal_lahir')" />
                            <x-select name="program_id" label="Program" :options="$programs->pluck('nama', 'id')->toArray()" :selected="old('program_id')" required x-model="programId" />
                            <x-select name="tahun_ajaran_id" label="Tahun ajaran masuk" :options="$academicYears->pluck('nama', 'id')->toArray()" :selected="old('tahun_ajaran_id')" required />
                            <x-select name="rombel_id" label="Rombel / kelas" :options="$classrooms->pluck('nama_rombel', 'id')->toArray()" :selected="old('rombel_id')" />
                            <div class="md:col-span-2">
                                <x-textarea name="alamat" label="Alamat lengkap" :value="old('alamat')" required rows="2" />
                            </div>
                            <x-select name="student_status" label="Status peserta didik" :options="$studentStatuses" :selected="old('student_status', 'aktif')" required />
                        </div>
                    </div>

                    <div class="border-t border-border pt-6">
                        <h2 class="text-sm font-semibold text-navy uppercase tracking-wider">C. Konfirmasi</h2>
                        <div class="mt-4 rounded-xl border border-border bg-background p-4">
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                                <div>
                                    <dt class="text-xs text-muted">Nama siswa &amp; akun</dt>
                                    <dd class="font-medium text-navy" x-text="name || 'Belum diisi'"></dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-muted">Email login</dt>
                                    <dd class="font-medium text-navy" x-text="email || 'Belum diisi'"></dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-muted">NIS</dt>
                                    <dd class="font-medium text-navy" x-text="nis || 'Belum diisi'"></dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-muted">Program</dt>
                                    <dd class="font-medium text-navy" x-text="programLabel"></dd>
                                </div>
                            </dl>
                            <p class="mt-3 text-xs text-muted">
                                Role akun otomatis Peserta Didik. Data siswa dan akun disimpan bersama — bila salah satu gagal, tidak ada data yang tersimpan sebagian.
                            </p>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 border-t border-border pt-4">
                        <a href="{{ route('users.index') }}" class="btn-secondary">Batal</a>
                        <button type="submit" class="btn-primary">
                            <x-icon name="plus" class="w-4 h-4" />
                            Simpan siswa &amp; akun
                        </button>
                    </div>
                </div>
            </form>
            @endif
        @endif
    </div>
</x-app-layout>
