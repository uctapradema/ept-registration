# UML Diagrams - Mermaid.js

Semua diagram dalam format Mermaid.js untuk rendering di GitHub.

---

## 1. Use Case Diagram

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
        UC_M_DASH[View Dashboard]
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
        UC_A_DASH[View Dashboard]
    end

    subgraph "Finance Features"
        UC_F_VERIFY[Verify Payment]
        UC_F_REJECT[Reject Payment]
        UC_F_SCORING[Input Scores]
        UC_F_PARTICIPANTS[View Participants]
    end

    M --> UC_REG
    M --> UC_LOGIN
    M --> UC_RESET
    M --> UC_BROWSE
    M --> UC_REGISTER
    M --> UC_UPLOAD
    M --> UC_VIEW_STATUS
    M --> UC_CARD
    M --> UC_CANCEL
    M --> UC_M_DASH

    A --> UC_LOGIN
    A --> UC_A_DASH
    A --> UC_MANAGE_USER
    A --> UC_MANAGE_SCHEDULE
    A --> UC_MANAGE_REG
    A --> UC_VERIFY_PAY
    A --> UC_REJECT_PAY
    A --> UC_SCORING
    A --> UC_PARTICIPANTS
    A --> UC_EXPORT

    F --> UC_LOGIN
    F --> UC_A_DASH
    F --> UC_F_VERIFY
    F --> UC_F_REJECT
    F --> UC_F_SCORING
    F --> UC_F_PARTICIPANTS
    F --> UC_EXPORT

    UC_REGISTER -.->|include| UC_BROWSE
    UC_EXPORT -.->|include| UC_PARTICIPANTS
```

---

## 2. Class Diagram

```mermaid
classDiagram
    class User {
        -int id
        -string name
        -string email
        -string password
        -string role
        -string nim
        -string phone
        -string major
        -string faculty
        +isAdmin() bool
        +isFinance() bool
        +isMahasiswa() bool
        +hasActiveRegistration() bool
    }

    class Registration {
        -int id
        -int user_id
        -int exam_schedule_id
        -string registration_number
        -RegistrationStatus status
        -string payment_proof
        -datetime payment_uploaded_at
        -datetime payment_verified_at
        -int verified_by
        -text rejection_reason
        -datetime expires_at
        -int unique_code
        -int listening_score
        -int structure_score
        -int reading_score
        -decimal average_score
        -int graded_by
        -datetime graded_at
        -bool ready_for_scoring
        +isExpired() bool
        +isAwaitingVerification() bool
        +canBeCancelled() bool
        +generateRegistrationNumber() string
        +generateUniqueCode() int
    }

    class ExamSchedule {
        -int id
        -string title
        -string session
        -date exam_date
        -time start_time
        -time end_time
        -int quota
        -datetime registration_deadline
        -int payment_deadline_hours
        -decimal price
        -string bank_name
        -string bank_account
        -string account_holder
        -bool is_active
        -int created_by
        -int unique_code_min
        -int unique_code_max
        +registeredCount() int
        +availableQuota() int
        +isAvailable() bool
        +getSessionOptions() array
    }

    class RegistrationStatus {
        <<enum>>
        PENDING_PAYMENT
        AWAITING_VERIFICATION
        VERIFIED
        REJECTED
        CANCELLED
        EXPIRED
        +label() string
        +color() string
        +tailwindClasses() string
        +options() array
    }

    class RegistrationService {
        +createRegistration() Registration
        +cancelRegistration() void
        +uploadPayment() void
    }

    class PaymentVerificationService {
        +verify() void
        +reject() void
        +canVerify() bool
        +canReject() bool
    }

    class ScoringService {
        +inputScores() Registration
        +calculateAverage() float
        +isPassingScore() bool
    }

    class ExportService {
        +getParticipantsQuery() Builder
        +generateCsv() void
        +getPrintView() string
    }

    class FileStorageService {
        +storePaymentProof() string
    }

    class ResponseService {
        +success() JsonResponse
        +error() JsonResponse
    }

    class RegistrationStatusChanged {
        +Registration registration
        +?RegistrationStatus oldStatus
        +RegistrationStatus newStatus
    }

    class SendRegistrationNotification {
        +handle() void
    }

    class RegistrationException {
        +quotaFull() self
        +alreadyRegistered() self
        +paymentExpired() self
        +cannotBeCancelled() self
        +invalidPaymentStatus() self
        +examCardNotAvailable() self
    }

    class ScoringException {
        +scoreOutOfRange() self
        +notReadyForScoring() self
    }

    class RegistrationPolicy {
        +viewAny() bool
        +view() bool
        +create() bool
        +update() bool
        +delete() bool
        +uploadPayment() bool
        +cancel() bool
        +viewCard() bool
    }

    User "1" --> "*" Registration : has
    ExamSchedule "1" --> "*" Registration : has
    Registration --> RegistrationStatus : uses

    RegistrationService ..> Registration : manages
    PaymentVerificationService ..> Registration : verifies
    ScoringService ..> Registration : scores
    ExportService ..> Registration : exports
    FileStorageService ..> Registration : stores

    RegistrationStatusChanged --> Registration
    SendRegistrationNotification ..> RegistrationStatusChanged : listens

    RegistrationPolicy ..> Registration
