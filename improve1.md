# IMPROVEMENT PLAN - APLIKASI EPT

**Tanggal:** 17 Februari 2026  
**Versi:** 1.0  
**Status:** Ready for Implementation

---

## 📋 DAFTAR ISI

1. [Ringkasan Aplikasi](#ringkasan-aplikasi)
2. [Sisi User/Mahasiswa - Kekurangan & Solusi](#sisi-usermahasiswa)
3. [Sisi Admin - Kekurangan & Solusi](#sisi-admin)
4. [Struktur Database - Kekurangan](#struktur-database)
5. [Prioritas Implementasi](#prioritas-implementasi)
6. [Checklist Progress](#checklist-progress)

---

## Ringkasan Aplikasi

Aplikasi EPT (English Proficiency Test) adalah sistem pendaftaran ujian bahasa Inggris dengan 2 sisi:
- **User/Mahasiswa**: http://127.0.0.1:8000/login
- **Admin**: http://127.0.0.1:8000/admin

**Tech Stack:**
- Laravel 12.x + Filament 3.x
- MySQL Database
- Tailwind CSS + Blade
- Spatie Permission

---

## Sisi User/Mahasiswa

### 🔴 HIGH PRIORITY (Wajib Segera)

#### 1. Notifikasi Email/SMS
**Masalah:** Mahasiswa tidak tahu jika status pembayaran berubah (diverifikasi/ditolak)

**Solusi:**
- [ ] Install package: `composer require laravel-notification-channels/twilio` (untuk SMS)
- [ ] Buat Notification classes:
  - `PaymentVerifiedNotification`
  - `PaymentRejectedNotification`
  - `RegistrationExpiredNotification`
- [ ] Kirim email saat:
  - Status berubah menjadi `verified`
  - Status berubah menjadi `rejected`
  - Pendaftaran akan expired (reminder)

**File yang diubah:**
- `app/Notifications/PaymentVerifiedNotification.php` (baru)
- `app/Notifications/PaymentRejectedNotification.php` (baru)
- `app/Filament/Resources/RegistrationResource.php` (tambah trigger)
- `.env` (konfigurasi mail/SMS)

**Estimasi:** 4-6 jam

---

#### 2. Kartu Ujian Digital (PDF)
**Masalah:** Mahasiswa harus cetak manual, tidak ada bukti resmi

**Solusi:**
- [ ] Install package: `composer require barryvdh/laravel-dompdf`
- [ ] Buat view: `resources/views/mahasiswa/registrations/card.blade.php`
- [ ] Buat route: `/mahasiswa/registrations/{id}/card`
- [ ] Generate PDF dengan:
  - Nomor pendaftaran
  - Data mahasiswa
  - Jadwal ujian
  - QR Code (untuk validasi)
  - Logo institusi

**File yang diubah:**
- `app/Http/Controllers/Mahasiswa/RegistrationController.php` (tambah method)
- `resources/views/mahasiswa/registrations/card.blade.php` (baru)
- `resources/views/mahasiswa/dashboard.blade.php` (tambah tombol download)

**Estimasi:** 3-4 jam

---

#### 3. Validasi Double Booking (1 NIM = 1 Pendaftaran Aktif)
**Masalah:** 1 mahasiswa bisa daftar berkali-kali di jadwal berbeda jika yang pertama cancelled

**Solusi:**
- [ ] Tambah validasi di `RegistrationController@store`
- [ ] Cek apakah mahasiswa punya pendaftaran dengan status:
  - `pending_payment`
  - `awaiting_verification`
  - `verified`
- [ ] Jika ada, tolak dengan pesan error

**File yang diubah:**
- `app/Http/Controllers/Mahasiswa/RegistrationController.php`

**Code snippet:**
```php
$hasActive = Registration::where('user_id', auth()->id())
    ->whereIn('status', ['pending_payment', 'awaiting_verification', 'verified'])
    ->exists();

if ($hasActive) {
    return back()->withErrors(['Anda masih memiliki pendaftaran aktif']);
}
```

**Estimasi:** 1 jam

---

#### 4. Reminder Email Sebelum Deadline
**Masalah:** Banyak pendaftaran expired karena mahasiswa lupa bayar

**Solusi:**
- [ ] Buat command: `php artisan make:command SendPaymentReminders`
- [ ] Jalankan via cron job setiap jam
- [ ] Kirim reminder 12 jam dan 2 jam sebelum expired
- [ ] Tambah field `reminder_sent_at` di tabel registrations

**File yang diubah:**
- `app/Console/Commands/SendPaymentReminders.php` (baru)
- `app/Console/Kernel.php` (schedule command)
- `database/migrations/xxxx_add_reminder_sent_at_to_registrations.php` (baru)

**Estimasi:** 3-4 jam

---

### 🟡 MEDIUM PRIORITY (Penting)

#### 5. Reset Password via Email
**Masalah:** User lupa password harus hubungi admin manual

**Solusi:**
- [ ] Aktifkan fitur forgot password Laravel Breeze
- [ ] Buat view: `resources/views/auth/forgot-password.blade.php`
- [ ] Buat view: `resources/views/auth/reset-password.blade.php`
- [ ] Setup mail trap untuk testing

**File yang diubah:**
- `routes/auth.php` (uncomment route reset password)
- `resources/views/auth/login.blade.php` (tambah link forgot password)

**Estimasi:** 2-3 jam

---

#### 6. Upload Foto Profil
**Masalah:** Profil tidak lengkap, tidak ada foto

**Solusi:**
- [ ] Tambah field `photo` di tabel users
- [ ] Update `ProfileController@update`
- [ ] Update view profil mahasiswa
- [ ] Validasi: max 2MB, jpg/png only

**File yang diubah:**
- `database/migrations/xxxx_add_photo_to_users.php`
- `app/Http/Controllers/ProfileController.php`
- `app/Http/Requests/ProfileUpdateRequest.php`
- `resources/views/profile/edit.blade.php`

**Estimasi:** 2-3 jam

---

### 🟢 LOW PRIORITY (Nice to Have)

#### 7. Search & Filter Riwayat Pendaftaran
**Masalah:** Sulit mencari pendaftaran lama jika sudah banyak

**Solusi:**
- [ ] Tambah input search di `registrations/index.blade.php`
- [ ] Filter berdasarkan: status, jadwal, tanggal
- [ ] Pagination dengan search persistence

**File yang diubah:**
- `app/Http/Controllers/Mahasiswa/RegistrationController.php` (method index)
- `resources/views/mahasiswa/registrations/index.blade.php`

**Estimasi:** 2 jam

---

#### 8. Real-time Countdown Timer
**Masalah:** Mahasiswa tidak melihat waktu tersisa secara live

**Solusi:**
- [ ] Tambah JavaScript countdown di dashboard
- [ ] Auto-refresh saat waktu habis
- [ ] Tampilkan status expired real-time

**File yang diubah:**
- `resources/views/mahasiswa/dashboard.blade.php`
- `resources/views/mahasiswa/registrations/show.blade.php`

**Estimasi:** 2-3 jam

---

## Sisi Admin

### 🔴 HIGH PRIORITY (Wajib Segera)

#### 9. Dashboard Statistik (Widgets)
**Masalah:** Admin tidak bisa lihat overview pendaftaran

**Solusi:**
- [ ] Buat Filament Widgets:
  - `StatsOverviewWidget`: Total pendaftaran, pending, verified, rejected
  - `RegistrationChartWidget`: Grafik pendaftaran per bulan
  - `ExamScheduleQuotaWidget`: Kuota tersisa per jadwal

**File yang dibuat:**
- `app/Filament/Widgets/StatsOverviewWidget.php`
- `app/Filament/Widgets/RegistrationChartWidget.php`
- `app/Filament/Widgets/ExamScheduleQuotaWidget.php`

**File yang diubah:**
- `app/Providers/Filament/AdminPanelProvider.php` (register widgets)

**Estimasi:** 4-6 jam

---

#### 10. Log Aktivitas Admin
**Masalah:** Tidak bisa audit siapa yang verifikasi/reject pembayaran

**Solusi:**
- [ ] Install: `composer require spatie/laravel-activity-log`
- [ ] Publish config & migration
- [ ] Log setiap action:
  - Verifikasi pembayaran
  - Reject pembayaran
  - Edit jadwal
  - Hapus user

**File yang diubah:**
- `app/Filament/Resources/RegistrationResource.php` (tambah logging)
- `app/Filament/Resources/ExamScheduleResource.php` (tambah logging)
- `app/Filament/Resources/UserResource.php` (tambah logging)

**Estimasi:** 3-4 jam

---

### 🟡 MEDIUM PRIORITY (Penting)

#### 11. Export Excel dengan Styling (laravel-excel)
**Masalah:** Export saat ini CSV sederhana, tidak ada styling

**Solusi:**
- [ ] Install: `composer require maatwebsite/excel`
- [ ] Buat Export class: `app/Exports/ParticipantsExport.php`
- [ ] Tambah styling: header bold, auto-width, freeze panes
- [ ] Export dengan filter yang sedang aktif

**File yang dibuat:**
- `app/Exports/ParticipantsExport.php`
- `app/Exports/RegistrationsExport.php`

**File yang diubah:**
- `app/Filament/Pages/Participants.php` (update method export)

**Estimasi:** 3-4 jam

---

#### 12. Duplikat Jadwal
**Masalah:** Buat jadwal baru harus input ulang semua data

**Solusi:**
- [ ] Tambah action "Duplikat" di `ExamScheduleResource`
- [ ] Copy semua data kecuali: id, registered_count, timestamps
- [ ] Auto-generate title dengan suffix "(Copy)"

**File yang diubah:**
- `app/Filament/Resources/ExamScheduleResource.php` (tambah action)

**Estimasi:** 2 jam

---

### 🟢 LOW PRIORITY (Nice to Have)

#### 13. Broadcast Pengumuman
**Masalah:** Harus kontak mahasiswa satu per satu

**Solusi:**
- [ ] Buat tabel `announcements`
- [ ] Buat resource `AnnouncementResource` di Filament
- [ ] Kirim email ke semua mahasiswa aktif
- [ ] Tampilkan di dashboard mahasiswa

**File yang dibuat:**
- `database/migrations/xxxx_create_announcements_table.php`
- `app/Models/Announcement.php`
- `app/Filament/Resources/AnnouncementResource.php`
- `app/Notifications/AnnouncementNotification.php`

**Estimasi:** 4-5 jam

---

#### 14. Bulk Actions di Filament
**Masalah:** Harus verifikasi satu per satu

**Solusi:**
- [ ] Tambah bulk action: Verifikasi multiple pembayaran
- [ ] Tambah bulk action: Export selected
- [ ] Tambah bulk action: Delete with confirmation

**File yang diubah:**
- `app/Filament/Resources/RegistrationResource.php`

**Estimasi:** 2-3 jam

---

## Struktur Database

### Kekurangan yang Perlu Diperbaiki:

#### 15. Tambah Tabel Master
**Masalah:** Data tidak konsisten, banyak duplikasi

**Tabel yang perlu dibuat:**
- [ ] `banks` - Data bank untuk pembayaran
- [ ] `faculties` - Data fakultas
- [ ] `majors` - Data program studi (relasi ke faculties)
- [ ] `exam_sessions` - Master sesi ujian (pagi, siang, sore)

**Estimasi:** 4-6 jam (semua tabel + seeder)

---

#### 16. Tambah Kolom Hasil Ujian
**Masalah:** Tidak bisa input nilai EPT

**Solusi:**
- [ ] Tambah kolom di `registrations`:
  - `listening_score` (integer)
  - `structure_score` (integer)
  - `reading_score` (integer)
  - `total_score` (integer)
  - `certificate_issued_at` (datetime)
  - `certificate_file` (string, path file PDF)

**Estimasi:** 2-3 jam

---

## Prioritas Implementasi

### Minggu 1-2 (HIGH PRIORITY)
1. ✅ Notifikasi Email/SMS (4-6 jam)
2. ✅ Kartu Ujian PDF (3-4 jam)
3. ✅ Validasi Double Booking (1 jam)
4. ✅ Dashboard Statistik Admin (4-6 jam)

### Minggu 3-4 (HIGH + MEDIUM)
5. ✅ Reminder Email (3-4 jam)
6. ✅ Log Aktivitas Admin (3-4 jam)
7. ✅ Reset Password (2-3 jam)
8. ✅ Export Excel dengan Styling (3-4 jam)

### Minggu 5-6 (MEDIUM + LOW)
9. ✅ Upload Foto Profil (2-3 jam)
10. ✅ Duplikat Jadwal (2 jam)
11. ✅ Search & Filter Riwayat (2 jam)
12. ✅ Tabel Master (4-6 jam)

### Minggu 7-8 (LOW PRIORITY)
13. ✅ Real-time Countdown (2-3 jam)
14. ✅ Broadcast Pengumuman (4-5 jam)
15. ✅ Bulk Actions (2-3 jam)
16. ✅ Kolom Hasil Ujian (2-3 jam)

**Total Estimasi:** 45-60 jam kerja

---

## Checklist Progress

### Sisi User/Mahasiswa
- [ ] 1. Notifikasi Email/SMS
- [x] 2. Kartu Ujian PDF
- [x] 3. Validasi Double Booking
- [ ] 4. Reminder Email
- [ ] 5. Reset Password
- [ ] 6. Upload Foto Profil
- [ ] 7. Search & Filter Riwayat
- [ ] 8. Real-time Countdown

### Sisi Admin
- [x] 9. Dashboard Statistik
- [ ] 10. Log Aktivitas
- [ ] 11. Export Excel dengan Styling
- [ ] 12. Duplikat Jadwal
- [ ] 13. Broadcast Pengumuman
- [ ] 14. Bulk Actions

### Database
- [ ] 15. Tabel Master (banks, faculties, majors, exam_sessions)
- [ ] 16. Kolom Hasil Ujian

---

## Catatan Pengembangan

### Yang Perlu Diperhatikan:
1. **Testing**: Setiap fitur wajib di-test sebelum deploy
2. **Backup Database**: Selalu backup sebelum migration
3. **Environment**: Gunakan `.env.testing` untuk development
4. **Git Commit**: Commit per fitur dengan pesan jelas

### Command yang Sering Digunakan:
```bash
# Clear cache
php artisan optimize:clear
php artisan view:clear
php artisan filament:clear-cache

# Migration
php artisan migrate
php artisan migrate:rollback

# Make file
php artisan make:controller NamaController
php artisan make:model NamaModel -m
php artisan make:migration add_column_to_table
php artisan make:command NamaCommand
php artisan make:notification NamaNotification

# Queue (untuk email)
php artisan queue:work
```

### Package yang Diperlukan:
```bash
# Notifikasi SMS
composer require laravel-notification-channels/twilio

# PDF
composer require barryvdh/laravel-dompdf

# Excel
composer require maatwebsite/excel

# Activity Log
composer require spatie/laravel-activity-log
```

---

**Dibuat oleh:** AI Assistant  
**Untuk:** Tim Pengembangan EPT  
**Versi:** 1.0  
**Status:** Siap Implementasi

---

## Kontak & Support

Jika ada pertanyaan tentang implementasi, silakan konsultasi dengan tim developer.

**Selamat Mengembangkan! 🚀**
