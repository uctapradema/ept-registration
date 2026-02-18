<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Kartu Ujian EPT</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 11px; padding: 0; }
        .card { 
            width: 210mm; height: 148mm;
            border: 1px solid #000;
            margin: 0 auto;
            padding: 6mm;
            position: relative;
        }
        .header { text-align: center; margin-bottom: 4mm; }
        .header h1 { font-size: 16px; font-weight: bold; margin-bottom: 1mm; }
        .header p { font-size: 9px; color: #666; }
        .reg-no { 
            background: #f0f0f0; 
            padding: 2mm; text-align: center; 
            border-radius: 2mm; margin-bottom: 4mm;
            font-weight: bold; font-size: 11px;
        }
        
        .cols { display: flex; gap: 4mm; }
        .col { flex: 1; }
        
        .row { margin-bottom: 3mm; }
        .label { font-weight: bold; font-size: 9px; margin-bottom: 0.5mm; }
        .value { font-size: 10px; }
        
        .status-box { 
            background: #10b981; color: white; 
            padding: 2mm; text-align: center; 
            border-radius: 2mm; font-weight: bold; font-size: 10px;
        }
        
        .footer { 
            position: absolute; bottom: 6mm; left: 6mm; right: 6mm;
            text-align: center; font-size: 8px; color: #666;
            border-top: 1px dashed #ccc; padding-top: 2mm;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h1>KARTU UJIAN EPT</h1>
            <p>English Proficiency Test</p>
        </div>
        
        <div class="reg-no">
            {{ $registration->registration_number }}
        </div>
        
        <div class="cols">
            <div class="col">
                <div class="row">
                    <div class="label">Nama</div>
                    <div class="value">{{ $registration->user->name }}</div>
                </div>
                <div class="row">
                    <div class="label">NIM</div>
                    <div class="value">{{ $registration->user->nim ?? '-' }}</div>
                </div>
                <div class="row">
                    <div class="label">Prodi</div>
                    <div class="value">{{ $registration->user->major ?? '-' }}</div>
                </div>
                <div class="row">
                    <div class="label">Fakultas</div>
                    <div class="value">{{ $registration->user->faculty ?? '-' }}</div>
                </div>
            </div>
            <div class="col">
                <div class="row">
                    <div class="label">Jadwal</div>
                    <div class="value">{{ $registration->examSchedule->title ?? '-' }}</div>
                </div>
                <div class="row">
                    <div class="label">Tanggal</div>
                    <div class="value">{{ $registration->examSchedule && $registration->examSchedule->exam_date ? \Carbon\Carbon::parse($registration->examSchedule->exam_date)->format('d/m/Y') : '-' }}</div>
                </div>
                <div class="row">
                    <div class="label">Sesi</div>
                    <div class="value">{{ $registration->examSchedule->session ?? '-' }}</div>
                </div>
                <div class="row">
                    <div class="label">Ruang</div>
                    <div class="value">{{ $registration->examSchedule->location ?? '-' }}</div>
                </div>
                <div class="row">
                    <div class="label">Status</div>
                    <div class="status-box">TERVERIFIKASI</div>
                </div>
            </div>
        </div>
        
        <div class="footer">
            <strong>Wajib dibawa saat ujian</strong> | Dicetak: {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>
</body>
</html>
