<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-lg sm:text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Jadwal Ujian Tersedia') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8">
            <!-- Alert Messages -->
            @if(session('success'))
                <div class="mb-4 sm:mb-6 p-3 sm:p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg text-sm sm:text-base">
                    {{ session('success') }}
                </div>
            @endif
            
            @if(session('error'))
                <div class="mb-4 sm:mb-6 p-3 sm:p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg text-sm sm:text-base">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Legend -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-4 sm:mb-6">
                <div class="p-3 sm:p-4">
                    <h4 class="text-xs sm:text-sm font-semibold text-gray-900 dark:text-white mb-2 sm:mb-3">Keterangan Status:</h4>
                    <div class="flex flex-wrap gap-2 sm:gap-4">
                        <div class="flex items-center">
                            <span class="inline-block w-2.5 sm:w-3 h-2.5 sm:h-3 rounded-full bg-green-500 mr-1.5 sm:mr-2"></span>
                            <span class="text-xs sm:text-sm text-gray-600 dark:text-gray-400">Tersedia</span>
                        </div>
                        <div class="flex items-center">
                            <span class="inline-block w-2.5 sm:w-3 h-2.5 sm:h-3 rounded-full bg-yellow-500 mr-1.5 sm:mr-2"></span>
                            <span class="text-xs sm:text-sm text-gray-600 dark:text-gray-400">Terbatas</span>
                        </div>
                        <div class="flex items-center">
                            <span class="inline-block w-2.5 sm:w-3 h-2.5 sm:h-3 rounded-full bg-red-500 mr-1.5 sm:mr-2"></span>
                            <span class="text-xs sm:text-sm text-gray-600 dark:text-gray-400">Penuh</span>
                        </div>
                        <div class="flex items-center">
                            <span class="inline-block w-2.5 sm:w-3 h-2.5 sm:h-3 rounded-full bg-gray-400 mr-1.5 sm:mr-2"></span>
                            <span class="text-xs sm:text-sm text-gray-600 dark:text-gray-400">Ditutup</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Schedules Grid -->
            @if($schedules->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                    @foreach($schedules as $schedule)
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border-2 {{ $schedule->status === 'full' ? 'border-red-200 dark:border-red-800' : ($schedule->status === 'limited' ? 'border-yellow-200 dark:border-yellow-800' : 'border-gray-200 dark:border-gray-700') }}">
                            <!-- Status Badge -->
                            <div class="px-3 sm:px-6 py-2 sm:py-3 border-b border-gray-200 dark:border-gray-700 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-1 sm:gap-0 {{ $schedule->status === 'full' ? 'bg-red-50 dark:bg-red-900/20' : ($schedule->status === 'limited' ? 'bg-yellow-50 dark:bg-yellow-900/20' : 'bg-green-50 dark:bg-green-900/20') }}">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $schedule->status === 'full' ? 'bg-red-100 text-red-800' : ($schedule->status === 'limited' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                                    <span class="w-1.5 h-1.5 mr-1 rounded-full {{ $schedule->status === 'full' ? 'bg-red-500' : ($schedule->status === 'limited' ? 'bg-yellow-500' : 'bg-green-500') }}"></span>
                                    {{ $schedule->statusLabel }}
                                </span>
                                <span class="text-xs font-medium text-gray-600 dark:text-gray-400 whitespace-nowrap">
                                    Batas: {{ $schedule->registration_deadline->format('d M Y, H:i') }}
                                </span>
                            </div>

                            <div class="p-3 sm:p-6">
                                <!-- Title -->
                                <h3 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white mb-1 sm:mb-2">
                                    {{ $schedule->title }}
                                </h3>

                                <!-- Description -->
                                @if($schedule->description)
                                    <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mb-3 sm:mb-4 line-clamp-2">
                                        {{ $schedule->description }}
                                    </p>
                                @endif

                                <!-- Details -->
                                <div class="space-y-2 sm:space-y-3 mb-4 sm:mb-6">
                                    <div class="flex items-start text-xs sm:text-sm">
                                        <svg class="h-4 w-4 sm:h-5 sm:w-5 text-gray-400 mr-2 sm:mr-3 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span class="text-gray-900 dark:text-white">
                                            {{ $schedule->exam_date->format('l, d F Y') }}
                                        </span>
                                    </div>
                                    
                                    <div class="flex items-center text-xs sm:text-sm">
                                        <svg class="h-4 w-4 sm:h-5 sm:w-5 text-gray-400 mr-2 sm:mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span class="text-gray-900 dark:text-white">
                                            {{ $schedule->start_time->format('H:i') }} - {{ $schedule->end_time->format('H:i') }}
                                        </span>
                                    </div>

                                    <div class="flex items-center text-xs sm:text-sm">
                                        <svg class="h-4 w-4 sm:h-5 sm:w-5 text-gray-400 mr-2 sm:mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span class="text-gray-900 dark:text-white">
                                            Rp {{ number_format($schedule->price, 0, ',', '.') }}
                                        </span>
                                    </div>

                                    <!-- Quota Progress -->
                                    <div class="mt-2 sm:mt-4">
                                        <div class="flex justify-between text-xs sm:text-sm mb-1">
                                            <span class="text-gray-600 dark:text-gray-400">Kuota Tersedia</span>
                                            <span class="font-medium {{ $schedule->availableQuota() <= 5 ? 'text-red-600' : 'text-gray-900 dark:text-white' }}">
                                                {{ $schedule->availableQuota() }} / {{ $schedule->quota }}
                                            </span>
                                        </div>
                                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                            @php
                                                $quotaPercentage = (($schedule->quota - $schedule->availableQuota()) / $schedule->quota) * 100;
                                                if ($quotaPercentage >= 100) {
                                                    $barColor = 'bg-red-600';
                                                    $barWidth = 'w-full';
                                                } elseif ($quotaPercentage >= 90) {
                                                    $barColor = 'bg-yellow-500';
                                                    $barWidth = 'w-11/12';
                                                } elseif ($quotaPercentage >= 75) {
                                                    $barColor = 'bg-green-600';
                                                    $barWidth = 'w-3/4';
                                                } elseif ($quotaPercentage >= 50) {
                                                    $barColor = 'bg-green-600';
                                                    $barWidth = 'w-1/2';
                                                } elseif ($quotaPercentage >= 25) {
                                                    $barColor = 'bg-green-600';
                                                    $barWidth = 'w-1/4';
                                                } else {
                                                    $barColor = 'bg-green-600';
                                                    $barWidth = 'w-0';
                                                }
                                            @endphp
                                            <div class="{{ $barColor }} {{ $barWidth }} h-2 rounded-full transition-all duration-300"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Button -->
                                @if($schedule->status === 'full')
                                    <button disabled class="w-full inline-flex justify-center items-center px-3 sm:px-4 py-2.5 sm:py-2 bg-gray-300 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs sm:text-sm text-gray-500 dark:text-gray-400 uppercase tracking-widest cursor-not-allowed">
                                        Kuota Penuh
                                    </button>
                                @elseif(!Auth::user()->hasActiveRegistration())
                                    <a href="{{ route('mahasiswa.registrations.create', $schedule) }}" 
                                       class="w-full inline-flex justify-center items-center px-3 sm:px-4 py-2.5 sm:py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs sm:text-sm text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        Daftar Sekarang
                                    </a>
                                @else
                                    <button disabled class="w-full inline-flex justify-center items-center px-3 sm:px-4 py-2.5 sm:py-2 bg-gray-300 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs sm:text-sm text-gray-500 dark:text-gray-400 uppercase tracking-widest cursor-not-allowed">
                                        Anda Sudah Terdaftar
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-8 sm:p-12 text-center">
                        <svg class="mx-auto h-10 w-10 sm:h-12 sm:w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <h3 class="mt-3 sm:mt-4 text-base sm:text-lg font-medium text-gray-900 dark:text-white">Tidak Ada Jadwal Tersedia</h3>
                        <p class="mt-1 sm:mt-2 text-sm text-gray-500 dark:text-gray-400">
                            Saat ini tidak ada jadwal ujian yang tersedia untuk pendaftaran.
                        </p>
                        <p class="text-xs sm:text-sm text-gray-400 dark:text-gray-500 mt-1">
                            Silakan kembali lagi nanti.
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
