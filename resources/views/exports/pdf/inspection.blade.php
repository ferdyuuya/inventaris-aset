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
            @if($conditionAfter)
                | Kondisi Setelah: {{ ucfirst(str_replace('_', ' ', $conditionAfter)) }}
            @endif
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="summary">
        <h3>Ringkasan Kondisi</h3>
        <table style="width: 100%;">
            <tr>
                <td style="text-align: center; width: 25%; padding: 10px;">
                    <div style="font-size: 20px; font-weight: bold; color: #2563eb;">{{ $inspections->count() }}</div>
                    <div style="font-size: 9px; color: #64748b; text-transform: uppercase;">Total Inspeksi</div>
                </td>
                <td style="text-align: center; width: 25%; padding: 10px;">
                    <div style="font-size: 20px; font-weight: bold; color: #10b981;">{{ $inspections->where('condition_after', 'baik')->count() }}</div>
                    <div style="font-size: 9px; color: #64748b; text-transform: uppercase;">Kondisi Baik</div>
                </td>
                <td style="text-align: center; width: 25%; padding: 10px;">
                    <div style="font-size: 20px; font-weight: bold; color: #f59e0b;">{{ $inspections->where('condition_after', 'perlu_perbaikan')->count() }}</div>
                    <div style="font-size: 9px; color: #64748b; text-transform: uppercase;">Perlu Perbaikan</div>
                </td>
                <td style="text-align: center; width: 25%; padding: 10px;">
                    <div style="font-size: 20px; font-weight: bold; color: #ef4444;">{{ $inspections->where('condition_after', 'rusak')->count() }}</div>
                    <div style="font-size: 9px; color: #64748b; text-transform: uppercase;">Rusak</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Data Table -->
    @if($inspections->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 30px;">No</th>
                    <th style="width: 100px;">Kode Aset</th>
                    <th style="width: 150px;">Nama Aset</th>
                    <th style="width: 90px;">Tanggal Inspeksi</th>
                    <th style="width: 100px;">Inspektor</th>
                    <th style="width: 80px;">Kondisi Sebelum</th>
                    <th style="width: 80px;">Kondisi Sesudah</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($inspections as $index => $inspection)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $inspection->asset?->asset_code ?? '-' }}</td>
                        <td>{{ $inspection->asset?->name ?? '-' }}</td>
                        <td class="text-center">{{ $inspection->inspected_at?->format('d/m/Y') ?? '-' }}</td>
                        <td>{{ $inspection->inspector?->name ?? '-' }}</td>
                        <td class="text-center">
                            @switch($inspection->condition_before)
                                @case('baik')
                                    <span class="badge badge-success">Baik</span>
                                    @break
                                @case('perlu_perbaikan')
                                    <span class="badge badge-warning">Perlu Perbaikan</span>
                                    @break
                                @case('rusak')
                                    <span class="badge badge-danger">Rusak</span>
                                    @break
                                @default
                                    <span class="badge badge-secondary">{{ ucfirst(str_replace('_', ' ', $inspection->condition_before ?? '-')) }}</span>
                            @endswitch
                        </td>
                        <td class="text-center">
                            @switch($inspection->condition_after)
                                @case('baik')
                                    <span class="badge badge-success">Baik</span>
                                    @break
                                @case('perlu_perbaikan')
                                    <span class="badge badge-warning">Perlu Perbaikan</span>
                                    @break
                                @case('rusak')
                                    <span class="badge badge-danger">Rusak</span>
                                    @break
                                @default
                                    <span class="badge badge-secondary">{{ ucfirst(str_replace('_', ' ', $inspection->condition_after ?? '-')) }}</span>
                            @endswitch
                        </td>
                        <td>{{ \Illuminate\Support\Str::limit($inspection->notes ?? '-', 80) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-data">
            <p>Tidak ada data inspeksi untuk filter yang dipilih.</p>
        </div>
    @endif
@endsection
