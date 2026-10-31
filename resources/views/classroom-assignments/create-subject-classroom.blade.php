<x-app-layout>
    <x-slot name="title">Aliokasikan Mata Pelajaran ke Rombel</x-slot>

    <div class="max-w-2xl mx-auto" x-data="{
        programId: '{{ old('program_id') }}',
        tahunAjaranId: '{{ old('tahun_ajaran_id') }}',
        classroomId: '{{ old('classroom_id') }}',
        classrooms: {{ Js::from($classrooms->map(fn($c) => ['id' => $c->id, 'nama_rombel' => $c->nama_rombel, 'kode_rombel' => $c->kode_rombel, 'program_id' => $c->program_id, 'tahun_ajaran_id' => $c->tahun_ajaran_id])) }},
        get filteredClassrooms() {
            return this.classrooms.filter(c => {
                if (this.programId && c.program_id != this.programId) return false;
                if (this.tahunAjaranId && c.tahun_ajaran_id != this.tahunAjaranId) return false;
                return true;
            });
        },
        onClassroomSelect() {
            if (this.classroomId) {
                const c = this.classrooms.find(x => x.id == this.classroomId);
                if (c) {
                    this.programId = c.program_id;
                    this.tahunAjaranId = c.tahun_ajaran_id;
                }
            }
        }
    }">
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
                                <input type="checkbox" name="subjects[]" value="{{ $subject->id }}" {{ in_array($subject->id, old('subjects', [])) ? 'checked' : '' }} class="w-4 h-4 text-primary focus:ring-primary border-border mr-2">
                                <span>{{ $subject->kode . ': ' . $subject->nama }}</span>
                            </label>
                        </div>
                        @endforeach
                    </div>
                    @error('subjects')
                    <p class="input-error text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label">Filter Program</label>
                    <select name="program_id" x-model="programId" class="input @error('program_id') input-error @enderror">
                        <option value="">-- Semua Program --</option>
                        @foreach($programs as $program)
                            <option value="{{ $program->id }}">
                                {{ $program->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('program_id')
                    <p class="input-error text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label">Filter Tahun Ajaran</label>
                    <select name="tahun_ajaran_id" x-model="tahunAjaranId" class="input @error('tahun_ajaran_id') input-error @enderror">
                        <option value="">-- Semua Tahun Ajaran --</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}">
                                {{ $year->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('tahun_ajaran_id')
                    <p class="input-error text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label">Rombel <span class="text-red-500">*</span></label>
                    <select name="classroom_id" x-model="classroomId" @change="onClassroomSelect()" class="input @error('classroom_id') input-error @enderror">
                        <option value="">-- Pilih Rombel --</option>
                        <template x-for="c in filteredClassrooms" :key="c.id">
                            <option :value="c.id" x-text="c.nama_rombel + ' (' + c.kode_rombel + ')'" :selected="c.id == classroomId"></option>
                        </template>
                    </select>
                    <template x-if="filteredClassrooms.length === 0">
                        <p class="text-sm text-amber-600 mt-1">Tidak ada rombel yang sesuai dengan program/tahun ajaran ini.</p>
                    </template>
                    @error('classroom_id')
                    <p class="input-error text-red-600 mt-1">{{ $message }}</p>
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
