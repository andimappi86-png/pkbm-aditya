@props([
    'targetClassroom',
    'scheduleId' => null,
    'tanggal' => null,
    'redirectTo' => null,
])

@php
    $redirectTo = $redirectTo ?? request()->fullUrl();
@endphp

<!-- MODAL TAMBAH SISWA KE KELAS (3 METODE) -->
<div x-show="showAddModal"
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     aria-labelledby="modal-title"
     role="dialog"
     aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Backdrop -->
        <div x-show="showAddModal"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 transition-opacity bg-slate-900/50 backdrop-blur-xs"
             @click="showAddModal = false"
             aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Dialog Box -->
        <div x-show="showAddModal"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="inline-block w-full max-w-2xl text-left align-bottom transition-all transform bg-white rounded-xl shadow-xl sm:my-8 sm:align-middle border border-slate-200 max-h-[90vh] overflow-hidden flex flex-col">

            <!-- Modal Header -->
            <div class="px-5 py-4 bg-slate-50/80 border-b border-slate-200 flex items-center justify-between shrink-0">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <x-icon name="user-plus" class="w-4 h-4 text-teal-700 shrink-0" />
                        <span>Tambah Warga Belajar ke Kelas</span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Rombel: <span class="font-semibold text-slate-800">{{ $targetClassroom->nama_rombel }}</span>
                        &bull; {{ $targetClassroom->program->nama ?? 'Program' }}
                    </p>
                </div>
                <button type="button"
                        @click="showAddModal = false"
                        class="p-1 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition-colors">
                    <x-icon name="x" class="w-5 h-5" />
                </button>
            </div>

            <!-- Segmented Method Switcher -->
            <div class="p-3 bg-white border-b border-slate-200 shrink-0">
                <div class="grid grid-cols-3 gap-1 p-1 bg-slate-100 rounded-lg text-xs font-semibold">
                    <button type="button"
                            @click="addMethod = 'pilih'"
                            :class="addMethod === 'pilih' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                            class="py-2 px-2 rounded-md transition-colors text-center whitespace-nowrap shrink-0 flex items-center justify-center gap-1.5">
                        <x-icon name="users" class="w-3.5 h-3.5" />
                        <span>1. Pilih Terdaftar</span>
                    </button>
                    <button type="button"
                            @click="addMethod = 'cepat'"
                            :class="addMethod === 'cepat' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                            class="py-2 px-2 rounded-md transition-colors text-center whitespace-nowrap shrink-0 flex items-center justify-center gap-1.5">
                        <x-icon name="plus-circle" class="w-3.5 h-3.5" />
                        <span>2. Input Cepat (10d)</span>
                    </button>
                    <button type="button"
                            @click="addMethod = 'bulk'"
                            :class="addMethod === 'bulk' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                            class="py-2 px-2 rounded-md transition-colors text-center whitespace-nowrap shrink-0 flex items-center justify-center gap-1.5">
                        <x-icon name="clipboard-list" class="w-3.5 h-3.5" />
                        <span>3. Paste Massal</span>
                    </button>
                </div>
            </div>

            <!-- Modal Content (Scrollable) -->
            <div class="p-5 overflow-y-auto flex-1 text-xs sm:text-sm">
                <!-- TAB 1: PILIH DARI DATA WARGA BELAJAR (BELUM MASUK KELAS) -->
                <div x-show="addMethod === 'pilih'" class="space-y-4">
                    <div class="flex flex-col sm:flex-row gap-2 items-stretch sm:items-center justify-between">
                        <div class="relative flex-1">
                            <input type="text"
                                   x-model="searchUnassigned"
                                   placeholder="Cari nama atau NISN siswa..."
                                   class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg border border-slate-300 focus:outline-hidden focus:border-teal-600 focus:ring-1 focus:ring-teal-600" />
                            <x-icon name="search" class="w-4 h-4 text-slate-400 absolute left-2.5 top-2" />
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <button type="button"
                                    @click="toggleSelectAllUnassigned()"
                                    class="px-2.5 py-1.5 text-xs font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors whitespace-nowrap shrink-0">
                                <span x-text="selectedStudentIds.length === filteredUnassigned.length && filteredUnassigned.length > 0 ? 'Batal Pilih Semua' : 'Pilih Semua'"></span>
                            </button>
                            <button type="button"
                                    @click="fetchUnassigned()"
                                    title="Segarkan daftar"
                                    class="p-1.5 text-slate-600 hover:bg-slate-100 rounded-lg border border-slate-300">
                                <x-icon name="refresh" class="w-3.5 h-3.5" />
                            </button>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('rombel.students.assign', $targetClassroom) }}">
                        @csrf
                        <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">
                        @if($scheduleId)
                            <input type="hidden" name="schedule_id" value="{{ $scheduleId }}">
                        @endif
                        @if($tanggal)
                            <input type="hidden" name="tanggal" value="{{ $tanggal }}">
                        @endif

                        <template x-for="id in selectedStudentIds" :key="id">
                            <input type="hidden" name="student_ids[]" :value="id">
                        </template>

                        <!-- List Warga Belajar -->
                        <div class="border border-slate-200 rounded-lg overflow-hidden max-h-60 overflow-y-auto divide-y divide-slate-100">
                            <div x-show="loadingUnassigned" class="p-6 text-center text-slate-500 text-xs">
                                Memuat daftar siswa yang belum memiliki kelas...
                            </div>
                            <div x-show="!loadingUnassigned && filteredUnassigned.length === 0" class="p-6 text-center text-slate-500 text-xs">
                                Tidak ada siswa terdaftar yang belum memiliki kelas (atau tidak cocok dengan pencarian).
                            </div>

                            <template x-for="student in filteredUnassigned" :key="student.id">
                                <label class="flex items-center gap-3 p-2.5 hover:bg-slate-50 cursor-pointer select-none transition-colors">
                                    <input type="checkbox"
                                           :value="student.id"
                                           x-model="selectedStudentIds"
                                           class="rounded border-slate-300 text-teal-700 focus:ring-teal-600 w-4 h-4 shrink-0" />
                                    <div class="flex-1 min-w-0">
                                        <div class="font-semibold text-slate-900 truncate" x-text="student.nama_lengkap"></div>
                                        <div class="text-[11px] text-slate-500 flex items-center gap-2 mt-0.5">
                                            <span class="font-mono" x-text="student.nis || student.nisn || 'Tanpa NISN'"></span>
                                            <span>&bull;</span>
                                            <span x-text="student.jenis_kelamin || '-'"></span>
                                            <span x-show="student.nik">&bull;</span>
                                            <span x-show="student.nik" class="font-mono text-slate-400" x-text="'NIK: ' + student.nik"></span>
                                        </div>
                                    </div>
                                </label>
                            </template>
                        </div>

                        <!-- Footer Actions -->
                        <div class="mt-4 pt-3 border-t border-slate-200 flex items-center justify-between">
                            <div class="text-xs text-slate-600">
                                Terpilih: <strong class="text-teal-800 tabular-nums" x-text="selectedStudentIds.length"></strong> siswa
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button"
                                        @click="showAddModal = false"
                                        class="px-3 py-1.5 text-xs font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors whitespace-nowrap shrink-0">
                                    Batal
                                </button>
                                <button type="submit"
                                        :disabled="selectedStudentIds.length === 0"
                                        class="px-4 py-1.5 text-xs font-semibold text-white bg-teal-700 hover:bg-teal-800 disabled:opacity-50 disabled:cursor-not-allowed rounded-lg transition-colors whitespace-nowrap shrink-0 shadow-2xs">
                                    Tambahkan ke Kelas Terpilih
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- TAB 2: INPUT CEPAT SISWA BARU (10 DETIK) -->
                <div x-show="addMethod === 'cepat'" class="space-y-4" style="display: none;">
                    <form method="POST" action="{{ route('rombel.students.quick-store', $targetClassroom) }}" class="space-y-3.5">
                        @csrf
                        <input type="hidden" name="rombel_id" value="{{ $targetClassroom->id }}">
                        <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">
                        @if($scheduleId)
                            <input type="hidden" name="schedule_id" value="{{ $scheduleId }}">
                        @endif
                        @if($tanggal)
                            <input type="hidden" name="tanggal" value="{{ $tanggal }}">
                        @endif

                        <!-- 1. Nama Lengkap -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-800 mb-1">
                                Nama Lengkap Warga Belajar <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   name="name"
                                   required
                                   maxlength="150"
                                   placeholder="Contoh: Muhammad Ilham"
                                   class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 focus:outline-hidden focus:border-teal-600 focus:ring-1 focus:ring-teal-600" />
                        </div>

                        <!-- 2. NISN / No. Induk + Tombol Nomor Sementara -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-semibold text-slate-800">
                                    NISN / Nomor Induk Warga Belajar <span class="text-red-500">*</span>
                                </label>
                                <button type="button"
                                        @click="generateTempNisn()"
                                        class="text-[11px] font-semibold text-teal-700 hover:text-teal-900 underline flex items-center gap-1">
                                    <x-icon name="sparkles" class="w-3 h-3" />
                                    <span>Buat Nomor Sementara</span>
                                </button>
                            </div>
                            <input type="text"
                                   name="nisn"
                                   x-model="tempNisn"
                                   required
                                   maxlength="30"
                                   placeholder="10 digit NISN atau nomor identitas siswa"
                                   class="w-full px-3 py-2 text-xs font-mono rounded-lg border border-slate-300 focus:outline-hidden focus:border-teal-600 focus:ring-1 focus:ring-teal-600 tabular-nums" />
                            <p class="text-[11px] text-slate-400 mt-1">Gunakan tombol "Buat Nomor Sementara" jika siswa baru belum memiliki NISN resmi dari Dapodik/Kemdikbud.</p>
                        </div>

                        <!-- 3. Jenis Kelamin -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-800 mb-1">
                                Jenis Kelamin <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-2 gap-2">
                                <label class="flex items-center gap-2 p-2 border border-slate-300 rounded-lg cursor-pointer hover:bg-slate-50 transition-colors">
                                    <input type="radio" name="gender" value="L" checked class="text-teal-700 focus:ring-teal-600" />
                                    <span class="text-xs font-medium text-slate-800">Laki-laki (L)</span>
                                </label>
                                <label class="flex items-center gap-2 p-2 border border-slate-300 rounded-lg cursor-pointer hover:bg-slate-50 transition-colors">
                                    <input type="radio" name="gender" value="P" class="text-teal-700 focus:ring-teal-600" />
                                    <span class="text-xs font-medium text-slate-800">Perempuan (P)</span>
                                </label>
                            </div>
                        </div>

                        <!-- 4. Status Masuk & Kontak -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-800 mb-1">
                                    Status Masuk
                                </label>
                                <select name="status_masuk" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 focus:outline-hidden focus:border-teal-600 focus:ring-1 focus:ring-teal-600">
                                    <option value="Warga Belajar Baru">Warga Belajar Baru</option>
                                    <option value="Pindahan / Mutasi Masuk">Pindahan / Mutasi Masuk</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-800 mb-1">
                                    No. WhatsApp / Orang Tua (Opsional)
                                </label>
                                <input type="tel"
                                       name="kontak"
                                       maxlength="30"
                                       placeholder="Contoh: 08123456789"
                                       class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 focus:outline-hidden focus:border-teal-600 focus:ring-1 focus:ring-teal-600" />
                            </div>
                        </div>

                        <!-- Footer Actions -->
                        <div class="mt-4 pt-3 border-t border-slate-200 flex items-center justify-end gap-2">
                            <button type="button"
                                    @click="showAddModal = false"
                                    class="px-3 py-1.5 text-xs font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors whitespace-nowrap shrink-0">
                                Batal
                            </button>
                            <button type="submit"
                                    class="px-4 py-1.5 text-xs font-semibold text-white bg-teal-700 hover:bg-teal-800 rounded-lg transition-colors whitespace-nowrap shrink-0 shadow-2xs">
                                Simpan & Tambahkan ke Kelas
                            </button>
                        </div>
                    </form>
                </div>

                <!-- TAB 3: TAMBAH MASSAL (COPY-PASTE EXCEL / WHATSAPP) -->
                <div x-show="addMethod === 'bulk'" class="space-y-4" style="display: none;">
                    <div class="p-3 bg-amber-50/70 border border-amber-200 rounded-lg text-xs text-amber-900">
                        <div class="font-semibold mb-1">Petunjuk Format Tempel (1 siswa per baris):</div>
                        <ul class="list-disc list-inside space-y-0.5 text-[11px] text-amber-800">
                            <li>Format 1: <code class="font-mono bg-white px-1 py-0.5 rounded border border-amber-200">Nama Lengkap</code> (nomor identitas dibuatkan otomatis)</li>
                            <li>Format 2: <code class="font-mono bg-white px-1 py-0.5 rounded border border-amber-200">NISN, Nama Lengkap, L/P</code></li>
                            <li>Format 3: <code class="font-mono bg-white px-1 py-0.5 rounded border border-amber-200">Nama Lengkap, Nomor Identitas, L/P, No HP, Status</code></li>
                        </ul>
                    </div>

                    <form method="POST" action="{{ route('rombel.students.bulk-store', $targetClassroom) }}" class="space-y-3">
                        @csrf
                        <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">
                        @if($scheduleId)
                            <input type="hidden" name="schedule_id" value="{{ $scheduleId }}">
                        @endif
                        @if($tanggal)
                            <input type="hidden" name="tanggal" value="{{ $tanggal }}">
                        @endif

                        <div>
                            <textarea name="raw_data"
                                      x-model="bulkRawText"
                                      rows="6"
                                      placeholder="Tempelkan daftar siswa di sini...&#10;Contoh:&#10;Ahmad Fauzan&#10;0051234567, Siti Nurhaliza, P&#10;Budi Santoso, 0059876543, L, 0812345678, Pindahan"
                                      class="w-full px-3 py-2 text-xs font-mono rounded-lg border border-slate-300 focus:outline-hidden focus:border-teal-600 focus:ring-1 focus:ring-teal-600"></textarea>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button"
                                    @click="validateBulkPreview()"
                                    :disabled="!bulkRawText.trim() || bulkLoading"
                                    class="px-3 py-1.5 text-xs font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 disabled:opacity-50 rounded-lg transition-colors whitespace-nowrap shrink-0 flex items-center gap-1.5">
                                <x-icon name="eye" class="w-3.5 h-3.5" />
                                <span x-text="bulkLoading ? 'Memeriksa...' : 'Pratinjau / Periksa Data'"></span>
                            </button>
                            <span x-show="bulkErrorMsg" class="text-xs text-red-600 font-medium" x-text="bulkErrorMsg"></span>
                        </div>

                        <!-- Preview Table -->
                        <div x-show="bulkPreviewData" class="border border-slate-200 rounded-lg overflow-hidden max-h-48 overflow-y-auto">
                            <table class="w-full text-left border-collapse text-[11px]">
                                <thead class="bg-slate-50 border-b border-slate-200 font-semibold text-slate-600">
                                    <tr>
                                        <th class="px-2.5 py-1.5">No</th>
                                        <th class="px-2.5 py-1.5">Nama Siswa</th>
                                        <th class="px-2.5 py-1.5">Nomor Identitas</th>
                                        <th class="px-2.5 py-1.5">L/P</th>
                                        <th class="px-2.5 py-1.5">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <template x-for="row in (bulkPreviewData?.rows || [])" :key="row.index">
                                        <tr :class="row.is_valid ? 'hover:bg-slate-50' : 'bg-red-50/50'">
                                            <td class="px-2.5 py-1 text-slate-400 font-mono" x-text="row.index"></td>
                                            <td class="px-2.5 py-1 font-medium text-slate-800" x-text="row.nama_lengkap"></td>
                                            <td class="px-2.5 py-1 font-mono text-slate-600" x-text="row.nomor_identitas"></td>
                                            <td class="px-2.5 py-1 text-center" x-text="row.jenis_kelamin === 'Laki-laki' ? 'L' : 'P'"></td>
                                            <td class="px-2.5 py-1">
                                                <span x-show="row.is_valid" class="text-emerald-700 font-semibold">Siap Ditambah</span>
                                                <span x-show="!row.is_valid" class="text-red-600 font-medium" x-text="row.error"></span>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>

                        <!-- Footer Actions -->
                        <div class="mt-4 pt-3 border-t border-slate-200 flex items-center justify-between">
                            <div class="text-xs text-slate-600" x-show="bulkPreviewData">
                                Siap diproses: <strong class="text-emerald-700" x-text="bulkPreviewData?.valid_count || 0"></strong> siswa
                            </div>
                            <div class="flex items-center gap-2 ml-auto">
                                <button type="button"
                                        @click="showAddModal = false"
                                        class="px-3 py-1.5 text-xs font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors whitespace-nowrap shrink-0">
                                    Batal
                                </button>
                                <button type="submit"
                                        :disabled="!bulkRawText.trim() || (bulkPreviewData && bulkPreviewData.valid_count === 0)"
                                        class="px-4 py-1.5 text-xs font-semibold text-white bg-teal-700 hover:bg-teal-800 disabled:opacity-50 disabled:cursor-not-allowed rounded-lg transition-colors whitespace-nowrap shrink-0 shadow-2xs">
                                    Proses & Tambahkan Semua
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL KONFIRMASI KELUARKAN SISWA -->
<div x-show="confirmRemoveModal"
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     aria-labelledby="modal-remove-title"
     role="dialog"
     aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="confirmRemoveModal"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 transition-opacity bg-slate-900/50 backdrop-blur-xs"
             @click="confirmRemoveModal = false"
             aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div x-show="confirmRemoveModal"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-xl shadow-xl border border-slate-200">

            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-600 shrink-0">
                    <x-icon name="alert-triangle" class="w-5 h-5" />
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900" id="modal-remove-title">
                        Keluarkan Siswa dari Kelas?
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Konfirmasi pengeluaran peserta didik dari rombel ini.
                    </p>
                </div>
            </div>

            <div class="mt-4 p-3 bg-slate-50 rounded-lg border border-slate-200 text-xs text-slate-700">
                Siswa <strong class="text-slate-900" x-text="studentToRemove?.name"></strong> akan dikeluarkan dari rombel <strong>{{ $targetClassroom->nama_rombel }}</strong>.
                Data siswa tetap tersimpan di pangkalan data dan dapat dimasukkan kembali sewaktu-waktu.
            </div>

            <form method="POST" :action="removeActionUrl" class="mt-5 flex items-center justify-end gap-2">
                @csrf
                <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">
                <button type="button"
                        @click="confirmRemoveModal = false"
                        class="px-3.5 py-1.5 text-xs font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors whitespace-nowrap shrink-0">
                    Batal
                </button>
                <button type="submit"
                        class="px-4 py-1.5 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors whitespace-nowrap shrink-0 shadow-2xs">
                    Ya, Keluarkan Siswa
                </button>
            </form>
        </div>
    </div>
</div>
