<x-app-layout>
    <x-slot name="title">Aliokasikan Tutor ke Rombel</x-slot>

    <div class="max-w-2xl mx-auto">
        <x-breadcrumb :items="[
            ['label' => 'Pengelolaan', 'href' => route('classroom-assignments.index')],
            ['label' => 'Aliokasikan Tutor ke Rombel']
        ]" />

        <div class="card p-6">
            <form method="POST" action="{{ route('classroom-assignments.store-tutor-classroom') }}" class="space-y-6">
                @csrf

                <div>
                    <label class="label">Tutor</label>
                    <select name="tutor_id" class="input @error('tutor_id') input-error @enderror">
                        <option value="">-- Pilih Tutor --</option>
                        @foreach($tutors as $tutor)
                            <option value="{{ $tutor->id }}" {{ old('tutor_id') == $tutor->id ? 'selected' : '' }}>
                                {{ $tutor->nama_lengkap }}
                            </option>
                        @endforeach
                    </select>
                    @error('tutor_id')
                    <p class="input-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label">Rombel</label>
                    <select name="classroom_kode" class="input @error('classroom_kode') input-error @enderror">
                        {{-- Note: We'll use a simplified approach - just create new classroom if not exists --}}
                        <option value="">-- Pilih Rombel yang Ada --</option>
                        @foreach($classrooms as $classroom)
                            <option value="{{ $classroom->id }}" {{ old('classroom_id') == $classroom->id ? 'selected' : '' }}>
                                {{ $classroom->nama_rombel }} ({{ $classroom->kode_rombel }})
                            </option>
                        @endforeach
                    </select>
                    @error('classroom_kode')
                    <p class="input-error">{{ $message }}</p>
                    @enderror
                </div>

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
                    @error('program_id')
                    <p class="input-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label">Tahun Ajaran</label>
                    <select name="tahun_ajaran_id" class="input @error('tahun_ajaran_id') input-error @enderror">
                        <option value="">-- Pilih Tahun Ajaran --</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ old('tahun_ajaran_id') == $year->id ? 'selected' : '' }}>
                                {{ $year->nama_ajaran }}
                            </option>
                        @endforeach
                    </select>
                    @error('tahun_ajaran_id')
                    <p class="input-error">{{ $message }}</p>
                    @enderror
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