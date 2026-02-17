<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg sm:text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Konfirmasi Pendaftaran') }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-3xl mx-auto px-2 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white mb-4 sm:mb-6">
                        Detail Jadwal Ujian
                    </h3>

                    <!-- Schedule Details -->
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 sm:p-6 mb-4 sm:mb-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-6">
                            <div>
                                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Nama Ujian</p>
                                <p class="font-medium text-sm sm:text-base text-gray-900 dark:text-white">{{ $schedule->title }}</p>
                            </div>
                            
                            <div>
                                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Tanggal Ujian</p>
                                <p class="font-medium text-sm sm:text-base text-gray-900 dark:text-white">
                                    {{ $schedule->exam_date->format('l, d F Y') }}
                                </p>
                            </div>
                            
                            <div>
                                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Waktu</p>
                                <p class="font-medium text-sm sm:text-base text-gray-900 dark:text-white">
                                    {{ $schedule->start_time->format('H:i') }} - {{ $schedule->end_time->format('H:i') }}
                                </p>
                            </div>
                            
                            <div>
                                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Biaya Pendaftaran</p>
                                <p class="font-medium text-base sm:text-lg text-indigo-600 dark:text-indigo-400">
                                    Rp {{ number_format($schedule->price, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>

                        @if($schedule->description)
                            <div class="mt-4 sm:mt-6 pt-4 sm:pt-6 border-t border-gray-200 dark:border-gray-600">
                                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Deskripsi</p>
                                <p class="mt-1 text-sm sm:text-base text-gray-900 dark:text-white">{{ $schedule->description }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Important Information -->
                    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-3 sm:p-4 mb-4 sm:mb-6">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 mt-0.5">
                                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-xs sm:text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                    Informasi Penting
                                </h3>
                                <div class="mt-1 sm:mt-2 text-xs sm:text-sm text-yellow-700 dark:text-yellow-300">
                                    <ul class="list-disc pl-4 sm:pl-5 space-y-1">
                                        <li>Anda memiliki waktu 24 jam untuk melakukan pembayaran setelah mendaftar.</li>
                                        <li>Pendaftaran yang tidak dibayar dalam 24 jam akan otomatis dibatalkan.</li>
                                        <li>Pembayaran dapat dilakukan melalui transfer bank.</li>
                                        <li>Upload bukti pembayaran dalam format JPG, PNG, atau PDF (maksimal 5MB).</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- User Information -->
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 sm:p-6 mb-4 sm:mb-6">
                        <h4 class="text-xs sm:text-sm font-medium text-gray-900 dark:text-white mb-3 sm:mb-4">Data Pendaftar</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                            <div>
                                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Nama Lengkap</p>
                                <p class="font-medium text-sm sm:text-base text-gray-900 dark:text-white">{{ Auth::user()->name }}</p>
                            </div>
                            <div>
                                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">NIM</p>
                                <p class="font-medium text-sm sm:text-base text-gray-900 dark:text-white">{{ Auth::user()->nim ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Email</p>
                                <p class="font-medium text-sm sm:text-base text-gray-900 dark:text-white">{{ Auth::user()->email }}</p>
                            </div>
                            <div>
                                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">No. Telepon</p>
                                <p class="font-medium text-sm sm:text-base text-gray-900 dark:text-white">{{ Auth::user()->phone ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Registration Form -->
                    <form method="POST" action="{{ route('mahasiswa.registrations.store') }}">
                        @csrf
                        <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">

                        <!-- Agreement Checkbox -->
                        <div class="mb-4 sm:mb-6">
                            <label class="flex items-start">
                                <input type="checkbox" name="agreement" value="1" 
                                       class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                       {{ old('agreement') ? 'checked' : '' }}>
                                <span class="ml-2 sm:ml-3 text-xs sm:text-sm text-gray-600 dark:text-gray-400">
                                    Saya menyetujui bahwa data yang saya masukkan adalah benar dan saya berkomitmen untuk melakukan pembayaran dalam waktu 24 jam.
                                </span>
                            </label>
                            @error('agreement')
                                <p class="mt-1 text-xs sm:text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-2 sm:gap-0">
                            <a href="{{ route('mahasiswa.schedules.index') }}" 
                               class="inline-flex items-center justify-center w-full sm:w-auto px-4 py-2.5 sm:py-2 bg-gray-200 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs sm:text-sm text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 order-2 sm:order-1">
                                Kembali
                            </a>
                            
                            <button type="submit" 
                                    class="inline-flex items-center justify-center w-full sm:w-auto px-5 sm:px-6 py-2.5 sm:py-3 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs sm:text-sm text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 order-1 sm:order-2">
                                Konfirmasi Daftar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
