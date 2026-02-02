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
                | Status: {{ ucfirst($status) }}
            @endif
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="summary">
        <h3>Ringkasan</h3>
        <table style="width: 100%;">
            <tr>
                <td style="text-align: center; width: 25%; padding: 10px;">
                    <div style="font-size: 20px; font-weight: bold; color: #2563eb;">{{ $requests->count() }}</div>
                    <div style="font-size: 9px; color: #64748b; text-transform: uppercase;">Total Request</div>
                </td>
                <td style="text-align: center; width: 25%; padding: 10px;">
                    <div style="font-size: 20px; font-weight: bold; color: #f59e0b;">{{ $requests->where('status', 'pending')->count() }}</div>
                    <div style="font-size: 9px; color: #64748b; text-transform: uppercase;">Pending</div>
                </td>
                <td style="text-align: center; width: 25%; padding: 10px;">
                    <div style="font-size: 20px; font-weight: bold; color: #10b981;">{{ $requests->where('status', 'approved')->count() }}</div>
                    <div style="font-size: 9px; color: #64748b; text-transform: uppercase;">Approved</div>
                </td>
                <td style="text-align: center; width: 25%; padding: 10px;">
                    <div style="font-size: 20px; font-weight: bold; color: #ef4444;">{{ $requests->where('status', 'rejected')->count() }}</div>
                    <div style="font-size: 9px; color: #64748b; text-transform: uppercase;">Rejected</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Data Table -->
    @if($requests->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 30px;">No</th>
                    <th style="width: 100px;">Kode Aset</th>
                    <th style="width: 150px;">Nama Aset</th>
                    <th style="width: 90px;">Tanggal Request</th>
                    <th style="width: 100px;">Pemohon</th>
                    <th style="width: 70px;">Status</th>
                    <th style="width: 100px;">Approver</th>
                    <th>Deskripsi Masalah</th>
                </tr>
            </thead>
            <tbody>
                @foreach($requests as $index => $request)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $request->asset?->asset_code ?? '-' }}</td>
                        <td>{{ $request->asset?->name ?? '-' }}</td>
                        <td class="text-center">{{ $request->created_at?->format('d/m/Y') ?? '-' }}</td>
                        <td>{{ $request->requester?->name ?? '-' }}</td>
                        <td class="text-center">
                            @switch($request->status)
                                @case('approved')
                                    <span class="badge badge-success">Approved</span>
                                    @break
                                @case('pending')
                                    <span class="badge badge-warning">Pending</span>
                                    @break
                                @case('rejected')
                                    <span class="badge badge-danger">Rejected</span>
                                    @break
                                @default
                                    <span class="badge badge-secondary">{{ ucfirst($request->status ?? '-') }}</span>
                            @endswitch
                        </td>
                        <td>{{ $request->approver?->name ?? '-' }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($request->issue_description ?? '-', 60) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-data">
            <p>Tidak ada data maintenance request untuk filter yang dipilih.</p>
        </div>
    @endif
@endsection