```

---

## 3. Sequence Diagram - Registration & Payment Flow

```mermaid
sequenceDiagram
    actor M as Mahasiswa
    actor A as Admin
    participant Web as Web Router
    participant RC as RegistrationController
    participant RS as RegistrationService
    participant FS as FileStorageService
    participant PVS as PaymentVerificationService
    participant DB as Database
    participant Event as Event Dispatcher
    participant Listener as SendRegistrationNotification
    participant Email as Email

    rect rgb(230, 245, 255)
        Note over M,Email: Registration Flow
        M->>Web: GET /mahasiswa/schedules
        Web->>RC: index()
        RC->>DB: Query ExamSchedules
        DB-->>RC: Schedules
        RC-->>M: Show schedules list

        M->>Web: GET /mahasiswa/registrations/create/{schedule}
        Web->>RC: create($scheduleId)
        RC->>DB: Check hasActiveRegistration()
        RC->>DB: Check schedule.isAvailable()
        RC-->>M: Show registration form

        M->>Web: POST /mahasiswa/registrations
        Web->>RC: store(StoreRegistrationRequest)
        RC->>RS: createRegistration($user, $schedule)
        RS->>DB: BEGIN TRANSACTION
        RS->>DB: lockForUpdate(ExamSchedule)
        RS->>DB: Check quota > 0
        RS->>DB: Generate registration_number
        RS->>DB: Generate unique_code
        RS->>DB: Create Registration
        RS->>DB: COMMIT
        RS->>Event: dispatch(RegistrationStatusChanged)
        Event->>Listener: handle()
        Listener->>Email: Send RegistrationSuccessNotification
        Email-->>M: Email: "Pendaftaran Berhasil"
        RS-->>RC: Registration
        RC-->>M: Redirect to show page
    end

    rect rgb(255, 245, 230)
        Note over M,Email: Payment Upload Flow
        M->>Web: GET /mahasiswa/registrations/{id}/payment
        Web->>RC: uploadPayment($registration)
        RC->>RC: authorize('uploadPayment')
        RC->>RC: validatePaymentStatus()
        RC-->>M: Show payment form

        M->>Web: POST /mahasiswa/registrations/{id}/payment
        Web->>RC: storePayment(StorePaymentRequest, $registration)
        RC->>RC: authorize('uploadPayment')
        RC->>RC: validatePaymentStatus()
        RC->>FS: storePaymentProof($file)
        FS->>DB: Store file in /payments
        FS-->>RC: $path
        RC->>RS: uploadPayment($registration, $path, $note)
        RS->>DB: Update registration (status=AWAITING_VERIFICATION)
        RS->>Event: dispatch(RegistrationStatusChanged)
        RS-->>RC: void
        RC->>RC: ResponseService->success()
        RC-->>M: Redirect with success message
    end

    rect rgb(230, 255, 230)
        Note over A,Email: Payment Verification Flow (Admin/Finance)
        A->>Web: Login to /admin
        Web->>A: Show Filament Dashboard

        A->>Web: Navigate to Registrations
        Web->>A: Show RegistrationResource table

        A->>Web: Click "Verifikasi" button
        Web->>A: Show confirmation modal
        A->>Web: Confirm verification
        Web->>PVS: verify($registration, $verifier)
        PVS->>DB: BEGIN TRANSACTION
        PVS->>DB: Update status=VERIFIED
        PVS->>DB: Set verified_by, payment_verified_at
        PVS->>DB: COMMIT
        PVS->>Event: dispatch(RegistrationStatusChanged)
        Event->>Listener: handle()
        Listener->>Email: Send PaymentVerifiedNotification
        Email-->>M: Email: "Pembayaran Diverifikasi"
        PVS-->>Web: void
        Web-->>A: Show success message
    end

    rect rgb(255, 230, 230)
        Note over A,Email: Payment Rejection Flow
        A->>Web: Click "Tolak" button
        Web->>A: Show rejection reason modal
        A->>Web: Submit rejection reason
        Web->>PVS: reject($registration, $reason, $rejector)
        PVS->>DB: BEGIN TRANSACTION
        PVS->>DB: Update status=REJECTED
        PVS->>DB: Set rejection_reason
        PVS->>DB: COMMIT
        PVS->>Event: dispatch(RegistrationStatusChanged)
        Event->>Listener: handle()
        Listener->>Email: Send PaymentRejectedNotification
        Email-->>M: Email: "Pembayaran Ditolak"
        PVS-->>Web: void
        Web-->>A: Show success message
    end

    rect rgb(245, 230, 255)
        Note over M,RC: Exam Card Download
        M->>Web: GET /mahasiswa/registrations/{id}/card
        Web->>RC: card($registration)
        RC->>RC: authorize('viewCard')
        RC->>RC: Check status == VERIFIED
        RC->>DB: Load examSchedule, user
        RC->>RC: Generate PDF (DomPDF)
        RC-->>M: Stream PDF file
    end
