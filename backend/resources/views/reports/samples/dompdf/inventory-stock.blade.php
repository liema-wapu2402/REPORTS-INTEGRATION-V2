<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>{{ $title }}</title>
<style>
    @page { margin: 28px 30px 40px; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #0f172a; }
    h1 { font-size: 18px; margin: 0; }
    .meta { color: #64748b; margin: 3px 0 12px; }
    .alert { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 8px 10px; margin-bottom: 12px; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #0f172a; color: #fff; padding: 6px 7px; text-align: left; font-size: 9px; text-transform: uppercase; }
    td { padding: 5px 7px; border-bottom: 1px solid #e2e8f0; }
    .r { text-align: right; }
    .low td { background: #fef2f2; }
    .tag { font-weight: bold; }
    .tag.re { color: #dc2626; } .tag.ok { color: #059669; }
    tfoot td { border-top: 2px solid #0891b2; font-weight: bold; background: #ecfeff; }
</style>
</head>
<body>
@php $low = collect($rows)->where('status', 'Reorder'); @endphp
<h1>{{ $title }}</h1>
<div class="meta">Generated {{ $generated_at }}</div>
@if ($low->count())
    <div class="alert"><strong>{{ $low->count() }} item(s) need reordering:</strong> {{ $low->pluck('item')->implode(', ') }}</div>
@endif
<table>
    <thead><tr><th>SKU</th><th>Item</th><th class="r">On hand</th><th class="r">Reorder at</th><th class="r">Unit cost</th><th class="r">Stock value</th><th>Status</th></tr></thead>
    <tbody>
    @foreach ($rows as $r)
        <tr class="{{ $r['status'] === 'Reorder' ? 'low' : '' }}">
            <td>{{ $r['sku'] }}</td><td>{{ $r['item'] }}</td>
            <td class="r">{{ $r['on_hand'] }}</td><td class="r">{{ $r['reorder_level'] }}</td>
            <td class="r">{{ number_format($r['unit_cost'], 2) }}</td><td class="r">{{ number_format($r['stock_value'], 2) }}</td>
            <td><span class="tag {{ $r['status'] === 'Reorder' ? 're' : 'ok' }}">{{ $r['status'] }}</span></td>
        </tr>
    @endforeach
    </tbody>
    <tfoot><tr><td colspan="5" class="r">Total inventory value</td><td class="r">{{ number_format(collect($rows)->sum('stock_value'), 2) }}</td><td></td></tr></tfoot>
</table>
</body>
</html>
