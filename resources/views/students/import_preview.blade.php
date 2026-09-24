<x-app-layout>
    <x-slot name="title">Preview Import Peserta Didik</x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        <x-breadcrumb :items="[['label' => 'Peserta Didik', 'url' => route('peserta-didik.index')], ['label' => 'Import', 'url' => route('peserta-didik.import')], ['label' => 'Preview']]" />

        <div class="card p-6">
            <div class="mb-6 border-b border-border pb-6">
                <h2 class="text-xl font-bold text-text mb-2">Preview Data Import</h2>
                <p class="text-muted text-sm">Periksa data sebelum disimpan ke database.</p>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="card p-4 bg-emerald-50 border-emerald-200">
                    <div class="text-2xl font-bold text-emerald-700">{{ $results['success'] }}</div>
                    <div class="text-sm text-emerald-600">Valid</div>
                </div>
                <div class="card p-4 bg-rose-50 border-rose-200">
                    <div class="text-2xl font-bold text-rose-700">{{ $results['failed'] }}</div>
                    <div class="text-sm text-rose-600">Error Validasi</div>
                </div>
                <div class="card p-4 bg-amber-50 border-amber-200">
                    <div class="text-2xl font-bold text-amber-700">{{ $results['duplicates'] }}</div>
                    <div class="text-sm text-amber-600">Duplikat</div>
                </div>
                <div class="card p-4 bg-blue-50 border-blue-200">
                    <div class="text-2xl font-bold text-blue-700">{{ $results['success'] + $results['failed'] + $results['duplicates'] }}</div>
                    <div class="text-sm text-blue-600">Total Baris</div>
                </div>
            </div>

            @if(!empty($results['preview_data']))
            <div class="mb-6">
                <h3 class="font-medium text-text mb-3">Contoh Data Valid (Maks 10 baris)</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-border text-sm">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-2 text-left font-medium text-muted">NIS</th>
                                <th class="px-4 py-2 text-left font-medium text-muted">NIK</th>
                                <th class="px-4 py-2 text-left font-medium text-muted">Nama Lengkap</th>
                                <th class="px-4 py-2 text-left font-medium text-muted">Jenis Kelamin</th>
                                <th class="px-4 py-2 text-left font-medium text-muted">Program</th>
                                <th class="px-4 py-2 text-left font-medium text-muted">Tahun Ajaran</th>
                                <th class="px-4 py-2 text-left font-medium text-muted">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            @foreach($results['preview_data'] as $row)
                            <tr class="hover:bg-primary-soft/30">
                                <td class="px-4 py-2 text-muted italic">{{ $row['nis'] ?? 'Otomatis dibuat' }}</td>
                                <td class="px-4 py-2">{{ $row['nik'] ?? '-' }}</td>
                                <td class="px-4 py-2 font-medium">{{ $row['nama_lengkap'] ?? '-' }}</td>
                                <td class="px-4 py-2">{{ $row['jenis_kelamin'] ?? '-' }}</td>
                                <td class="px-4 py-2">{{ \App\Models\Program::find($row['program_id'] ?? null)?->nama ?? ($row['program_id'] ?? '-') }}</td>
                                <td class="px-4 py-2">{{ \App\Models\AcademicYear::find($row['tahun_ajaran_id'] ?? null)?->nama_ajaran ?? ($row['tahun_ajaran_id'] ?? '-') }}</td>
                                <td class="px-4 py-2">
                                    <span class="badge badge-{{ ($row['status'] ?? '') === 'aktif' ? 'success' : 'warning' }}">{{ $row['status'] ?? 'aktif' }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            @if(!empty($results['errors']))
            <div class="mb-6 border-t border-border pt-6">
                <h3 class="font-medium text-rose-700 mb-3 flex items-center gap-2">
                    <x-icon name="alert-triangle" class="w-5 h-5" />
                    Error Validasi & Duplikat ({{ count($results['errors']) }})
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
            </div>
            @endif

            <div class="flex justify-between items-center pt-4 border-t border-border">
                <a href="{{ route('peserta-didik.import') }}" class="btn-secondary">
                    <x-icon name="chevron-left" class="w-4 h-4 mr-2" />
                    Kembali Upload
                </a>

                <form action="{{ route('peserta-didik.import.process') }}" method="POST">
                    @csrf
                    <input type="hidden" name="path" value="{{ $path }}">
                    @if($results['success'] > 0)
                    <button type="submit" class="btn-primary" onclick="return confirm('Yakin ingin mengimpor {{ $results['success'] }} data valid? Error/duplikat akan dilewati.')">
                        <x-icon name="database" class="w-4 h-4 mr-2" />
                        Import {{ $results['success'] }} Data
                    </button>
                    @else
                    <button type="button" class="btn-primary opacity-50 cursor-not-allowed" disabled>
                        Tidak Ada Data Valid
                    </button>
                    @endif
                </form>
            </div>
        </div>
    </div>
</x-app-layout>