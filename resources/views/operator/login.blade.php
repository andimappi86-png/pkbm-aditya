<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Operator - PKBM Aditya</title>
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
<body class="bg-background min-h-screen flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-primary-soft mx-auto mb-4">
                <svg class="w-10 h-10 text-primary-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-text">PKBM ADITYA</h1>
            <p class="text-muted text-sm">Kabupaten Mamuju - Sulawesi Barat</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
            <div class="mb-6 text-center">
                <h2 class="text-xl font-semibold text-text">Login Operator</h2>
                <p class="text-muted text-sm mt-1">Masuk ke dashboard operator untuk monitoring sistem</p>
            </div>

            @if(session('status'))
                <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('operator.login') }}" method="POST" class="space-y-4">
                @csrf
                
                <div>
                    <label for="email" class="block text-sm font-medium text-text mb-1">Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="{{ old('email') }}" 
                        required
                        autocomplete="email"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary-soft focus:outline-none transition-colors"
                        placeholder="operator@pkbm-aditya.test"
                    >
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-text mb-1">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        autocomplete="current-password"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary-soft focus:outline-none transition-colors"
                        placeholder="••••••••"
                    >
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input 
                            type="checkbox" 
                            name="remember" 
                            class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary focus:ring-2"
                        >
                        <span class="text-sm text-text">Ingat saya</span>
                    </label>
                </div>

                <button 
                    type="submit" 
                    class="w-full bg-primary text-white py-3 rounded-lg font-medium hover:bg-primary-dark focus:ring-2 focus:ring-primary-soft focus:ring-offset-2 transition-colors"
                >
                    Masuk ke Dashboard
                </button>
            </form>

            <div class="mt-6 pt-6 border-t border-gray-100 text-center">
                <p class="text-sm text-muted">
                    Demo: <code class="bg-surface px-2 py-1 rounded text-xs">operator@pkbm-aditya.test</code> / <code class="bg-surface px-2 py-1 rounded text-xs">password</code>
                </p>
            </div>
        </div>

        <div class="text-center mt-6 text-sm text-muted">
            &copy; {{ date('Y') }} PKBM Aditya Kabupaten Mamuju. All rights reserved.
        </div>
    </div>
</body>
</html>