```

---

## 4. Activity Diagram - Registration Workflow

> **UML Notation:** ● = Initial Node | ⊙ = Activity Final Node | ⊗ = Flow Final Node | ◇ = Decision/Merge Node | [] = Action | {} = Guard Condition

```mermaid
flowchart TD
    %% Initial Node
    start((●))

    %% Swimlane: Mahasiswa
    subgraph MH[" "]
        direction TB
        M1[/"Browse Exam Schedules"/]
        M2[/"Select Schedule"/]
        M3[/"Fill Registration Form"/]
        M4[/"Make Bank Transfer"/]
        M5[/"Upload Payment Proof"/]
        M6[/"Download Exam Card"/]
        M7[/"Attend Exam"/]
        M8[/"Re-upload Payment"/]
    end

    %% Swimlane: System
    subgraph SY[" "]
        direction TB
        S1[/"Verify Credentials"/]
        S2[/"Check Schedule Availability"/]
        S3[/"Check Active Registration"/]
        S4[/"Generate Registration Number"/]
        S5[/"Generate Unique Code"/]
        S6[/"Create Registration<br/>(status: PENDING_PAYMENT)"/]
        S7[/"Validate File Upload"/]
        S8[/"Store Payment Proof"/]
        S9[/"Update Status<br/>(status: AWAITING_VERIFICATION)"/]
        S10[/"Check Payment Deadline"/]
        S11[/"Update Status<br/>(status: EXPIRED)"/]
        S12[/"Update Status<br/>(status: VERIFIED)"/]
        S13[/"Set verified_by,<br/>payment_verified_at"/]
        S14[/"Update Status<br/>(status: REJECTED)"/]
        S15[/"Set rejection_reason"/]
        S16[/"Validate Scores<br/>(0-100)"/]
        S17[/"Calculate Average Score"/]
        S18[/"Save Scores"/]
        S19[/"Set graded_by,<br/>graded_at"/]
    end

    %% Swimlane: Admin/Finance
    subgraph AF[" "]
        direction TB
        A1[/"Review Payment Proof"/]
        A2[/"Decide: Verify or Reject"/]
        A3[/"Enter Rejection Reason"/]
        A4[/"Input Exam Scores"/]
    end

    %% Flow: Initial to Authentication
    start --> S1
    S1 -->|{Valid?}| D1{◇}

    %% Decision: Valid Credentials
    D1 -->|No| E1[/"Show Error Message"/]
    D1 -->|Yes| M1

    %% Flow: Browse to Register
    M1 --> M2
    M2 --> S2
    S2 -->|{Available?}| D2{◇}

    %% Decision: Schedule Available
    D2 -->|No| E2[/"Show Error Message"/]
    D2 -->|Yes| S3

    %% Check Active Registration
    S3 -->|{Has Active?}| D3{◇}

    %% Decision: Has Active Registration
    D3 -->|Yes| E3[/"Show Warning:<br/>Redirect to Existing"/]
    D3 -->|No| M3

    %% Registration Flow
    M3 --> S4
    S4 --> S5
    S5 --> S6
    S6 --> M4

    %% Payment Upload Flow
    M4 --> M5
    M5 --> S7
    S7 -->|{Valid File?}| D4{◇}

    %% Decision: Valid File
    D4 -->|No| E4[/"Show Error Message"/]
    D4 -->|Yes| S8

    S8 --> S9
    S9 --> S10

    %% Check Payment Deadline
    S10 -->|{Expired?}| D5{◇}

    %% Decision: Payment Expired
    D5 -->|Yes| S11
    D5 -->|No| A1

    %% Admin/Finance Verification
    A1 --> A2
    A2 -->|{Decision?}| D6{◇}

    %% Decision: Verify or Reject
    D6 -->|Verify| S12
    D6 -->|Reject| A3

    %% Verified Path
    S12 --> S13
    S13 --> M6
    M6 --> M7
    M7 --> A4

    %% Scoring Flow
    A4 --> S16
    S16 -->|{Valid Scores?}| D7{◇}

    %% Decision: Valid Scores
    D7 -->|No| E5[/"Show Error:<br/>Scores out of range"/]
    D7 -->|Yes| S17

    S17 --> S18
    S18 --> S19
    S19 --> finish((⊙))

    %% Rejected Path
    A3 --> S14
    S14 --> S15
    S15 --> M8
    M8 --> M5

    %% Expired Path
    S11 --> finish2((⊗))

    %% Error Paths
    E1 --> finish3((⊗))
    E2 --> finish4((⊗))
    E3 --> finish5((⊗))
    E4 --> finish6((⊗))
    E5 --> finish7((⊗))

    %% Styling
    style start fill:#000,color:#fff,stroke:#000,stroke-width:4px
    style finish fill:#fff,color:#000,stroke:#000,stroke-width:4px
    style finish2 fill:#fff,color:#000,stroke:#000,stroke-width:4px
    style finish3 fill:#fff,color:#000,stroke:#000,stroke-width:4px
    style finish4 fill:#fff,color:#000,stroke:#000,stroke-width:4px
    style finish5 fill:#fff,color:#000,stroke:#000,stroke-width:4px
    style finish6 fill:#fff,color:#000,stroke:#000,stroke-width:4px
    style finish7 fill:#fff,color:#000,stroke:#000,stroke-width:4px

    style D1 fill:#FFD700,color:#000,stroke:#000
    style D2 fill:#FFD700,color:#000,stroke:#000
    style D3 fill:#FFD700,color:#000,stroke:#000
    style D4 fill:#FFD700,color:#000,stroke:#000
    style D5 fill:#FFD700,color:#000,stroke:#000
    style D6 fill:#FFD700,color:#000,stroke:#000
    style D7 fill:#FFD700,color:#000,stroke:#000

    style MH fill:#E3F2FD,stroke:#1565C0,stroke-width:2px
    style SY fill:#E8F5E9,stroke:#2E7D32,stroke-width:2px
    style AF fill:#FFF3E0,stroke:#E65100,stroke-width:2px
