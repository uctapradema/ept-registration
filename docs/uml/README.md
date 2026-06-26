# UML Diagrams - EPT Registration System

## Overview

Dokumentasi UML untuk aplikasi EPT Registration System. Tersedia dalam dua format:
- **Mermaid.js** — Untuk rendering langsung di GitHub (recommended)
- **PlantUML** — Untuk rendering di PlantUML editor

## Daftar Diagram

| No | Diagram | Mermaid | PlantUML | Deskripsi |
|----|---------|---------|----------|-----------|
| 1 | Use Case | `DIAGRAMS.md` | `01-use-case-diagram.puml` | Aktor (Mahasiswa, Admin, Finance) dan fitur sistem |
| 2 | Class | `DIAGRAMS.md` | `02-class-diagram.puml` | Struktur class, model, service, event, policy |
| 3 | Sequence (Registration) | `DIAGRAMS.md` | `03-sequence-diagram.puml` | Alur registrasi & verifikasi pembayaran |
| 4 | Activity | `DIAGRAMS.md` | `04-activity-diagram.puml` | Workflow lengkap dari login sampai penilaian |
| 5 | Sequence (Scoring) | `DIAGRAMS.md` | `05-sequence-scoring-flow.puml` | Alur input nilai oleh Admin/Finance |
| 6 | Component | `DIAGRAMS.md` | `06-component-diagram.puml` | Arsitektur 4-layer (Presentation → Application → Domain → Infrastructure) |
| 7 | Data Flow | `DIAGRAMS.md` | `07-data-flow-diagram.puml` | Alir data antar proses dan data store |

## Cara Render

### Mermaid.js (Recommended untuk GitHub)

**Di GitHub:**
- Diagram Mermaid otomatis di-render di markdown files
- Tidak perlu additional tools
- Cukup gunakan code block ` ```mermaid `

**Di VS Code:**
1. Install extension "Markdown Preview Mermaid Support"
2. Buka README.md atau DIAGRAMS.md
3. Press `Ctrl+Shift+V` untuk preview

**Online:**
1. Buka https://mermaid.live
2. Copy isi code block ` ```mermaid `
3. Paste ke editor

### PlantUML

**Online:**
1. Buka https://www.plantuml.com/plantuml/uml
2. Copy isi file `.puml`
3. Paste ke editor online
4. Diagram akan otomatis di-render

**VS Code:**
1. Install extension "PlantUML" by jebbs
2. Buka file `.puml`
3. Press `Alt + D` untuk preview

**Command Line:**
```bash
# Install PlantUML (butuh Java)
java -jar plantuml.jar *.puml

# Output akan generate file .png di direktori yang sama
```

## Arsitektur Sistem

```
┌─────────────────────────────────────────────────────────┐
│                  Presentation Layer                      │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  │
│  │ Filament Admin│  │ Filament     │  │ Mahasiswa    │  │
│  │ Panel         │  │ Scoring      │  │ Web Routes   │  │
│  └──────────────┘  └──────────────┘  └──────────────┘  │
└─────────────────────────────────────────────────────────┘
                           │
┌─────────────────────────────────────────────────────────┐
│                  Application Layer                       │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  │
│  │ Controllers   │  │ Filament     │  │ Policies     │  │
│  │               │  │ Resources    │  │              │  │
│  └──────────────┘  └──────────────┘  └──────────────┘  │
└─────────────────────────────────────────────────────────┘
                           │
┌─────────────────────────────────────────────────────────┐
│                    Domain Layer                          │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  │
│  │ Registration  │  │ Payment      │  │ Scoring      │  │
│  │ Service       │  │ Verification │  │ Service      │  │
│  └──────────────┘  └──────────────┘  └──────────────┘  │
└─────────────────────────────────────────────────────────┘
                           │
┌─────────────────────────────────────────────────────────┐
│                Infrastructure Layer                      │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  │
│  │ Eloquent      │  │ Database     │  │ Email        │  │
│  │ Models        │  │ Migrations   │  │ Notifications│  │
│  └──────────────┘  └──────────────┘  └──────────────┘  │
└─────────────────────────────────────────────────────────┘
```

## Flow Utama

### 1. Registration Flow
```
Mahasiswa → Browse Schedules → Select Schedule → Register → Upload Payment → Await Verification
```

### 2. Payment Verification Flow
```
Admin/Finance → View Registration → Review Payment Proof → Verify/Reject → Email Notification
```

### 3. Scoring Flow
```
Admin/Finance → Select Registration → Input Scores → Calculate Average → Save → Email Notification
```

## Key Models

| Model | Relationships | Key Fields |
|-------|--------------|------------|
| User | hasMany Registration | name, email, role, nim, phone, major, faculty |
| Registration | belongsTo User, belongsTo ExamSchedule | status, scores, payment_proof, unique_code |
| ExamSchedule | hasMany Registration | title, session, exam_date, quota, price |

## Status Flow

```
PENDING_PAYMENT → AWAITING_VERIFICATION → VERIFIED
        ↓                  ↓
    EXPIRED              REJECTED
                            ↓
                    AWAITING_VERIFICATION (re-upload)
```

## File Structure

```
docs/uml/
├── DIAGRAMS.md              # Mermaid diagrams (recommended)
├── 01-use-case-diagram.puml # PlantUML files (backup)
├── 02-class-diagram.puml
├── 03-sequence-diagram.puml
├── 04-activity-diagram.puml
├── 05-sequence-scoring-flow.puml
├── 06-component-diagram.puml
├── 07-data-flow-diagram.puml
└── README.md
```

## Author

EPT Registration System - UCTA Pradema
