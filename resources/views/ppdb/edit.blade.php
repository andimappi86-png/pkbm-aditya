<x-app-layout>
    <x-slot name="title">Edit Data PPDB</x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-navy">Edit Pendaftaran: {{ $ppdb->nomor_pendaftaran }}</h2>
            <a href="{{ route('ppdb.show', $ppdb) }}" class="btn-secondary">Kembali</a>
        </div>

        <div class="card p-6">
            <form action="{{ route('ppdb.update', $ppdb) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf
                @method('PUT')
                
                <!-- Pilihan Program -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-navy border-b border-border pb-2">Pilihan Program & Tahun Ajaran</h3>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="tahun_ajaran_id" value="Tahun Ajaran" />
                            <x-select id="tahun_ajaran_id" name="tahun_ajaran_id" class="w-full mt-1" required>
                                <option value="">Pilih Tahun Ajaran</option>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}" {{ $ppdb->tahun_ajaran_id == $year->id ? 'selected' : '' }}>{{ $year->nama }}</option>
                                @endforeach
                            </x-select>
                        </div>
                        <div>
                            <x-input-label for="program_id" value="Program Pilihan" />
                            <x-select id="program_id" name="program_id" class="w-full mt-1" required>
                                <option value="">Pilih Program</option>
                                @foreach($programs as $program)
                                    <option value="{{ $program->id }}" {{ $ppdb->program_id == $program->id ? 'selected' : '' }}>{{ $program->nama }}</option>
                                @endforeach
                            </x-select>
                        </div>
                    </div>
                </div>

                <!-- Data Pribadi -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-navy border-b border-border pb-2">Data Pribadi Calon Peserta Didik</h3>
                    
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="nik" value="NIK" />
                            <x-text-input id="nik" name="nik" type="text" class="w-full mt-1" value="{{ old('nik', $ppdb->nik) }}" required maxlength="16" />
                        </div>
                        <div>
                            <x-input-label for="nisn" value="NISN (Jika Ada)" />
                            <x-text-input id="nisn" name="nisn" type="text" class="w-full mt-1" value="{{ old('nisn', $ppdb->nisn) }}" maxlength="10" />
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="nama_lengkap" value="Nama Lengkap" />
                            <x-text-input id="nama_lengkap" name="nama_lengkap" type="text" class="w-full mt-1" value="{{ old('nama_lengkap', $ppdb->nama_lengkap) }}" required />
                        </div>
                        <div>
                            <x-input-label for="nama_panggilan" value="Nama Panggilan" />
                            <x-text-input id="nama_panggilan" name="nama_panggilan" type="text" class="w-full mt-1" value="{{ old('nama_panggilan', $ppdb->nama_panggilan) }}" />
                        </div>
                    </div>

                    <div class="grid md:grid-cols-3 gap-4">
                        <div>
                            <x-input-label for="jenis_kelamin" value="Jenis Kelamin" />
                            <x-select id="jenis_kelamin" name="jenis_kelamin" class="w-full mt-1" required>
                                <option value="L" {{ $ppdb->jenis_kelamin == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="P" {{ $ppdb->jenis_kelamin == 'P' ? 'selected' : '' }}>Perempuan</option>
                            </x-select>
                        </div>
                        <div>
                            <x-input-label for="tempat_lahir" value="Tempat Lahir" />
                            <x-text-input id="tempat_lahir" name="tempat_lahir" type="text" class="w-full mt-1" value="{{ old('tempat_lahir', $ppdb->tempat_lahir) }}" required />
                        </div>
                        <div>
                            <x-input-label for="tanggal_lahir" value="Tanggal Lahir" />
                            <x-text-input id="tanggal_lahir" name="tanggal_lahir" type="date" class="w-full mt-1" value="{{ old('tanggal_lahir', $ppdb->tanggal_lahir->format('Y-m-d')) }}" required />
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="agama" value="Agama" />
                            <x-select id="agama" name="agama" class="w-full mt-1" required>
                                @foreach(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'] as $agama)
                                    <option value="{{ $agama }}" {{ $ppdb->agama == $agama ? 'selected' : '' }}>{{ $agama }}</option>
                                @endforeach
                            </x-select>
                        </div>
                    </div>

                    <div>
                        <x-input-label for="alamat" value="Alamat Lengkap" />
                        <textarea id="alamat" name="alamat" class="w-full mt-1 rounded-xl border-border focus:border-primary focus:ring focus:ring-primary/20 shadow-sm" rows="3" required>{{ old('alamat', $ppdb->alamat) }}</textarea>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <x-input-label for="rt" value="RT" />
                            <x-text-input id="rt" name="rt" type="text" class="w-full mt-1" value="{{ old('rt', $ppdb->rt) }}" />
                        </div>
                        <div>
                            <x-input-label for="rw" value="RW" />
                            <x-text-input id="rw" name="rw" type="text" class="w-full mt-1" value="{{ old('rw', $ppdb->rw) }}" />
                        </div>
                        <div>
                            <x-input-label for="kelurahan" value="Kelurahan/Desa" />
                            <x-text-input id="kelurahan" name="kelurahan" type="text" class="w-full mt-1" value="{{ old('kelurahan', $ppdb->kelurahan) }}" />
                        </div>
                        <div>
                            <x-input-label for="kecamatan" value="Kecamatan" />
                            <x-text-input id="kecamatan" name="kecamatan" type="text" class="w-full mt-1" value="{{ old('kecamatan', $ppdb->kecamatan) }}" />
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="kabupaten" value="Kabupaten/Kota" />
                            <x-text-input id="kabupaten" name="kabupaten" type="text" class="w-full mt-1" value="{{ old('kabupaten', $ppdb->kabupaten) }}" required />
                        </div>
                        <div>
                            <x-input-label for="provinsi" value="Provinsi" />
                            <x-text-input id="provinsi" name="provinsi" type="text" class="w-full mt-1" value="{{ old('provinsi', $ppdb->provinsi) }}" required />
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="no_hp" value="No. HP/WhatsApp" />
                            <x-text-input id="no_hp" name="no_hp" type="text" class="w-full mt-1" value="{{ old('no_hp', $ppdb->no_hp) }}" required />
                        </div>
                        <div>
                            <x-input-label for="email" value="Email" />
                            <x-text-input id="email" name="email" type="email" class="w-full mt-1 bg-gray-100" value="{{ old('email', $ppdb->email) }}" required readonly />
                        </div>
                    </div>
                </div>

                <!-- Data Orang Tua -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-navy border-b border-border pb-2">Data Orang Tua / Wali</h3>
                    
                    <div class="grid md:grid-cols-3 gap-4">
                        <div>
                            <x-input-label for="nama_ayah" value="Nama Ayah" />
                            <x-text-input id="nama_ayah" name="nama_ayah" type="text" class="w-full mt-1" value="{{ old('nama_ayah', $ppdb->nama_ayah) }}" required />
                        </div>
                        <div>
                            <x-input-label for="pekerjaan_ayah" value="Pekerjaan Ayah" />
                            <x-text-input id="pekerjaan_ayah" name="pekerjaan_ayah" type="text" class="w-full mt-1" value="{{ old('pekerjaan_ayah', $ppdb->pekerjaan_ayah) }}" />
                        </div>
                        <div>
                            <x-input-label for="no_hp_ayah" value="No. HP Ayah" />
                            <x-text-input id="no_hp_ayah" name="no_hp_ayah" type="text" class="w-full mt-1" value="{{ old('no_hp_ayah', $ppdb->no_hp_ayah) }}" />
                        </div>
                    </div>

                    <div class="grid md:grid-cols-3 gap-4">
                        <div>
                            <x-input-label for="nama_ibu" value="Nama Ibu" />
                            <x-text-input id="nama_ibu" name="nama_ibu" type="text" class="w-full mt-1" value="{{ old('nama_ibu', $ppdb->nama_ibu) }}" required />
                        </div>
                        <div>
                            <x-input-label for="pekerjaan_ibu" value="Pekerjaan Ibu" />
                            <x-text-input id="pekerjaan_ibu" name="pekerjaan_ibu" type="text" class="w-full mt-1" value="{{ old('pekerjaan_ibu', $ppdb->pekerjaan_ibu) }}" />
                        </div>
                        <div>
                            <x-input-label for="no_hp_ibu" value="No. HP Ibu" />
                            <x-text-input id="no_hp_ibu" name="no_hp_ibu" type="text" class="w-full mt-1" value="{{ old('no_hp_ibu', $ppdb->no_hp_ibu) }}" />
                        </div>
                    </div>

                    <div class="grid md:grid-cols-3 gap-4">
                        <div>
                            <x-input-label for="nama_wali" value="Nama Wali (Opsional)" />
                            <x-text-input id="nama_wali" name="nama_wali" type="text" class="w-full mt-1" value="{{ old('nama_wali', $ppdb->nama_wali) }}" />
                        </div>
                        <div>
                            <x-input-label for="pekerjaan_wali" value="Pekerjaan Wali" />
                            <x-text-input id="pekerjaan_wali" name="pekerjaan_wali" type="text" class="w-full mt-1" value="{{ old('pekerjaan_wali', $ppdb->pekerjaan_wali) }}" />
                        </div>
                        <div>
                            <x-input-label for="no_hp_wali" value="No. HP Wali" />
                            <x-text-input id="no_hp_wali" name="no_hp_wali" type="text" class="w-full mt-1" value="{{ old('no_hp_wali', $ppdb->no_hp_wali) }}" />
                        </div>
                    </div>
                </div>

                <!-- Dokumen Persyaratan -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-navy border-b border-border pb-2">Dokumen Persyaratan</h3>
                    <p class="text-sm text-muted mb-4">Kosongkan jika tidak ingin mengganti. Upload dokumen dalam format PDF, JPG, atau PNG (Maks 2MB).</p>
                    
                    <div class="grid md:grid-cols-2 gap-6">
                        @foreach([
                            'dok_kk' => 'Kartu Keluarga (KK)',
                            'dok_akta_kelahiran' => 'Akta Kelahiran',
                            'dok_ijasah' => 'Ijazah Terakhir',
                            'dok_foto' => 'Pas Foto Berwarna'
                        ] as $field => $label)
                            <div>
                                <x-input-label for="{{ $field }}" value="{{ $label }}" />
                                @if($ppdb->$field)
                                    <a href="{{ Storage::url($ppdb->$field) }}" target="_blank" class="inline-flex items-center text-sm text-primary hover:underline mb-2">
                                        <x-icon name="paperclip" class="w-4 h-4 mr-1" /> Dokumen Saat Ini
                                    </a>
                                @endif
                                <input id="{{ $field }}" name="{{ $field }}" type="file" class="w-full text-sm text-muted file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-soft file:text-primary hover:file:bg-primary/20" @if($field == 'dok_foto') accept="image/*" @endif />
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-border">
                    <a href="{{ route('ppdb.show', $ppdb) }}" class="btn-secondary">Batal</a>
                    <x-primary-button type="submit">Update Pendaftaran</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>