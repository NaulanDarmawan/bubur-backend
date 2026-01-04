<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-xl text-gray-800 leading-tight flex items-center gap-2">
                <i class="fas fa-id-card text-bubur-primary"></i>
                {{ __('Verifikasi Pengguna (KYC)') }}
            </h2>
            <span class="px-3 py-1 text-xs font-semibold text-bubur-primary bg-orange-100 rounded-full border border-orange-200">
                Total Pending: {{ $users->where('kyc_status', 'pending')->count() }}
            </span>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen font-sans">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Flash Message --}}
            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" class="mb-6 flex items-center p-4 bg-green-50 border-l-4 border-green-500 rounded-r shadow-sm">
                    <i class="fas fa-check-circle text-green-500 mr-3 text-xl"></i>
                    <div class="flex-1 text-green-700 font-medium">{{ session('success') }}</div>
                    <button @click="show = false" class="text-green-400 hover:text-green-600"><i class="fas fa-times"></i></button>
                </div>
            @endif

            <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">

                {{-- Table Container --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gradient-to-r from-gray-50 to-gray-100 text-gray-600 uppercase text-xs tracking-wider border-b border-gray-200">
                                <th class="px-6 py-4 font-bold">User Profile</th>
                                <th class="px-6 py-4 font-bold text-center">Status</th>
                                <th class="px-6 py-4 font-bold">Dokumen KTP</th>
                                <th class="px-6 py-4 font-bold text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($users as $user)
                            <tr class="hover:bg-blue-50/30 transition-colors duration-200 group">

                                {{-- Kolom 1: User Info --}}
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <img class="h-10 w-10 rounded-full object-cover border border-gray-200 shadow-sm"
                                                 src="{{ $user->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=random' }}"
                                                 alt="">
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-bold text-gray-900">{{ $user->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                            <div class="text-xs text-gray-400 mt-0.5"><i class="fas fa-phone-alt text-[10px]"></i> {{ $user->phone ?? '-' }}</div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Kolom 2: Status Badge --}}
                                <td class="px-6 py-4 text-center">
                                    @if($user->kyc_status == 'verified')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                            <i class="fas fa-check-circle mr-1"></i> Verified
                                        </span>
                                    @elseif($user->kyc_status == 'pending')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 border border-yellow-200 animate-pulse">
                                            <i class="fas fa-clock mr-1"></i> Review
                                        </span>
                                    @elseif($user->kyc_status == 'rejected')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200">
                                            <i class="fas fa-times-circle mr-1"></i> Ditolak
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500 border border-gray-200">
                                            <i class="fas fa-minus mr-1"></i> Kosong
                                        </span>
                                    @endif
                                </td>

                                {{-- Kolom 3: Dokumen Preview (Modal Alpine.js) --}}
                                <td class="px-6 py-4">
                                    @if($user->ktp_image_url)
                                        <div x-data="{ open: false }">
                                            {{-- Thumbnail Clickable --}}
                                            <div @click="open = true" class="cursor-pointer group/img flex items-center space-x-3">
                                                <img src="{{ asset($user->ktp_image_url) }}" class="h-10 w-16 object-cover rounded shadow-sm border border-gray-200 group-hover/img:scale-105 transition-transform">
                                                <div class="text-xs">
                                                    <p class="font-semibold text-bubur-accent hover:underline">Lihat Foto</p>
                                                    <p class="text-gray-400 text-[10px]">NIK: {{ $user->ktp_nik }}</p>
                                                </div>
                                            </div>

                                            {{-- Modal Fullscreen --}}
                                            <div x-show="open" style="display: none"
                                                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4"
                                                 x-transition.opacity>
                                                <div @click.away="open = false" class="bg-white rounded-lg shadow-2xl max-w-2xl w-full overflow-hidden">
                                                    <div class="p-3 bg-gray-100 border-b flex justify-between items-center">
                                                        <h3 class="font-bold text-gray-700 text-sm">Dokumen: {{ $user->name }}</h3>
                                                        <button @click="open = false" class="text-gray-400 hover:text-red-500 transition"><i class="fas fa-times text-lg"></i></button>
                                                    </div>
                                                    <div class="p-5 flex justify-center bg-gray-50">
                                                        <img src="{{ asset($user->ktp_image_url) }}" class="max-h-[60vh] rounded shadow-md">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400 italic">Belum upload</span>
                                    @endif
                                </td>

                                {{-- Kolom 4: Action Buttons --}}
                                <td class="px-6 py-4 text-center">
                                    @if($user->kyc_status == 'pending')
                                        <div class="flex items-center justify-center space-x-2">
                                            {{-- Approve Button --}}
                                            <form action="{{ route('admin.users.approve', $user->id) }}" method="POST">
                                                @csrf @method('PATCH')
                                                <button type="submit" onclick="return confirm('Verifikasi user ini?')"
                                                        class="w-8 h-8 flex items-center justify-center rounded-full bg-green-50 text-green-600 hover:bg-green-500 hover:text-white transition-all shadow-sm border border-green-200"
                                                        title="Terima">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>

                                            {{-- Reject Button --}}
                                            <form action="{{ route('admin.users.reject', $user->id) }}" method="POST">
                                                @csrf @method('PATCH')
                                                <button type="submit" onclick="return confirm('Tolak user ini?')"
                                                        class="w-8 h-8 flex items-center justify-center rounded-full bg-red-50 text-red-600 hover:bg-red-500 hover:text-white transition-all shadow-sm border border-red-200"
                                                        title="Tolak">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400">-</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-gray-500">
                                    <i class="fas fa-folder-open text-4xl text-gray-300 mb-3"></i>
                                    <p>Belum ada data user.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination Area --}}
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
