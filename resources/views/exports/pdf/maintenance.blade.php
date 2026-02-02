@extends('exports.pdf.layout')

@section('content')
    <!-- Header -->
    <div class="header">
        <div class="company-name">SISTEM INVENTARIS ASET</div>
        <h1>{{ $title }}</h1>
        <div class="subtitle">
            @if($dateFrom && $dateTo)
                Periode: {{ $dateFrom->format('d/m/Y') }} - {{ $dateTo->format('d/m/Y') }}
            @elseif($dateFrom)
                Dari: {{ $dateFrom->format('d/m/Y') }}
            @elseif($dateTo)
                Sampai: {{ $dateTo->format('d/m/Y') }}
            @else
                Semua Periode
            @endif
            @if($status)
                | Status: {{ ucfirst(str_replace('_', ' ', $status)) }}
            @endif
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="summary">
        <h3>Ringkasan</h3>
        <table style="width: 100%;">
            <tr>
                <td style="text-align: center; width: 25%; padding: 10px;">
                    <div style="font-size: 20px; font-weight: bold; color: #2563eb;">{{ $maintenances->count() }}</div>
                    <div style="font-size: 9px; color: #64748b; text-transform: uppercase;">Total Record</div>
                </td>
                <td style="text-align: center; width: 25%; padding: 10px;">
                    <div style="font-size: 20px; font-weight: bold; color: #f59e0b;">{{ $maintenances->where('status', 'dalam_proses')->count() }}</div>
                    <div style="font-size: 9px; color: #64748b; text-transform: uppercase;">Dalam Proses</div>
                </td>
                <td style="text-align: center; width: 25%; padding: 10px;">
                    <div style="font-size: 20px; font-weight: bold; color: #10b981;">{{ $maintenances->where('status', 'selesai')->count() }}</div>
                    <div style="font-size: 9px; color: #64748b; text-transform: uppercase;">Selesai</div>
                </td>
                <td style="text-align: center; width: 25%; padding: 10px;">
                    <div style="font-size: 20px; font-weight: bold; color: #ef4444;">{{ $maintenances->where('status', 'dibatalkan')->count() }}</div>
                    <div style="font-size: 9px; color: #64748b; text-transform: uppercase;">Dibatalkan</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Data Table -->
    @if($maintenances->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 30px;">No</th>
                    <th style="width: 100px;">Kode Aset</th>
                    <th style="width: 150px;">Nama Aset</th>
                    <th style="width: 80px;">Tanggal</th>
                    <th style="width: 70px;">Jenis</th>
                    <th style="width: 100px;">PIC</th>
                    <th style="width: 70px;">Status</th>
                    <th style="width: 70px;">Hasil</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($maintenances as $index => $maintenance)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $maintenance->asset?->asset_code ?? '-' }}</td>
                        <td>{{ $maintenance->asset?->name ?? '-' }}</td>
                        <td class="text-center">{{ $maintenance->maintenance_date?->format('d/m/Y') ?? '-' }}</td>
                        <td class="text-center">
                            @if($maintenance->maintenance_type === 'preventif')
                                <span class="badge badge-info">Preventif</span>
                            @elseif($maintenance->maintenance_type === 'korektif')
                                <span class="badge badge-warning">Korektif</span>
                            @else
                                {{ ucfirst($maintenance->maintenance_type ?? '-') }}
                            @endif
                        </td>
                        <td>{{ $maintenance->pic?->name ?? '-' }}</td>
                        <td class="text-center">
                            @switch($maintenance->status)
                                @case('selesai')
                                    <span class="badge badge-success">Selesai</span>
                                    @break
                                @case('dalam_proses')
                                    <span class="badge badge-warning">Proses</span>
                                    @break
                                @case('dibatalkan')
                                    <span class="badge badge-danger">Batal</span>
                                    @break
                                @default
                                    <span class="badge badge-secondary">{{ ucfirst($maintenance->status ?? '-') }}</span>
                            @endswitch
                        </td>
                        <td class="text-center">
                            @if($maintenance->result === 'berhasil')
                                <span class="badge badge-success">Berhasil</span>
                            @elseif($maintenance->result === 'gagal')
                                <span class="badge badge-danger">Gagal</span>
                            @elseif($maintenance->result === 'pending')
                                <span class="badge badge-warning">Pending</span>
                            @else
                                {{ $maintenance->result ?? '-' }}
                            @endif
                        </td>
                        <td>{{ \Illuminate\Support\Str::limit($maintenance->feedback ?? '-', 50) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-data">
            <p>Tidak ada data maintenance untuk filter yang dipilih.</p>
        </div>
    @endif
@endsection
