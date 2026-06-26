# Aplikasi Pendaftaran English Professional Test (EPT)

Aplikasi web untuk mengelola pendaftaran ujian English Professional Test di lingkungan kampus, dibangun dengan Laravel 12 dan Filament 3.

## Fitur Utama

### 1. Tiga Role Pengguna
- **Mahasiswa** - Mendaftar ujian, upload bukti pembayaran, cek status
- **Admin** - Kelola jadwal ujian, monitor pendaftar, kelola user, input nilai
- **Finance** - Verifikasi pembayaran mahasiswa, input nilai, lihat data

### 2. Modul Mahasiswa (Frontend)
- Registrasi dan login
- Melihat jadwal ujian tersedia dengan informasi kuota real-time
- Pendaftaran ujian dengan validasi (hanya 1 pendaftaran aktif)
- Upload bukti pembayaran (maksimal 24 jam)
- Melihat status pendaftaran dengan countdown timer
- Download kartu ujian (PDF)

### 3. Modul Admin (Filament Panel)
- Kelola jadwal ujian (CRUD)
- Monitor semua pendaftaran
- Filter dan search data
- Export data pendaftar (CSV)
- Manajemen user
- Input nilai ujian

### 4. Modul Keuangan (Filament Panel)
- Dashboard khusus dengan statistik
- Daftar pendaftar menunggu verifikasi
- Verifikasi/tolak pembayaran dengan modal
- Preview bukti transfer
- Riwayat verifikasi
- Input nilai ujian

### 5. Fitur Keamanan & Validasi
- Role-based access control dengan Spatie Permission
- Database transaction dengan locking untuk mencegah race condition
- Validasi batas waktu pembayaran 24 jam
- Scheduler otomatis untuk cek pendaftaran expired
- Soft deletes untuk data penting
- Custom exception handling (RegistrationException, ScoringException)
- Event-driven notifications (RegistrationStatusChanged event)

## Tech Stack

- **Framework**: Laravel 12.x
- **Admin Panel**: Filament 3.x
- **Database**: MySQL
- **Autentikasi**: Laravel Breeze
- **Role Management**: Spatie Laravel Permission
- **Frontend**: Blade + Tailwind CSS + Alpine.js
- **Queue**: Database (default)
- **PDF Export**: DomPDF

## Arsitektur

### Clean Architecture (Layered)
```
app/
├── Constants/          # AppConstants
├── Enums/              # RegistrationStatus
├── Exceptions/         # RegistrationException, ScoringException
├── Events/             # RegistrationStatusChanged
├── Listeners/          # SendRegistrationNotification
├── Filament/
│   ├── Actions/        # VerifyPaymentAction, RejectPaymentAction
│   ├── Columns/        # RegistrationColumns
│   ├── Filters/        # RegistrationFilters
│   ├── Resources/      # RegistrationResource, ScoringResource, etc.
│   └── Pages/          # Participants
├── Http/
│   ├── Controllers/
│   │   ├── Admin/      # Filament controllers
│   │   └── Mahasiswa/  # RegistrationController, DashboardController
│   └── Middleware/      # EnsureAdmin, EnsureMahasiswa
├── Models/             # User, Registration, ExamSchedule
├── Providers/          # AppServiceProvider, Filament AdminPanelProvider
└── Services/           # Business logic services
    ├── RegistrationService.php
    ├── PaymentVerificationService.php
    ├── ScoringService.php
    ├── ExportService.php
    ├── FileStorageService.php
    └── ResponseService.php
```

### Key Design Patterns
- **Service Layer**: Business logic di-services, bukan di-controller
- **Event-Driven**: Status changes dispatch events untuk notifications
- **Policy-Based Authorization**: Gate/Policy untuk akses control
- **Custom Exceptions**: Named constructors untuk error handling
- **Query Scopes**: Reusable query logic di-models

## Instalasi

