<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Peserta Ujian EPT</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            font-size: 18px;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 12px;
            color: #666;
        }
        .schedule-block {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        .schedule-title {
            background: #f3f4f6;
            padding: 10px;
            font-weight: bold;
            border: 1px solid #ddd;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background: #f9fafb;
            font-weight: bold;
        }
        .print-btn {
            display: none;
        }
        @media print {
            body {
                padding: 0;
            }
            .print-btn {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="print-btn" style="margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer;">Print / Simpan PDF</button>
    </div>

    <div class="header">
        <h1>DAFTAR PESERTA UJIAN ENGLISH PROFICIENCY TEST (EPT)</h1>
        <p>Tanggal Cetak: {{ now()->format('d F Y') }}</p>
    </div>

    @if($registrations->count() > 0)
        @foreach($registrations as $scheduleId => $scheduleRegistrations)
            @php
                $schedule = $scheduleRegistrations->first()->examSchedule;
            @endphp
            <div class="schedule-block">
                <div class="schedule-title">
                    <strong>{{ $schedule->title }}</strong><br>
                    Tanggal: {{ $schedule->exam_date->format('d F Y') }} | 
                    Sesi: {{ $schedule->session ?? '-' }} ({{ $schedule->start_time->format('H:i') }} - {{ $schedule->end_time->format('H:i') }}) |
                    Total Peserta: {{ $scheduleRegistrations->count() }} orang
                </div>

                <table>
                    <thead>
                        <tr>
                            <th style="width: 40px;">No</th>
                            <th style="width: 150px;">No. Daftar</th>
                            <th>Nama</th>
                            <th style="width: 100px;">NIM</th>
                            <th style="width: 120px;">Prodi</th>
                            <th style="width: 120px;">Fakultas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($scheduleRegistrations as $index => $reg)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $reg->registration_number }}</td>
                                <td>{{ $reg->user->name }}</td>
                                <td>{{ $reg->user->nim ?? '-' }}</td>
                                <td>{{ $reg->user->major ?? '-' }}</td>
                                <td>{{ $reg->user->faculty ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    @else
        <p style="text-align: center; padding: 20px;">Belum ada peserta yang terverifikasi.</p>
    @endif

    <script>
        window.onload = function() {
            // Auto print when page loads
            // window.print();
        }
    </script>
</body>
</html>
