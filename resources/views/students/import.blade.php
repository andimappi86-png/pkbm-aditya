<x-app-layout>
    <x-slot name="title">Import Peserta Didik</x-slot>

    <div class="max-w-3xl mx-auto space-y-6">
        <x-breadcrumb :items="[['label' => 'Peserta Didik', 'url' => route('peserta-didik.index')], ['label' => 'Import']]" />

        <div class="card p-6">
            <div class="mb-6 border-b border-border pb-6">
                <h2 class="text-xl font-bold text-text mb-2">Import Peserta Didik</h2>
                <p class="text-muted text-sm">
                    Gunakan fitur ini untuk memasukkan data Peserta Didik dalam jumlah banyak sekaligus.
                    Halaman ini hanya menerima data Peserta Didik (bukan Tutor).
                </p>
            </div>

            @if(session('error'))
                <div class="bg-rose-50 border border-rose-200 text-rose-700 p-4 rounded-xl mb-6">
                    <div class="flex items-center gap-2">
                        <x-icon name="exclamation-circle" class="w-5 h-5 flex-shrink-0" />
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            <div class="bg-primary-soft/50 border border-primary/20 rounded-xl p-4 mb-6">
                <h3 class="font-medium text-primary-dark mb-2 flex items-center gap-2">
                    <x-icon name="information-circle" class="w-5 h-5" />
                    Langkah-langkah Import:
                </h3>
                <ol class="list-decimal list-inside text-sm text-muted space-y-1 ml-1">
                    <li>Download template yang telah disediakan (CSV atau Excel).</li>
                    <li>Isi data sesuai dengan kolom pada template.</li>
                    <li>Pastikan tidak ada NIS/NISN/NIK yang duplikat.</li>
                    <li>Upload file CSV, XLSX, atau XLS yang telah diisi.</li>
                    <li>Sistem akan memvalidasi data terlebih dahulu sebelum disimpan.</li>
                </ol>
                <div class="mt-4 flex gap-3">
                    <a href="{{ route('peserta-didik.template') }}" class="btn-secondary text-sm">
                        <x-icon name="download" class="w-4 h-4 mr-2" />
                        Download Template Excel
                    </a>
                    <a href="{{ route('peserta-didik.template.csv') }}" class="btn-secondary text-sm">
                        <x-icon name="download" class="w-4 h-4 mr-2" />
                        Download Template CSV
                    </a>
                </div>
            </div>

            <form action="{{ route('peserta-didik.import.preview') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-text mb-2">Pilih File (CSV, XLSX, atau XLS)</label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-border border-dashed rounded-xl hover:border-primary/50 transition-colors bg-surface">
                        <div class="space-y-1 text-center">
                            <x-icon name="document-text" class="mx-auto h-12 w-12 text-muted" />
                            <div class="flex text-sm text-text justify-center">
                                <label for="file" class="relative cursor-pointer rounded-md font-medium text-primary hover:text-primary-dark focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary">
                                    <span>Pilih file</span>
                                    <input id="file" name="file" type="file" class="sr-only" accept=".csv,.xlsx,.xls,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel" required>
                                </label>
                                <p class="pl-1 text-muted">atau drag and drop</p>
                            </div>
                            <p class="text-xs text-muted">CSV, XLSX, XLS — maksimal 5MB</p>
                        </div>
                    </div>
                    @error('file')
                        <p class="mt-2 text-sm text-red-600">
                            @if(str_contains($message, 'mimes'))
                                File harus berformat CSV, XLSX, atau XLS.
                            @else
                                {{ $message }}
                            @endif
                        </p>
                    @enderror
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-border">
                    <a href="{{ route('peserta-didik.index') }}" class="btn-secondary">Batal</a>
                    <button type="submit" class="btn-primary" onclick="this.innerHTML='<svg class=\'animate-spin w-4 h-4 mr-2\' fill=\'none\' viewBox=\'0 0 24 24\'><circle class=\'opacity-25\' cx=\'12\' cy=\'12\' r=\'10\' stroke=\'currentColor\' stroke-width=\'4\'></circle><path class=\'opacity-75\' fill=\'currentColor\' d=\'M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z\'></path></svg> Memproses...'; this.form.submit(); this.disabled=true;">
                        Validasi & Preview
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>