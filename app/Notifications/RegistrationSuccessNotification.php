<?php

namespace App\Notifications;

use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class RegistrationSuccessNotification extends Notification implements ShouldQueue
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
            ->subject('Pendaftaran EPT Berhasil - ' . $this->registration->registration_number)
            ->greeting('Halo, ' . $notifiable->name . '!')
            ->line('Pendaftaran Anda telah berhasil dibuat.')
            ->line('**Nomor Pendaftaran:** ' . $this->registration->registration_number)
            ->line('**Jadwal:** ' . ($schedule->title ?? '-'))
            ->line('**Tanggal:** ' . ($schedule->exam_date ? $schedule->exam_date->format('d F Y') : '-'))
            ->line('**Total Pembayaran:** Rp ' . number_format($this->registration->total_payment, 0, ',', '.'))
            ->line('')
            ->line('Silakan upload bukti pembayaran sebelum **' . $this->registration->expires_at->format('d F Y, H:i') . '**.')
            ->line('Batas waktu pembayaran: ' . $this->registration->expires_at->diffForHumans())
            ->action('Upload Pembayaran', route('mahasiswa.registrations.payment', $this->registration))
            ->line('')
            ->line('Jika ada pertanyaan, silakan hubungi administrator.');
    }
}