```

---

## 5. Sequence Diagram - Scoring Flow

```mermaid
sequenceDiagram
    actor A as Admin
    actor F as Finance
    participant Panel as Filament Panel
    participant SR as ScoringResource
    participant SS as ScoringService
    participant DB as Database
    participant Event as Event Dispatcher
    participant Listener as SendRegistrationNotification
    participant Email as Email
    participant RR as RegistrationResource

    rect rgb(230, 245, 255)
        Note over A,RR: View Participants for Scoring
        A->>Panel: Login as Admin
        Panel->>RR: Navigate to RegistrationResource
        RR->>DB: Query registrations (verified status)
        RR->>Panel: Show RegistrationResource table

        A->>Panel: Click "Input Nilai" button
    end

    rect rgb(255, 245, 230)
        Note over A,Email: Input Scores (Admin)
        Panel->>SR: ScoringResource->editForm()
        SR->>DB: Load registration with user + schedule
        SR->>Panel: Show scoring form

        A->>Panel: Enter scores (Listening, Structure, Reading)
        A->>Panel: Submit form

        Panel->>SR: ScoringResource->handleRecordOperation()
        SR->>SS: inputScores($registration, $data, $admin)
        SS->>SS: validateScores($listening, $structure, $reading)
        SS->>SS: isScoreInRange() check (0-100)
        SS->>SS: calculateAverage($listening, $structure, $reading)
        SS->>DB: BEGIN TRANSACTION
        SS->>DB: Update registration with scores
        SS->>DB: Set graded_by = $admin->id
        SS->>DB: Set graded_at = now()
        SS->>DB: Update ready_for_scoring = false
        SS->>DB: COMMIT

        SS->>Event: dispatch(RegistrationStatusChanged)
        Event->>Listener: handle()
        Listener->>Email: Send ScoreEnteredNotification
        Email-->>A: Email: "Penilaian Telah Dijalankan"

        SS-->>SR: Registration
        SR-->>Panel: Redirect with success
    end

    rect rgb(230, 255, 230)
        Note over F,Email: Input Scores (Finance)
        F->>Panel: Login as Finance
        Panel->>SR: Navigate to ScoringResource
        SR->>DB: Query registrations (verified + no scores)
        SR->>Panel: Show ScoringResource table

        F->>Panel: Click "Input Nilai" button
        Panel->>SR: editForm()
        SR->>DB: Load registration
        SR->>Panel: Show scoring form

        F->>Panel: Enter scores
        F->>Panel: Submit form

        Panel->>SR: handleRecordOperation()
        SR->>SS: inputScores()
        SS->>SS: validateScores()
        SS->>SS: calculateAverage()
        SS->>DB: Update registration
        SS->>DB: Set graded_by = $finance->id
        SS->>DB: Set graded_at = now()

        SS->>Event: dispatch(RegistrationStatusChanged)
        Event->>Listener: handle()
        Listener->>Email: Send ScoreEnteredNotification
        Email-->>F: Email: "Penilaian Telah Dijalankan"
    end

    rect rgb(245, 230, 255)
        Note over A,DB: View Scores
        A->>Panel: View RegistrationResource
        Panel->>DB: Query registrations with scores
        Panel->>Panel: Show scores in table columns
        Panel->>A: Display: Listening, Structure, Reading, Average, Status
    end
