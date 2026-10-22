<x-app-layout>
    <x-slot name="title">Aliokasikan Tutor ke Rombel</x-slot>

    <div class="max-w-2xl mx-auto" x-data="{ modeTutor: 'select', modeRombel: 'select' }">
        <x-breadcrumb :items="[
            ['label' => 'Pengelolaan', 'href' => route('classroom-assignments.index')],
            ['label' => 'Aliokasikan Tutor ke Rombel']
        ]" />

        <div class="card p-6">
            <form method="POST" action="{{ route('classroom-assignments.store-tutor-classroom') }}" class="space-y-6">
                @csrf

                {{-- Tutor --}}
                <div x-data="{ mode: 'select' }">
                    <label class="label">Tutor</label>
                    <div class="flex gap-2 mb-2">
                        <button type="button" @click="mode = 'select'; modeTutor = 'select'" :class="mode === 'select' ? 'btn-primary' : 'btn-secondary'" class="text-xs py-1 px-2">Pilih</button>
                        <button type="button" @click="mode = 'manual'; modeTutor = 'manual'" :class="mode === 'manual' ? 'btn-primary' : 'btn-secondary'" class="text-xs py-1 px-2">Baru</button>
                        <input type="hidden" name="mode_tutor" x-model="modeTutor">
                    </div>

                    <select name="tutor_id" x-show="mode === 'select'" class="input @error('tutor_id') input-error @enderror">
                        <option value="">-- Pilih Tutor --</option>
                        @foreach($tutors as $tutor)
                            <option value="{{ $tutor->id }}" {{ old('tutor_id') == $tutor->id ? 'selected' : '' }}>
                                {{ $tutor->nama_lengkap }}
                            </option>
                        @endforeach
                    </select>

                    <input type="text" name="tutor_nama" x-show="mode === 'manual'" placeholder="Masukkan nama tutor baru" class="input @error('tutor_nama') input-error @enderror" value="{{ old('tutor_nama') }}">

                    @error('tutor_id') <p class="input-error">{{ $message }}</p> @enderror
                    @error('tutor_nama') <p class="input-error">{{ $message }}</p> @enderror
                </div>

                {{-- Rombel --}}
                <div x-data="{ mode: 'select' }">
                    <label class="label">Rombel</label>
                    <div class="flex gap-2 mb-2">
                        <button type="button" @click="mode = 'select'; modeRombel = 'select'" :class="mode === 'select' ? 'btn-primary' : 'btn-secondary'" class="text-xs py-1 px-2">Pilih</button>
                        <button type="button" @click="mode = 'manual'; modeRombel = 'manual'" :class="mode === 'manual' ? 'btn-primary' : 'btn-secondary'" class="text-xs py-1 px-2">Baru</button>
                        <input type="hidden" name="mode_rombel" x-model="modeRombel">
                    </div>

                    <select name="classroom_id" x-show="mode === 'select'" class="input @error('classroom_id') input-error @enderror">
                        <option value="">-- Pilih Rombel --</option>
                        @foreach($classrooms as $classroom)
                            <option value="{{ $classroom->id }}" {{ old('classroom_id') == $classroom->id ? 'selected' : '' }}>
                                {{ $classroom->nama_rombel }} ({{ $classroom->kode_rombel }})
                            </option>
                        @endforeach
                    </select>

                    <div x-show="mode === 'manual'" class="space-y-2">
                        <input type="text" name="classroom_nama" placeholder="Nama Rombel" class="input @error('classroom_nama') input-error @enderror" value="{{ old('classroom_nama') }}">
                        <input type="text" name="classroom_kode" placeholder="Kode Rombel" class="input @error('classroom_kode') input-error @enderror" value="{{ old('classroom_kode') }}">
                    </div>

                    @error('classroom_id') <p class="input-error">{{ $message }}</p> @enderror
                    @error('classroom_nama') <p class="input-error">{{ $message }}</p> @enderror
                    @error('classroom_kode') <p class="input-error">{{ $message }}</p> @enderror
                </div>

                {{-- Program --}}
                <div>
                    <label class="label">Program</label>
                    <select name="program_id" class="input @error('program_id') input-error @enderror">
                        <option value="">-- Pilih Program --</option>
                        @foreach($programs as $program)
                            <option value="{{ $program->id }}" {{ old('program_id') == $program->id ? 'selected' : '' }}>
                                {{ $program->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('program_id') <p class="input-error">{{ $message }}</p> @enderror
                </div>

                {{-- Tahun Ajaran --}}
                <div>
                    <label class="label">Tahun Ajaran</label>
                    <select name="tahun_ajaran_id" class="input @error('tahun_ajaran_id') input-error @enderror">
                        <option value="">-- Pilih Tahun Ajaran --</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ old('tahun_ajaran_id') == $year->id ? 'selected' : '' }}>
                                {{ $year->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('tahun_ajaran_id') <p class="input-error">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-end pt-4">
                    <a href="{{ route('classroom-assignments.index') }}" class="btn-secondary">Batal</a>
                    <button type="submit" class="btn-primary">
                        <x-icon name="plus" class="w-4 h-4 mr-2" />
                        Aliokasikan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
