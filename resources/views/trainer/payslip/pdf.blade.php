<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1f2937; background: white; }
    .page { padding: 32px; max-width: 700px; margin: 0 auto; }

    /* Header / KOP */
    .kop { display: flex; align-items: center; gap: 16px; border-bottom: 2px solid #0d59f2; padding-bottom: 16px; margin-bottom: 20px; }
    .kop-logo { width: 52px; height: 52px; background: #0d59f2; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-size: 22px; font-weight: 700; }
    .kop-title { font-size: 18px; font-weight: 700; color: #0d59f2; }
    .kop-sub { font-size: 10px; color: #6b7280; margin-top: 2px; }

    /* Slip header box */
    .slip-header { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 14px 18px; margin-bottom: 20px; display: flex; justify-content: space-between; }
    .slip-header .label { font-size: 10px; color: #6b7280; margin-bottom: 2px; }
    .slip-header .value { font-weight: 600; font-size: 13px; }

    /* Tables */
    table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
    th { background: #f3f4f6; text-align: left; padding: 8px 12px; font-size: 10px; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; }
    td { padding: 7px 12px; border-bottom: 1px solid #f3f4f6; }
    tr:last-child td { border-bottom: none; }
    .amount { text-align: right; font-variant-numeric: tabular-nums; }

    /* Summary box */
    .summary { border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; margin-top: 20px; }
    .summary-row { display: flex; justify-content: space-between; padding: 8px 16px; border-bottom: 1px solid #f3f4f6; }
    .summary-row:last-child { border-bottom: none; }
    .summary-net { background: #0d59f2; color: white; font-size: 14px; font-weight: 700; padding: 12px 16px; display: flex; justify-content: space-between; }

    .badge-green { background: #dcfce7; color: #166534; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: 600; }
    .badge-red   { background: #fee2e2; color: #991b1b; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: 600; }

    /* Footer */
    .footer { margin-top: 32px; border-top: 1px dashed #e5e7eb; padding-top: 16px; text-align: center; font-size: 10px; color: #9ca3af; }
</style>
</head>
<body>
<div class="page">

    {{-- KOP --}}
    <div class="kop">
        <div class="kop-logo">K</div>
        <div>
            <div class="kop-title">Kampung Inggris Pontianak</div>
            <div class="kop-sub">Lembaga Kursus Bahasa Inggris · Pontianak, Kalimantan Barat</div>
        </div>
    </div>

    <div style="text-align:center; margin-bottom:20px;">
        <h2 style="font-size:15px; font-weight:700; letter-spacing:0.1em; color:#1f2937;">SLIP GAJI</h2>
        <p style="font-size:11px; color:#6b7280;">
            Periode: {{ \Carbon\Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y') }}
        </p>
    </div>

    {{-- Info Trainer --}}
    <div class="slip-header">
        <div>
            <div class="label">Nama Trainer</div>
            <div class="value">{{ $trainer->name }}</div>
        </div>
        <div>
            <div class="label">Kode Pegawai</div>
            <div class="value">{{ $trainer->employee_code ?? '-' }}</div>
        </div>
        <div>
            <div class="label">Posisi</div>
            <div class="value">{{ $trainer->role }}</div>
        </div>
        <div>
            <div class="label">Tanggal Terbit</div>
            <div class="value">{{ now()->translatedFormat('d M Y') }}</div>
        </div>
    </div>

    {{-- Rincian Harian --}}
    <p style="font-weight:600; font-size:11px; margin-bottom:8px;">Rincian Kelas Harian</p>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Murid Hadir</th>
                <th>Murid Cover</th>
                <th>Total</th>
                <th>Tier</th>
                <th style="text-align:right">Rate (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payslipData['daily_breakdown'] as $day)
            <tr>
                <td>{{ \Carbon\Carbon::parse($day['date'])->translatedFormat('d M') }}</td>
                <td>{{ $day['own_students'] }}</td>
                <td>{{ $day['sub_students'] }}</td>
                <td><strong>{{ $day['total'] }}</strong></td>
                <td>{{ $day['tier'] }}</td>
                <td class="amount">{{ number_format($day['rate'], 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align:center;color:#9ca3af;padding:20px">Tidak ada kelas di bulan ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- Insentif & Deduksi --}}
    @if($payslipData['transactions']->isNotEmpty())
    <p style="font-weight:600; font-size:11px; margin-bottom:8px;">Insentif & Deduksi</p>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Tipe</th>
                <th>Kategori</th>
                <th>Catatan</th>
                <th style="text-align:right">Jumlah (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payslipData['transactions'] as $tx)
            <tr>
                <td>{{ $tx->date->translatedFormat('d M Y') }}</td>
                <td>
                    @if($tx->type === 'Incentive')
                        <span class="badge-green">+ Insentif</span>
                    @else
                        <span class="badge-red">− Deduksi</span>
                    @endif
                </td>
                <td>{{ $tx->category }}</td>
                <td style="color:#6b7280">{{ $tx->notes ?? '-' }}</td>
                <td class="amount">{{ number_format($tx->amount, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- Summary --}}
    <div class="summary">
        <div class="summary-row">
            <span style="color:#6b7280">Total Base Earnings</span>
            <span>Rp {{ number_format($payslipData['base_earnings'], 0, ',', '.') }}</span>
        </div>
        <div class="summary-row">
            <span style="color:#16a34a">+ Total Insentif</span>
            <span style="color:#16a34a">Rp {{ number_format($payslipData['total_incentives'], 0, ',', '.') }}</span>
        </div>
        <div class="summary-row">
            <span style="color:#dc2626">− Total Deduksi</span>
            <span style="color:#dc2626">Rp {{ number_format($payslipData['total_deductions'], 0, ',', '.') }}</span>
        </div>
        <div class="summary-net">
            <span>NET TAKE-HOME</span>
            <span>Rp {{ number_format($payslipData['net_take_home'], 0, ',', '.') }}</span>
        </div>
    </div>

    {{-- TTD --}}
    <div style="display:flex; justify-content:flex-end; margin-top:32px;">
        <div style="text-align:center;">
            <p style="font-size:10px; color:#6b7280;">Pontianak, {{ now()->translatedFormat('d F Y') }}</p>
            <p style="font-size:10px; color:#6b7280; margin-bottom:48px;">Management KIP</p>
            <div style="border-top:1px solid #374151; padding-top:4px;">
                <p style="font-size:10px; font-weight:600;">(_____________________)</p>
            </div>
        </div>
    </div>

    <div class="footer">
        Dokumen ini digenerate otomatis oleh KIP Portal · {{ now()->format('d/m/Y H:i') }} · Konfidensial
    </div>
</div>
</body>
</html>