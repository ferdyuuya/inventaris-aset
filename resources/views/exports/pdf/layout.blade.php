<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $title ?? 'Laporan' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
        }

        .container {
            padding: 20px;
        }

        /* Header Styles */
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #333;
        }

        .header h1 {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .header h2 {
            font-size: 14px;
            font-weight: normal;
            margin-bottom: 5px;
        }

        .header .subtitle {
            font-size: 11px;
            color: #666;
        }

        .header .company-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #2563eb;
        }

        /* Meta Information */
        .meta-info {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f9fafb;
            border-radius: 5px;
        }

        .meta-info table {
            width: 100%;
        }

        .meta-info td {
            padding: 3px 10px;
            font-size: 10px;
        }

        .meta-info .label {
            font-weight: bold;
            width: 150px;
        }

        /* Table Styles */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .data-table thead th {
            background-color: #2563eb;
            color: white;
            padding: 8px 5px;
            text-align: left;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
            border: 1px solid #1d4ed8;
        }

        .data-table tbody td {
            padding: 6px 5px;
            border: 1px solid #e5e7eb;
            font-size: 9px;
            vertical-align: top;
        }

        .data-table tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }

        .data-table tbody tr:hover {
            background-color: #f3f4f6;
        }

        /* Text alignment classes */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }

        /* Status badges */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-success { background-color: #dcfce7; color: #166534; }
        .badge-warning { background-color: #fef3c7; color: #92400e; }
        .badge-danger { background-color: #fee2e2; color: #991b1b; }
        .badge-info { background-color: #dbeafe; color: #1e40af; }
        .badge-secondary { background-color: #f3f4f6; color: #374151; }

        /* Summary section */
        .summary {
            margin-top: 20px;
            padding: 15px;
            background-color: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 5px;
        }

        .summary h3 {
            font-size: 12px;
            margin-bottom: 10px;
            color: #0369a1;
        }

        .summary-grid {
            display: table;
            width: 100%;
        }

        .summary-item {
            display: table-cell;
            text-align: center;
            padding: 10px;
        }

        .summary-item .value {
            font-size: 18px;
            font-weight: bold;
            color: #2563eb;
        }

        .summary-item .label {
            font-size: 9px;
            color: #64748b;
            text-transform: uppercase;
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 10px 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 8px;
            color: #6b7280;
        }

        .footer table {
            width: 100%;
        }

        .page-number:after {
            content: counter(page);
        }

        /* Group header */
        .group-header {
            background-color: #e0e7ff;
            padding: 8px 10px;
            margin-top: 15px;
            margin-bottom: 5px;
            font-weight: bold;
            font-size: 11px;
            color: #3730a3;
            border-left: 4px solid #6366f1;
        }

        /* Money formatting */
        .money {
            font-family: 'DejaVu Sans Mono', monospace;
            text-align: right;
        }

        /* No data message */
        .no-data {
            text-align: center;
            padding: 40px;
            color: #6b7280;
            font-style: italic;
        }

        /* Page break */
        .page-break {
            page-break-after: always;
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="container">
        @yield('content')
    </div>

    <div class="footer">
        <table>
            <tr>
                <td style="text-align: left;">
                    Sistem Inventaris Aset
                </td>
                <td style="text-align: center;">
                    Dicetak: {{ $generatedAt->format('d/m/Y H:i:s') }}
                </td>
                <td style="text-align: right;">
                    Halaman <span class="page-number"></span>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
