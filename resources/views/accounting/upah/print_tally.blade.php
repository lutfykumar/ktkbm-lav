<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $title ?? 'Cetak Rekap Tally Truk & Tonase' }}</title>

  <style>
    * { box-sizing: border-box; font-family: 'Courier New', Courier, monospace; }
    body { background: #f0f0f0; margin: 0; padding: 20px; color: #000; font-size: 13px; }

    .sheet-outer.A4 .sheet {
      width: 210mm;
      min-height: 297mm;
      background: white;
      margin: 0 auto;
      box-shadow: 0 0 10px rgba(0,0,0,0.15);
      padding: 25mm 20mm;
      position: relative;
    }

    @page {
      size: A4 portrait;
      margin: 0;
    }

    @media print {
      body { background: none; padding: 0; }
      .sheet-outer.A4 .sheet { margin: 0; box-shadow: none; border: none; width: 100%; min-height: 100vh; padding: 20mm 15mm; }
      .no-print { display: none !important; }
    }

    .print-controls { text-align: center; margin-bottom: 20px; font-family: Arial, sans-serif; }
    .btn-print {
      background: #17a2b8; color: white; border: none; padding: 8px 18px; font-size: 14px;
      border-radius: 4px; cursor: pointer; font-weight: bold;
    }
    .btn-print:hover { background: #138496; }

    /* Top Reference Note */
    .top-ref { position: absolute; top: 15mm; right: 20mm; font-size: 26px; font-family: Arial, sans-serif; font-weight: 300; color: #333; }

    /* Header */
    .header-center { text-align: center; margin-bottom: 25px; }
    .header-center h3 { margin: 0 0 5px 0; font-size: 16px; font-weight: bold; letter-spacing: 1px; }
    .header-center h4 { margin: 0 0 10px 0; font-size: 13px; font-weight: normal; }
    .header-divider { border-top: 2px dashed #000; border-bottom: 2px dashed #000; padding: 6px 0; font-weight: bold; font-size: 12px; margin-top: 10px; }

    /* Daily rows */
    .tally-list { margin: 35px 0; line-height: 2.2; font-size: 12.5px; }
    .tally-row { display: flex; justify-content: space-between; border-bottom: 1px dotted #ccc; padding: 4px 0; }
    .tally-col-date { width: 38%; }
    .tally-col-client { width: 15%; text-align: center; }
    .tally-col-truck { width: 22%; text-align: right; }
    .tally-col-tons { width: 25%; text-align: right; font-weight: bold; }

    /* Summary Totals */
    .summary-section { margin-top: 60px; text-align: center; }
    .total-line { font-size: 14px; font-weight: bold; margin-bottom: 15px; }
    .double-divider { border-top: 2px solid #000; border-bottom: 2px solid #000; padding: 8px 0; margin: 15px auto; width: 65%; font-size: 15px; font-weight: bold; letter-spacing: 1px; }

    /* Stamp */
    .stamp-round {
      position: absolute;
      bottom: 60mm;
      right: 25mm;
      width: 110px;
      height: 110px;
      border: 3px double #0056b3;
      border-radius: 50%;
      color: #0056b3;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      font-size: 9px;
      font-weight: bold;
      transform: rotate(-12deg);
      opacity: 0.8;
      pointer-events: none;
      line-height: 1.2;
    }
  </style>
</head>
<body>

  <div class="print-controls no-print">
    <button class="btn-print" onclick="window.print()">
      🖨️ Cetak Lembar Rekap Tally Truk & Tonase
    </button>
  </div>

  <div class="sheet-outer A4">
    <section class="sheet">

      <!-- TOP RIGHT NO KWITANSI REF -->
      @if (!empty($upah->NO_KWITANSI))
        <div class="top-ref">{{ explode('/', $upah->NO_KWITANSI)[0] ?? '' }}</div>
      @endif

      <!-- HEADER -->
      <div class="header-center">
        <h3>{{ strtoupper($upah->NAMA_CLIENT ?: 'PT. BERKAH MAKMUR SEJAHTERA') }}</h3>
        <h4>{{ strtoupper(date('l, d F Y', strtotime($upah->TGL_KEGIATAN_START ?? date('Y-m-d')))) }}</h4>
        <div class="header-divider">
          *** 1. {{ strtoupper($upah->NAMA_KAPAL ?: 'BG. SATRIA LAUT 346') }}, VIA RAMPDOOR PELABUHAN {{ strtoupper($upah->NAMA_DERMAGA ?: 'JETTY SIAM MASPION TERMINAL') }} ***
        </div>
      </div>

      <!-- TALLY LIST -->
      <div class="tally-list">
        @php
          $totalTruck = 0;
          $totalVolume = 0;
          $clientAbbr = 'PBM';
          if (!empty($upah->NAMA_CLIENT)) {
            $cleanName = preg_replace('/^(PT|CV|UD)\.?\s+/i', '', trim($upah->NAMA_CLIENT));
            if (preg_match_all('/\b[A-Za-z]/', $cleanName, $matches)) {
              $clientAbbr = strtoupper(implode('', $matches[0]));
            }
          }
        @endphp

        @foreach ($details as $d)
          @php
            $truck = intval($d->JUMLAH_TRUCK);
            $vol = floatval($d->VOLUME_BORONGAN);
            $totalTruck += $truck;
            $totalVolume += $vol;
          @endphp
          <div class="tally-row">
            <span class="tally-col-date">{{ date('d/m/Y', strtotime($d->TGL_KERJA)) }}. {{ strtoupper($upah->NAMA_KAPAL ?: '-') }}.</span>
            <span class="tally-col-client">{{ $clientAbbr }}</span>
            <span class="tally-col-truck">{{ $truck }} TRUCK</span>
            <span class="tally-col-tons">{{ number_format($vol, 3, ',', '.') }} TONS.</span>
          </div>
        @endforeach
      </div>

      <!-- SUMMARY SECTION -->
      <div class="summary-section">
        <div class="total-line">
          TOTAL &nbsp;=&nbsp; {{ $totalTruck }} &nbsp;TRUCK
        </div>

        <div class="double-divider">
          VOLUME &nbsp;=&nbsp; {{ number_format($totalVolume, 3, ',', '.') }} &nbsp;MT
        </div>
      </div>

      <!-- CAP STEMPEL KOPERASI TKBM -->
      <div class="stamp-round">
        <span>KOPERASI TKBM</span>
        <span style="font-size:12px; margin:2px 0;">PELABUHAN</span>
        <span>GRESIK</span>
        <span style="font-size:8px; margin-top:2px;">SEKTOR UTARA</span>
      </div>

    </section>
  </div>

</body>
</html>

