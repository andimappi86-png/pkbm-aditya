@props(['title' => ''])


<header class="bg-surface/95 backdrop-blur border-b border-border sticky top-0 z-40 shadow-sm">
    <div class="flex items-center justify-between h-16 px-4 lg:px-6">
        <!-- Mobile menu button -->
        <button 
            type="button"
            @click="sidebarOpen = !sidebarOpen" 
            :aria-expanded="sidebarOpen.toString()"
            aria-label="Toggle sidebar"
            class="lg:hidden p-2 rounded-lg text-muted hover:text-primary-dark hover:bg-primary-soft transition-colors focus:outline-none focus:ring-2 focus:ring-primary/20"
        >
            <x-icon name="menu" />
        </button>

        <!-- Breadcrumb / Title -->
        <div class="flex-1">
            @if($title)
                <h1 class="text-xl font-semibold text-navy">{{ $title }}</h1>
            @else
                {{ $slot }}
            @endif
        </div>

        <!-- Right section -->
        <div class="flex items-center gap-3">
            <!-- User dropdown -->
            <div class="relative" x-data="{ open: false }">
                <button 
                    @click="open = !open" 
                    @click.away="open = false"
                    :aria-expanded="open.toString()"
                    aria-haspopup="true"
                    class="flex items-center gap-3 p-2 rounded-xl hover:bg-primary-soft transition-colors focus:outline-none focus:ring-2 focus:ring-primary/20"
                >
                    <x-avatar 
                        :src="auth()->user()->avatar" 
                        :alt="auth()->user()->name" 
                        size="sm" 
                    />
                    <div class="hidden lg:block text-left">
                        <div class="text-sm font-medium text-navy">{{ auth()->user()->name }}</div>
                        <div class="text-xs text-muted">{{ ucfirst(auth()->user()->primaryRole()) }}</div>
                    </div>
                    <x-icon name="chevron-down" class="w-4 h-4 text-muted" />
                </button>

                <!-- Dropdown menu -->
                <div 
                    x-show="open" 
                    x-cloak
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave-end="transform opacity-0 scale-95"
                    class="absolute right-0 mt-2 w-56 bg-surface rounded-xl shadow-soft-lg border border-border overflow-hidden"
                >
                    <div class="p-3 border-b border-border">
                        <div class="font-medium text-navy">{{ auth()->user()->name }}</div>
                        <div class="text-sm text-muted">{{ auth()->user()->email }}</div>
                    </div>
                    
                    <div class="py-1">
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-navy hover:bg-background transition-colors">
                            <x-icon name="user-check" class="w-4 h-4" />
                            Profil Saya
                        </a>
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-navy hover:bg-background transition-colors">
                            <x-icon name="cog" class="w-4 h-4" />
                            Pengaturan
                        </a>
                    </div>

                    <div class="border-t border-border py-1">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex items-center gap-3 w-full px-4 py-2 text-sm text-accent hover:bg-accent-soft transition-colors">
                                <x-icon name="logout" class="w-4 h-4" />
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>