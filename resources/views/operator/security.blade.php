<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security / AES-256-GCM - PKBM Aditya</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: { DEFAULT: '#167C80', dark: '#0F5F63', soft: '#E8F5F4' },
                        text: '#172B3A',
                        background: '#F5F7F8',
                        white: '#FFFFFF',
                        accent: '#C62828',
                        warning: '#F59E0B',
                        danger: '#EF4444',
                        success: '#10B981',
                    },
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                }
            }
        }
    </script>
    <style>
        * { font-family: 'Inter', sans-serif; }
        .mono { font-family: 'Monospace', monospace; }
    </style>
</head>
<body class="bg-background min-h-screen">
    <!-- Sidebar -->
    <aside class="fixed inset-y-0 left-0 z-40 w-64 bg-white border-r border-gray-200 hidden lg:block">
        <div class="flex flex-col h-full">
            <div class="p-6 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-primary-soft flex items-center justify-center">
                        <svg class="w-6 h-6 text-primary-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="font-bold text-text">PKBM ADITYA</h1>
                        <p class="text-xs text-muted">Operator Panel</p>
                    </div>
                </div>
            </div>
            <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
                <a href="{{ route('operator.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-text hover:bg-primary-soft hover:text-primary-dark transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    Dashboard
                </a>
                
                <a href="{{ route('operator.security') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-primary-soft text-primary-dark font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    Security / AES-256-GCM
                </a>

                <div class="pt-4 mt-4 border-t border-gray-100">
                    <a href="{{ route('operator.dashboard') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit()" class="flex items-center gap-3 px-4 py-3 rounded-xl text-text hover:bg-red-50 hover:text-red-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Logout
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                        @csrf
                    </form>
                </div>
            </nav>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="lg:ml-64 min-h-screen">
        <header class="sticky top-0 z-30 bg-white border-b border-gray-200">
            <div class="flex items-center justify-between px-6 py-4">
                <h1 class="text-xl font-semibold text-text">Security / AES-256-GCM Research</h1>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-primary-soft flex items-center justify-center">
                            <svg class="w-5 h-5 text-primary-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div class="text-right hidden sm:block">
                            <p class="text-sm font-medium text-text">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-muted">Operator</p>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <div class="p-6 lg:p-8">
            <!-- Algorithm Info Card -->
            <div class="card p-6 mb-8">
                <h3 class="text-lg font-semibold text-text mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    Konfigurasi Algoritma
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="p-4 rounded-xl border border-gray-200 bg-surface text-center">
                        <div class="text-2xl font-bold text-primary">AES-256-GCM</div>
                        <div class="text-sm text-muted">Algorithm</div>
                    </div>
                    <div class="p-4 rounded-xl border border-gray-200 bg-surface text-center">
                        <div class="text-2xl font-bold text-primary">{{ $keyLength }} bit</div>
                        <div class="text-sm text-muted">Key Size</div>
                    </div>
                    <div class="p-4 rounded-xl border border-gray-200 bg-surface text-center">
                        <div class="text-2xl font-bold text-primary">96 bit (12 byte)</div>
                        <div class="text-sm text-muted">IV / Nonce</div>
                    </div>
                    <div class="p-4 rounded-xl border border-gray-200 bg-surface text-center">
                        <div class="text-2xl font-bold text-primary">128 bit (16 byte)</div>
                        <div class="text-sm text-muted">Auth Tag</div>
                    </div>
                </div>
            </div>

            <!-- Data Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                <div class="card p-4 text-center">
                    <div class="text-2xl font-bold text-primary">{{ $dummyStats['encrypted'] }}</div>
                    <div class="text-sm text-muted">Data Terenkripsi</div>
                </div>
                <div class="card p-4 text-center">
                    <div class="text-2xl font-bold text-primary">{{ $dummyStats['plaintext'] }}</div>
                    <div class="text-sm text-muted">Data Plaintext</div>
                </div>
                <div class="card p-4 text-center">
                    <div class="text-2xl font-bold text-warning" id="benchmark-enc">-</div>
                    <div class="text-sm text-muted">Encryption (ms)</div>
                </div>
                <div class="card p-4 text-center">
                    <div class="text-2xl font-bold text-warning" id="benchmark-dec">-</div>
                    <div class="text-sm text-muted">Decryption (ms)</div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="card">
                <div class="border-b border-gray-200">
                    <nav class="flex -mb-px space-x-8" aria-label="Tabs">
                        <button onclick="showTab('encrypt')" class="tab-btn active py-4 px-1 border-b-2 border-primary font-medium text-sm text-primary">Encrypt/Decrypt</button>
                        <button onclick="showTab('avalanche')" class="tab-btn py-4 px-1 border-b-2 border-transparent font-medium text-sm text-muted hover:text-text">Avalanche Test</button>
                        <button onclick="showTab('benchmark')" class="tab-btn py-4 px-1 border-b-2 border-transparent font-medium text-sm text-muted hover:text-text">Benchmark</button>
                        <button onclick="showTab('auth-tag')" class="tab-btn py-4 px-1 border-b-2 border-transparent font-medium text-sm text-muted hover:text-text">Auth Tag Test</button>
                    </nav>
                </div>

                <!-- Encrypt/Decrypt Tab -->
                <div id="tab-encrypt" class="tab-content p-6">
                    <div class="grid lg:grid-cols-2 gap-8">
                        <!-- Encrypt -->
                        <div>
                            <h4 class="font-semibold text-text mb-4">Encrypt Plaintext</h4>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-text mb-1">Plaintext</label>
                                    <textarea id="encrypt-input" rows="6" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary-soft focus:outline-none transition-colors mono text-sm" placeholder='Masukkan plaintext (JSON format untuk dummy data):&#10;{"nik":"3273041234567890","nama":"Budi Santoso","email":"budi@example.com","alamat":"Jl. Poros Mamuju No. 123","telepon":"081234567890"}'></textarea>
                                </div>
                                <button onclick="encrypt()" class="w-full btn-primary py-3">Encrypt</button>
                            </div>
                        </div>

                        <!-- Decrypt -->
                        <div>
                            <h4 class="font-semibold text-text mb-4">Decrypt Payload</h4>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-text mb-1">Encrypted Payload (Base64)</label>
                                    <textarea id="decrypt-input" rows="6" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary-soft focus:outline-none transition-colors mono text-sm" placeholder="Paste encrypted payload here..."></textarea>
                                </div>
                                <button onclick="decrypt()" class="w-full btn-secondary py-3">Decrypt</button>
                            </div>
                        </div>
                    </div>

                    <!-- Results -->
                    <div class="mt-6 grid lg:grid-cols-2 gap-6">
                        <div>
                            <h4 class="font-semibold text-text mb-2">Hasil Encrypt</h4>
                            <div class="p-4 rounded-lg border border-gray-200 bg-surface">
                                <textarea id="encrypt-output" rows="8" class="w-full mono text-sm text-text bg-transparent border-none focus:outline-none resize-none" readonly></textarea>
                            </div>
                            <div class="mt-2 flex gap-2">
                                <button onclick="copyToClipboard('encrypt-output')" class="btn-secondary text-sm">Copy</button>
                                <button onclick="document.getElementById('decrypt-input').value = document.getElementById('encrypt-output').value; showTab('decrypt')" class="btn-primary text-sm">Test Decrypt</button>
                            </div>
                        </div>
                        <div>
                            <h4 class="font-semibold text-text mb-2">Hasil Decrypt</h4>
                            <div class="p-4 rounded-lg border border-gray-200 bg-surface">
                                <textarea id="decrypt-output" rows="8" class="w-full mono text-sm text-text bg-transparent border-none focus:outline-none resize-none" readonly></textarea>
                            </div>
                            <div class="mt-2">
                                <span id="decrypt-status" class="text-sm text-muted"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Avalanche Tab -->
                <div id="tab-avalanche" class="tab-content p-6 hidden">
                    <div class="grid lg:grid-cols-2 gap-8">
                        <div>
                            <h4 class="font-semibold text-text mb-4">Plaintext A</h4>
                            <textarea id="avalanche-a" rows="6" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary-soft focus:outline-none transition-colors mono text-sm" placeholder='Plaintext A:&#10;{"nik":"3273041234567890","nama":"Budi Santoso","email":"budi@example.com","alamat":"Jl. Poros Mamuju No. 123","telepon":"081234567890"}'></textarea>
                        </div>
                        <div>
                            <h4 class="font-semibold text-text mb-4">Plaintext B (Sedikit Berbeda)</h4>
                            <textarea id="avalanche-b" rows="6" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary-soft focus:outline-none transition-colors mono text-sm" placeholder='Plaintext B (ubah 1 karakter):&#10;{"nik":"3273041234567891","nama":"Budi Santoso","email":"budi@example.com","alamat":"Jl. Poros Mamuju No. 123","telepon":"081234567890"}'></textarea>
                        </div>
                    </div>
                    <button onclick="avalanche()" class="btn-primary mt-4">Hitung Avalanche Effect</button>
                    
                    <div id="avalanche-result" class="mt-6 hidden">
                        <h4 class="font-semibold text-text mb-4">Hasil Avalanche Effect</h4>
                        <div class="grid grid-cols-3 gap-4">
                            <div class="p-4 rounded-xl border border-gray-200 bg-surface text-center">
                                <div class="text-2xl font-bold text-primary" id="avalanche-total">-</div>
                                <div class="text-sm text-muted">Total Bits</div>
                            </div>
                            <div class="p-4 rounded-xl border border-gray-200 bg-surface text-center">
                                <div class="text-2xl font-bold text-warning" id="avalanche-changed">-</div>
                                <div class="text-sm text-muted">Changed Bits</div>
                            </div>
                            <div class="p-4 rounded-xl border border-gray-200 bg-surface text-center">
                                <div class="text-2xl font-bold text-success" id="avalanche-percent">-</div>
                                <div class="text-sm text-muted">Avalanche %</div>
                            </div>
                        </div>
                        <div class="mt-4 p-4 rounded-lg border border-gray-200 bg-surface">
                            <div class="text-sm text-muted mb-2">Binary Diff (First 256 bits):</div>
                            <pre id="avalanche-diff" class="mono text-xs text-text overflow-x-auto"></pre>
                        </div>
                    </div>
                </div>

                <!-- Benchmark Tab -->
                <div id="tab-benchmark" class="tab-content p-6 hidden">
                    <div class="flex items-center gap-4 mb-6">
                        <label class="text-sm font-medium text-text">Jumlah Record</label>
                        <select id="benchmark-count" class="px-4 py-2 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary-soft focus:outline-none transition-colors">
                            <option value="100">100</option>
                            <option value="500">500</option>
                            <option value="1000" selected>1000</option>
                            <option value="5000">5000</option>
                        </select>
                    </div>
                    <button onclick="benchmark()" class="btn-primary" id="benchmark-btn">Jalankan Benchmark</button>
                    
                    <div id="benchmark-result" class="mt-6 hidden">
                        <h4 class="font-semibold text-text mb-4">Hasil Benchmark</h4>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead>
                                    <tr class="bg-surface border-b border-gray-200">
                                        <th class="p-4 font-semibold text-text">Metric</th>
                                        <th class="p-4 font-semibold text-text">Value</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr><td class="p-4 text-text">Record Count</td><td class="p-4 mono font-medium" id="bm-count">-</td></tr>
                                    <tr><td class="p-4 text-text">Plaintext Time (ms)</td><td class="p-4 mono font-medium" id="bm-plain">-</td></tr>
                                    <tr><td class="p-4 text-text">Encryption Time (ms)</td><td class="p-4 mono font-medium" id="bm-enc">-</td></tr>
                                    <tr><td class="p-4 text-text">Encryption Throughput (rec/s)</td><td class="p-4 mono font-medium" id="bm-enc-tp">-</td></tr>
                                    <tr><td class="p-4 text-text">Decryption Time (ms)</td><td class="p-4 mono font-medium" id="bm-dec">-</td></tr>
                                    <tr><td class="p-4 text-text">Decryption Throughput (rec/s)</td><td class="p-4 mono font-medium" id="bm-dec-tp">-</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Auth Tag Test Tab -->
                <div id="tab-auth-tag" class="tab-content p-6 hidden">
                    <h4 class="font-semibold text-text mb-4">Authentication Tag Validation Test</h4>
                    <p class="text-muted mb-6">Test ini memverifikasi bahwa AES-GCM dengan benar mendeteksi modifikasi ciphertext/tag.</p>

                    <div class="space-y-4 mb-6">
                        <button onclick="authTagTest()" class="btn-primary">Jalankan Test Authentication Tag</button>
                    </div>

                    <div id="auth-tag-result" class="hidden">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div class="p-4 rounded-xl border border-gray-200 bg-surface text-center" id="auth-step1">
                                <div class="text-2xl font-bold text-primary">1</div>
                                <div class="text-sm text-muted">Encrypt</div>
                                <div class="text-xs text-success mt-1">Success</div>
                            </div>
                            <div class="p-4 rounded-xl border border-gray-200 bg-surface text-center" id="auth-step2">
                                <div class="text-2xl font-bold text-primary">2</div>
                                <div class="text-sm text-muted">Decrypt Original</div>
                                <div class="text-xs text-success mt-1">Success</div>
                            </div>
                            <div class="p-4 rounded-xl border border-gray-200 bg-surface text-center" id="auth-step3">
                                <div class="text-2xl font-bold text-danger">3</div>
                                <div class="text-sm text-muted">Decrypt Tampered</div>
                                <div class="text-xs text-danger mt-1">Failed (Expected)</div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-text mb-1">Plaintext Original</label>
                                <textarea id="auth-plaintext" rows="4" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary-soft focus:outline-none transition-colors mono text-sm bg-gray-50" readonly></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-text mb-1">Payload Asli (Base64)</label>
                                <textarea id="auth-payload-original" rows="4" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary-soft focus:outline-none transition-colors mono text-sm bg-gray-50" readonly></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-text mb-1">Payload Dimodifikasi (1 bit ciphertext diubah)</label>
                                <textarea id="auth-payload-tampered" rows="4" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary-soft focus:outline-none transition-colors mono text-sm bg-gray-50" readonly></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-text mb-1">Hasil Decrypt Payload Asli</label>
                                <textarea id="auth-decrypt-original" rows="4" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary-soft focus:outline-none transition-colors mono text-sm bg-gray-50" readonly></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-text mb-1">Error Decrypt Payload Dimodifikasi</label>
                                <textarea id="auth-decrypt-tampered" rows="4" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary-soft focus:outline-none transition-colors mono text-sm bg-gray-50" readonly></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        function showTab(tabId) {
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active', 'border-primary', 'text-primary');
                btn.classList.add('border-transparent', 'text-muted');
            });
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });
            
            const activeBtn = document.querySelector(`button[onclick="showTab('${tabId}')"]`);
            activeBtn.classList.add('active', 'border-primary', 'text-primary');
            activeBtn.classList.remove('border-transparent', 'text-muted');
            document.getElementById('tab-' + tabId).classList.remove('hidden');
        }

        async function encrypt() {
            const input = document.getElementById('encrypt-input').value.trim();
            if (!input) {
                alert('Masukkan plaintext terlebih dahulu');
                return;
            }

            try {
                const response = await fetch('{{ route('operator.encrypt') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ plaintext: input })
                });

                const data = await response.json();
                if (data.encrypted) {
                    document.getElementById('encrypt-output').value = data.encrypted;
                    document.getElementById('benchmark-enc').textContent = data.time_ms + ' ms';
                } else {
                    throw new Error(data.message || 'Encryption failed');
                }
            } catch (e) {
                alert('Error: ' + e.message);
            }
        }

        async function decrypt() {
            const input = document.getElementById('decrypt-input').value.trim();
            if (!input) {
                alert('Masukkan encrypted payload terlebih dahulu');
                return;
            }

            try {
                const response = await fetch('{{ route('operator.decrypt') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ payload: input })
                });

                const data = await response.json();
                if (data.success) {
                    document.getElementById('decrypt-output').value = data.plaintext;
                    document.getElementById('decrypt-status').textContent = '✓ Decrypt berhasil';
                    document.getElementById('decrypt-status').className = 'text-sm text-success';
                    document.getElementById('benchmark-dec').textContent = data.time_ms + ' ms';
                } else {
                    document.getElementById('decrypt-output').value = '';
                    document.getElementById('decrypt-status').textContent = '✗ ' + data.message;
                    document.getElementById('decrypt-status').className = 'text-sm text-danger';
                }
            } catch (e) {
                alert('Error: ' + e.message);
            }
        }

        async function avalanche() {
            const a = document.getElementById('avalanche-a').value.trim();
            const b = document.getElementById('avalanche-b').value.trim();
            if (!a || !b) {
                alert('Masukkan kedua plaintext');
                return;
            }

            try {
                const response = await fetch('{{ route('operator.avalanche') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ plaintextA: a, plaintextB: b })
                });

                const data = await response.json();
                document.getElementById('avalanche-total').textContent = data.total_bits.toLocaleString();
                document.getElementById('avalanche-changed').textContent = data.changed_bits.toLocaleString();
                document.getElementById('avalanche-percent').textContent = data.percentage + '%';
                document.getElementById('avalanche-diff').textContent = data.binary_a.substring(0, 256) + '\n' + data.binary_b.substring(0, 256);
                document.getElementById('avalanche-result').classList.remove('hidden');
            } catch (e) {
                alert('Error: ' + e.message);
            }
        }

        async function benchmark() {
            const count = parseInt(document.getElementById('benchmark-count').value);
            const btn = document.getElementById('benchmark-btn');
            btn.disabled = true;
            btn.textContent = 'Running...';

            try {
                const response = await fetch('{{ route('operator.benchmark') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ count: count })
                });

                const data = await response.json();
                document.getElementById('bm-count').textContent = data.count;
                document.getElementById('bm-plain').textContent = data.plaintext_time_ms.toLocaleString();
                document.getElementById('bm-enc').textContent = data.encryption_time_ms.toLocaleString();
                document.getElementById('bm-enc-tp').textContent = data.encryption_throughput.toLocaleString();
                document.getElementById('bm-dec').textContent = data.decryption_time_ms.toLocaleString();
                document.getElementById('bm-dec-tp').textContent = data.decryption_throughput.toLocaleString();
                
                document.getElementById('benchmark-enc').textContent = data.encryption_time_ms.toFixed(2) + ' ms';
                document.getElementById('benchmark-dec').textContent = data.decryption_time_ms.toFixed(2) + ' ms';
                document.getElementById('benchmark-result').classList.remove('hidden');
            } catch (e) {
                alert('Error: ' + e.message);
            } finally {
                btn.disabled = false;
                btn.textContent = 'Jalankan Benchmark';
            }
        }

        async function authTagTest() {
            const plaintext = '{"nik":"3273041234567890","nama":"Budi Santoso","email":"budi@example.com","alamat":"Jl. Poros Mamuju No. 123","telepon":"081234567890"}';
            document.getElementById('auth-plaintext').value = plaintext;

            // Step 1: Encrypt
            document.getElementById('auth-step1').classList.add('ring-2', 'ring-yellow-400');
            const encResp = await fetch('{{ route('operator.encrypt') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ plaintext })
            });
            const encData = await encResp.json();
            if (!encData.encrypted) throw new Error(encData.message || 'Encrypt failed');
            
            const originalPayload = encData.encrypted;
            document.getElementById('auth-payload-original').value = originalPayload;
            document.getElementById('auth-step1').classList.remove('ring-yellow-400');
            document.getElementById('auth-step1').classList.add('ring-2', 'ring-green-400');

            // Step 2: Decrypt original
            document.getElementById('auth-step2').classList.add('ring-2', 'ring-yellow-400');
            const decResp = await fetch('{{ route('operator.decrypt') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ payload: originalPayload })
            });
            const decData = await decResp.json();
            if (!decData.success) throw new Error(decData.message || 'Decrypt failed');
            
            document.getElementById('auth-decrypt-original').value = decData.plaintext;
            document.getElementById('auth-step2').classList.remove('ring-yellow-400');
            document.getElementById('auth-step2').classList.add('ring-2', 'ring-green-400');

            // Step 3: Tamper with ciphertext (flip 1 bit in the base64)
            document.getElementById('auth-step3').classList.add('ring-2', 'ring-yellow-400');
            // Decode base64, modify 1 byte, re-encode
            let tamperedPayload = originalPayload;
            const decoded = atob(originalPayload);
            const bytes = new Uint8Array(decoded.length);
            for (let i = 0; i < decoded.length; i++) bytes[i] = decoded.charCodeAt(i);
            // Flip 1 bit in the middle
            bytes[Math.floor(bytes.length / 2)] ^= 0x01;
            // Re-encode
            tamperedPayload = btoa(String.fromCharCode.apply(null, bytes));
            
            document.getElementById('auth-payload-tampered').value = tamperedPayload;

            const tamperedResp = await fetch('{{ route('operator.decrypt') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ payload: tamperedPayload })
            });
            const tamperedData = await tamperedResp.json();
            
            if (!tamperedData.success) {
                document.getElementById('auth-decrypt-tampered').value = tamperedData.message || 'Authentication tag validation failed (expected)';
                document.getElementById('auth-step3').classList.remove('ring-yellow-400');
                document.getElementById('auth-step3').classList.add('ring-2', 'ring-green-400');
            } else {
                document.getElementById('auth-decrypt-tampered').value = 'ERROR: Tampered payload should have failed!';
                document.getElementById('auth-step3').classList.remove('ring-yellow-400');
                document.getElementById('auth-step3').classList.add('ring-2', 'ring-red-400');
            }

            document.getElementById('auth-tag-result').classList.remove('hidden');
        }

        function copyToClipboard(elementId) {
            const textarea = document.getElementById(elementId);
            textarea.select();
            document.execCommand('copy');
            alert('Copied to clipboard!');
        }
    </script>
</body>
</html>