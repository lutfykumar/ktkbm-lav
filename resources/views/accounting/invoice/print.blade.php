<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $title ?? 'Print Invoice' }}</title>
  
  <style>
    /* Reset and Basic Print Styling */
    * { box-sizing: border-box; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; }
    body { background: #e0e0e0; margin: 0; padding: 20px; color: #333; font-size: 13px; }
    
    .sheet-outer.A4 .sheet { width: 210mm; min-height: 297mm; background: white; margin: 0 auto; box-shadow: 0 0 10px rgba(0,0,0,0.1); padding: 15mm; position: relative; }
    
    @page { size: A4; margin: 0; }
    @media print {
      body { background: none; padding: 0; }
      .sheet-outer.A4 .sheet { margin: 0; box-shadow: none; border: none; width: 100%; }
      .no-print { display: none !important; }
    }

    /* Print Controls */
    .print-controls { text-align: center; margin-bottom: 20px; }
    .btn-print { background: #007bff; color: white; border: none; padding: 10px 20px; font-size: 16px; border-radius: 5px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; font-weight: bold; }
    .btn-print:hover { background: #0056b3; }

    /* Header */
    .header { border-bottom: 2px solid #2c3e50; padding-bottom: 10px; margin-bottom: 20px; display: flex; align-items: flex-end; justify-content: space-between; }
    .company-info h2 { margin: 0 0 5px 0; font-size: 24px; color: #2c3e50; }
    .company-info p { margin: 0; font-size: 12px; color: #555; }
    
    /* Invoice Title */
    .invoice-title { text-align: center; font-size: 20px; font-weight: bold; text-transform: uppercase; margin: 10px 0 20px 0; letter-spacing: 2px; color: #2c3e50; }

    /* Info Blocks */
    .info-grid { display: flex; justify-content: space-between; margin-bottom: 25px; gap: 20px; }
    .info-box { flex: 1; padding: 10px 15px; border-radius: 6px; background: #f8f9fa; border: 1px solid #e9ecef; }
    .info-box.client-box { background: white; border-color: #dee2e6; }
    .info-table { width: 100%; }
    .info-table td { padding: 3px 0; vertical-align: top; }
    .info-table td:first-child { font-weight: bold; width: 120px; color: #555; }

    /* Items Table */
    .items-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
    .items-table th { background: #2c3e50; color: white; text-align: left; padding: 10px; font-size: 12px; text-transform: uppercase; border: 1px solid #2c3e50; }
    .items-table td { padding: 10px; border: 1px solid #dee2e6; vertical-align: top; }
    .items-table .text-center { text-align: center; }
    .items-table .text-right { text-align: right; }
    .items-table .fw-bold { font-weight: bold; }
    
    /* Summary Section */
    .summary-section { width: 40%; margin-left: auto; }
    .summary-table { width: 100%; border-collapse: collapse; }
    .summary-table td { padding: 8px 10px; border-bottom: 1px solid #eee; }
    .summary-table tr:last-child td { border-bottom: none; border-top: 2px solid #2c3e50; font-size: 16px; font-weight: bold; }

    /* Terbilang & Catatan */
    .terbilang-box { background: #f8f9fa; padding: 10px 15px; border-left: 4px solid #2c3e50; margin-bottom: 20px; font-style: italic; }
    .catatan-box { margin-bottom: 30px; }
    
    /* Signatures */
    .signature-grid { display: flex; justify-content: space-between; margin-top: 50px; text-align: center; }
    .signature-box { width: 30%; }
    .signature-line { border-top: 1px solid #333; margin-top: 70px; font-weight: bold; padding-top: 5px; }

    /* Status Watermark */
    .watermark { position: absolute; top: 30%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg); font-size: 100px; opacity: 0.1; font-weight: bold; text-transform: uppercase; letter-spacing: 10px; pointer-events: none; z-index: 0; }
    .watermark.paid { color: green; }
    .watermark.cancelled { color: red; }
    .watermark.draft { color: gray; }
  </style>

</head>
<body>

  <div class="print-controls no-print">
    <button class="btn-print" onclick="window.print()">
      <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16" style="margin-right:8px"><path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/><path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/></svg>
      Cetak Invoice
    </button>
  </div>

  <div class="sheet-outer A4">
    <section class="sheet">
      
      @if (in_array(strtolower($invoice->STATUS ?? ''), ['paid', 'cancelled', 'draft']))
      <div class="watermark {{ strtolower($invoice->STATUS) }}">{{ strtoupper($invoice->STATUS) }}</div>
      @endif

      <div class="header">
        <div class="company-info">
          <h2>{{ get_app_setting('app_name', 'Nama Perusahaan') }}</h2>
          <p>{{ get_app_setting('app_address', 'Alamat Perusahaan') }}</p>
          <p>Email: {{ get_app_setting('app_email', '-') }} | Telp: {{ get_app_setting('app_phone', '-') }}</p>
        </div>
        @php $logo = get_app_setting('app_logo'); @endphp
        @if ($logo)
          <img src="{{ asset($logo) }}" alt="Logo" style="height: 60px;">
        @endif
      </div>

      <div class="invoice-title">INVOICE</div>

      <div class="info-grid">
        <div class="info-box client-box">
          <strong>Kepada Yth:</strong><br>
          <div style="font-size:16px; font-weight:bold; color:#2c3e50; margin:5px 0;">{{ $invoice->NAMA_CLIENT }}</div>
          {!! nl2br(e($invoice->ALAMAT_CLIENT)) !!}<br>
          Telp: {{ $invoice->KONTAK_CLIENT ?: '-' }}
        </div>
        
        <div class="info-box">
          <table class="info-table">
            <tr><td>No Invoice</td><td>: <strong>{{ $invoice->NO_INVOICE }}</strong></td></tr>
            <tr><td>Tanggal</td><td>: {{ $invoice->TGL_INVOICE_FORMAT }}</td></tr>
            <tr><td>Jatuh Tempo</td><td>: <strong>{{ $invoice->TGL_JATUH_TEMPO_FORMAT }}</strong></td></tr>
            <tr><td>Ref. WO</td><td>: {{ $invoice->NO_WORK_ORDER }}</td></tr>
            <tr><td>Status</td><td>: <span style="text-transform:uppercase; font-weight:bold;">{{ $invoice->STATUS }}</span></td></tr>
          </table>
        </div>
      </div>

      <table class="items-table">
        <thead>
          <tr>
            <th width="5%" class="text-center">NO</th>
            <th>DESKRIPSI TAGIHAN</th>
            <th width="10%" class="text-center">QTY</th>
            <th width="12%">SATUAN</th>
            <th width="18%" class="text-right">HARGA (Rp)</th>
            <th width="20%" class="text-right">JUMLAH (Rp)</th>
          </tr>
        </thead>
        <tbody>
          @php $no = 1; @endphp
          @foreach ($items as $item)
          <tr>
            <td class="text-center">{{ $no++ }}</td>
            <td>
              <strong>{{ $item->DESKRIPSI }}</strong><br>
              <small style="color:#666">
                Kapal: {{ $invoice->NAMA_KAPAL }} | 
                Lokasi: {{ $invoice->NAMA_PELABUHAN }}
              </small>
            </td>
            <td class="text-center">{{ $item->JUMLAH }}</td>
            <td>{{ $item->SATUAN }}</td>
            <td class="text-right">{{ number_format($item->HARGA_SATUAN, 0, ',', '.') }}</td>
            <td class="text-right fw-bold">{{ number_format($item->SUBTOTAL, 0, ',', '.') }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>

      <div style="display:flex; justify-content:space-between; align-items:flex-start;">
        <div style="width:55%;">
          
          <div class="terbilang-box">
            <strong>Terbilang:</strong><br>
            {{ ucwords(terbilang($invoice->TOTAL)) }} Rupiah
          </div>
          
          @if (!empty($invoice->CATATAN))
          <div class="catatan-box">
            <strong>Catatan:</strong><br>
            {!! nl2br(e($invoice->CATATAN)) !!}
          </div>
          @endif

          <div class="catatan-box">
            <strong>Pembayaran dapat ditransfer ke:</strong><br>
            Bank BCA: 1234567890<br>
            A/N: {{ get_app_setting('app_name', 'Perusahaan') }}
          </div>
          
        </div>

        <div class="summary-section">
          <table class="summary-table">
            <tr>
              <td>Subtotal</td>
              <td class="text-right">Rp {{ number_format($invoice->SUBTOTAL, 0, ',', '.') }}</td>
            </tr>
            <tr>
              <td>PPN ({{ floatval($invoice->PAJAK_PERSEN) }}%)</td>
              <td class="text-right">Rp {{ number_format($invoice->PAJAK_NOMINAL, 0, ',', '.') }}</td>
            </tr>
            <tr>
              <td>TOTAL TAGIHAN</td>
              <td class="text-right">Rp {{ number_format($invoice->TOTAL, 0, ',', '.') }}</td>
            </tr>
          </table>
        </div>
      </div>

      <div class="signature-grid">
        <div class="signature-box">
          <p>Disetujui Oleh,</p>
          <div class="signature-line">Manajer / Direktur</div>
        </div>
        <div class="signature-box">
          <p>Dibuat Oleh,</p>
          <div class="signature-line">{{ $invoice->CREATED_USER ?? '-' }}</div>
        </div>
      </div>

    </section>
  </div>

</body>
</html>
