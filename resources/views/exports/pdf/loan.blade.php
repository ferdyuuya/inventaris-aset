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
        <h3>Ringkasan Status Peminjaman</h3>
        <table style="width: 100%;">
            <tr>
                <td style="text-align: center; width: 25%; padding: 10px;">
                    <div style="font-size: 20px; font-weight: bold; color: #2563eb;">{{ $loans->count() }}</div>
                    <div style="font-size: 9px; color: #64748b; text-transform: uppercase;">Total Peminjaman</div>
                </td>
                <td style="text-align: center; width: 25%; padding: 10px;">
                    <div style="font-size: 20px; font-weight: bold; color: #f59e0b;">{{ $loans->where('status', 'dipinjam')->count() }}</div>
                    <div style="font-size: 9px; color: #64748b; text-transform: uppercase;">Sedang Dipinjam</div>
                </td>
                <td style="text-align: center; width: 25%; padding: 10px;">
                    <div style="font-size: 20px; font-weight: bold; color: #10b981;">{{ $loans->where('status', 'selesai')->count() }}</div>
                    <div style="font-size: 9px; color: #64748b; text-transform: uppercase;">Selesai</div>
                </td>
                <td style="text-align: center; width: 25%; padding: 10px;">
                    <div style="font-size: 20px; font-weight: bold; color: #ef4444;">{{ $loans->where('status', 'hilang')->count() }}</div>
                    <div style="font-size: 9px; color: #64748b; text-transform: uppercase;">Hilang</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Data Table -->
    @if($loans->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 30px;">No</th>
                    <th style="width: 100px;">Kode Aset</th>
                    <th style="width: 130px;">Nama Aset</th>
                    <th style="width: 100px;">Peminjam</th>
                    <th style="width: 75px;">Tgl Pinjam</th>
                    <th style="width: 75px;">Tgl Kembali</th>
                    <th style="width: 60px;">Status</th>
                    <th style="width: 70px;">Kondisi Kembali</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($loans as $index => $loan)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $loan->asset?->asset_code ?? '-' }}</td>
                        <td>{{ $loan->asset?->name ?? '-' }}</td>
                        <td>{{ $loan->borrower?->name ?? '-' }}</td>
                        <td class="text-center">{{ $loan->loan_date?->format('d/m/Y') ?? '-' }}</td>
                        <td class="text-center">
                            @if($loan->return_date)
                                {{ $loan->return_date->format('d/m/Y') }}
                            @else
                                <span style="color: #9ca3af;">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @switch($loan->status)
                                @case('dipinjam')
                                    <span class="badge badge-warning">Dipinjam</span>
                                    @break
                                @case('selesai')
                                    <span class="badge badge-success">Selesai</span>
                                    @break
                                @case('hilang')
                                    <span class="badge badge-danger">Hilang</span>
                                    @break
                                @default
                                    <span class="badge badge-secondary">{{ ucfirst($loan->status ?? '-') }}</span>
                            @endswitch
                        </td>
                        <td class="text-center">
                            @if($loan->condition_after_return)
                                @switch($loan->condition_after_return)
                                    @case('baik')
                                        <span class="badge badge-success">Baik</span>
                                        @break
                                    @case('rusak')
                                        <span class="badge badge-danger">Rusak</span>
                                        @break
                                    @default
                                        <span class="badge badge-secondary">{{ ucfirst($loan->condition_after_return) }}</span>
                                @endswitch
                            @else
                                <span style="color: #9ca3af;">-</span>
                            @endif
                        </td>
                        <td>{{ \Illuminate\Support\Str::limit($loan->notes ?? '-', 60) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-data">
            <p>Tidak ada data peminjaman untuk filter yang dipilih.</p>
        </div>
    @endif
@endsection
