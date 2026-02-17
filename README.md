# Aplikasi Pendaftaran English Professional Test (EPT)

Aplikasi web untuk mengelola pendaftaran ujian English Professional Test di lingkungan kampus, dibangun dengan Laravel 11 dan Filament 3.

## Fitur Utama

### 1. Tiga Role Pengguna
- **Mahasiswa** - Mendaftar ujian, upload bukti pembayaran, cek status
- **Admin** - Kelola jadwal ujian, monitor pendaftar, kelola user
- **Bagian Keuangan** - Verifikasi pembayaran mahasiswa

### 2. Modul Mahasiswa (Frontend)
- Registrasi dan login
- Melihat jadwal ujian tersedia dengan informasi kuota real-time
- Pendaftaran ujian dengan validasi (hanya 1 pendaftaran aktif)
- Upload bukti pembayaran (maksimal 24 jam)
- Melihat status pendaftaran dengan countdown timer

### 3. Modul Admin (Filament Panel)
- Kelola jadwal ujian (CRUD)
- Monitor semua pendaftaran
- Filter dan search data
- Export data pendaftar
- Manajemen user

### 4. Modul Keuangan (Filament Panel)
- Dashboard khusus dengan statistik
- Daftar pendaftar menunggu verifikasi
- Verifikasi/tolak pembayaran dengan modal
- Preview bukti transfer
- Riwayat verifikasi

### 5. Fitur Keamanan & Validasi
- Role-based access control dengan Spatie Permission
- Database transaction dengan locking untuk mencegah race condition
- Validasi batas waktu pembayaran 24 jam
- Scheduler otomatis untuk cek pendaftaran expired
- Soft deletes untuk data penting

## Tech Stack

- **Framework**: Laravel 11.x
- **Admin Panel**: Filament 3.x
- **Database**: SQLite (default), bisa diganti ke MySQL/PostgreSQL
- **Autentikasi**: Laravel Breeze
- **Role Management**: Spatie Laravel Permission
- **Frontend**: Blade + Tailwind CSS + Alpine.js
- **Queue**: Database (default)

## Instalasi

### 1. Clone Repository
```bash
git clone <repository-url>
cd ept
```

### 2. Install Dependencies
```bash
composer install
npm install
npm run build
```

### 3. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Database Setup
```bash
# Untuk SQLite (default)
touch database/database.sqlite

# Untuk MySQL/PostgreSQL, sesuaikan di .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=ept
# DB_USERNAME=root
# DB_PASSWORD=
```

### 5. Migration & Seeding
```bash
php artisan migrate:fresh --seed
```

### 6. Storage Link
```bash
php artisan storage:link
```

### 7. Jalankan Aplikasi
```bash
php artisan serve
```

Akses aplikasi di `http://localhost:8000`

## Akun Default

Setelah seeding, tersedia akun berikut:

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@ept.com | password |
| Finance | finance@ept.com | password |
| Mahasiswa | john@student.com | password |

## Struktur URL

### Frontend (Mahasiswa)
- `/` - Welcome page
- `/login` - Login
- `/register` - Registrasi mahasiswa
- `/dashboard` - Dashboard mahasiswa
- `/schedules` - Lihat jadwal tersedia
- `/registrations/create/{schedule}` - Form pendaftaran
- `/registrations/{registration}` - Detail pendaftaran
- `/registrations/{registration}/payment` - Upload bukti bayar

### Admin Panel (Filament)
- `/admin` - Login admin/finance
- `/admin/exam-schedules` - Kelola jadwal (admin only)
- `/admin/registrations` - Kelola pendaftaran
- `/admin/users` - Kelola user (admin only)

## Command Penting

### Cek Pendaftaran Expired (Manual)
```bash
php artisan registrations:check-expired
```

Command ini berjalan otomatis setiap jam via scheduler. Untuk menjalankan scheduler secara lokal:
```bash
php artisan schedule:work
```

### Queue Worker
Jika menggunakan queue untuk notifikasi email:
```bash
php artisan queue:work
```

## Business Rules

1. **Satu Pendaftaran Aktif**: Mahasiswa hanya bisa memiliki 1 pendaftaran dengan status aktif (pending_payment, awaiting_verification, atau verified)

