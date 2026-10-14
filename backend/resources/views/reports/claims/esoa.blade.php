<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page {
            margin: 20px 26px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #1a1a1a;
        }

        .box {
            border: 1.5px solid #1a1a1a;
            padding: 10px 14px;
            margin-bottom: 12px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .header h1 {
            font-size: 15px;
            margin: 0;
            text-transform: uppercase;
        }

        .header .meta {
            text-align: right;
            font-size: 9px;
        }

        .header .meta .label {
            color: #666;
        }

        .member-info {
            display: flex;
            justify-content: space-between;
            margin-top: 8px;
            font-size: 9.5px;
        }

        .member-info div {
            line-height: 1.5;
        }

        .member-info .label {
            color: #666;
            display: inline-block;
            width: 90px;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }

        table.items thead th {
            border-bottom: 1.5px solid #1a1a1a;
            text-align: left;
            font-size: 8.5px;
            text-transform: uppercase;
            padding: 5px 6px;
        }

        table.items tbody td {
            padding: 4px 6px;
            border-bottom: 0.5px solid #ccc;
            page-break-inside: avoid;
        }

        table.items tbody tr {
            page-break-inside: avoid;
        }

        .text-right {
            text-align: right;
        }

        .totals {
            width: 260px;
            margin-left: auto;
            margin-top: 10px;
        }

        .totals table {
            width: 100%;
            border-collapse: collapse;
        }

        .totals td {
            padding: 4px 6px;
            font-size: 10px;
        }

        .totals .grand-total td {
            border-top: 1.5px solid #1a1a1a;
            font-weight: bold;
            font-size: 11px;
        }

        .footer {
            margin-top: 20px;
            font-size: 8px;
            color: #888;
            text-align: center;
        }
    </style>
</head>

<body>

    <div class="box">
        <div class="header">
            <h1>Statement of Account</h1>
            <div class="meta">
                <div><span class="label">SOA No.:</span> {{ $soa_number ?? 'SOA-' . now()->format('Ymd-His') }}</div>
                <div><span class="label">Date:</span> {{ $generated_at }}</div>
            </div>
        </div>

        <div class="member-info">
            <div>
                <div><span class="label">Member/Patient:</span> {{ $member_name ?? $title }}</div>
                <div><span class="label">Member No.:</span> {{ $member_no ?? '—' }}</div>
            </div>
            <div>
                <div><span class="label">Billing Period:</span> {{ $billing_period ?? '—' }}</div>
                <div><span class="label">Status:</span> {{ $status ?? 'Unpaid' }}</div>
            </div>
        </div>
    </div>

    @if (count($rows) > 0)
        <table class="items">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Description</th>
                    <th class="text-right">Charges</th>
                    <th class="text-right">Payments</th>
                    <th class="text-right">Balance</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    @php $row = (array) $row; @endphp
                    <tr>
                        <td>{{ $row['date'] ?? '' }}</td>
                        <td>{{ $row['description'] ?? '' }}</td>
                        <td class="text-right">{{ number_format((float) ($row['charges'] ?? 0), 2) }}</td>
                        <td class="text-right">{{ number_format((float) ($row['payments'] ?? 0), 2) }}</td>
                        <td class="text-right">{{ number_format((float) ($row['balance'] ?? 0), 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <table>
                <tr>
                    <td>Total Charges</td>
                    <td class="text-right">
                        {{ number_format(collect($rows)->sum(fn($r) => (float) (((array) $r)['charges'] ?? 0)), 2) }}
                    </td>
                </tr>
                <tr>
                    <td>Total Payments</td>
                    <td class="text-right">
                        {{ number_format(collect($rows)->sum(fn($r) => (float) (((array) $r)['payments'] ?? 0)), 2) }}
                    </td>
                </tr>
                <tr class="grand-total">
                    <td>Amount Due</td>
                    <td class="text-right">
                        {{ number_format((float) (((array) (collect($rows)->last() ?? []))['balance'] ?? 0), 2) }}
                    </td>
                </tr>
            </table>
        </div>
    @else
        <p style="text-align:center; color:#888; font-style: italic; padding: 20px 0;">No line items on this statement.
        </p>
    @endif

    <div class="footer">
        This is a system-generated Statement of Account. Generated {{ $generated_at }}.
    </div>

</body>

</html>
