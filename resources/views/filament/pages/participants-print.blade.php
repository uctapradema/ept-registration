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
            font-size: 11px;
            padding: 15px;
            background: white;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #000;
        }
        .header h1 {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 10px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: left;
            font-size: 10px;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .font-mono { font-family: monospace; }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>DAFTAR PESERTA UJIAN ENGLISH PROFICIENCY TEST (EPT)</h1>
        <p>Tanggal Cetak: {{ now()->format('d F Y') }}</p>
    </div>

    @if($registrations->count() > 0)
        <table>
            <thead>
                <tr>
                    <th class="text-center" style="width: 30px;">No</th>
                    <th>No. Daftar</th>
                    <th>Nama Peserta</th>
                    <th>NIM</th>
                    <th>Program Studi</th>
                    <th>Fakultas</th>
                    <th>Jadwal</th>
                    <th>Tanggal</th>
                    <th>Tgl Verifikasi</th>
                </tr>
            </thead>
            <tbody>
                @php $no = 1; @endphp
                @foreach($registrations as $reg)
                    <tr>
                        <td class="text-center">{{ $no++ }}</td>
                        <td class="font-mono">{{ $reg->registration_number }}</td>
                        <td class="font-bold">{{ $reg->user->name ?? '-' }}</td>
                        <td>{{ $reg->user->nim ?? '-' }}</td>
                        <td>{{ $reg->user->major ?? '-' }}</td>
                        <td>{{ $reg->user->faculty ?? '-' }}</td>
                        <td>{{ $reg->examSchedule->title ?? '-' }}</td>
                        <td>{{ $reg->examSchedule->exam_date ? $reg->examSchedule->exam_date->format('d F Y') : '-' }}</td>
                        <td>{{ $reg->payment_verified_at ? $reg->payment_verified_at->format('d F Y, H:i') : '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin-top: 15px; padding: 8px; background-color: #f0f0f0; border: 1px solid #000;">
            <strong>Total Peserta:</strong> {{ $registrations->count() }} Orang
        </div>
    @else
        <p style="text-align: center; padding: 20px;">Tidak ada peserta yang diverifikasi.</p>
    @endif

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
