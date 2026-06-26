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

```mermaid
flowchart TD
    Start((Start)) --> M_Open[Mahasiswa: Open EPT Website]
    M_Open --> M_Login[Mahasiswa: Login with credentials]

    M_Login --> S_Verify[System: Verify credentials]
    S_Verify -->|Invalid| S_Error[System: Show error message]
    S_Error --> Stop1((Stop))
    S_Verify -->|Valid| S_Session[System: Create session]

    S_Session --> M_Dash[Mahasiswa: View Dashboard]
    M_Dash --> M_Browse[Mahasiswa: Browse Exam Schedules]

    M_Browse --> S_Query[System: Query active schedules]
    S_Query --> S_CheckDead[System: Check registration deadline]
    S_CheckDead --> S_CheckQuota[System: Check available quota]

    S_CheckQuota --> M_Select[Mahasiswa: Select schedule]
    M_Select --> M_Click[Mahasiswa: Click Register]

    M_Click --> S_CheckActive[System: Check if user has active registration]
    S_CheckActive -->|Has active| S_Warn[System: Show warning message]
    S_Warn --> Stop2((Stop))
    S_CheckActive -->|No active| S_CheckAvail[System: Check schedule availability]

    S_CheckAvail -->|Not available| S_ErrorMsg[System: Show error message]
    S_ErrorMsg --> Stop3((Stop))
    S_CheckAvail -->|Available| S_GenRegNum[System: Generate registration number]
    S_GenRegNum --> S_GenCode[System: Generate unique code]
    S_GenCode --> S_CreateReg[System: Create registration]
    S_CreateReg --> S_SetDeadline[System: Set payment deadline]
    S_SetDeadline --> S_Dispatch1[System: Dispatch RegistrationStatusChanged event]
    S_Dispatch1 --> S_SendEmail1[System: Send RegistrationSuccessNotification email]

    S_SendEmail1 --> M_ViewReg[Mahasiswa: View registration details]
    M_ViewReg --> M_Note[Mahasiswa: Note unique code and total payment]
    M_Note --> M_Transfer[Mahasiswa: Make bank transfer]

    M_Transfer --> M_Upload[Mahasiswa: Upload payment proof]
    M_Upload --> M_AddNote[Mahasiswa: Add payment note optional]
    M_AddNote --> S_Validate[System: Validate file upload]
    S_Validate --> S_StoreFile[System: Store payment proof file]
    S_StoreFile --> S_UpdateStatus1[System: Update registration]
    S_UpdateStatus1 --> S_Dispatch2[System: Dispatch RegistrationStatusChanged event]

    S_Dispatch2 --> A_View[Admin/Finance: View RegistrationResource table]
    A_View --> A_Review[Admin/Finance: Review payment proof]
    A_Review --> A_Click[Admin/Finance: Click Verifikasi or Tolak]

    A_Click -->|Verify| S_UpdateVerified[System: Update status to VERIFIED]
    S_UpdateVerified --> S_SetVerified[System: Set verified_by, payment_verified_at]
    S_SetVerified --> S_Dispatch3[System: Dispatch event]
    S_Dispatch3 --> S_SendEmail2[System: Send PaymentVerifiedNotification email]

    S_SendEmail2 --> M_ReceiveOK[Mahasiswa: Receive verification email]
    M_ReceiveOK --> M_Download[Mahasiswa: Download Exam Card PDF]
    M_Download --> M_Attend[Mahasiswa: Attend exam]

    M_Attend --> A_Score[Admin/Finance: Input exam scores]
    A_Score --> S_ValidateScore[System: Validate scores 0-100]
    S_ValidateScore --> S_CalcAvg[System: Calculate average score]
    S_CalcAvg --> S_UpdateScores[System: Update registration with scores]
    S_UpdateScores --> S_SetGraded[System: Set graded_by, graded_at]

    A_Click -->|Reject| A_Reason[Admin/Finance: Enter rejection reason]
    A_Reason --> S_UpdateRejected[System: Update status to REJECTED]
    S_UpdateRejected --> S_SetReason[System: Set rejection_reason]
    S_SetReason --> S_Dispatch4[System: Dispatch event]
    S_Dispatch4 --> S_SendEmail3[System: Send PaymentRejectedNotification email]
    S_SendEmail3 --> M_ReceiveReject[Mahasiswa: Receive rejection email]
    M_ReceiveReject --> M_ReUpload[Mahasiswa: Re-upload payment proof]
    M_ReUpload --> M_Upload

    S_SetGraded --> S_CheckExpired[System: Check expired registrations hourly]
    S_CheckExpired -->|Deadline passed| S_UpdateExpired[System: Update status to EXPIRED]
    S_UpdateExpired --> S_Dispatch5[System: Dispatch event]
    S_Dispatch5 --> Stop4((Stop))
    S_CheckExpired -->|Not expired| Stop5((Stop))

    style Start fill:#4CAF50,color:#fff
    style Stop1 fill:#f44336,color:#fff
    style Stop2 fill:#f44336,color:#fff
    style Stop3 fill:#f44336,color:#fff
    style Stop4 fill:#2196F3,color:#fff
    style Stop5 fill:#2196F3,color:#fff
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