### 1. Clone Repository
```bash
git clone https://github.com/uctapradema/ept-registration.git
cd ept-registration
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
# MySQL
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ept
DB_USERNAME=root
DB_PASSWORD=your_password
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
- `/registrations/{registration}/card` - Download kartu ujian

### Admin Panel (Filament)
- `/admin` - Login admin/finance
- `/admin/exam-schedules` - Kelola jadwal (admin only)
- `/admin/registrations` - Kelola pendaftaran
- `/admin/scoring` - Input nilai (admin/finance)
- `/admin/users` - Kelola user (admin only)
- `/admin/participants` - Lihat data peserta

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

4. **Verifikasi Manual**: Admin/Finance harus memverifikasi atau menolak pembayaran secara manual

5. **Pengembalian Kuota**: Kuota dikembalikan jika pendaftaran expired atau ditolak

6. **Status Flow**: PENDING_PAYMENT → AWAITING_VERIFICATION → VERIFIED / REJECTED / EXPIRED

## Testing

### Manual Testing Checklist

**Mahasiswa Flow:**
1. Register akun baru
2. Login sebagai mahasiswa
3. Lihat jadwal tersedia
4. Pilih jadwal dan daftar
5. Upload bukti pembayaran
6. Cek status pendaftaran
7. Download kartu ujian

**Admin Flow:**
1. Login ke `/admin` dengan akun admin
2. Buat jadwal ujian baru
3. Edit jadwal
4. Lihat daftar pendaftar
5. Verifikasi pembayaran
6. Input nilai ujian
7. Export data ke CSV

**Finance Flow:**
1. Login ke `/admin` dengan akun finance
2. Lihat dashboard dengan jumlah pending
3. Verifikasi pembayaran mahasiswa
4. Lihat riwayat verifikasi
5. Input nilai ujian

## UML Documentation

Dokumentasi UML tersedia di `docs/uml/`. Berikut diagram-diagram utama:

### Use Case Diagram

```mermaid
graph LR
    subgraph Actors
        M((Mahasiswa))
        A((Admin))
        F((Finance))
    end

    subgraph Authentication
        UC_REG[Register Account]
        UC_LOGIN[Login]
        UC_RESET[Reset Password]
    end

    subgraph "Mahasiswa Features"
        UC_BROWSE[Browse Exam Schedules]
        UC_REGISTER[Register for Exam]
        UC_UPLOAD[Upload Payment Proof]
        UC_VIEW_STATUS[View Registration Status]
        UC_CARD[Download Exam Card]
        UC_CANCEL[Cancel Registration]
    end

    subgraph "Admin Features"
        UC_MANAGE_USER[Manage Users]
        UC_MANAGE_SCHEDULE[Manage Exam Schedules]
        UC_MANAGE_REG[Manage Registrations]
        UC_VERIFY_PAY[Verify Payment]
        UC_REJECT_PAY[Reject Payment]
        UC_SCORING[Input Exam Scores]
        UC_PARTICIPANTS[View Participants]
        UC_EXPORT[Export Data]
    end

    subgraph "Finance Features"
        UC_F_VERIFY[Verify Payment]
        UC_F_REJECT[Reject Payment]
        UC_F_SCORING[Input Scores]
        UC_F_PARTICIPANTS[View Participants]
    end

    M --> UC_REG
    M --> UC_LOGIN
    M --> UC_BROWSE
    M --> UC_REGISTER
    M --> UC_UPLOAD
    M --> UC_VIEW_STATUS
    M --> UC_CARD
    M --> UC_CANCEL

    A --> UC_LOGIN
    A --> UC_MANAGE_USER
    A --> UC_MANAGE_SCHEDULE
    A --> UC_MANAGE_REG
    A --> UC_VERIFY_PAY
    A --> UC_REJECT_PAY
    A --> UC_SCORING
    A --> UC_PARTICIPANTS
    A --> UC_EXPORT

    F --> UC_LOGIN
    F --> UC_F_VERIFY
    F --> UC_F_REJECT
    F --> UC_F_SCORING
    F --> UC_F_PARTICIPANTS
    F --> UC_EXPORT

    UC_REGISTER -.->|include| UC_BROWSE
    UC_EXPORT -.->|include| UC_PARTICIPANTS
