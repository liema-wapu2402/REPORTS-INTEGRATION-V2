<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>{{ $title }}</title>
<style>
    @page { margin: 28px 30px 40px; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #0f172a; }
    .banner { background: #0f172a; color: #fff; padding: 16px 20px; }
    .banner h1 { margin: 0; font-size: 18px; letter-spacing: 1px; }
    .banner .sub { color: #22d3ee; font-size: 9px; margin-top: 3px; }
    .summary { width: 100%; margin: 14px 0; border-collapse: separate; border-spacing: 6px 0; }
    .summary td { background: #f1f5f9; border-left: 3px solid #0891b2; padding: 8px 10px; }
    .summary .n { font-size: 16px; font-weight: bold; }
    .summary .l { color: #64748b; font-size: 8px; text-transform: uppercase; }
    table.data { width: 100%; border-collapse: collapse; }
    table.data th { background: #0891b2; color: #fff; text-align: left; padding: 6px 7px; font-size: 9px; text-transform: uppercase; }
    table.data td { padding: 5px 7px; border-bottom: 1px solid #e2e8f0; }
    table.data tr.alt td { background: #f8fafc; }
    .badge { padding: 1px 6px; font-size: 8px; font-weight: bold; }
    .ok { background: #dcfce7; color: #166534; }
    .leave { background: #fef3c7; color: #92400e; }
    .foot { position: fixed; bottom: -22px; left: 0; right: 0; text-align: center; font-size: 8px; color: #94a3b8; }
</style>
</head>
<body>
@php
    $byDept = collect($rows)->groupBy('department')->map->count();
@endphp
<div class="banner">
    <h1>{{ strtoupper($title) }}</h1>
    <div class="sub">Generated {{ $generated_at }} &middot; {{ count($rows) }} employees</div>
</div>

<table class="summary">
    <tr>
        @foreach ($byDept->take(4) as $dept => $count)
            <td><div class="n">{{ $count }}</div><div class="l">{{ $dept }}</div></td>
        @endforeach
    </tr>
</table>

<table class="data">
    <thead>
    <tr><th>No.</th><th>Name</th><th>Department</th><th>Position</th><th>Email</th><th>Hired</th><th>Status</th></tr>
    </thead>
    <tbody>
    @foreach ($rows as $i => $r)
        <tr class="{{ $i % 2 ? 'alt' : '' }}">
            <td>{{ $r['employee_no'] }}</td>
            <td><strong>{{ $r['name'] }}</strong></td>
            <td>{{ $r['department'] }}</td>
            <td>{{ $r['position'] }}</td>
            <td>{{ $r['email'] }}</td>
            <td>{{ $r['date_hired'] }}</td>
            <td><span class="badge {{ $r['status'] === 'Active' ? 'ok' : 'leave' }}">{{ $r['status'] }}</span></td>
        </tr>
    @endforeach
    </tbody>
</table>
<div class="foot">Report Integrations &middot; Employee Directory &middot; Confidential</div>
</body>
</html>
