<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>{{ $title }}</title>
<style>
    body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #0f172a; margin: 0; }
    .wrap { padding: 10px 6px; }
    table { width: 100%; border-collapse: collapse; }
    .head td { vertical-align: top; }
    .brand { font-size: 22px; font-weight: bold; color: #0891b2; }
    .inv { text-align: right; }
    .inv .big { font-size: 26px; font-weight: bold; letter-spacing: 3px; color: #0f172a; }
    .rule { border-top: 3px solid #0891b2; margin: 14px 0; }
    .bill td { padding: 2px 0; }
    .lbl { color: #64748b; font-size: 10px; text-transform: uppercase; }
    table.items th { background: #0f172a; color: #fff; padding: 8px; text-align: left; font-size: 11px; }
    table.items td { padding: 8px; border-bottom: 1px solid #e2e8f0; }
    .r { text-align: right; }
    .totals { width: 45%; margin-left: 55%; margin-top: 14px; }
    .totals td { padding: 5px 8px; }
    .totals .due td { background: #0891b2; color: #fff; font-size: 14px; font-weight: bold; }
    .note { margin-top: 30px; color: #64748b; font-size: 10px; }
</style>
</head>
<body>
@php
    $subtotal = collect($rows)->sum('amount');
    $vat = round($subtotal * 0.12, 2);
@endphp
<div class="wrap">
    <table class="head"><tr>
        <td><div class="brand">AVANT GUARD</div><div class="lbl">Technology &amp; Reporting Services</div></td>
        <td class="inv"><div class="big">INVOICE</div><div>No. INV-2026-{{ now()->format('md') }}</div><div>{{ $generated_at }}</div></td>
    </tr></table>
    <div class="rule"></div>
    <table class="bill"><tr>
        <td style="width:50%"><div class="lbl">Bill to</div><strong>Angkong Noodle House</strong><br>123 Rizal Avenue, Manila<br>accounts@example.com</td>
        <td style="width:50%"><div class="lbl">Terms</div>Net 15 days<br><div class="lbl" style="margin-top:6px">Reference</div>{{ $title }}</td>
    </tr></table>
    <br>
    <table class="items">
        <thead><tr><th>Description</th><th class="r">Qty</th><th class="r">Unit price</th><th class="r">Amount</th></tr></thead>
        <tbody>
        @foreach ($rows as $r)
            <tr><td>{{ $r['description'] }}</td><td class="r">{{ $r['qty'] }}</td><td class="r">{{ number_format($r['unit_price'], 2) }}</td><td class="r">{{ number_format($r['amount'], 2) }}</td></tr>
        @endforeach
        </tbody>
    </table>
    <table class="totals">
        <tr><td>Subtotal</td><td class="r">{{ number_format($subtotal, 2) }}</td></tr>
        <tr><td>VAT (12%)</td><td class="r">{{ number_format($vat, 2) }}</td></tr>
        <tr class="due"><td>Amount due (PHP)</td><td class="r">{{ number_format($subtotal + $vat, 2) }}</td></tr>
    </table>
    <div class="note">Please settle within the stated terms. This is a sample document generated with wkhtmltopdf (Snappy).</div>
</div>
</body>
</html>
