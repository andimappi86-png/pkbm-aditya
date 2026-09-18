<x-app-layout>
    <x-slot name="title">Form Pendaftaran PPDB</x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex items-center gap-4">
            <h2 class="text-2xl font-bold text-navy">Formulir Pendaftaran</h2>
        </div>

        <div class="card p-6">
            <form action="{{ route('ppdb.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf
                
                <!-- Pilihan Program -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-navy border-b border-border pb-2">Pilihan Program & Tahun Ajaran</h3>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="tahun_ajaran_id" value="Tahun Ajaran" />
                            <x-select id="tahun_ajaran_id" name="tahun_ajaran_id" class="w-full mt-1 bg-gray-100" required readonly>
                                <option value="{{ $academicYear->id }}">{{ $academicYear->nama }}</option>
                            </x-select>
                        </div>
                        <div>
                            <x-input-label for="program_id" value="Program Pilihan" />
                            <x-select id="program_id" name="program_id" class="w-full mt-1" required>
                                <option value="">Pilih Program</option>
                                @foreach($programs as $program)
                                    <option value="{{ $program->id }}" {{ old('program_id') == $program->id ? 'selected' : '' }}>{{ $program->nama }}</option>
                                @endforeach
                            </x-select>
                            <x-input-error :messages="$errors->get('program_id')" class="mt-2" />
                        </div>
                    </div>
                </div>

                <!-- Data Pribadi -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-navy border-b border-border pb-2">Data Pribadi Calon Peserta Didik</h3>
                    
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="nik" value="NIK (Nomor Induk Kependudukan)" />
                            <x-text-input id="nik" name="nik" type="text" class="w-full mt-1" value="{{ old('nik') }}" required maxlength="16" />
                            <x-input-error :messages="$errors->get('nik')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="nisn" value="NISN (Jika Ada)" />
                            <x-text-input id="nisn" name="nisn" type="text" class="w-full mt-1" value="{{ old('nisn') }}" maxlength="10" />
                            <x-input-error :messages="$errors->get('nisn')" class="mt-2" />
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="nama_lengkap" value="Nama Lengkap" />
                            <x-text-input id="nama_lengkap" name="nama_lengkap" type="text" class="w-full mt-1" value="{{ old('nama_lengkap') }}" required />
                            <x-input-error :messages="$errors->get('nama_lengkap')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="nama_panggilan" value="Nama Panggilan" />
                            <x-text-input id="nama_panggilan" name="nama_panggilan" type="text" class="w-full mt-1" value="{{ old('nama_panggilan') }}" />
                        </div>
                    </div>

                    <div class="grid md:grid-cols-3 gap-4">
                        <div>
                            <x-input-label for="jenis_kelamin" value="Jenis Kelamin" />
                            <x-select id="jenis_kelamin" name="jenis_kelamin" class="w-full mt-1" required>
                                <option value="">Pilih</option>
                                <option value="L" {{ old('jenis_kelamin') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="P" {{ old('jenis_kelamin') == 'P' ? 'selected' : '' }}>Perempuan</option>
                            </x-select>
                            <x-input-error :messages="$errors->get('jenis_kelamin')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="tempat_lahir" value="Tempat Lahir" />
                            <x-text-input id="tempat_lahir" name="tempat_lahir" type="text" class="w-full mt-1" value="{{ old('tempat_lahir') }}" required />
                            <x-input-error :messages="$errors->get('tempat_lahir')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="tanggal_lahir" value="Tanggal Lahir" />
                            <x-text-input id="tanggal_lahir" name="tanggal_lahir" type="date" class="w-full mt-1" value="{{ old('tanggal_lahir') }}" required />
                            <x-input-error :messages="$errors->get('tanggal_lahir')" class="mt-2" />
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="agama" value="Agama" />
                            <x-select id="agama" name="agama" class="w-full mt-1" required>
                                <option value="">Pilih Agama</option>
                                @foreach(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'] as $agama)
                                    <option value="{{ $agama }}" {{ old('agama') == $agama ? 'selected' : '' }}>{{ $agama }}</option>
                                @endforeach
                            </x-select>
                            <x-input-error :messages="$errors->get('agama')" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="alamat" value="Alamat Lengkap" />
                        <textarea id="alamat" name="alamat" class="w-full mt-1 rounded-xl border-border focus:border-primary focus:ring focus:ring-primary/20 shadow-sm" rows="3" required>{{ old('alamat') }}</textarea>
                        <x-input-error :messages="$errors->get('alamat')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <x-input-label for="rt" value="RT" />
                            <x-text-input id="rt" name="rt" type="text" class="w-full mt-1" value="{{ old('rt') }}" />
                        </div>
                        <div>
                            <x-input-label for="rw" value="RW" />
                            <x-text-input id="rw" name="rw" type="text" class="w-full mt-1" value="{{ old('rw') }}" />
                        </div>
                        <div>
                            <x-input-label for="kelurahan" value="Kelurahan/Desa" />
                            <x-text-input id="kelurahan" name="kelurahan" type="text" class="w-full mt-1" value="{{ old('kelurahan') }}" />
                        </div>
                        <div>
                            <x-input-label for="kecamatan" value="Kecamatan" />
                            <x-text-input id="kecamatan" name="kecamatan" type="text" class="w-full mt-1" value="{{ old('kecamatan') }}" />
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="kabupaten" value="Kabupaten/Kota" />
                            <x-text-input id="kabupaten" name="kabupaten" type="text" class="w-full mt-1" value="{{ old('kabupaten', 'Mamuju') }}" required />
                        </div>
                        <div>
                            <x-input-label for="provinsi" value="Provinsi" />
                            <x-text-input id="provinsi" name="provinsi" type="text" class="w-full mt-1" value="{{ old('provinsi', 'Sulawesi Barat') }}" required />
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="no_hp" value="No. HP/WhatsApp" />
                            <x-text-input id="no_hp" name="no_hp" type="text" class="w-full mt-1" value="{{ old('no_hp', auth()->user()->phone) }}" required />
                            <x-input-error :messages="$errors->get('no_hp')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="email" value="Email" />
                            <x-text-input id="email" name="email" type="email" class="w-full mt-1 bg-gray-100" value="{{ old('email', auth()->user()->email) }}" required readonly />
                        </div>
                    </div>
                </div>

                <!-- Data Orang Tua -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-navy border-b border-border pb-2">Data Orang Tua / Wali</h3>
                    
                    <div class="grid md:grid-cols-3 gap-4">
                        <div>
                            <x-input-label for="nama_ayah" value="Nama Ayah" />
                            <x-text-input id="nama_ayah" name="nama_ayah" type="text" class="w-full mt-1" value="{{ old('nama_ayah') }}" required />
                            <x-input-error :messages="$errors->get('nama_ayah')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="pekerjaan_ayah" value="Pekerjaan Ayah" />
                            <x-text-input id="pekerjaan_ayah" name="pekerjaan_ayah" type="text" class="w-full mt-1" value="{{ old('pekerjaan_ayah') }}" />
                        </div>
                        <div>
                            <x-input-label for="no_hp_ayah" value="No. HP Ayah" />
                            <x-text-input id="no_hp_ayah" name="no_hp_ayah" type="text" class="w-full mt-1" value="{{ old('no_hp_ayah') }}" />
                        </div>
                    </div>

                    <div class="grid md:grid-cols-3 gap-4">
                        <div>
                            <x-input-label for="nama_ibu" value="Nama Ibu" />
                            <x-text-input id="nama_ibu" name="nama_ibu" type="text" class="w-full mt-1" value="{{ old('nama_ibu') }}" required />
                            <x-input-error :messages="$errors->get('nama_ibu')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="pekerjaan_ibu" value="Pekerjaan Ibu" />
                            <x-text-input id="pekerjaan_ibu" name="pekerjaan_ibu" type="text" class="w-full mt-1" value="{{ old('pekerjaan_ibu') }}" />
                        </div>
                        <div>
                            <x-input-label for="no_hp_ibu" value="No. HP Ibu" />
                            <x-text-input id="no_hp_ibu" name="no_hp_ibu" type="text" class="w-full mt-1" value="{{ old('no_hp_ibu') }}" />
                        </div>
                    </div>

                    <div class="grid md:grid-cols-3 gap-4">
                        <div>
                            <x-input-label for="nama_wali" value="Nama Wali (Opsional)" />
                            <x-text-input id="nama_wali" name="nama_wali" type="text" class="w-full mt-1" value="{{ old('nama_wali') }}" />
                        </div>
                        <div>
                            <x-input-label for="pekerjaan_wali" value="Pekerjaan Wali" />
                            <x-text-input id="pekerjaan_wali" name="pekerjaan_wali" type="text" class="w-full mt-1" value="{{ old('pekerjaan_wali') }}" />
                        </div>
                        <div>
                            <x-input-label for="no_hp_wali" value="No. HP Wali" />
                            <x-text-input id="no_hp_wali" name="no_hp_wali" type="text" class="w-full mt-1" value="{{ old('no_hp_wali') }}" />
                        </div>
                    </div>
                </div>

                <!-- Dokumen Persyaratan -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-navy border-b border-border pb-2">Dokumen Persyaratan</h3>
                    <p class="text-sm text-muted mb-4">Upload dokumen dalam format PDF, JPG, atau PNG (Maks 2MB).</p>
                    
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="dok_kk" value="Kartu Keluarga (KK)" />
                            <input id="dok_kk" name="dok_kk" type="file" class="w-full mt-1 text-sm text-muted file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-soft file:text-primary hover:file:bg-primary/20" />
                            <x-input-error :messages="$errors->get('dok_kk')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="dok_akta_kelahiran" value="Akta Kelahiran" />
                            <input id="dok_akta_kelahiran" name="dok_akta_kelahiran" type="file" class="w-full mt-1 text-sm text-muted file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-soft file:text-primary hover:file:bg-primary/20" />
                            <x-input-error :messages="$errors->get('dok_akta_kelahiran')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="dok_ijasah" value="Ijazah Terakhir" />
                            <input id="dok_ijasah" name="dok_ijasah" type="file" class="w-full mt-1 text-sm text-muted file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-soft file:text-primary hover:file:bg-primary/20" />
                            <x-input-error :messages="$errors->get('dok_ijasah')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="dok_foto" value="Pas Foto Berwarna" />
                            <input id="dok_foto" name="dok_foto" type="file" class="w-full mt-1 text-sm text-muted file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-soft file:text-primary hover:file:bg-primary/20" accept="image/*" />
                            <x-input-error :messages="$errors->get('dok_foto')" class="mt-2" />
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-border">
                    <a href="{{ route('dashboard') }}" class="btn-secondary">Batal</a>
                    <x-primary-button type="submit">Submit Pendaftaran</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>