<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Daftar Peserta Ujian') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Filter & Actions -->
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                        <form method="GET" action="{{ route('admin.participants') }}" class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                            <select name="exam_schedule_id" onchange="this.form.submit()" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">-- Semua Jadwal --</option>
                                @foreach($examSchedules as $schedule)
                                    <option value="{{ $schedule->id }}" {{ $examScheduleId == $schedule->id ? 'selected' : '' }}>
                                        {{ $schedule->title }} - {{ $schedule->exam_date->format('d F Y') }}
                                    </option>
                                @endforeach
                            </select>
                        </form>

                        <div class="flex gap-2">
                            <a href="{{ route('admin.participants.print', ['exam_schedule_id' => $examScheduleId]) }}" target="_blank"
                               class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-medium text-sm text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                Print
                            </a>
                            <button onclick="window.print()" 
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-medium text-sm text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Download PDF
                            </button>
                        </div>
                    </div>

                    @if($registrations->count() > 0)
                        @foreach($registrations as $scheduleId => $scheduleRegistrations)
                            @php
                                $schedule = $scheduleRegistrations->first()->examSchedule;
                            @endphp
                            <div class="mb-8">
                                <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-lg p-4 mb-4">
                                    <h3 class="text-lg font-semibold text-indigo-900 dark:text-indigo-200">
                                        {{ $schedule->title }}
                                    </h3>
                                    <p class="text-sm text-indigo-700 dark:text-indigo-300">
                                        Tanggal: {{ $schedule->exam_date->format('d F Y') }} | 
                                        Sesi: {{ $schedule->session ?? '-' }} |
                                        Total Peserta: {{ $scheduleRegistrations->count() }} orang
                                    </p>
                                </div>

                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-gray-50 dark:bg-gray-700">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">No</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">No. Daftar</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Nama</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">NIM</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Prodi</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Fakultas</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                            @foreach($scheduleRegistrations as $index => $reg)
                                                <tr>
                                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $index + 1 }}</td>
                                                    <td class="px-4 py-3 text-sm font-mono text-gray-900 dark:text-white">{{ $reg->registration_number }}</td>
                                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $reg->user->name }}</td>
                                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $reg->user->nim ?? '-' }}</td>
                                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $reg->user->major ?? '-' }}</td>
                                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $reg->user->faculty ?? '-' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-8">
                            <p class="text-gray-500 dark:text-gray-400">Belum ada peserta yang terverifikasi.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