2. **Batas Waktu 24 Jam**: Setelah pendaftaran, mahasiswa memiliki waktu 24 jam untuk upload bukti pembayaran

3. **Kuota Terbatas**: Setiap jadwal memiliki kuota terbatas. Sistem menggunakan database locking untuk mencegah overbooking

4. **Verifikasi Manual**: Bagian keuangan harus memverifikasi atau menolak pembayaran secara manual

5. **Pengembalian Kuota**: Kuota dikembalikan jika pendaftaran expired atau ditolak

## Customization

### Mengganti Database ke MySQL
Edit `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ept
DB_USERNAME=root
DB_PASSWORD=your_password
```

### Mengubah Biaya Pendaftaran
Edit seeder `database/seeders/ExamScheduleSeeder.php` dan jalankan ulang:
```bash
php artisan db:seed --class=ExamScheduleSeeder
```

### Menambahkan Email Notifikasi
1. Setup mail driver di `.env`
2. Buat Mailable classes
3. Dispatch jobs ke queue

## Testing

### Manual Testing Checklist

**Mahasiswa Flow:**
1. Register akun baru
2. Login sebagai mahasiswa
3. Lihat jadwal tersedia
4. Pilih jadwal dan daftar
5. Upload bukti pembayaran
6. Cek status pendaftaran

**Admin Flow:**
1. Login ke `/admin` dengan akun admin
2. Buat jadwal ujian baru
3. Edit jadwal
4. Lihat daftar pendaftar

**Finance Flow:**
1. Login ke `/admin` dengan akun finance
2. Lihat dashboard dengan jumlah pending
3. Verifikasi pembayaran mahasiswa
4. Lihat riwayat verifikasi

**Race Condition Test:**
1. Buka 2 browser berbeda
2. Login dengan 2 akun mahasiswa berbeda
3. Coba daftar jadwal yang sama secara bersamaan
4. Pastikan hanya 1 yang berhasil jika kuota tinggal 1

## Troubleshooting

### Error: "no such table: roles"
```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
```

### Error: "Class not found"
```bash
composer dump-autoload
```

### Permission Denied pada Storage
```bash
chmod -R 775 storage bootstrap/cache
```

### Filament Panel Error
Pastikan middleware dan provider terdaftar dengan benar di `bootstrap/app.php` dan `bootstrap/providers.php`

## Development

### Menambahkan Fitur Baru

1. **Model**: Tambahkan di `app/Models/`
2. **Migration**: Buat di `database/migrations/`
3. **Filament Resource**: Generate dengan `php artisan make:filament-resource`
4. **Policy**: Buat di `app/Policies/`
5. **Routes**: Tambahkan di `routes/web.php`

### Code Style
Proyek ini menggunakan PSR-12. Pastikan untuk:
- Menggunakan type hints
- Menambahkan return types
- Menggunakan docblocks untuk kompleksitas

## Deployment

### Production Checklist
- [ ] Ganti APP_ENV=production
- [ ] Ganti APP_DEBUG=false
- [ ] Setup database production
- [ ] Konfigurasi mail server
- [ ] Setup queue worker (supervisor)
- [ ] Setup cron job untuk scheduler
- [ ] Optimasi: `php artisan optimize`
- [ ] Konfigurasi SSL/HTTPS

### Server Requirements
- PHP >= 8.2
- Extensions: BCMath, Ctype, cURL, DOM, Fileinfo, JSON, Mbstring, OpenSSL, PCRE, PDO, Tokenizer, XML
- MySQL >= 5.7 atau PostgreSQL >= 12
- Composer
- Node.js & NPM (untuk build assets)

## Lisensi

MIT License

## Kontribusi

Silakan buat Pull Request untuk kontribusi. Pastikan untuk:
1. Fork repository
2. Buat branch fitur (`git checkout -b feature/fitur-baru`)
3. Commit perubahan (`git commit -am 'Add fitur baru'`)
4. Push ke branch (`git push origin feature/fitur-baru`)
5. Buat Pull Request

## Support

Untuk pertanyaan atau issue, silakan buat GitHub Issue.

---

**Dibuat dengan ❤️ menggunakan Laravel 11 & Filament 3**
