<x-app-layout>
    <x-slot name="title">Upload Dokumen</x-slot>

    <div class="max-w-3xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-navy">Upload Dokumen Baru</h2>
            <a href="{{ route('documents.index') }}" class="btn-secondary">Kembali</a>
        </div>

        <div class="card p-6">
            <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6" id="documentForm">
                @csrf

                <!-- Mode Selection (only for users with documents.manage) -->
                @if(auth()->user()->can('documents.manage'))
                <div x-data="{ mode: '{{ old('mode', $defaultMode) }}' }" class="space-y-4">
                    <x-input-label for="mode" value="Mode Pencatatan" />
                    <div class="flex flex-col sm:flex-row gap-4 mt-1">
                        <label class="flex items-center gap-2 cursor-pointer flex-1 p-3 border rounded-xl hover:bg-primary-soft transition-colors" :class="mode === 'manual' ? 'border-primary bg-primary-soft' : 'border-border'">
                            <input type="radio" name="mode" value="manual" x-model="mode" class="sr-only peer" {{ old('mode', $defaultMode) === 'manual' ? 'checked' : '' }} required>
                            <div class="w-full text-center">
                                <x-icon name="file" class="w-6 h-6 mx-auto text-primary" />
                                <p class="text-sm font-medium text-navy mt-1">Dokumen Manual / Umum</p>
                                <p class="text-xs text-muted">Tanpa tautan ke profil siswa</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer flex-1 p-3 border rounded-xl hover:bg-primary-soft transition-colors" :class="mode === 'siswa' ? 'border-primary bg-primary-soft' : 'border-border'">
                            <input type="radio" name="mode" value="siswa" x-model="mode" class="sr-only peer" {{ old('mode', $defaultMode) === 'siswa' ? 'checked' : '' }}>
                            <div class="w-full text-center">
                                <x-icon name="user" class="w-6 h-6 mx-auto text-primary" />
                                <p class="text-sm font-medium text-navy mt-1">Peserta Didik</p>
                                <p class="text-xs text-muted">Kaitkan dengan profil siswa</p>
                            </div>
                        </label>
                    </div>
                    <x-input-error :messages="$errors->get('mode')" class="mt-2" />
                </div>
                @else
                    @if($students->isNotEmpty())
                        <input type="hidden" name="mode" value="siswa">
                        <input type="hidden" name="student_id" value="{{ $students->first()->id }}">
                    @endif
                @endif

                <!-- Peserta Didik Dropdown (shown when mode = siswa) -->
                @if(auth()->user()->can('documents.manage'))
                <div x-show="mode === 'siswa'" x-transition class="space-y-2" id="studentField">
                    <x-input-label for="student_id" value="Pilih Peserta Didik" />
                    <x-select id="student_id" name="student_id" class="w-full mt-1">
                        <option value="">- Pilih Peserta Didik -</option>
                        @foreach($students as $s)
                            <option value="{{ $s->id }}" {{ old('student_id') == $s->id ? 'selected' : '' }}>{{ $s->nama_lengkap }} (NIS: {{ $s->nis }})</option>
                        @endforeach
                    </x-select>
                    <x-input-error :messages="$errors->get('student_id')" class="mt-2" />
                </div>
                @else
                    @if($students->isNotEmpty())
                        <input type="hidden" name="student_id" value="{{ $students->first()->id }}">
                    @endif
                @endif

                <!-- Optional Catatan for Manual mode -->
                @if(auth()->user()->can('documents.manage'))
                <div x-show="mode === 'manual'" x-transition class="space-y-2" id="catatanField">
                    <x-input-label for="catatan" value="Keterangan / Catatan (Opsional)" />
                    <textarea id="catatan" name="catatan" rows="2" class="w-full mt-1 rounded-xl border-border focus:border-primary focus:ring focus:ring-primary/20" placeholder="Mis: Nomor referensi, nama pemilik, atau informasi identifikasi lain...">{{ old('catatan') }}</textarea>
                    <p class="text-xs text-muted">Catatan ini akan ditampilkan sebagai informasi pemilik dokumen manual.</p>
                </div>
                @endif

                <div>
                    <x-input-label for="jenis_dokumen" value="Jenis Dokumen" />
                    <x-select id="jenis_dokumen" name="jenis_dokumen" class="w-full mt-1" required>
                        <option value="">Pilih Jenis</option>
                        @foreach(\App\Models\Document::jenisOptions() as $val => $label)
                            <option value="{{ $val }}" {{ old('jenis_dokumen') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </x-select>
                    <x-input-error :messages="$errors->get('jenis_dokumen')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="file" value="Pilih File (PDF, JPG, PNG - Max 2MB)" />
                    <input id="file" name="file" type="file" required
                        class="w-full mt-1 text-sm text-muted file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-soft file:text-primary hover:file:bg-primary/20" />
                    <x-input-error :messages="$errors->get('file')" class="mt-2" />
                </div>

                <div class="flex justify-end pt-4 border-t border-border">
                    <x-primary-button type="submit">Upload Dokumen</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>