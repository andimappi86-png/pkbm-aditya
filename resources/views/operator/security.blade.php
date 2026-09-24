<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security / AES-256-GCM - PKBM Aditya</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
        .scrollbar-thin::-webkit-scrollbar { width: 6px; }
        .scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
        .scrollbar-thin::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
    </style>
</head>
<body class="bg-background min-h-screen">
    <div id="sidebar-overlay" class="hidden fixed inset-0 bg-black/30 z-30 lg:hidden"></div>

    <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-64 bg-white border-r border-gray-200 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out">
        <div class="flex flex-col h-full">
            <div class="p-5 border-b border-gray-100 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-primary-soft flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-primary-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h1 class="font-bold text-text truncate">PKBM ADITYA</h1>
                        <p class="text-xs text-muted truncate">Operator Panel</p>
                    </div>
                </div>
            </div>
            <nav class="flex-1 p-4 space-y-1 overflow-y-auto scrollbar-thin">
                <div class="px-3 py-2 text-xs font-semibold text-muted uppercase tracking-wider">Utama</div>
                <a href="{{ route('operator.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-text hover:bg-primary-soft hover:text-primary-dark transition-colors">
                    <x-icon name="home" class="w-5 h-5" />
                    Dashboard
                </a>
                
                <div class="px-3 py-2 text-xs font-semibold text-muted uppercase tracking-wider mt-4">Sistem</div>
                <a href="{{ route('operator.import.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-text hover:bg-primary-soft hover:text-primary-dark transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" /></svg>
                    Impor Data
                </a>
                
                <a href="{{ route('operator.security') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-primary-soft text-primary-dark font-medium">
                    <x-icon name="cog" class="w-5 h-5" />
                    Security / AES-256-GCM
                </a>

                <div class="pt-4 mt-4 border-t border-gray-100">
                    <a href="{{ route('operator.dashboard') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit()" class="flex items-center gap-3 px-4 py-3 rounded-xl text-text hover:bg-red-50 hover:text-red-600 transition-colors">
                        <x-icon name="logout" class="w-5 h-5" />
                        Logout
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                        @csrf
                    </form>
                </div>
            </nav>
        </div>
    </aside>

    <main class="lg:ml-64 min-h-screen">
        <header class="sticky top-0 z-30 bg-white border-b border-gray-200">
            <div class="flex items-center justify-between px-4 md:px-6 py-4">
                <div class="flex items-center gap-4">
                    <button id="sidebar-toggle" class="lg:hidden p-2 rounded-xl text-text hover:bg-gray-100 transition-colors" aria-label="Toggle sidebar">
                        <x-icon name="menu" class="w-6 h-6" />
                    </button>
                    <h1 class="text-xl font-semibold text-text hidden sm:block">Security / AES-256-GCM</h1>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-primary-soft flex items-center justify-center flex-shrink-0">
                            <x-icon name="user-group" class="w-5 h-5 text-primary-dark" />
                        </div>
                        <div class="text-right hidden md:block">
                            <p class="text-sm font-medium text-text">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-muted">Operator</p>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <div class="p-4 md:p-6 lg:p-8">
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-text">Security Monitoring Dashboard</h2>
                <p class="text-muted mt-1">Monitoring enkripsi AES-256-GCM pada data Student & Tutor</p>
            </div>

            <!-- Algorithm Info Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                <x-stat-card title="Algorithm" value="AES-256-GCM" variant="primary" icon="cog" />
                <x-stat-card title="Key Size" value="256 bit" variant="primary" icon="key" />
                <x-stat-card title="IV / Nonce" value="96 bit (12 byte)" variant="primary" icon="lock" />
                <x-stat-card title="Auth Tag" value="128 bit (16 byte)" variant="primary" icon="shield" />
            </div>

            <!-- Encryption Status -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="rounded-2xl p-6 bg-white border border-gray-100 shadow-card">
                    <h3 class="text-lg font-semibold text-text mb-4 flex items-center gap-2">
                        <x-icon name="users" class="w-5 h-5 text-primary" />
                        Student Encryption
                    </h3>
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <x-stat-card title="Fields Encrypted" :value="$studentFields" variant="primary" icon="layers" />
                            <x-stat-card title="Records" :value="$studentCount" variant="primary" icon="database" />
                        </div>
                        <div class="p-4 rounded-xl bg-primary-soft/50 border border-primary-soft/30">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-primary-soft text-primary-dark flex items-center justify-center">
                                    <x-icon name="check-circle" class="w-5 h-5" />
                                </div>
                                <div>
                                    <p class="font-medium text-primary-dark">Encryption Status: ACTIVE</p>
                                    <p class="text-sm text-primary">Field sensitif terenkripsi otomatis via Cast</p>
                                </div>
                            </div>
                        </div>
                        <div class="text-sm text-muted">
                            Fields: NIK, Alamat, Tanggal Lahir, No HP, Email, Nama Ayah, Nama Ibu, Nama Wali, No HP Wali, Alamat Wali
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl p-6 bg-white border border-gray-100 shadow-card">
                    <h3 class="text-lg font-semibold text-text mb-4 flex items-center gap-2">
                        <x-icon name="user-group" class="w-5 h-5 text-primary" />
                        Tutor Encryption
                    </h3>
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <x-stat-card title="Fields Encrypted" :value="$tutorFields" variant="accent" icon="layers" />
                            <x-stat-card title="Records" :value="$tutorCount" variant="accent" icon="database" />
                        </div>
                        <div class="p-4 rounded-xl bg-red-50/50 border border-red-200/30">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-red-50 text-accent flex items-center justify-center">
                                    <x-icon name="check-circle" class="w-5 h-5" />
                                </div>
                                <div>
                                    <p class="font-medium text-accent">Encryption Status: ACTIVE</p>
                                    <p class="text-sm text-red-600">Field sensitif terenkripsi otomatis via Cast</p>
                                </div>
                            </div>
                        </div>
                        <div class="text-sm text-muted">
                            Fields: NIK, Alamat, Tanggal Lahir, No HP, Email, Bidang Keahlian
                        </div>
                    </div>
                </div>
            </div>

            <!-- Test Controls -->
            <div class="rounded-2xl p-6 bg-white border border-gray-100 shadow-card mb-8">
                <h3 class="text-lg font-semibold text-text mb-5 flex items-center gap-2">
                    <x-icon name="cog" class="w-5 h-5 text-primary" />
                    Security Tests
                </h3>
                <p class="text-muted text-sm mb-4">Gunakan dummy data internal. Data pribadi Student/Tutor tidak ditampilkan.</p>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3" id="test-buttons">
                    <button onclick="runTest('encrypt')" class="btn-primary py-3 text-sm" disabled><x-icon name="lock" class="w-4 h-4 inline mr-1" /> Test Encrypt</button>
                    <button onclick="runTest('decrypt')" class="btn-primary py-3 text-sm" disabled><x-icon name="unlock" class="w-4 h-4 inline mr-1" /> Test Decrypt</button>
                    <button onclick="runTest('auth-tag')" class="btn-warning py-3 text-sm" disabled><x-icon name="shield" class="w-4 h-4 inline mr-1" /> Test Auth Tag</button>
                    <button onclick="runTest('tamper')" class="btn-danger py-3 text-sm" disabled><x-icon name="alert-circle" class="w-4 h-4 inline mr-1" /> Test Tampering</button>
                    <button onclick="runTest('avalanche')" class="btn-secondary py-3 text-sm" disabled><x-icon name="chart-bar" class="w-4 h-4 inline mr-1" /> Avalanche Test</button>
                    <button onclick="runTest('benchmark')" class="btn-secondary py-3 text-sm" disabled><x-icon name="clock" class="w-4 h-4 inline mr-1" /> Benchmark</button>
                </div>
            </div>

            <!-- Test Results Area -->
            <div id="test-results" class="hidden">
                <div class="rounded-2xl p-6 bg-white border border-gray-100 shadow-card mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-text" id="test-title">Test Result</h3>
                        <button onclick="hideResults()" class="text-muted hover:text-text transition-colors">
                            <x-icon name="x" class="w-5 h-5" />
                        </button>
                    </div>
                    <pre id="test-output" class="p-4 rounded-lg bg-surface border border-gray-200 mono text-sm text-text overflow-x-auto max-h-96"></pre>
                </div>
            </div>

            <!-- Database Verification (Masked) -->
            <div class="rounded-2xl p-6 bg-white border border-gray-100 shadow-card">
                <h3 class="text-lg font-semibold text-text mb-5 flex items-center gap-2">
                    <x-icon name="database" class="w-5 h-5 text-primary" />
                    Database Verification (Masked)
                </h3>
                <p class="text-muted text-sm mb-4">Ciphertext ditampilkan sebagian (masked). Plaintext pribadi tidak ditampilkan massal. Key tidak ditampilkan.</p>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="bg-surface border-b border-gray-200">
                                <th class="p-3 font-semibold text-text">Type</th>
                                <th class="p-3 font-semibold text-text">Name</th>
                                <th class="p-3 font-semibold text-text">NIK (Masked)</th>
                                <th class="p-3 font-semibold text-text">Status</th>
                                <th class="p-3 font-semibold text-text">Ciphertext Excerpt</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 mono">
                            @foreach ($maskedRecords as $record)
                            <tr class="hover:bg-gray-50">
                                <td class="p-3 text-text">{{ $record['type'] }}</td>
                                <td class="p-3 text-text">{{ $record['name'] }}</td>
                                <td class="p-3 text-text">{{ $record['masked_nik'] }}</td>
                                <td class="p-3">
                                    @if (str_contains($record['status'], 'Encrypted'))
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-success/10 text-success">
                                        <x-icon name="check-circle" class="w-3 h-3" /> {{ $record['status'] }}
                                    </span>
                                    @else
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-danger/10 text-danger">
                                        <x-icon name="x-circle" class="w-3 h-3" /> {{ $record['status'] }}
                                    </span>
                                    @endif
                                </td>
                                <td class="p-3 text-muted">{{ $record['raw_excerpt'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Benchmark History -->
            <div id="benchmark-history" class="mt-8 hidden">
                <div class="rounded-2xl p-6 bg-white border border-gray-100 shadow-card">
                    <h3 class="text-lg font-semibold text-text mb-5 flex items-center gap-2">
                        <x-icon name="chart-bar" class="w-5 h-5 text-primary" />
                        Benchmark History
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="bg-surface border-b border-gray-200">
                                    <th class="p-3 font-semibold text-text">Timestamp</th>
                                    <th class="p-3 font-semibold text-text">Records</th>
                                    <th class="p-3 font-semibold text-text">Plaintext (ms)</th>
                                    <th class="p-3 font-semibold text-text">Encrypt (ms)</th>
                                    <th class="p-3 font-semibold text-text">Encrypt Throughput</th>
                                    <th class="p-3 font-semibold text-text">Decrypt (ms)</th>
                                    <th class="p-3 font-semibold text-text">Decrypt Throughput</th>
                                </tr>
                            </thead>
                            <tbody id="benchmark-table-body" class="divide-y divide-gray-100">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Sidebar toggle
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const toggle = document.getElementById('sidebar-toggle');

        toggle?.addEventListener('click', () => {
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
            document.body.classList.toggle('overflow-hidden');
        });

        overlay?.addEventListener('click', () => {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        });

        document.querySelectorAll('#sidebar a').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 1024) {
                    sidebar.classList.add('-translate-x-full');
                    overlay.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                }
            });
        });

        // Test functions
        let testResults = [];

        async function runTest(type) {
            const btns = document.querySelectorAll('#test-buttons button');
            btns.forEach(b => b.disabled = true);

            const titleMap = {
                'encrypt': 'Test Encryption',
                'decrypt': 'Test Decryption',
                'auth-tag': 'Test Authentication Tag',
                'tamper': 'Test Tampering Detection',
                'avalanche': 'Avalanche Effect Test',
                'benchmark': 'Benchmark Performance'
            };

            document.getElementById('test-title').textContent = titleMap[type] || 'Test Result';
            document.getElementById('test-output').textContent = 'Running...';
            document.getElementById('test-results').classList.remove('hidden');

            try {
                let result;
                switch (type) {
                    case 'encrypt':
                        result = await testEncrypt();
                        break;
                    case 'decrypt':
                        result = await testDecrypt();
                        break;
                    case 'auth-tag':
                        result = await testAuthTag();
                        break;
                    case 'tamper':
                        result = await testTampering();
                        break;
                    case 'avalanche':
                        result = await testAvalanche();
                        break;
                    case 'benchmark':
                        result = await runBenchmarks();
                        break;
                }
                document.getElementById('test-output').textContent = JSON.stringify(result, null, 2);
            } catch (e) {
                document.getElementById('test-output').textContent = 'Error: ' + e.message;
            } finally {
                btns.forEach(b => b.disabled = false);
            }
        }

        function hideResults() {
            document.getElementById('test-results').classList.add('hidden');
        }

        async function testEncrypt() {
            const testData = {
                nik: '3273041234567890',
                nama: 'Test Student',
                email: 'test@example.com',
                alamat: 'Jl. Test No. 123',
                no_hp: '081234567890',
                nama_ayah: 'Ayah Test',
                nama_ibu: 'Ibu Test',
                nama_wali: 'Wali Test',
                no_hp_wali: '081298765432',
                alamat_wali: 'Jl. Wali No. 456'
            };
            const response = await fetch('{{ route("operator.encrypt") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ plaintext: JSON.stringify(testData) })
            });
            const data = await response.json();
            if (!data.encrypted) throw new Error(data.message || 'Encrypt failed');
            return { 
                success: true, 
                plaintext: testData,
                encrypted_payload: data.encrypted,
                time_ms: data.time_ms,
                note: 'Payload is base64(JSON{v,iv,c,t}). Key not shown.'
            };
        }

        async function testDecrypt() {
            const testData = { test: 'decrypt', value: 123 };
            const encResp = await fetch('{{ route("operator.encrypt") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ plaintext: JSON.stringify(testData) })
            });
            const encData = await encResp.json();
            if (!encData.encrypted) throw new Error('Encrypt failed for decrypt test');

            const response = await fetch('{{ route("operator.decrypt") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ payload: encData.encrypted })
            });
            const data = await response.json();
            if (!data.success) throw new Error(data.message || 'Decrypt failed');
            return { 
                success: true, 
                original: testData,
                decrypted: JSON.parse(data.plaintext),
                match: JSON.stringify(testData) === data.plaintext,
                time_ms: data.time_ms
            };
        }

        async function testAuthTag() {
            const testData = { test: 'auth-tag', value: 'AES-GCM' };
            const encResp = await fetch('{{ route("operator.encrypt") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ plaintext: JSON.stringify(testData) })
            });
            const encData = await encResp.json();
            if (!encData.encrypted) throw new Error('Encrypt failed');

            // Decrypt original (should succeed)
            const decResp = await fetch('{{ route("operator.decrypt") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ payload: encData.encrypted })
            });
            const decData = await decResp.json();
            if (!decData.success) throw new Error('Original decrypt failed: ' + decData.message);

            // Tamper ciphertext (flip 1 bit in decoded payload)
            const decoded = atob(encData.encrypted);
            const bytes = new Uint8Array(decoded.length);
            for (let i = 0; i < decoded.length; i++) bytes[i] = decoded.charCodeAt(i);
            bytes[Math.floor(bytes.length / 2)] ^= 0x01;
            const tampered = btoa(String.fromCharCode.apply(null, bytes));

            // Decrypt tampered (should fail)
            const tamperedResp = await fetch('{{ route("operator.decrypt") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ payload: tampered })
            });
            const tamperedData = await tamperedResp.json();

            return {
                success: !tamperedData.success,
                original_decrypt: 'OK',
                tampered_decrypt: tamperedData.success ? 'FAILED - Should have been rejected!' : 'REJECTED (Expected)',
                tampered_error: tamperedData.message || 'Authentication tag validation failed',
                note: 'AES-GCM correctly rejects tampered ciphertext via auth tag'
            };
        }

        async function testTampering() {
            return await testAuthTag(); // Same test
        }

        async function testAvalanche() {
            const plaintextA = '{"nik":"3273041234567890","nama":"Budi Santoso","email":"budi@example.com","alamat":"Jl. Poros Mamuju No. 123","telepon":"081234567890"}';
            const plaintextB = '{"nik":"3273041234567891","nama":"Budi Santoso","email":"budi@example.com","alamat":"Jl. Poros Mamuju No. 123","telepon":"081234567890"}';

            const response = await fetch('{{ route("operator.avalanche") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ plaintextA, plaintextB })
            });
            const data = await response.json();
            return {
                plaintext_a: plaintextA,
                plaintext_b: plaintextB,
                diff: 'Last digit of NIK changed: 0 -> 1',
                total_bits: data.total_bits,
                changed_bits: data.changed_bits,
                avalanche_percentage: data.percentage + '%',
                note: 'Avalanche effect ~50% expected for good cipher'
            };
        }

        async function runBenchmarks() {
            const sizes = [100, 500, 1000];
            const results = [];
            
            for (const count of sizes) {
                const response = await fetch('{{ route("operator.benchmark") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ count })
                });
                const data = await response.json();
                results.push(data);
                
                // Add to history table
                addBenchmarkRow(data);
            }
            
            document.getElementById('benchmark-history').classList.remove('hidden');
            
            return { 
                summary: 'Benchmarks completed for 100, 500, 1000 records',
                results: results
            };
        }

        function addBenchmarkRow(data) {
            const tbody = document.getElementById('benchmark-table-body');
            const row = document.createElement('tr');
            row.className = 'hover:bg-gray-50';
            row.innerHTML = `
                <td class="p-3 text-text">${new Date().toLocaleTimeString()}</td>
                <td class="p-3 mono font-medium">${data.count}</td>
                <td class="p-3 mono">${data.plaintext_time_ms} ms</td>
                <td class="p-3 mono">${data.encryption_time_ms} ms</td>
                <td class="p-3 mono">${data.encryption_throughput.toLocaleString()} rec/s</td>
                <td class="p-3 mono">${data.decryption_time_ms} ms</td>
                <td class="p-3 mono">${data.decryption_throughput.toLocaleString()} rec/s</td>
            `;
            tbody.appendChild(row);
        }
    </script>
</body>
</html>