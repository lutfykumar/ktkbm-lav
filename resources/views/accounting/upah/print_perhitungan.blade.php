<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $title ?? 'Cetak Perhitungan Upah TKBM' }}</title>

  <style>
    * { box-sizing: border-box; font-family: 'Arial', sans-serif; }
    body { background: #f0f0f0; margin: 0; padding: 20px; color: #000; font-size: 11px; }

    .sheet-outer.A4 .sheet {
      width: 210mm;
      min-height: 297mm;
      background: white;
      margin: 0 auto;
      box-shadow: 0 0 10px rgba(0,0,0,0.15);
      padding: 12mm 15mm;
      position: relative;
    }

    @page {
      size: A4 portrait;
      margin: 0;
    }

    @media print {
      body { background: none; padding: 0; font-size: 10.5px; }
      .sheet-outer.A4 .sheet { margin: 0; box-shadow: none; border: none; width: 100%; min-height: 100vh; padding: 10mm 12mm; }
      .no-print { display: none !important; }
    }

    .print-controls { text-align: center; margin-bottom: 20px; }
    .btn-print {
      background: #007bff; color: white; border: none; padding: 8px 18px; font-size: 14px;
      border-radius: 4px; cursor: pointer; font-weight: bold;
    }
    .btn-print:hover { background: #0056b3; }

    /* Kop Surat */
    .kop-header { display: flex; justify-content: flex-end; text-align: right; margin-bottom: 15px; }
    .kop-text { font-size: 9.5px; line-height: 1.3; }
    .kop-text strong { font-size: 10.5px; }

    /* Title */
    .doc-title { text-align: center; font-size: 18px; font-weight: bold; letter-spacing: 1.5px; margin: 10px 0 20px 0; text-decoration: underline; }

    /* Meta Info */
    .meta-table { width: 100%; margin-bottom: 15px; border-collapse: collapse; }
    .meta-table td { padding: 3px 0; font-size: 11px; vertical-align: top; }
    .meta-table td.label { width: 130px; font-weight: bold; }
    .meta-table td.colon { width: 15px; text-align: center; }

    /* Detail Table */
    .data-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 10.5px; }
    .data-table th, .data-table td { border: 1px solid #000; padding: 5px 4px; }
    .data-table th { background: #f2f2f2; text-align: center; font-weight: bold; text-transform: uppercase; font-size: 10px; }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .fw-bold { font-weight: bold; }

    /* Summary & Terbilang */
    .summary-box { border: 1px solid #000; padding: 8px 12px; margin-bottom: 20px; background: #fafafa; }
    .summary-row { display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 12px; }
    .terbilang-text { font-size: 11px; font-style: italic; font-weight: bold; margin-top: 5px; }

    /* Signatures */
    .signature-grid { display: flex; justify-content: space-between; margin-top: 35px; text-align: center; }
    .signature-box { width: 45%; position: relative; }
    .signature-title { font-weight: bold; font-size: 11px; margin-bottom: 60px; }
    .signature-name { font-weight: bold; font-size: 11px; text-decoration: underline; }

    /* Cap Lunas Stempel */
    .stamp-lunas {
      position: absolute;
      top: 20px;
      left: 50%;
      transform: translateX(-50%) rotate(-10deg);
      border: 3px solid #0056b3;
      color: #0056b3;
      font-size: 18px;
      font-weight: bold;
      padding: 6px 14px;
      border-radius: 8px;
      text-transform: uppercase;
      letter-spacing: 2px;
      opacity: 0.85;
      pointer-events: none;
    }
  </style>
</head>
<body>

  <div class="print-controls no-print">
    <button class="btn-print" onclick="window.print()">
      🖨️ Cetak Lembar Perhitungan Upah TKBM
    </button>
  </div>

  <div class="sheet-outer A4">
    <section class="sheet">

      <!-- KOP K.TKBM GRESIK -->
      <div class="kop-header">
        <div class="kop-text">
          <strong>K.TKBM PELABUHAN GRESIK</strong><br>
          Badan Hukum Nomor : 6879 / BH / II / 1990 Tanggal 18 Juni 1990<br>
          UNIT USAHA PENYEDIA JASA TKBM PELABUHAN GRESIK<br>
          SEKTOR UTARA : Jl. Yos Sudarso Gresik Kode Pos : 61114 Telp. 3994273
        </div>
      </div>

      <div class="doc-title">PERHITUNGAN UPAH TKBM</div>

      <!-- META INFO -->
      <table class="meta-table">
        <tr>
          <td class="label">Kapal</td>
          <td class="colon">:</td>
          <td><strong>{{ $upah->NAMA_KAPAL ?: '-' }}</strong></td>
          <td class="label" style="text-align:right; width:160px;">Total Tonase :</td>
          <td style="width:120px; text-align:right;"><strong>{{ number_format(floatval($upah->TOTAL_VOLUME), 2, ',', '.') }} Tons</strong></td>
        </tr>
        <tr>
          <td class="label">P B M</td>
          <td class="colon">:</td>
          <td colspan="3"><strong>{{ $upah->NAMA_CLIENT ?: '-' }}</strong></td>
        </tr>
        <tr>
          <td class="label">Bongkar / Muat</td>
          <td class="colon">:</td>
          <td colspan="3">{{ $upah->KOMODITAS ?: '-' }} ({{ $upah->NAMA_DERMAGA ?? 'Pelabuhan Gresik' }})</td>
        </tr>
      </table>

      <!-- TABEL RINCIAN SHIFT -->
      <table class="data-table">
        <thead>
          <tr>
            <th width="11%">TANGGAL</th>
            <th width="10%">JAM KERJA</th>
            <th width="6%">GANG</th>
            <th width="7%">HARIAN</th>
            <th width="11%">BORONGAN</th>
            <th width="12%">UPAH</th>
            <th width="10%">ADMINISTRASI</th>
            <th width="10%">TOESLAG</th>
            <th width="8%">LEMBUR</th>
            <th width="12%">JUMLAH UPAH</th>
            <th width="13%">KETERANGAN</th>
          </tr>
        </thead>
        <tbody>
          @php
            $sumGang = 0;
            $sumHarian = 0;
            $sumBorongan = 0;
            $sumUpah = 0;
            $sumAdm = 0;
            $sumToeslag = 0;
            $sumLembur = 0;
            $sumTotal = 0;
          @endphp

          @foreach ($details as $d)
            @php
              $sumGang += $d->JUMLAH_GANG;
              $sumHarian += intval($d->JUMLAH_HARIAN ?? 0);
              $sumBorongan += floatval($d->VOLUME_BORONGAN);
              $sumUpah += floatval($d->UPAH_NOMINAL);
              $sumAdm += floatval($d->ADM_NOMINAL);
              $sumToeslag += floatval($d->TOESLAG_NOMINAL);
              $sumLembur += floatval($d->LEMBUR_NOMINAL);
              $sumTotal += floatval($d->JUMLAH_UPAH);
            @endphp
            <tr>
              <td class="text-center">{{ date('d M Y', strtotime($d->TGL_KERJA)) }}</td>
              <td class="text-center">{{ $d->JAM_KERJA }}</td>
              <td class="text-center">{{ $d->JUMLAH_GANG }}</td>
              <td class="text-center">{{ $d->JUMLAH_HARIAN ?: '-' }}</td>
              <td class="text-right">{{ number_format(floatval($d->VOLUME_BORONGAN), 3, ',', '.') }}</td>
              <td class="text-right">{{ number_format(floatval($d->UPAH_NOMINAL), 0, ',', '.') }}</td>
              <td class="text-right">{{ number_format(floatval($d->ADM_NOMINAL), 0, ',', '.') }}</td>
              <td class="text-right">{{ number_format(floatval($d->TOESLAG_NOMINAL), 0, ',', '.') }}</td>
              <td class="text-right">{{ number_format(floatval($d->LEMBUR_NOMINAL), 0, ',', '.') }}</td>
              <td class="text-right fw-bold">{{ number_format(floatval($d->JUMLAH_UPAH), 0, ',', '.') }}</td>
              <td style="font-size:9.5px;">{{ $d->KETERANGAN ?: '-' }}</td>
            </tr>
          @endforeach
        </tbody>
        <tfoot>
          <tr class="fw-bold" style="background:#f9f9f9;">
            <td colspan="2" class="text-center">TOTAL</td>
            <td class="text-center">{{ $sumGang }}</td>
            <td class="text-center">{{ $sumHarian ?: '-' }}</td>
            <td class="text-right">{{ number_format($sumBorongan, 3, ',', '.') }}</td>
            <td class="text-right">{{ number_format($sumUpah, 0, ',', '.') }}</td>
            <td class="text-right">{{ number_format($sumAdm, 0, ',', '.') }}</td>
            <td class="text-right">{{ number_format($sumToeslag, 0, ',', '.') }}</td>
            <td class="text-right">{{ number_format($sumLembur, 0, ',', '.') }}</td>
            <td class="text-right fs-12">{{ number_format($sumTotal, 0, ',', '.') }}</td>
            <td></td>
          </tr>
        </tfoot>
      </table>

      <!-- SUMMARY & TERBILANG -->
      <div class="summary-box">
        <div class="summary-row">
          <span class="fw-bold">Yang Harus dibayar Rp :</span>
          <span class="fw-bold" style="font-size:14px;">{{ number_format(floatval($upah->GRAND_TOTAL), 2, ',', '.') }}</span>
        </div>
        <div class="terbilang-text">
          TERBILANG : {{ $upah->TERBILANG }}
        </div>
      </div>

      <!-- TANDA TANGAN KOORDINATOR & KOPERASI -->
      <div class="signature-grid">
        <div class="signature-box">
          <div class="signature-title">
            Mengetahui :<br>
            KOORDINATOR UTARA / SELATAN
          </div>
          @if ($upah->STATUS === 'paid')
            <div class="stamp-lunas">L U N A S</div>
          @endif
          <div class="signature-name">{{ $upah->KOORDINATOR_NAMA ?: 'H. M. THOHIR, SE' }}</div>
        </div>

        <div class="signature-box">
          <div class="signature-title">
            Gresik, {{ date('d F Y', strtotime($upah->TGL_KEGIATAN_END ?? date('Y-m-d'))) }}<br>
            K.TKBM PELABUHAN GRESIK
          </div>
          <div class="signature-name">H. MARYONO</div>
        </div>
      </div>

    </section>
  </div>

</body>
</html>

