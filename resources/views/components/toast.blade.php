<div
    x-data="{
        notices: [],
        add(notice) {
            notice.id = Date.now() + Math.random();
            this.notices.push(notice);
            setTimeout(() => {
                this.remove(notice.id);
            }, notice.timeout || 4500);
        },
        remove(id) {
            this.notices = this.notices.filter(n => n.id !== id);
        }
    }"
    x-init="
        @if (session('status') || session('success'))
            add({ type: 'success', text: '{{ addslashes(session('status') ?? session('success')) }}' });
        @endif
        @if (session('error'))
            add({ type: 'error', text: '{{ addslashes(session('error')) }}' });
        @endif
        @if (session('warning'))
            add({ type: 'warning', text: '{{ addslashes(session('warning')) }}' });
        @endif
        @if ($errors->any() && !request()->routeIs('login') && !request()->routeIs('password.*'))
            add({ type: 'error', text: 'Terdapat kesalahan pada formulir yang Anda kirim.' });
        @endif
    "
    class="fixed top-5 right-5 z-50 flex flex-col space-y-3 pointer-events-none max-w-sm w-full"
    aria-live="polite"
>
    <template x-for="notice in notices" :key="notice.id">
        <div
            x-show="true"
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="opacity-0 translate-y-2 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-2 scale-95"
            :class="{
                'bg-surface border-l-4 border-primary text-navy shadow-soft-lg': notice.type === 'success',
                'bg-surface border-l-4 border-accent text-navy shadow-soft-lg': notice.type === 'error',
                'bg-surface border-l-4 border-muted text-navy shadow-soft-lg': notice.type === 'warning'
            }"
            class="pointer-events-auto p-4 rounded-xl border border-border flex items-start gap-3"
            role="alert"
        >
            <!-- Icon -->
            <div class="flex-shrink-0 mt-0.5">
                <template x-if="notice.type === 'success'">
                    <div class="w-6 h-6 rounded-full bg-primary-soft text-primary-dark flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </template>
                <template x-if="notice.type === 'error'">
                    <div class="w-6 h-6 rounded-full bg-accent-soft text-accent flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                </template>
                <template x-if="notice.type === 'warning'">
                    <div class="w-6 h-6 rounded-full bg-background text-muted flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </template>
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-muted uppercase tracking-wider" x-text="notice.type === 'success' ? 'Berhasil' : (notice.type === 'error' ? 'Perhatian' : 'Peringatan')"></p>
                <p class="text-sm font-medium text-navy mt-0.5 leading-snug" x-text="notice.text"></p>
            </div>

            <!-- Close button -->
            <button
                type="button"
                @click="remove(notice.id)"
                class="flex-shrink-0 text-muted hover:text-navy p-1 rounded-lg transition"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </template>
</div>
