<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold tracking-tight text-gray-950 dark:text-white">
                    Selamat Datang, {{ auth()->user()->name }}! 👋
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Sistem Inventori & Keuangan DC JB Printing siap digunakan hari ini.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('filament.admin.resources.stock-outs.index') }}" class="inline-flex items-center justify-center py-2 px-4 text-sm font-semibold text-white bg-primary-600 hover:bg-primary-500 rounded-lg shadow">
                    + Barang Keluar
                </a>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>