<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>{{ $title }}</title>
<style>
    body { font-family: Arial, Helvetica, sans-serif; font-size: 11px; color: #0f172a; }
    h1 { font-size: 20px; margin: 0; color: #0f172a; }
    .sub { color: #0891b2; font-weight: bold; margin: 2px 0 12px; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #0891b2; color: #fff; padding: 7px; text-align: left; font-size: 10px; text-transform: uppercase; }
    td { padding: 6px 7px; border-bottom: 1px solid #e2e8f0; }
    .r { text-align: right; }
    tr.alt td { background: #f8fafc; }
    tfoot td { background: #0f172a; color: #fff; font-weight: bold; }
    .sign { margin-top: 50px; }
    .sign td { border: 0; width: 33%; text-align: center; padding-top: 30px; }
    .line { border-top: 1px solid #0f172a; margin: 0 20px; padding-top: 4px; font-size: 10px; }
</style>
</head>
<body>
<h1>{{ $title }}</h1>
<div class="sub">Pay period: September 1&ndash;30, 2026 &nbsp;|&nbsp; Generated {{ $generated_at }}</div>
<table>
    <thead><tr><th>No.</th><th>Employee</th><th class="r">Basic</th><th class="r">Allowances</th><th class="r">Gross</th><th class="r">Deductions</th><th class="r">Net pay</th></tr></thead>
    <tbody>
    @foreach ($rows as $i => $r)
        <tr class="{{ $i % 2 ? 'alt' : '' }}">
            <td>{{ $r['employee_no'] }}</td><td>{{ $r['name'] }}</td>
            <td class="r">{{ number_format($r['basic_pay'], 2) }}</td><td class="r">{{ number_format($r['allowances'], 2) }}</td>
            <td class="r">{{ number_format($r['gross_pay'], 2) }}</td><td class="r">{{ number_format($r['deductions'], 2) }}</td>
            <td class="r"><strong>{{ number_format($r['net_pay'], 2) }}</strong></td>
        </tr>
    @endforeach
    </tbody>
    <tfoot><tr>
        <td colspan="2">TOTAL</td>
        @foreach (['basic_pay', 'allowances', 'gross_pay', 'deductions', 'net_pay'] as $col)
            <td class="r">{{ number_format(collect($rows)->sum($col), 2) }}</td>
        @endforeach
    </tr></tfoot>
</table>
<table class="sign"><tr>
    <td><div class="line">Prepared by</div></td><td><div class="line">Checked by</div></td><td><div class="line">Approved by</div></td>
</tr></table>
</body>
</html>
