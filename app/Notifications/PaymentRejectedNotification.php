<?php

namespace App\Notifications;

use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class PaymentRejectedNotification extends Notification implements ShouldQueue
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
        $reason = $this->registration->rejection_reason ?? 'Tidak ada alasan spesifik';

        return (new MailMessage)
            ->subject('Pembayaran Ditolak - Pendaftaran EPT')
            ->greeting('Halo, ' . $notifiable->name . '!')
            ->line('Mohon maaf, pembayaran Anda **ditolak**.')
            ->line('**Nomor Pendaftaran:** ' . $this->registration->registration_number)
            ->line('**Alasan Penolakan:** ' . $reason)
            ->line('')
            ->line('Silakan login dan upload bukti pembayaran yang valid.')
            ->action('Upload Ulang Pembayaran', route('mahasiswa.registrations.payment', $this->registration))
            ->line('')
            ->line('Jika ada pertanyaan, silakan hubungi administrator.');
    }
}
