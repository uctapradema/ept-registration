<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg sm:text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Riwayat Pendaftaran') }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-4 p-3 sm:p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg text-sm sm:text-base">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-3 sm:p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg text-sm sm:text-base">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-4 sm:p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Daftar Pendaftaran
                        </h3>
                        <a href="{{ route('mahasiswa.schedules.index') }}" 
                           class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md transition">
                            Daftar Ujian Baru
                        </a>
                    </div>

                    @if($registrations->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">No. Daftar</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Jadwal</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Tanggal</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($registrations as $reg)
                                        @php
                                            $statusColors = [
                                                'pending_payment' => 'bg-yellow-100 text-yellow-800',
                                                'awaiting_verification' => 'bg-blue-100 text-blue-800',
                                                'verified' => 'bg-green-100 text-green-800',
                                                'rejected' => 'bg-red-100 text-red-800',
                                                'cancelled' => 'bg-gray-100 text-gray-800',
                                                'expired' => 'bg-orange-100 text-orange-800',
                                            ];
                                            $statusLabels = [
                                                'pending_payment' => 'Menunggu Pembayaran',
                                                'awaiting_verification' => 'Menunggu Verifikasi',
                                                'verified' => 'Terverifikasi',
                                                'rejected' => 'Ditolak',
                                                'cancelled' => 'Dibatalkan',
                                                'expired' => 'Kadaluarsa',
                                            ];
                                        @endphp
                                        <tr>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm font-mono text-gray-900 dark:text-white">
                                                {{ $reg->registration_number }}
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                {{ $reg->examSchedule->title ?? '-' }}
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ $reg->examSchedule->exam_date->format('d F Y') ?? '-' }}
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColors[$reg->status] ?? 'bg-gray-100 text-gray-800' }}">
                                                    {{ $statusLabels[$reg->status] ?? $reg->status }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm">
                                                <a href="{{ route('mahasiswa.registrations.show', $reg) }}" 
                                                   class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                                    Detail
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-4">
                            {{ $registrations->links() }}
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <p class="mt-2 text-gray-500 dark:text-gray-400">Belum ada pendaftaran</p>
                            <a href="{{ route('mahasiswa.schedules.index') }}" 
                               class="mt-4 inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md transition">
                                Daftar Ujian
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
