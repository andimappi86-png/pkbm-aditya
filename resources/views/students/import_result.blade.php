<x-app-layout>
    <x-slot name="title">Hasil Import Peserta Didik</x-slot>

    <div class="max-w-3xl mx-auto space-y-6">
        <x-breadcrumb :items="[['label' => 'Peserta Didik', 'url' => route('peserta-didik.index')], ['label' => 'Import', 'url' => route('peserta-didik.import')], ['label' => 'Hasil']]" />

        <div class="card p-6">
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 mb-4">
                    <x-icon name="check-circle" class="w-8 h-8" />
                </div>
                <h2 class="text-2xl font-bold text-text mb-1">Import Selesai</h2>
                <p class="text-muted">Proses import data Peserta Didik telah selesai.</p>
            </div>

            <!-- Summary Stats -->
            <div class="grid grid-cols-3 gap-4 mb-6">
                <div class="card p-4 text-center bg-emerald-50 border-emerald-200">
                    <div class="text-3xl font-bold text-emerald-700">{{ $results['success'] }}</div>
                    <div class="text-sm text-emerald-600">Berhasil Diimport</div>
                </div>
                <div class="card p-4 text-center bg-amber-50 border-amber-200">
                    <div class="text-3xl font-bold text-amber-700">{{ $results['duplicates'] }}</div>
                    <div class="text-sm text-amber-600">Duplikat Dilewati</div>
                </div>
                <div class="card p-4 text-center bg-rose-50 border-rose-200">
                    <div class="text-3xl font-bold text-rose-700">{{ $results['failed'] }}</div>
                    <div class="text-sm text-rose-600">Gagal Validasi</div>
                </div>
            </div>

            <div class="p-4 bg-gray-50 rounded-xl mb-6">
                <div class="text-sm text-muted mb-1">Total Baris Diproses</div>
                <div class="text-2xl font-bold text-text">{{ $results['success'] + $results['failed'] + $results['duplicates'] }}</div>
            </div>

            @if(!empty($results['errors']))
            <div class="mb-6">
                <h3 class="font-medium text-rose-700 mb-3 flex items-center gap-2">
                    <x-icon name="alert-triangle" class="w-5 h-5" />
                    Detail Error ({{ count($results['errors']) }})
                </h3>
                <div class="overflow-x-auto max-h-96">
                    <table class="min-w-full divide-y divide-border text-sm">
                        <thead>
                            <tr class="bg-rose-50">
                                <th class="px-4 py-2 text-left font-medium text-rose-700">Baris</th>
                                <th class="px-4 py-2 text-left font-medium text-rose-700">Pesan</th>
                                <th class="px-4 py-2 text-left font-medium text-rose-700">Data</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            @foreach($results['errors'] as $error)
                            <tr class="hover:bg-rose-50">
                                <td class="px-4 py-2 font-mono text-rose-600">{{ $error['row'] }}</td>
                                <td class="px-4 py-2 text-rose-700">{{ $error['message'] }}</td>
                                <td class="px-4 py-2 text-muted text-xs font-mono max-w-xs truncate">{{ json_encode($error['data']) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4">
                    <a href="{{ route('peserta-didik.index') }}" class="btn-secondary">
                        <x-icon name="download" class="w-4 h-4 mr-2" />
                        Download Laporan Error (JSON)
                    </a>
                </div>
            @else
            <div class="text-center py-4 text-muted">
                <x-icon name="check-circle" class="mx-auto w-12 h-12 text-emerald-500 mb-2" />
                <p>Semua data berhasil diimport tanpa error!</p>
            </div>
            @endif

            <div class="flex justify-center gap-3 pt-4 border-t border-border">
                <a href="{{ route('peserta-didik.index') }}" class="btn-primary w-full md:w-auto">
                    <x-icon name="list" class="w-4 h-4 mr-2" />
                    Kembali ke Daftar Peserta Didik
                </a>
                <a href="{{ route('peserta-didik.import') }}" class="btn-secondary w-full md:w-auto">
                    <x-icon name="upload" class="w-4 h-4 mr-2" />
                    Import Lagi
                </a>
            </div>
        </div>
    </div>
</x-app-layout>