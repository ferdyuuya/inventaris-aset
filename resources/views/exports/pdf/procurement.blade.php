@extends('exports.pdf.layout')

@section('content')
    <!-- Header -->
    <div class="header">
        <div class="company-name">SISTEM INVENTARIS ASET</div>
        <h1>{{ $title }}</h1>
        <div class="subtitle">
            Total: {{ $procurements->count() }} Pengadaan | {{ $totalQuantity }} Unit | Rp {{ number_format($totalCost, 0, ',', '.') }}
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="summary">
        <h3>Ringkasan Pengadaan Tahun {{ $year }}</h3>
        <table style="width: 100%;">
            <tr>
                <td style="text-align: center; width: 33%; padding: 10px;">
                    <div style="font-size: 20px; font-weight: bold; color: #2563eb;">{{ $procurements->count() }}</div>
                    <div style="font-size: 9px; color: #64748b; text-transform: uppercase;">Total Transaksi</div>
                </td>
                <td style="text-align: center; width: 33%; padding: 10px;">
                    <div style="font-size: 20px; font-weight: bold; color: #10b981;">{{ number_format($totalQuantity, 0, ',', '.') }}</div>
                    <div style="font-size: 9px; color: #64748b; text-transform: uppercase;">Total Unit</div>
                </td>
                <td style="text-align: center; width: 34%; padding: 10px;">
                    <div style="font-size: 16px; font-weight: bold; color: #7c3aed;">Rp {{ number_format($totalCost, 0, ',', '.') }}</div>
                    <div style="font-size: 9px; color: #64748b; text-transform: uppercase;">Total Biaya</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Data Grouped by Month -->
    @if($procurements->count() > 0)
        @foreach($groupedByMonth as $month => $monthProcurements)
            <div class="group-header">
                {{ $month }} {{ $year }} ({{ $monthProcurements->count() }} transaksi)
            </div>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 25px;">No</th>
                        <th style="width: 70px;">Tanggal</th>
                        <th style="width: 140px;">Nama Barang</th>
                        <th style="width: 80px;">Kategori</th>
                        <th style="width: 100px;">Supplier</th>
                        <th style="width: 45px;">Qty</th>
                        <th style="width: 80px;">Harga Satuan</th>
                        <th style="width: 90px;">Total</th>
                        <th>Kode Aset</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($monthProcurements as $index => $procurement)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="text-center">{{ $procurement->procurement_date?->format('d/m/Y') ?? '-' }}</td>
                            <td>{{ $procurement->name ?? '-' }}</td>
                            <td>{{ $procurement->category?->name ?? '-' }}</td>
                            <td>{{ $procurement->supplier?->name ?? '-' }}</td>
                            <td class="text-center">{{ $procurement->quantity ?? 0 }}</td>
                            <td class="money">{{ number_format($procurement->unit_price ?? 0, 0, ',', '.') }}</td>
                            <td class="money">{{ number_format($procurement->total_cost ?? 0, 0, ',', '.') }}</td>
                            <td style="font-size: 8px;">
                                @if($procurement->assets && $procurement->assets->count() > 0)
                                    {{ $procurement->assets->pluck('asset_code')->join(', ') }}
                                @else
                                    <span style="color: #9ca3af;">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    <!-- Month Subtotal -->
                    <tr style="background-color: #f3f4f6; font-weight: bold;">
                        <td colspan="5" class="text-right">Subtotal {{ $month }}:</td>
                        <td class="text-center">{{ $monthProcurements->sum('quantity') }}</td>
                        <td></td>
                        <td class="money">{{ number_format($monthProcurements->sum('total_cost'), 0, ',', '.') }}</td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        @endforeach

        <!-- Grand Total -->
        <div style="margin-top: 20px; padding: 15px; background-color: #1e40af; color: white; border-radius: 5px;">
            <table style="width: 100%;">
                <tr>
                    <td style="font-size: 12px; font-weight: bold;">GRAND TOTAL TAHUN {{ $year }}</td>
                    <td style="text-align: right; font-size: 12px;">
                        {{ number_format($totalQuantity, 0, ',', '.') }} Unit
                    </td>
                    <td style="text-align: right; font-size: 14px; font-weight: bold;">
                        Rp {{ number_format($totalCost, 0, ',', '.') }}
                    </td>
                </tr>
            </table>
        </div>
    @else
        <div class="no-data">
            <p>Tidak ada data pengadaan untuk tahun {{ $year }}.</p>
        </div>
    @endif
@endsection
