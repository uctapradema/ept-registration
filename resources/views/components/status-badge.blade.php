@props(['status' => ''])

@php
    $colors = [
        'pending_payment' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        'awaiting_verification' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        'verified' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        'rejected' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        'cancelled' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        'expired' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
    ];
    
    $labels = [
        'pending_payment' => 'Menunggu Pembayaran',
        'awaiting_verification' => 'Menunggu Verifikasi',
        'verified' => 'Terverifikasi',
        'rejected' => 'Ditolak',
        'cancelled' => 'Dibatalkan',
        'expired' => 'Kadaluarsa',
    ];
    
    $colorClass = $colors[$status] ?? 'bg-gray-100 text-gray-800';
    $label = $labels[$status] ?? $status;
@endphp

<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $colorClass }}">
    {{ $label }}
</span>
