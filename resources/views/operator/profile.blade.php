<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Operator - PKBM Aditya</title>
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
                    },
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                }
            }
        }
    </script>
    <style>
        * { font-family: 'Inter', sans-serif; }
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
                <div class="px-3 py-2 text-xs font-semibold text-muted uppercase tracking-wider">Utama</div>
                <a href="{{ route('operator.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-text hover:bg-primary-soft hover:text-primary-dark transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    Dashboard
                </a>
                
                <div class="px-3 py-2 text-xs font-semibold text-muted uppercase tracking-wider">Sistem</div>
                <a href="{{ route('operator.security') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-text hover:bg-primary-soft hover:text-primary-dark transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    Security / AES-256-GCM
                </a>
                
                <a href="{{ route('operator.profile') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-primary-soft text-primary-dark font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Profile
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
                <h1 class="text-xl font-semibold text-text">Profile Operator</h1>
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
            <div class="max-w-3xl mx-auto">
                <!-- Profile Header -->
                <div class="card p-8 mb-8 text-center">
                    <div class="w-24 h-24 rounded-full bg-primary-soft flex items-center justify-center mx-auto mb-6">
                        @if(auth()->user()->avatar)
                            <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="Avatar" class="w-full h-full rounded-full object-cover">
                        @else
                            <svg class="w-12 h-12 text-primary-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        @endif
                    </div>
                    <h2 class="text-2xl font-bold text-text">{{ auth()->user()->name }}</h2>
                    <div class="mt-2 flex items-center justify-center gap-2">
                        <span class="px-3 py-1 bg-primary-soft text-primary-dark text-sm rounded-full font-medium">Operator</span>
                    </div>
                    <p class="text-muted mt-4">{{ auth()->user()->email }}</p>
                </div>

                <!-- Profile Form -->
                <div class="card p-6">
                    <h3 class="text-lg font-semibold text-text mb-6">Informasi Profil</h3>
                    
                    @if(session('status'))
                        <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form action="{{ route('operator.profile.update') }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')
                        
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-text mb-1">Nama Lengkap</label>
                                <input type="text" id="name" name="name" value="{{ old('name', auth()->user()->name) }}" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary-soft focus:outline-none transition-colors">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="email" class="block text-sm font-medium text-text mb-1">Email</label>
                                <input type="email" id="email" name="email" value="{{ old('email', auth()->user()->email) }}" required class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary-soft focus:outline-none transition-colors">
                                @error('email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-text mb-1">Nomor Telepon</label>
                            <input type="tel" id="phone" name="phone" value="{{ old('phone', auth()->user()->phone ?? '') }}" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary-soft focus:outline-none transition-colors">
                        </div>

                        <div class="pt-4 border-t border-gray-100">
                            <h4 class="font-medium text-text mb-4">Ubah Password</h4>
                            <p class="text-sm text-muted mb-4">Kosongkan jika tidak ingin mengubah password.</p>
                            
                            <div class="grid md:grid-cols-3 gap-6">
                                <div>
                                    <label for="current_password" class="block text-sm font-medium text-text mb-1">Password Saat Ini</label>
                                    <input type="password" id="current_password" name="current_password" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary-soft focus:outline-none transition-colors">
                                    @error('current_password')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div>
                                    <label for="password" class="block text-sm font-medium text-text mb-1">Password Baru</label>
                                    <input type="password" id="password" name="password" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary-soft focus:outline-none transition-colors">
                                    @error('password')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div>
                                    <label for="password_confirmation" class="block text-sm font-medium text-text mb-1">Konfirmasi Password Baru</label>
                                    <input type="password" id="password_confirmation" name="password_confirmation" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary-soft focus:outline-none transition-colors">
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-4 pt-4 border-t border-gray-100">
                            <a href="{{ route('operator.dashboard') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium">Batal</a>
                            <button type="submit" class="px-4 py-2 bg-[#167C80] text-white rounded-lg hover:bg-[#0F5F63] transition-colors font-medium">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</body>
</html>