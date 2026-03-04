<?php

namespace App\Notifications;

use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class PaymentVerifiedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Registration $registration;

    public function __construct(Registration $registration)
    {
        $this->registration = $registration;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $schedule = $this->registration->examSchedule;

        return (new MailMessage)
            ->subject('Pembayaran Diverifikasi - Kartu Ujian EPT')
            ->greeting('Halo, ' . $notifiable->name . '!')
            ->line('Selamat! Pembayaran Anda telah **diverifikasi**.')
            ->line('**Nomor Pendaftaran:** ' . $this->registration->registration_number)
            ->line('**Jadwal:** ' . ($schedule->title ?? '-'))
            ->line('**Tanggal Ujian:** ' . ($schedule->exam_date ? $schedule->exam_date->format('d F Y') : '-'))
            ->line('**Waktu:** ' . ($schedule->start_time ? $schedule->start_time->format('H:i') . ' - ' . $schedule->end_time->format('H:i') : '-'))
            ->line('**Ruang:** ' . ($schedule->location ?? '-'))
            ->line('')
            ->line('Silakan download Kartu Ujian Anda dan bawa pada hari ujian.')
            ->action('Download Kartu Ujian', route('mahasiswa.registrations.card', $this->registration))
            ->line('')
            ->line('**Catatan:**')
            ->line('- Kartu ujian wajib dibawa saat ujian')
            ->line('- Datang 30 menit sebelum ujian dimulai')
            ->line('- Bawa identitas diri (KTP/Kartu Mahasiswa)')
            ->line('')
            ->line('Jika ada pertanyaan, silakan hubungi administrator.');
    }
}
