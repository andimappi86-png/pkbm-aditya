<x-app-layout>
    <x-slot name="title">Aliokasikan Tutor ke Mata Pelajaran</x-slot>

    <div class="max-w-2xl mx-auto">
        <x-breadcrumb :items="[
            ['label' => 'Pengelolaan', 'href' => route('classroom-assignments.index')],
            ['label' => 'Aliokasikan Tutor ke Mata Pelajaran']
        ]" />

        <div class="card p-6">
            <form method="POST" action="{{ route('classroom-assignments.store-tutor-subject') }}" class="space-y-6">
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
                    <p class="input-error text-red-600">{{ $message }}</p>
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