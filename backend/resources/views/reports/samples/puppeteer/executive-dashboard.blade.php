<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>{{ $title }}</title>
<style>
    @page { size: A4 landscape; margin: 0; }
    * { box-sizing: border-box; }
    html, body { margin: 0; }
    body { font-family: 'Segoe UI', Roboto, Arial, sans-serif; background: #0b1220; color: #e2e8f0; padding: 28px 34px; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    header { display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 1px solid #1e293b; padding-bottom: 14px; }
    h1 { margin: 0; font-size: 26px; background: linear-gradient(90deg, #22d3ee, #6366f1); -webkit-background-clip: text; background-clip: text; color: transparent; }
    .sub { color: #64748b; font-size: 12px; margin-top: 4px; }
    .pill { font-size: 11px; color: #22d3ee; border: 1px solid #164e63; padding: 4px 10px; border-radius: 999px; }
    .grid { display: grid; gap: 14px; margin-top: 18px; }
    .kpis { grid-template-columns: repeat(4, 1fr); }
    .charts { grid-template-columns: 1.5fr 1fr; }
    .card { background: linear-gradient(160deg, #111b2e, #0f172a); border: 1px solid #1e293b; border-radius: 14px; padding: 16px 18px; }
    .kpi .v { font-size: 24px; font-weight: 700; margin-top: 6px; }
    .kpi .l { font-size: 11px; color: #94a3b8; text-transform: uppercase; letter-spacing: .08em; }
    .kpi .d { font-size: 11px; color: #34d399; margin-top: 4px; }
    .card h3 { margin: 0 0 10px; font-size: 13px; color: #cbd5e1; font-weight: 600; }
    .bar-row { display: flex; align-items: center; gap: 10px; margin: 8px 0; font-size: 11px; }
    .bar-row .n { width: 90px; color: #94a3b8; }
    .bar { flex: 1; height: 10px; background: #1e293b; border-radius: 99px; overflow: hidden; }
    .bar i { display: block; height: 100%; background: linear-gradient(90deg, #06b6d4, #6366f1); border-radius: 99px; }
    .bar-row .a { width: 80px; text-align: right; color: #e2e8f0; }
    footer { margin-top: 16px; font-size: 10px; color: #475569; text-align: center; }
</style>
</head>
<body>
@php
    $peso = fn ($n) => '₱' . number_format($n);
    $monthly = $kpis['monthly'];
    $max = max($monthly) ?: 1;
    $w = 520; $h = 180; $pad = 30; $step = ($w - 2 * $pad) / (count($monthly) - 1);
    $points = [];
    $i = 0;
    foreach ($monthly as $m => $v) { $points[] = [round($pad + $i * $step, 1), round($h - ($v / $max) * ($h - 20), 1), $m, $v]; $i++; }
    $line = collect($points)->map(fn ($p) => $p[0] . ',' . $p[1])->implode(' ');
    $area = $pad . ',' . $h . ' ' . $line . ' ' . ($w - $pad) . ',' . $h;
    $branchMax = max($kpis['by_branch']) ?: 1;
    $catMax = max($kpis['by_category']) ?: 1;
@endphp
<header>
    <div><h1>{{ $title }}</h1><div class="sub">Angkong Noodle House &middot; All branches &middot; Generated {{ $generated_at }}</div></div>
    <div class="pill">Rendered with Puppeteer / Chromium</div>
</header>

<div class="grid kpis">
    <div class="card kpi"><div class="l">Gross sales</div><div class="v">{{ $peso($kpis['gross']) }}</div><div class="d">▲ 8.4% vs last period</div></div>
    <div class="card kpi"><div class="l">Net sales</div><div class="v">{{ $peso($kpis['net']) }}</div><div class="d">▲ 6.1% vs last period</div></div>
    <div class="card kpi"><div class="l">Discounts</div><div class="v">{{ $peso($kpis['discounts']) }}</div><div class="d" style="color:#fbbf24">● {{ round($kpis['discounts'] / max($kpis['gross'], 1) * 100, 1) }}% of gross</div></div>
    <div class="card kpi"><div class="l">Orders</div><div class="v">{{ number_format($kpis['orders']) }}</div><div class="d">▲ 3.2% vs last period</div></div>
</div>

<div class="grid charts">
    <div class="card">
        <h3>Net sales trend (6 months)</h3>
        <svg viewBox="0 0 {{ $w }} {{ $h + 24 }}" width="100%">
            <defs>
                <linearGradient id="g" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#22d3ee" stop-opacity=".5"/><stop offset="1" stop-color="#22d3ee" stop-opacity="0"/></linearGradient>
            </defs>
            <polygon points="{{ $area }}" fill="url(#g)"/>
            <polyline points="{{ $line }}" fill="none" stroke="#22d3ee" stroke-width="3" stroke-linejoin="round"/>
            @foreach ($points as $p)
                <circle cx="{{ $p[0] }}" cy="{{ $p[1] }}" r="4.5" fill="#0b1220" stroke="#22d3ee" stroke-width="2"/>
                <text x="{{ $p[0] }}" y="{{ $h + 18 }}" fill="#64748b" font-size="11" text-anchor="middle">{{ $p[2] }}</text>
            @endforeach
        </svg>
    </div>
    <div class="card">
        <h3>Net sales by category</h3>
        @foreach ($kpis['by_category'] as $name => $v)
            <div class="bar-row"><span class="n">{{ $name }}</span><span class="bar"><i style="width: {{ round($v / $catMax * 100) }}%"></i></span><span class="a">{{ $peso($v) }}</span></div>
        @endforeach
        <h3 style="margin-top:16px">Net sales by branch</h3>
        @foreach ($kpis['by_branch'] as $name => $v)
            <div class="bar-row"><span class="n">{{ $name }}</span><span class="bar"><i style="width: {{ round($v / $branchMax * 100) }}%"></i></span><span class="a">{{ $peso($v) }}</span></div>
        @endforeach
    </div>
</div>
<footer>Sample report &middot; Report Integrations</footer>
</body>
</html>
