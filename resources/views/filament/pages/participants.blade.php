@php
    $examScheduleId = request()->get('exam_schedule_id');
    $examSchedules = \App\Models\ExamSchedule::orderBy('exam_date', 'desc')->get();
@endphp

<x-filament::section>
    <x-slot:heading>
        Daftar Peserta Ujian
    </x-slot:heading>

    <x-slot:description>
        Peserta yang telah diverifikasi
    </x-slot:description>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <form method="GET" action="{{ url()->current() }}" class="flex flex-wrap items-center gap-3">
            <x-filament::input.select 
                name="exam_schedule_id" 
                onchange="this.form.submit()"
                class="w-full sm:w-64"
            >
                <option value="">-- Semua Jadwal --</option>
                @foreach($examSchedules as $schedule)
                    <option value="{{ $schedule->id }}" {{ $examScheduleId == $schedule->id ? 'selected' : '' }}>
                        {{ $schedule->title }} - {{ $schedule->exam_date->format('d F Y') }}
                    </option>
                @endforeach
            </x-filament::input.select>
            
            @if($examScheduleId)
                <a href="{{ url()->current() }}" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                    Reset Filter
                </a>
            @endif
        </form>

        <div class="flex items-center gap-2">
            <form method="GET" action="{{ url()->current() }}">
                <input type="hidden" name="exam_schedule_id" value="{{ $examScheduleId }}">
                <x-filament::button type="submit" name="action" value="export" color="success">
                    Download Excel
                </x-filament::button>
            </form>
            
            <x-filament::button tag="a" href="{{ url()->current() }}?exam_schedule_id={{ $examScheduleId }}&action=print" target="_blank">
                Print / PDF
            </x-filament::button>
        </div>
    </div>

    @if($registrations->count() > 0)
        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
            <table class="fi-ta-table w-full text-start">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="fi-ta-header-cell px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">No</th>
                        <th class="fi-ta-header-cell px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">No. Daftar</th>
                        <th class="fi-ta-header-cell px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">Nama Peserta</th>
                        <th class="fi-ta-header-cell px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">NIM</th>
                        <th class="fi-ta-header-cell px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">Program Studi</th>
                        <th class="fi-ta-header-cell px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">Fakultas</th>
                        <th class="fi-ta-header-cell px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">Jadwal</th>
                        <th class="fi-ta-header-cell px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">Tgl Verifikasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                    @foreach($registrations as $index => $reg)
                        <tr class="fi-ta-row hover:bg-gray-50 dark:hover:bg-gray-800">
                            <td class="fi-ta-cell px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $index + 1 }}</td>
                            <td class="fi-ta-cell px-4 py-3 text-sm font-mono text-gray-900 dark:text-gray-100">{{ $reg->registration_number }}</td>
                            <td class="fi-ta-cell px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $reg->user->name ?? '-' }}</td>
                            <td class="fi-ta-cell px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $reg->user->nim ?? '-' }}</td>
                            <td class="fi-ta-cell px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $reg->user->major ?? '-' }}</td>
                            <td class="fi-ta-cell px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $reg->user->faculty ?? '-' }}</td>
                            <td class="fi-ta-cell px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $reg->examSchedule->title ?? '-' }}</td>
                            <td class="fi-ta-cell px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $reg->payment_verified_at ? $reg->payment_verified_at->format('d F Y, H:i') : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">Total Peserta:</span>
                <span class="text-lg font-bold text-gray-900 dark:text-white">
                    {{ $registrations->count() }} Orang
                </span>
            </div>
        </div>
    @else
        <div class="flex flex-col items-center justify-center py-12 text-gray-500 dark:text-gray-400">
            <x-filament::icon icon="heroicon-o-user-group" class="w-16 h-16 text-gray-300 dark:text-gray-600 mb-4" />
            <p class="text-lg font-medium">Belum Ada Peserta</p>
            <p class="text-sm">Peserta yang sudah diverifikasi akan muncul di sini</p>
        </div>
    @endif
</x-filament::section>
