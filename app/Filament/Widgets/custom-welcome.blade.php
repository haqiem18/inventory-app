cat << 'EOF' > resources/views/filament/widgets/custom-welcome.blade.php
<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 py-2">
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white">
                    Selamat Datang, {{ auth()->user()->name ?? 'Superadmin' }}! 👋
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Sistem Inventori & Keuangan DC JB Printing siap digunakan hari ini.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('filament.admin.resources.stock-outs.index') }}" class="inline-flex items-center justify-center gap-1.5 py-2.5 px-4 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-500 transition rounded-xl shadow-md">
                    <span class="text-base font-bold">+</span> Barang Keluar
                </a>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
EOF