```

---

## 6. Component Diagram - Architecture

```mermaid
graph TD
    subgraph Presentation["Presentation Layer"]
        AdminPanel["Filament Admin Panel"]
        ScoringPanel["Filament Scoring Panel"]
        MahasiswaWeb["Mahasiswa Web Routes"]
        PDFExport["PDF Export (DomPDF)"]
    end

    subgraph Application["Application Layer"]
        Controllers["Controllers"]
        FilamentRes["Filament Resources"]
        FormReq["Form Requests"]
        Policies["Policies"]
        Middleware["Middleware"]
        EventsListeners["Events & Listeners"]
        Exceptions["Exceptions"]
    end

    subgraph Domain["Domain Layer"]
        RegService["Registration Service"]
        PayService["Payment Verification Service"]
        ScoreService["Scoring Service"]
        ExportService["Export Service"]
        FileService["File Storage Service"]
        ResService["Response Service"]
    end

    subgraph Infrastructure["Infrastructure Layer"]
        EloquentModels["Eloquent Models"]
        Migrations["Database Migrations"]
        EmailNotif["Email Notifications"]
        FileStorage["File Storage"]
    end

    subgraph External["External Dependencies"]
        Laravel["Laravel Framework"]
        FilamentPHP["Filament PHP"]
        SpatiePerm["Spatie Permission"]
        DomPDFLib["DomPDF"]
        Tailwind["Tailwind CSS"]
    end

    AdminPanel --> Controllers
    AdminPanel --> FilamentRes
    ScoringPanel --> FilamentRes
    MahasiswaWeb --> Controllers
    MahasiswaWeb --> FormReq

    Controllers --> RegService
    Controllers --> PayService
    Controllers --> ExportService
    FilamentRes --> ScoreService
    FilamentRes --> RegService
    FilamentRes --> PayService
    EventsListeners --> RegService
    EventsListeners --> PayService

    RegService --> EloquentModels
    PayService --> EloquentModels
    ScoreService --> EloquentModels
    ExportService --> EloquentModels
    FileService --> FileStorage
    EmailNotif --> Laravel

    EloquentModels --> Laravel
    FilamentRes --> FilamentPHP
    FormReq --> Laravel
    Policies --> SpatiePerm
    PDFExport --> DomPDFLib

    Middleware --> Policies
    Policies --> EloquentModels

    style Presentation fill:#E3F2FD,stroke:#1565C0
    style Application fill:#E8F5E9,stroke:#2E7D32
    style Domain fill:#FFF3E0,stroke:#E65100
    style Infrastructure fill:#F3E5F5,stroke:#6A1B9A
    style External fill:#ECEFF1,stroke:#546E6A
