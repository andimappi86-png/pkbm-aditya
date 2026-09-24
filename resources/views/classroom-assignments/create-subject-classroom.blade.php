<x-app-layout>
    <x-slot name="title">Aliokasikan Mata Pelajaran ke Rombel</x-slot>

    <div class="max-w-2xl mx-auto">
        <x-breadcrumb :items="[
            ['label' => 'Pengelolaan', 'href' => route('classroom-assignments.index')],
            ['label' => 'Aliokasikan Mata Pelajaran ke Rombel']
        ]" />

        <div class="card p-6">
            <form method="POST" action="{{ route('classroom-assignments.store-subject-classroom') }}" class="space-y-6">
                @csrf

                <div>
                    <label class="label">Mata Pelajaran</label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($subjects as $subject)
                        <div>
                            <label class="flex items-center cursor-pointer select-none px-3 py-1.5 rounded border border-border hover:border-primary/50">
                                <input type="checkbox" name="subjects[]" value="{{ $subject->id }}" {{ old('subjects', [])->contains($subject->id) ? 'checked' : '' }} class="w-4 h-4 text-primary focus:ring-primary border-border mr-2">
                                <span>{{ $subject->kode . ': ' . $subject->nama }}</span>
                            </label>
                        </div>
                        @endforeach
                    </div>
                    @error('subjects')
                    <p class="input-error text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label">Rombel</label>
                    <select name="classroom_kode" class="input @error('classroom_kode') input-error @enderror">
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