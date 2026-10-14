<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page {
            margin: 24px 28px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1a1a1a;
        }

        .header {
            text-align: center;
            margin-bottom: 16px;
            border-bottom: 2px solid #1a1a1a;
            padding-bottom: 10px;
        }

        .header h1 {
            font-size: 16px;
            margin: 0 0 2px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .header .subtitle {
            font-size: 11px;
            color: #555;
            margin: 0;
        }

        .header .generated {
            font-size: 9px;
            color: #888;
            margin-top: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        thead th {
            background: #1a1a1a;
            color: #fff;
            text-transform: uppercase;
            font-size: 9px;
            padding: 6px 8px;
            text-align: left;
        }

        tbody td {
            padding: 5px 8px;
            border-bottom: 1px solid #ddd;
        }

        tbody tr:nth-child(even) {
            background: #f7f7f7;
        }

        .text-right {
            text-align: right;
        }

        tfoot td {
            padding: 8px;
            font-weight: bold;
            border-top: 2px solid #1a1a1a;
        }

        .empty {
            text-align: center;
            padding: 20px;
            color: #888;
            font-style: italic;
        }

        .footer {
            margin-top: 24px;
            font-size: 8px;
            color: #999;
            text-align: center;
        }
    </style>
</head>

<body>

    <div class="header">
        <h1>Angkong Noodle House</h1>
        <p class="subtitle">{{ $title }}</p>
        <p class="generated">Generated: {{ $generated_at }}</p>
    </div>

    @if (count($rows) > 0)
        @php $columns = array_keys((array) $rows[0]); @endphp

        <table>
            <thead>
                <tr>
                    @foreach ($columns as $column)
                        <th>{{ str_replace('_', ' ', $column) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    <tr>
                        @foreach ($columns as $column)
                            @php $value = is_array($row) ? ($row[$column] ?? '') : ($row->$column ?? ''); @endphp
                            <td class="{{ is_numeric($value) ? 'text-right' : '' }}">
                                {{ is_numeric($value) ? number_format((float) $value, 2) : $value }}
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
            @if (isset($columns[array_key_last($columns)]) && is_numeric($rows[0][$columns[array_key_last($columns)]] ?? null))
                <tfoot>
                    <tr>
                        <td colspan="{{ count($columns) - 1 }}" class="text-right">Total</td>
                        <td class="text-right">
                            {{ number_format(collect($rows)->sum(fn($r) => (float) (is_array($r) ? end($r) : $r->{$columns[array_key_last($columns)]})), 2) }}
                        </td>
                    </tr>
                </tfoot>
            @endif
        </table>
    @else
        <div class="empty">No sales records for this period.</div>
    @endif

    <div class="footer">
        Consolidated Sales Report — Angkong Noodle House &middot; Page {{ '{PAGE_NUM}' }} of {{ '{PAGE_COUNT}' }}
    </div>

</body>

</html>
