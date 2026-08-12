<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Daily QA Report {{ $report->production_date?->format('Y-m-d') }}</title>
    <style>
        @page { size: A4; margin: 16mm; }
        body { font-family: Arial, sans-serif; color: #111827; font-size: 11px; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #111827; padding-bottom: 10px; }
        .logo img { width: 220px; height: 48px; }
        h1 { font-size: 18px; margin: 0; text-align: right; }
        .meta { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin: 16px 0; }
        .field { border: 1px solid #d1d5db; padding: 7px; min-height: 28px; }
        .label { display: block; color: #6b7280; font-size: 9px; text-transform: uppercase; margin-bottom: 3px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #9ca3af; padding: 7px; text-align: left; }
        th { background: #f3f4f6; }
        .signatures { display: grid; grid-template-columns: repeat(3, 1fr); gap: 28px; margin-top: 55px; }
        .signature { border-top: 1px solid #111827; padding-top: 6px; text-align: center; }
    </style>
</head>
<body>
    <div class="header"><div class="logo"><img src="{{ asset('logo.svg') }}" alt="QA Management System"></div><h1>DAILY QA REPORT</h1></div>
    <div class="meta">
        <div class="field"><span class="label">Production Date</span>{{ $report->production_date?->format('Y-m-d') }}</div>
        <div class="field"><span class="label">Plant / Line</span>{{ $report->plant?->name }} / {{ $report->line?->name }}</div>
        <div class="field"><span class="label">Machine / Shift</span>{{ $report->machine?->name }} / {{ $report->shift?->name }}</div>
        <div class="field"><span class="label">Status</span>{{ $report->status?->value }}</div>
        <div class="field"><span class="label">Product MM Number</span>{{ $report->mm_number }}</div>
        <div class="field"><span class="label">PO Number</span>{{ $report->po_number }}</div>
        <div class="field"><span class="label">Output</span>{{ number_format($report->output_box) }} boxes / {{ number_format($report->output_pcs) }} PCS</div>
        <div class="field"><span class="label">QA Checker</span>{{ $report->checker?->name }}</div>
    </div>
    <table><thead><tr><th>Defect</th><th>Category</th><th>Quantity</th><th>Remarks</th></tr></thead><tbody>@foreach($report->defects as $item)<tr><td>{{ $item->defect?->name }}</td><td>{{ $item->defect?->category }}</td><td>{{ number_format($item->quantity) }}</td><td>{{ $item->remarks }}</td></tr>@endforeach</tbody></table>
    <p><strong>Remarks:</strong> {{ $report->remarks }}</p>
    <div class="signatures"><div class="signature">QA Checker</div><div class="signature">QA Supervisor</div><div class="signature">Management</div></div>
</body>
</html>
