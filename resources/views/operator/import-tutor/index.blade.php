<x-app-layout title="Impor Data Tutor">
    <div class="max-w-[1280px] mx-auto">
        <x-breadcrumb :items="[['label' => 'Tutor', 'url' => route('tutors.index')], ['label' => 'Import']]" />

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-[#172B3A]">Impor Data Tutor</h1>
                <p class="text-[#64748B] mt-1">Impor data tutor secara massal melalui file CSV khusus Tutor.</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-[#E8F5F4] text-[#167C80]">
                    Operator
                </span>
            </div>
        </div>

        <div class="bg-white rounded-lg border border-[#D9E2E5] shadow-sm p-6">
            @if(session('error'))
                <div class="bg-[#FFF1F1] text-[#C62828] p-4 rounded-lg mb-6 border border-[#C62828]">
                    {{ session('error') }}
                </div>
            @endif

            <!-- CSV Tab -->
            <div class="space-y-6">
                @if(!isset($step))
                    <!-- Upload Area -->
                    <form method="POST" enctype="multipart/form-data" action="{{ route('operator.import-tutor.preview') }}" class="space-y-4">
                        @csrf
                        <div x-data="{ isDragging: false, file: null }" 
                             @dragover.prevent="isDragging = true" 
                             @dragleave.prevent="isDragging = false" 
                             @drop.prevent="isDragging = false; file = $event.dataTransfer.files[0]; document.getElementById('fileInput').files = $event.dataTransfer.files;"
                             :class="isDragging ? 'border-[#167C80] bg-[#E8F5F4]' : 'border-[#D9E2E5]'"
                             class="border-2 border-dashed rounded-xl p-12 text-center transition-all cursor-pointer">
                            
                            <input type="file" id="fileInput" name="file" accept=".csv" required class="hidden" @change="file = $event.target.files[0]">
                            
                            <div x-show="!file">
                                <svg class="w-12 h-12 text-[#64748B] mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                <h3 class="text-lg font-medium text-[#172B3A]">Tarik & lepaskan file CSV Tutor</h3>
                                <p class="text-[#64748B] my-2">atau</p>
                                <button type="button" @click="document.getElementById('fileInput').click()" class="text-[#167C80] font-semibold hover:underline">Pilih File CSV</button>
                                <p class="mt-4 text-xs text-[#64748B]">Format CSV • Maksimal 10 MB</p>
                            </div>

                            <div x-show="file" class="text-left flex items-center justify-between bg-[#F5F7F8] p-4 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <svg class="w-8 h-8 text-[#167C80]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <div>
                                        <p class="font-medium text-[#172B3A]" x-text="file ? file.name : ''"></p>
                                        <p class="text-xs text-[#64748B]" x-text="file ? (file.size/1024/1024).toFixed(2) + ' MB' : ''"></p>
                                    </div>
                                </div>
                                <button type="button" @click="file = null; document.getElementById('fileInput').value = ''" class="text-[#C62828] text-sm hover:underline">Ganti File</button>
                            </div>
                        </div>
                        
                        <div class="flex gap-4">
                            <button type="submit" class="flex-1 bg-[#167C80] hover:bg-[#0F5F63] text-white font-semibold py-3 px-6 rounded-lg transition-all shadow-sm">
                                Validasi & Lanjutkan
                            </button>
                            <a href="{{ route('operator.import-tutor.template') }}" class="flex-1 bg-white border border-[#D9E2E5] hover:bg-[#F5F7F8] text-[#172B3A] font-semibold py-3 px-6 rounded-lg transition-all shadow-sm text-center">
                                Download Template CSV Tutor
                            </a>
                        </div>
                    </form>
                @endif

                <!-- Preview Step -->
                @if(isset($step) && $step == 'preview')
                    <div class="space-y-6">
                        <h2 class="text-lg font-bold text-[#172B3A]">Validasi Data</h2>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            @foreach(['Total Data' => $results['total'], 'Valid' => $results['valid'], 'Bermasalah' => $results['invalid'], 'Duplikat' => $results['duplicates']] as $label => $val)
                                <div class="border border-[#D9E2E5] p-4 rounded-lg text-center">
                                    <p class="text-xs text-[#64748B] uppercase">{{ $label }}</p>
                                    <p class="text-xl font-bold text-[#172B3A]">{{ $val }}</p>
                                </div>
                            @endforeach
                        </div>

                        <div class="overflow-x-auto border border-[#D9E2E5] rounded-lg">
                            <table class="w-full text-sm text-left">
                                <thead class="bg-[#F5F7F8] border-b border-[#D9E2E5]">
                                    <tr>
                                        <th class="px-4 py-3">No</th>
                                        <th class="px-4 py-3">Nama</th>
                                        <th class="px-4 py-3">NIK</th>
                                        <th class="px-4 py-3">Email</th>
                                        <th class="px-4 py-3">Bidang Keahlian</th>
                                        <th class="px-4 py-3">Status</th>
                                        <th class="px-4 py-3">Validasi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#D9E2E5]">
                                    @foreach($results['rows'] as $row)
                                        <tr class="{{ $row['valid'] ? '' : 'bg-[#FFF1F1]' }}">
                                            <td class="px-4 py-3">{{ $row['row_number'] }}</td>
                                            <td class="px-4 py-3">{{ $row['nama'] }}</td>
                                            <td class="px-4 py-3 font-mono">327************{{ substr((string)$row['nik'], -4) }}</td>
                                            <td class="px-4 py-3">{{ $row['email'] }}</td>
                                            <td class="px-4 py-3">{{ $row['bidang_keahlian'] }}</td>
                                            <td class="px-4 py-3">{{ $row['status'] }}</td>
                                            <td class="px-4 py-3">
                                                <span class="px-2 py-1 rounded text-xs font-bold {{ $row['valid'] ? 'bg-[#E8F5F4] text-[#167C80]' : 'bg-[#FFF1F1] text-[#C62828]' }}">
                                                    {{ $row['valid'] ? 'VALID' : 'BERMASALAH' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($results['invalid'] > 0)
                            <div class="bg-[#FFF1F1] border border-[#C62828] p-4 rounded-lg">
                                <h4 class="font-bold text-[#C62828] mb-2">Perlu Perhatian</h4>
                                <p class="text-sm text-[#C62828] mb-4">{{ $results['invalid'] }} data tidak dapat diproses.</p>
                                <div class="text-sm space-y-1">
                                    @foreach($results['rows'] as $row)
                                        @if(!$row['valid'])
                                            <p><strong>Baris {{ $row['row_number'] }}</strong>: {{ $row['nama'] }} - {{ implode(', ', $row['errors']) }}</p>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="flex gap-4">
                            <a href="{{ route('operator.import-tutor.index') }}" class="px-6 py-2 border border-[#D9E2E5] rounded-lg text-[#172B3A] font-semibold hover:bg-[#F5F7F8]">Kembali</a>
                            @if($results['valid'] > 0)
                                <form method="POST" action="{{ route('operator.import-tutor.process') }}">
                                    @csrf
                                    <input type="hidden" name="path" value="{{ $path }}"/>
                                    <button type="submit" class="px-6 py-2 bg-[#167C80] text-white rounded-lg font-semibold hover:bg-[#0F5F63]">Import {{ $results['valid'] }} Tutor</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Result Step -->
                @if(isset($step) && $step == 'result')
                    <div class="text-center py-8">
                        <div class="w-16 h-16 bg-[#E8F5F4] text-[#167C80] rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <h2 class="text-2xl font-bold text-[#172B3A] mb-2">Import Berhasil</h2>
                        <p class="text-[#64748B] mb-8">{{ $results['success'] }} Tutor berhasil ditambahkan.</p>
                        
                        <div class="flex justify-center gap-4">
                            <a href="{{ route('tutors.index') }}" class="px-6 py-2 border border-[#D9E2E5] rounded-lg text-[#172B3A] font-semibold hover:bg-[#F5F7F8]">Lihat Data Tutor</a>
                            <a href="{{ route('operator.import-tutor.index') }}" class="px-6 py-2 bg-[#167C80] text-white rounded-lg font-semibold hover:bg-[#0F5F63]">Import Lagi</a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>