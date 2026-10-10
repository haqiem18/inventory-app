<x-filament-widgets::widget>
    <x-filament::section>
        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; gap: 1.5rem;">
            <div style="flex: 1; min-width: 0;">
                <h2 class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white truncate">
                    Selamat Datang, {{ ucwords(auth()->user()->name ?? 'Superadmin') }}! 👋
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Sistem Inventori & Keuangan DC JB Printing siap digunakan hari ini.
                </p>
            </div>
            <div style="flex-shrink: 0;">
                <a href="{{ route('filament.admin.resources.stock-outs.index') }}" class="inline-flex items-center justify-center gap-1.5 py-2.5 px-4 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-500 transition rounded-xl shadow-md whitespace-nowrap">
                    <span class="text-base font-bold">+</span> Barang Keluar
                </a>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>