<x-filament-panels::page>
    <div class="fixed inset-0 flex w-full h-screen bg-gray-50 dark:bg-gray-950 overflow-hidden z-50">
        <!-- Sisi Kiri: Branding / Informasi Perusahaan -->
        <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-slate-900 via-slate-800 to-gray-900 p-12 flex-col justify-between relative text-white shadow-2xl">
            <div class="absolute -right-10 -bottom-10 w-96 h-96 bg-primary-500/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -left-10 -top-10 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary-600 flex items-center justify-center font-bold text-xl shadow-lg">
                    JB
                </div>
                <span class="text-xl font-bold tracking-wider">DC JB PRINTING</span>
            </div>

            <div class="relative z-10 max-w-lg my-auto">
                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-primary-500/20 text-primary-300 border border-primary-500/30">
                    Enterprise System
                </span>
                <h1 class="text-4xl font-extrabold tracking-tight mt-4 mb-4 leading-tight">
                    Sistem Inventori & Keuangan Terpadu
                </h1>
                <p class="text-gray-300 text-base leading-relaxed">
                    Kelola stok bahan, transaksi cabang, barang masuk, barang keluar, hingga laporan keuangan dengan cepat, akurat, dan real-time.
                </p>
            </div>

            <div class="relative z-10 text-xs text-gray-400">
                &copy; {{ date('Y') }} DC JB Printing. All rights reserved.
            </div>
        </div>

        <!-- Sisi Kanan: Form Login Filament -->
        <div class="flex-1 flex items-center justify-center p-6 sm:p-12 bg-white dark:bg-gray-900 overflow-y-auto">
            <div class="w-full max-w-md space-y-6">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white">
                        Selamat Datang! 👋
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Silakan masuk menggunakan akun authorized Anda.
                    </p>
                </div>

                {{ $this->form }}

                <div class="mt-6">
                    {{ $this->loginAction }}
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>