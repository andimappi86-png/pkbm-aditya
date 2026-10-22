<x-app-layout>
    <x-slot name="title">Profil Saya</x-slot>

    <div class="max-w-6xl mx-auto space-y-5">
        <x-breadcrumb :items="[['label' => 'Profil Saya']]" />

        <div>
            <h1 class="text-xl font-bold text-navy">Profil Saya</h1>
            <p class="text-sm text-muted mt-1 max-w-2xl">
                Kelola informasi akun dan keamanan akun Anda. Perubahan di sini berlaku untuk seluruh aktivitas Anda di sistem.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
            <!-- Ringkasan identitas -->
            <aside class="lg:col-span-4">
                <div class="lg:sticky lg:top-24">
                    @include('profile.partials.profile-summary')
                </div>
            </aside>

            <!-- Formulir -->
            <div class="lg:col-span-8 space-y-5">
                <div class="card p-5 sm:p-6">
                    @include('profile.partials.update-profile-information-form')
                </div>

                <div class="card p-5 sm:p-6">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
