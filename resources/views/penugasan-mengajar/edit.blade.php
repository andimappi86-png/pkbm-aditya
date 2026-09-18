<x-app-layout>
    <x-slot name="title">Edit Penugasan Mengajar</x-slot>

    <div class="max-w-2xl mx-auto">
        <x-breadcrumb :items="[
            ['label' => 'Pengelolaan', 'href' => route('penugasan-mengajar.index')],
            ['label' => 'Edit Penugasan Mengajar']
        ]" />

        <div class="card p-6">
            <form method="POST" action="{{ route('penugasan-mengajar.update', $penugasanMengajar->id) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <input type="hidden" name="tutor_id" value="{{ $penugasanMengajar->tutor_id }}">

                <div>
                    <label class="label">Tutor</label>
                    <select name="tutor_id" class="input @error('tutor_id') input-error @enderror">
                        <option value="">-- Pilih Tutor --</option>
                        @foreach($tutors as $tutor)
                            <option value="{{ $tutor->id }}" {{ old('tutor_id') == $tutor->id || $penugasanMengajar->tutor_id == $tutor->id ? 'selected' : '' }}>
                                {{ $tutor->nama_lengkap }}
                            </option>
                        @endforeach
                    </select>
                    @error('tutor_id')
                    <p class="input-error text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label">Mata Pelajaran</label>
                    <select name="subject_id" class="input @error('subject_id') input-error @enderror">
                        <option value="">-- Pilih Mata Pelajaran --</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id || $penugasanMengajar->subject_id == $subject->id ? 'selected' : '' }}>
                                {{ $subject->kode . ': ' . $subject->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('subject_id')
                    <p class="input-error text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label">Rombel</label>
                    <select name="classroom_id" class="input @error('classroom_id') input-error @enderror">
                        <option value="">-- Pilih Rombel --</option>
                        @foreach($classrooms as $classroom)
                            <option value="{{ $classroom->id }}" {{ old('classroom_id') == $classroom->id || $penugasanMengajar->classroom_id == $classroom->id ? 'selected' : '' }}>
                                {{ $classroom->nama_rombel }} ({{ $classroom->kode_rombel }})
                            </option>
                        @endforeach
                    </select>
                    @error('classroom_id')
                    <p class="input-error text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label">Tahun Ajaran</label>
                    <select name="academic_year_id" class="input @error('academic_year_id') input-error @enderror">
                        <option value="">-- Pilih Tahun Ajaran --</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ old('academic_year_id') == $year->id || $penugasanMengajar->academic_year_id == $year->id ? 'selected' : '' }}>
                                {{ $year->nama_ajaran }}
                            </option>
                        @endforeach
                    </select>
                    @error('academic_year_id')
                    <p class="input-error text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label">Semester</label>
                    <select name="semester_id" class="input @error('semester_id') input-error @enderror">
                        <option value="">-- Pilih Semester --</option>
                        @foreach($semesters as $semester)
                            <option value="{{ $semester->id }}" {{ old('semester_id') == $semester->id || $penugasanMengajar->semester_id == $semester->id ? 'selected' : '' }}>
                                {{ $semester->nama_semester }} ({{ $semester->kode }})
                            </option>
                        @endforeach
                    </select>
                    @error('semester_id')
                    <p class="input-error text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="label">Status Aktif</label>
                    <select name="status_aktif" class="input @error('status_aktif') input-error @enderror">
                        <option value="">-- Pilih Status --</option>
                        <option value="1" {{ old('status_aktif') == '1' || $penugasanMengajar->status_aktif == true ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ old('status_aktif') == '0' || $penugasanMengajar->status_aktif == false ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                    @error('status_aktif')
                    <p class="input-error text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end pt-4">
                    <a href="{{ route('penugasan-mengajar.index') }}" class="btn-secondary">Batal</a>
                    <button type="submit" class="btn-primary">
                        <x-icon name="plus" class="w-4 h-4 mr-2" />
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>