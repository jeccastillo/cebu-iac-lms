<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Invoice {{ $meta['number'] ?? '' }}</title>
  <style>
    @page {
      margin: 12mm;
    }
    * { box-sizing: border-box; }
    body {
      font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
      font-size: 12px;
      color: #111;
      margin: 0;
      padding: 0;
    }
    .header {
      margin-bottom: 12px;
      padding-bottom: 8px;
      border-bottom: 1px solid #ccc;
    }
    .title {
      font-size: 20px;
      font-weight: bold;
      margin: 0;
      padding: 0;
    }
    .meta {
      margin-top: 4px;
      color: #444;
    }
    .row {
      width: 100%;
      display: table;
      table-layout: fixed;
    }
    .col {
      display: table-cell;
      vertical-align: top;
    }
    .col-6 { width: 50%; }
    .section {
      margin-top: 14px;
    }
    .section h3 {
      font-size: 14px;
      margin: 0 0 6px 0;
    }
    table.items {
      width: 100%;
      border-collapse: collapse;
      border-spacing: 0;
    }
    table.items th, table.items td {
      border: 1px solid #ddd;
      padding: 6px 8px;
      vertical-align: top;
    }
    table.items th {
      background: #f5f5f5;
      text-align: left;
      font-size: 12px;
    }
    .right { text-align: right; }
    .small { font-size: 11px; color: #555; }
    .totals {
      margin-top: 10px;
      width: 100%;
      display: table;
    }
    .totals .label {
      display: table-cell;
      text-align: right;
      padding-right: 8px;
      width: 70%;
      font-weight: bold;
    }
    .totals .value {
      display: table-cell;
      text-align: right;
      width: 30%;
    }
    .remarks {
      margin-top: 10px;
      padding: 8px;
      border: 1px solid #ddd;
      background: #fafafa;
      min-height: 40px;
    }
    .footer {
      position: fixed;
      bottom: 10mm;
      left: 12mm;
      right: 12mm;
      font-size: 10px;
      color: #666;
      border-top: 1px solid #ddd;
      padding-top: 6px;
    }
  </style>
</head>
<body>
  <div class="header">
    <div class="row">
      <div class="col col-6">
        <h1 class="title">Invoice</h1>
        <div class="meta">
          Number: <strong>{{ $meta['number'] ?? 'N/A' }}</strong><br>
          Date: <strong>{{ $meta['posted'] ?? '' }}</strong>
        </div>
      </div>
      <div class="col col-6" style="text-align:right;">
        <div class="meta">
          Type: <strong>{{ $meta['type'] ?? '-' }}</strong><br>
          Status: <strong>{{ $meta['status'] ?? '-' }}</strong>
        </div>
      </div>
    </div>
  </div>

  <div class="section">
    <h3>Invoice Information</h3>
    <div class="small">
      @php
        $studentName = '';
        if (!empty($invoice['student'])) {
          $s = $invoice['student'];
          $ln = $s['last_name'] ?? $s['strLastname'] ?? '';
          $fn = $s['first_name'] ?? $s['strFirstname'] ?? '';
          $mn = $s['middle_name'] ?? $s['strMiddlename'] ?? '';
          $studentName = trim($ln . ', ' . $fn . ' ' . $mn);
        }
        $studentNumber = $invoice['student']['student_number'] ?? ($invoice['student']['strStudentNumber'] ?? '');
        $syLabel = $invoice['sy_label'] ?? ($invoice['term_label'] ?? '');
      @endphp

      @if($studentName)
        Student: <strong>{{ $studentName }}</strong><br>
      @endif
      @if($studentNumber)
        Student Number: <strong>{{ $studentNumber }}</strong><br>
      @endif
      @if($syLabel)
        Term: <strong>{{ $syLabel }}</strong><br>
      @endif
    </div>
  </div>

  <div class="section">
    <h3>Items</h3>
    <table class="items">
      <thead>
        <tr>
          <th style="width:70%;">Description</th>
          <th class="right" style="width:30%;">Amount</th>
        </tr>
      </thead>
      <tbody>
        @php $running = 0; @endphp
        @if(!empty($items))
          @foreach($items as $it)
            @php
              $desc = $it['description'] ?? ($it['name'] ?? ($it['code'] ?? '—'));
              $amt = isset($it['amount']) && is_numeric($it['amount']) ? (float)$it['amount']
                   : (isset($it['total']) && is_numeric($it['total']) ? (float)$it['total'] : 0);
              $running += $amt;
            @endphp
            <tr>
              <td>{{ $desc }}</td>
              <td class="right">{{ number_format($amt, 2) }}</td>
            </tr>
          @endforeach
        @else
          <tr>
            <td colspan="2" class="small">No itemized entries available.</td>
          </tr>
        @endif
      </tbody>
    </table>

    @php
      $grand = isset($meta['total']) && is_numeric($meta['total']) ? (float)$meta['total'] : $running;
    @endphp

    <div class="totals">
      <div class="label">Total Amount</div>
      <div class="value"><strong>{{ number_format($grand, 2) }}</strong></div>
    </div>
  </div>

  @if(!empty($meta['remarks']))
    <div class="section">
      <h3>Remarks</h3>
      <div class="remarks">
        {{ $meta['remarks'] }}
      </div>
    </div>
  @endif

  <div class="footer">
    Generated by Cashier System • {{ date('Y-m-d H:i') }}
  </div>
</body>
</html>