```

### Class Diagram

```mermaid
classDiagram
    class User {
        -int id
        -string name
        -string email
        -string role
        +isAdmin() bool
        +isFinance() bool
        +isMahasiswa() bool
    }

    class Registration {
        -int id
        -int user_id
        -int exam_schedule_id
        -string registration_number
        -RegistrationStatus status
        -int listening_score
        -int structure_score
        -int reading_score
        -decimal average_score
        +isExpired() bool
        +canBeCancelled() bool
    }

    class ExamSchedule {
        -int id
        -string title
        -string session
        -date exam_date
        -int quota
        -decimal price
        +registeredCount() int
        +availableQuota() int
        +isAvailable() bool
    }

    class RegistrationStatus {
        <<enum>>
        PENDING_PAYMENT
        AWAITING_VERIFICATION
        VERIFIED
        REJECTED
        CANCELLED
        EXPIRED
    }

    class RegistrationService {
        +createRegistration() Registration
        +cancelRegistration() void
        +uploadPayment() void
    }

    class PaymentVerificationService {
        +verify() void
        +reject() void
    }

    class ScoringService {
        +inputScores() Registration
        +calculateAverage() float
    }

    User "1" --> "*" Registration : has
    ExamSchedule "1" --> "*" Registration : has
    Registration --> RegistrationStatus : uses
    RegistrationService ..> Registration : manages
    PaymentVerificationService ..> Registration : verifies
    ScoringService ..> Registration : scores
```

### Sequence Diagram - Registration Flow

```mermaid
sequenceDiagram
    actor M as Mahasiswa
    participant Web as Web Router
    participant RC as RegistrationController
    participant RS as RegistrationService
    participant DB as Database
    participant Event as Event Dispatcher
    participant Email as Email

    M->>Web: POST /mahasiswa/registrations
    Web->>RC: store(StoreRegistrationRequest)
    RC->>RS: createRegistration($user, $schedule)
    RS->>DB: BEGIN TRANSACTION
    RS->>DB: lockForUpdate(ExamSchedule)
    RS->>DB: Check quota > 0
    RS->>DB: Create Registration
    RS->>DB: COMMIT
    RS->>Event: dispatch(RegistrationStatusChanged)
    Event->>Email: Send RegistrationSuccessNotification
    Email-->>M: Email: "Pendaftaran Berhasil"
    RS-->>RC: Registration
    RC-->>M: Redirect to show page
```

### Sequence Diagram - Payment Verification Flow

```mermaid
sequenceDiagram
    actor A as Admin/Finance
    participant Panel as Filament Panel
    participant PVS as PaymentVerificationService
    participant DB as Database
    participant Event as Event Dispatcher
    participant Email as Email
    participant M as Mahasiswa

    A->>Panel: Click "Verifikasi" button
    Panel->>PVS: verify($registration, $verifier)
    PVS->>DB: BEGIN TRANSACTION
    PVS->>DB: Update status=VERIFIED
    PVS->>DB: Set verified_by, payment_verified_at
    PVS->>DB: COMMIT
    PVS->>Event: dispatch(RegistrationStatusChanged)
    Event->>Email: Send PaymentVerifiedNotification
    Email-->>M: Email: "Pembayaran Diverifikasi"
    PVS-->>Panel: void
    Panel-->>A: Show success message
```

### Activity Diagram - Registration Workflow

```mermaid
flowchart TD
    Start((Start)) --> M_Login[Mahasiswa: Login]
    M_Login --> S_Verify[System: Verify credentials]
    S_Verify -->|Invalid| S_Error[System: Show error]
    S_Error --> Stop1((Stop))
    S_Verify -->|Valid| M_Browse[Mahasiswa: Browse Schedules]
    M_Browse --> S_CheckQuota[System: Check quota]
    S_CheckQuota -->|Available| M_Register[Mahasiswa: Register]
    S_CheckQuota -->|Not available| S_ErrorMsg[System: Show error]
    S_ErrorMsg --> Stop2((Stop))
    M_Register --> S_CreateReg[System: Create registration]
    S_CreateReg --> M_Upload[Mahasiswa: Upload payment]
    M_Upload --> S_WaitVerify[System: Await verification]
    S_WaitVerify --> A_Verify[Admin/Finance: Verify]
    A_Verify -->|Verified| S_Verified[System: Status = VERIFIED]
    S_Verified --> M_Download[Mahasiswa: Download card]
    M_Download --> M_Exam[Mahasiswa: Attend exam]
    A_Verify -->|Rejected| S_Rejected[System: Status = REJECTED]
    S_Rejected --> M_ReUpload[Mahasiswa: Re-upload payment]
    M_ReUpload --> M_Upload
    M_Exam --> A_Score[Admin/Finance: Input scores]
    A_Score --> S_SaveScores[System: Save scores]
    S_SaveScores --> Stop3((Stop))

    style Start fill:#4CAF50,color:#fff
    style Stop1 fill:#f44336,color:#fff
    style Stop2 fill:#f44336,color:#fff
    style Stop3 fill:#2196F3,color:#fff
