<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg sm:text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard Mahasiswa') }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8">
            <!-- Welcome Message & Profile Info -->
            <div class="mb-6">
                <h3 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">
                    Selamat Datang, {{ Auth::user()->name }}!
                </h3>
                <p class="mt-1 sm:mt-2 text-gray-600 dark:text-gray-400">
                    Kelola pendaftaran ujian English Proficiency Test Anda di sini.
                </p>
            </div>

            <!-- Profile Info Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-4 sm:p-6">
                    <h4 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white mb-3 sm:mb-4">
                        Informasi Mahasiswa
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 sm:p-4">
                            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">NIM</p>
                            <p class="font-semibold text-sm sm:text-base text-gray-900 dark:text-white">{{ Auth::user()->nim ?? '-' }}</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 sm:p-4">
                            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Program Studi</p>
                            <p class="font-semibold text-sm sm:text-base text-gray-900 dark:text-white">{{ Auth::user()->major ?? '-' }}</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 sm:p-4">
                            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Fakultas</p>
                            <p class="font-semibold text-sm sm:text-base text-gray-900 dark:text-white">{{ Auth::user()->faculty ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alert Messages -->
            @if(session('success'))
                <div class="mb-4 sm:mb-6 p-3 sm:p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg text-sm sm:text-base">
                    {{ session('success') }}
                </div>
            @endif
            
            @if(session('warning'))
                <div class="mb-4 sm:mb-6 p-3 sm:p-4 bg-yellow-100 border border-yellow-400 text-yellow-700 rounded-lg text-sm sm:text-base">
                    {{ session('warning') }}
                </div>
            @endif
            
            @if(session('error'))
                <div class="mb-4 sm:mb-6 p-3 sm:p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg text-sm sm:text-base">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Active Registration Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-4 sm:p-6">
                    <h4 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white mb-3 sm:mb-4">
                        Status Pendaftaran Aktif
                    </h4>
                    
                    @if($activeRegistration)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3 sm:p-4">
                            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-3 sm:mb-4 gap-2">
                                <div>
                                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Nomor Pendaftaran</p>
                                    <p class="font-mono font-semibold text-base sm:text-lg text-gray-900 dark:text-white">
                                        {{ $activeRegistration->registration_number }}
                                    </p>
                                </div>
                                @php
                                    $statusColors = [
                                        'pending_payment' => 'bg-yellow-100 text-yellow-800',
                                        'awaiting_verification' => 'bg-blue-100 text-blue-800',
                                        'verified' => 'bg-green-100 text-green-800',
                                    ];
                                    $statusLabels = [
                                        'pending_payment' => 'Menunggu Pembayaran',
                                        'awaiting_verification' => 'Menunggu Verifikasi',
                                        'verified' => 'Terverifikasi',
                                    ];
                                @endphp
                                <span class="px-2 sm:px-3 py-1 rounded-full text-xs sm:text-sm font-medium {{ $statusColors[$activeRegistration->status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $statusLabels[$activeRegistration->status] ?? $activeRegistration->status }}
                                </span>
                            </div>

                            <!-- Alert untuk rejected dengan alasan -->
                            @if($activeRegistration->status === 'rejected' && $activeRegistration->rejection_reason)
                                <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                                    <p class="text-sm font-medium text-red-800">Alasan Penolakan:</p>
                                    <p class="text-sm text-red-700 mt-1">{{ $activeRegistration->rejection_reason }}</p>
                                </div>
                            @endif

                            <!-- Alert untuk expired (melebihi batas waktu) -->
                            @if($activeRegistration->status === 'expired')
                                <div class="mb-4 p-3 bg-orange-50 border border-orange-200 rounded-lg">
                                    <p class="text-sm font-medium text-orange-800">Pendaftaran Kadaluarsa:</p>
                                    <p class="text-sm text-orange-700 mt-1">
                                        Anda tidak melakukan pembayaran dalam batas waktu yang ditentukan ({{ $activeRegistration->examSchedule->payment_deadline_hours ?? 24 }} jam).
                                        Silakan daftar ulang untuk jadwal lainnya.
                                    </p>
                                </div>
                            @endif
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3 sm:mb-4">
                                <div>
                                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Jadwal Ujian</p>
                                    <p class="font-medium text-sm sm:text-base text-gray-900 dark:text-white">
                                        {{ $activeRegistration->examSchedule->title }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Tanggal Ujian</p>
                                    <p class="font-medium text-sm sm:text-base text-gray-900 dark:text-white">
                                        {{ $activeRegistration->examSchedule->exam_date->format('d F Y') }}
                                    </p>
                                </div>
                            </div>
                            
                            <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
                                <a href="{{ route('mahasiswa.registrations.show', $activeRegistration) }}" 
                                   class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs sm:text-sm text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Lihat Detail
                                </a>
                                @if($activeRegistration->status === 'pending_payment')
                                    <a href="{{ route('mahasiswa.registrations.payment', $activeRegistration) }}" 
                                       class="inline-flex items-center justify-center px-3 sm:px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs sm:text-sm text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        Upload Pembayaran
                                    </a>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="text-center py-6 sm:py-8 border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-lg px-4">
                            <svg class="mx-auto h-10 w-10 sm:h-12 sm:w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <p class="mt-2 text-sm sm:text-base text-gray-500 dark:text-gray-400">
                                Anda belum memiliki pendaftaran aktif.
                            </p>
                            <a href="{{ route('mahasiswa.schedules.index') }}" 
                               class="mt-3 sm:mt-4 inline-flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs sm:text-sm text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 w-full sm:w-auto">
                                Daftar Sekarang
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-4 sm:p-6">
                    <h4 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white mb-3 sm:mb-4">
                        Aksi Cepat
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        <a href="{{ route('mahasiswa.schedules.index') }}" 
                           class="flex items-center p-3 sm:p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            <div class="p-2 sm:p-3 bg-blue-100 dark:bg-blue-900 rounded-full mr-3 sm:mr-4">
                                <svg class="h-5 w-5 sm:h-6 sm:w-6 text-blue-600 dark:text-blue-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-sm sm:text-base text-gray-900 dark:text-white">Lihat Jadwal</p>
                                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Pilih jadwal ujian</p>
                            </div>
                        </a>
                        
                        <a href="{{ route('profile.edit') }}" 
                           class="flex items-center p-3 sm:p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            <div class="p-2 sm:p-3 bg-purple-100 dark:bg-purple-900 rounded-full mr-3 sm:mr-4">
                                <svg class="h-5 w-5 sm:h-6 sm:w-6 text-purple-600 dark:text-purple-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-sm sm:text-base text-gray-900 dark:text-white">Profil</p>
                                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Edit data pribadi</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Registrations History -->
            @if($recentRegistrations->count() > 0)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 sm:p-6">
                        <h4 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white mb-3 sm:mb-4">
                            Riwayat Pendaftaran
                        </h4>
                        <div class="overflow-x-auto -mx-4 sm:mx-0 px-4 sm:px-0">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">No. Daftar</th>
                                        <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider hidden sm:table-cell">Jadwal</th>
                                        <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                        <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider hidden md:table-cell">Keterangan</th>
                                        <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider hidden md:table-cell">Tanggal</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($recentRegistrations as $registration)
                                        <tr>
                                            <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap text-xs sm:text-sm font-mono text-gray-900 dark:text-white">
                                                {{ $registration->registration_number }}
                                            </td>
                                            <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900 dark:text-white hidden sm:table-cell">
                                                {{ $registration->examSchedule->title }}
                                            </td>
                                            <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap">
                                                @php
                                                    $historyStatusColors = [
                                                        'rejected' => 'bg-red-100 text-red-800',
                                                        'cancelled' => 'bg-gray-100 text-gray-800',
                                                        'expired' => 'bg-orange-100 text-orange-800',
                                                    ];
                                                    $historyStatusLabels = [
                                                        'rejected' => 'Ditolak',
                                                        'cancelled' => 'Dibatalkan',
                                                        'expired' => 'Kadaluarsa',
                                                    ];
                                                @endphp
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $historyStatusColors[$registration->status] ?? 'bg-gray-100 text-gray-800' }}">
                                                    {{ $historyStatusLabels[$registration->status] ?? $registration->status }}
                                                </span>
                                            </td>
                                            <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap text-xs text-gray-500 dark:text-gray-400 hidden md:table-cell">
                                                @if($registration->status === 'rejected' && $registration->rejection_reason)
                                                    <span class="text-red-600" title="{{ $registration->rejection_reason }}">
                                                        {{ Str::limit($registration->rejection_reason, 30) }}
                                                    </span>
                                                @elseif($registration->status === 'expired')
                                                    <span class="text-orange-600">
                                                        Melebihi {{ $registration->examSchedule->payment_deadline_hours ?? 24 }} jam
                                                    </span>
                                                @elseif($registration->status === 'cancelled' && $registration->rejection_reason)
                                                    {{ Str::limit($registration->rejection_reason, 30) }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="px-3 sm:px-6 py-2 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-500 dark:text-gray-400 hidden md:table-cell">
                                                {{ $registration->created_at->format('d M Y') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
