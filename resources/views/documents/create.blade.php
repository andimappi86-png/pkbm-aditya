<x-app-layout>
    <x-slot name="title">Upload Dokumen</x-slot>

    <div class="max-w-3xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-navy">Upload Dokumen Baru</h2>
            <a href="{{ route('documents.index') }}" class="btn-secondary">Kembali</a>
        </div>

        <div class="card p-6">
            <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                
                @if(auth()->user()->can('documents.manage'))
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="student_id" value="Kaitkan dengan Peserta Didik (Pilih salah satu)" />
                        <x-select id="student_id" name="student_id" class="w-full mt-1">
                            <option value="">- Kosong / Pilih -</option>
                            @foreach($students as $s)
                                <option value="{{ $s->id }}">{{ $s->nama_lengkap }} (NIS: {{ $s->nis }})</option>
                            @endforeach
                        </x-select>
                        <x-input-error :messages="$errors->get('student_id')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="ppdb_id" value="Kaitkan dengan PPDB (Opsional)" />
                        <x-select id="ppdb_id" name="ppdb_id" class="w-full mt-1">
                            <option value="">- Kosong / Pilih -</option>
                            @foreach($ppdbs as $p)
                                <option value="{{ $p->id }}">{{ $p->nama_lengkap }} ({{ $p->nomor_pendaftaran }})</option>
                            @endforeach
                        </x-select>
                        <x-input-error :messages="$errors->get('ppdb_id')" class="mt-2" />
                    </div>
                </div>
                @else
                    @if($students->isNotEmpty())
                        <div class="hidden">
                            <input type="hidden" name="student_id" value="{{ $students->first()->id }}">
                        </div>
                    @elseif($ppdbs->isNotEmpty())
                        <div class="hidden">
                            <input type="hidden" name="ppdb_id" value="{{ $ppdbs->first()->id }}">
                        </div>
                    @endif
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