```

### Component Diagram - Architecture

```mermaid
graph TD
    subgraph Presentation["Presentation Layer"]
        AdminPanel["Filament Admin Panel"]
        ScoringPanel["Filament Scoring Panel"]
        MahasiswaWeb["Mahasiswa Web Routes"]
    end

    subgraph Application["Application Layer"]
        Controllers["Controllers"]
        FilamentRes["Filament Resources"]
        Policies["Policies"]
        EventsListeners["Events & Listeners"]
    end

    subgraph Domain["Domain Layer"]
        RegService["Registration Service"]
        PayService["Payment Verification Service"]
        ScoreService["Scoring Service"]
        ExportService["Export Service"]
    end

    subgraph Infrastructure["Infrastructure Layer"]
        EloquentModels["Eloquent Models"]
        Migrations["Database Migrations"]
        EmailNotif["Email Notifications"]
    end

    AdminPanel --> Controllers
    AdminPanel --> FilamentRes
    ScoringPanel --> FilamentRes
    MahasiswaWeb --> Controllers

    Controllers --> RegService
    Controllers --> PayService
    FilamentRes --> ScoreService
    FilamentRes --> RegService
    EventsListeners --> RegService

    RegService --> EloquentModels
    PayService --> EloquentModels
    ScoreService --> EloquentModels
    ExportService --> EloquentModels

    style Presentation fill:#E3F2FD,stroke:#1565C0
    style Application fill:#E8F5E9,stroke:#2E7D32
    style Domain fill:#FFF3E0,stroke:#E65100
    style Infrastructure fill:#F3E5F5,stroke:#6A1B9A
```

### Data Flow Diagram (DFD)

```mermaid
graph LR
    subgraph External["External Entities"]
        M[Mahasiswa]
        A[Admin]
        F[Finance]
        ES[Email Server]
    end

    subgraph Processes["Processes"]
        P1["P1: Registration Management"]
        P2["P2: Payment Verification"]
        P3["P3: Scoring Management"]
        P4["P4: Data Export"]
        P5["P5: Authentication"]
        P6["P6: Notification Management"]
    end

    subgraph DataStores["Data Stores"]
        D1[("D1: Users")]
        D2[("D2: Registrations")]
        D3[("D3: Exam Schedules")]
    end

    M -->|Browse, Register| P1
    A -->|Verify/Reject| P2
    F -->|Verify/Reject| P2
    A -->|Input scores| P3
    F -->|Input scores| P3
    A -->|View, Export| P4
    M -->|Login| P5

    P1 -->|Query| D3
    P1 -->|Create/Update| D2
    P2 -->|Update status| D2
    P3 -->|Update scores| D2
    P4 -->|Query| D2
    P5 -->|Verify| D1
    P1 -->|Dispatch event| P6
    P6 -->|Send emails| ES

    style External fill:#E3F2FD,stroke:#1565C0
    style Processes fill:#E8F5E9,stroke:#2E7D32
    style DataStores fill:#FFF3E0,stroke:#E65100
```

Lihat `docs/uml/DIAGRAMS.md` untuk versi lengkap semua diagram.

## Development

### Code Quality Standards
- Type hints pada semua parameter
- Return types pada semua method
- Custom exceptions untuk error handling
- Service layer untuk business logic
- Query scopes untuk reusable queries
- Event-driven untuk loose coupling

### Menambahkan Fitur Baru

1. **Model**: Tambahkan di `app/Models/`
2. **Migration**: Buat di `database/migrations/`
3. **Filament Resource**: Generate dengan `php artisan make:filament-resource`
4. **Policy**: Buat di `app/Policies/`
5. **Service**: Buat di `app/Services/`
6. **Routes**: Tambahkan di `routes/web.php`

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
- [ ] Jalankan migrations

### Server Requirements
- PHP >= 8.2
- Extensions: BCMath, Ctype, cURL, DOM, Fileinfo, JSON, Mbstring, OpenSSL, PCRE, PDO, Tokenizer, XML
- MySQL >= 5.7
- Composer
- Node.js & NPM (untuk build assets)

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

## License

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

**Dibuat dengan ❤️ menggunakan Laravel 12 & Filament 3**
