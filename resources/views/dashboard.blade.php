<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Admin') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-bubur-light min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- WELCOME BANNER --}}
            <div class="bg-gradient-to-r from-bubur-primary to-bubur-secondary overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-white">
                    <h3 class="text-2xl font-bold">Halo, Admin! 👋</h3>
                    <p class="mt-2 opacity-90">Selamat datang di pusat kendali Bursa Barang Untuk Rental.</p>
                </div>
            </div>

            {{-- STATISTIC CARDS --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">

                {{-- Card 1: Users --}}
                <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-bubur-accent hover:shadow-lg transition duration-300">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-bubur-accent">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-500 text-sm">Total User</p>
                            <h4 class="text-2xl font-bold text-gray-800">{{ $totalUsers ?? 0 }}</h4>
                        </div>
                    </div>
                </div>

                {{-- Card 2: Products --}}
                <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-bubur-primary hover:shadow-lg transition duration-300">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-orange-100 text-bubur-primary">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-500 text-sm">Total Produk</p>
                            <h4 class="text-2xl font-bold text-gray-800">{{ $totalProducts ?? 0 }}</h4>
                        </div>
                    </div>
                </div>

                {{-- Card 3: Active Rentals --}}
                <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-yellow-400 hover:shadow-lg transition duration-300">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-500 text-sm">Sedang Disewa</p>
                            <h4 class="text-2xl font-bold text-gray-800">{{ $totalRentals ?? 0 }}</h4>
                        </div>
                    </div>
                </div>

                {{-- Card 4: KYC Pending --}}
                <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-red-500 hover:shadow-lg transition duration-300 relative overflow-hidden">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-red-100 text-red-500">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0c0 .884.356 3.25 3 3.25M13 6a2 2 0 012-2"></path></svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-500 text-sm">Butuh Verifikasi</p>
                            <h4 class="text-2xl font-bold text-gray-800">{{ $pendingKyc ?? 0 }}</h4>
                        </div>
                    </div>
                    @if(($pendingKyc ?? 0) > 0)
                        <span class="absolute top-2 right-2 flex h-3 w-3">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                        </span>
                    @endif
                </div>

            </div>

            {{-- CONTENT AREA --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-bold mb-4 border-b pb-2">Aksi Cepat</h3>
                    <p class="text-gray-500">Belum ada aktivitas terbaru.</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