```

---

## 7. Data Flow Diagram (DFD) Level 1

```mermaid
graph LR
    subgraph External["External Entities"]
        M[Mahasiswa]
        A[Admin]
        F[Finance]
        ES[Email Server]
        FS[File System]
    end

    subgraph Processes["Processes"]
        P1["P1: Registration Management"]
        P2["P2: Payment Verification"]
        P3["P3: Scoring Management"]
        P4["P4: Data Export"]
        P5["P5: Authentication & Authorization"]
        P6["P6: Notification Management"]
    end

    subgraph DataStores["Data Stores"]
        D1[("D1: Users")]
        D2[("D2: Registrations")]
        D3[("D3: Exam Schedules")]
        D4[("D4: Files")]
    end

    M -->|Browse schedules, Register, Cancel| P1
    A -->|Verify/Reject payment| P2
    F -->|Verify/Reject payment| P2
    A -->|Input exam scores| P3
    F -->|Input exam scores| P3
    A -->|View, Export CSV| P4
    F -->|View, Export CSV| P4
    M -->|Login, Register| P5
    A -->|Login| P5
    F -->|Login| P5

    P1 -->|Query schedules| D3
    P1 -->|Create/Update registration| D2
    P1 -->|Check user data| D1
    P1 -->|Store payment proof| D4
    P1 -->|Dispatch event| P6

    P2 -->|Update status| D2
    P2 -->|Dispatch event| P6

    P3 -->|Update scores| D2
    P3 -->|Dispatch event| P6

    P4 -->|Query registrations| D2
    P4 -->|Query user data| D1
    P4 -->|Query schedule data| D3

    P5 -->|Verify credentials| D1

    P6 -->|Send emails| ES

    style External fill:#E3F2FD,stroke:#1565C0
    style Processes fill:#E8F5E9,stroke:#2E7D32
    style DataStores fill:#FFF3E0,stroke:#E65100
```
