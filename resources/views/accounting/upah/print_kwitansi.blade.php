<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $title ?? 'Cetak Kwitansi Resmi K.TKBM' }}</title>

  <style>
    * { box-sizing: border-box; font-family: 'Times New Roman', Times, serif; }
    body { background: #e8e8e8; margin: 0; padding: 20px; color: #000; font-size: 13px; }

    .sheet-outer.kwitansi .sheet {
      width: 275mm;
      min-height: 185mm;
      background: #fdfdfd;
      margin: 0 auto;
      box-shadow: 0 0 10px rgba(0,0,0,0.15);
      padding: 12mm 16mm;
      position: relative;
      border: 1px solid #ddd;
    }

    @page {
      size: A4 landscape;
      margin: 10mm;
    }

    @media print {
      body { background: none; padding: 0; }
      .sheet-outer.kwitansi .sheet { margin: 0; box-shadow: none; border: none; width: 100%; padding: 10mm 15mm; }
      .no-print { display: none !important; }
    }

    .print-controls { text-align: center; margin-bottom: 20px; font-family: Arial, sans-serif; }
    .btn-print {
      background: #28a745; color: white; border: none; padding: 8px 18px; font-size: 14px;
      border-radius: 4px; cursor: pointer; font-weight: bold;
    }
    .btn-print:hover { background: #218838; }

    /* Kop Surat */
    .kop-wrapper { display: flex; align-items: center; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 12px; }
    .kop-logo { width: 75px; height: 75px; margin-right: 15px; }
    .kop-content { flex: 1; text-align: center; line-height: 1.25; }
    .kop-title { font-size: 16px; font-weight: bold; letter-spacing: 0.5px; }
    .kop-city { font-size: 18px; font-weight: bold; margin-bottom: 3px; }
    .kop-address { font-size: 10.5px; font-family: Arial, sans-serif; }
    .kop-legal { font-size: 10px; font-family: Arial, sans-serif; }
    .kop-unit { font-size: 11px; font-weight: bold; }

    /* Sub Kop / SP & No Kwitansi */
    .no-kwitansi-row { display: flex; justify-content: space-between; font-size: 11px; margin-bottom: 15px; font-weight: bold; }

    /* Isi Kwitansi */
    .kwitansi-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 13px; }
    .kwitansi-table td { padding: 4px 0; vertical-align: top; }
    .kwitansi-table td.field-label { width: 230px; }
    .kwitansi-table td.colon { width: 15px; text-align: center; }

    .terbilang-box {
      background: #f2f2f2;
      border-top: 1px dashed #666;
      border-bottom: 1px dashed #666;
      padding: 5px 8px;
      font-weight: bold;
      font-style: italic;
      font-size: 12px;
      letter-spacing: 0.5px;
    }

    .breakdown-list { margin: 4px 0 4px 20px; line-height: 1.6; }

    /* Bottom Info & Signatures */
    .bottom-grid { display: flex; justify-content: space-between; align-items: flex-end; margin-top: 15px; }
    .bank-info { font-size: 11px; line-height: 1.4; width: 55%; }
    .bank-info strong { font-size: 11.5px; }
    .note-ceq { font-size: 10px; font-style: italic; margin-top: 4px; }

    .nominal-badge {
      display: inline-block;
      border: 2px solid #000;
      background: #fcfcfc;
      padding: 6px 14px;
      font-size: 16px;
      font-weight: bold;
      margin-top: 10px;
      letter-spacing: 1px;
    }

    .signature-area { width: 40%; text-align: center; position: relative; }
    .sign-date { font-size: 12px; margin-bottom: 5px; }
    .sign-role { font-size: 11px; font-weight: bold; margin-bottom: 60px; line-height: 1.2; }
    .sign-name { font-size: 12px; font-weight: bold; text-decoration: underline; }
    .sign-sp { font-size: 10px; }

    /* Stempel Cap Lunas Bulat */
    .round-stamp-lunas {
      position: absolute;
      top: 25px;
      left: 15px;
      width: 105px;
      height: 105px;
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
      opacity: 0.85;
      pointer-events: none;
      line-height: 1.2;
    }
  </style>
</head>
<body>

  <div class="print-controls no-print">
    <button class="btn-print" onclick="window.print()">
      🖨️ Cetak Kwitansi Resmi K.TKBM
    </button>
  </div>

  <div class="sheet-outer kwitansi">
    <section class="sheet">

      <!-- KOP KOPERASI TKBM PELABUHAN GRESIK -->
      <div class="kop-wrapper">
        @php
          $logoSm = function_exists('get_app_setting') ? get_app_setting('logo_sm', 'assets/images/logo_sm_1765448314.png') : 'assets/images/logo_sm_1765448314.png';
        @endphp
        @if (!empty($logoSm) && file_exists(public_path($logoSm)))
          <img src="{{ asset($logoSm) }}" class="kop-logo" alt="Logo KTKBM" style="object-fit:contain;">
        @else
          <svg class="kop-logo" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="50" cy="50" r="46" stroke="#0056b3" stroke-width="4"/>
            <circle cx="50" cy="50" r="38" stroke="#0056b3" stroke-width="1.5" stroke-dasharray="3 3"/>
            <path d="M50 20 L58 36 L76 38 L62 50 L66 68 L50 58 L34 68 L38 50 L24 38 L42 36 Z" fill="#0056b3" opacity="0.85"/>
            <text x="50" y="85" text-anchor="middle" font-size="9" font-weight="bold" fill="#0056b3" font-family="Arial">TKBM GRESIK</text>
          </svg>
        @endif

        <div class="kop-content">
          <div class="kop-title">KOPERASI TENAGA KERJA BONGKAR MUAT</div>
          <div class="kop-city">PELABUHAN GRESIK</div>
          <div class="kop-address">Jl. Yos Sudarso Gresik Kode Pos : 61114 &nbsp; Telp. 3984273</div>
          <div class="kop-legal">Badan Hukum Nomor : 6678 / BH / II / 1990 Tanggal 14 Juni 1990</div>
          <div class="kop-unit">UNIT USAHA PENGERAH JASA TKBM PELABUHAN GRESIK SEKTOR UTARA</div>
        </div>
      </div>

      <!-- NO SP & NO KWITANSI -->
      <div class="no-kwitansi-row">
        <span>SP. No. : 20/K.KTKBM.X/Gsk/2016</span>
        <span style="font-size:13px; font-weight:bold;">NO. {{ $upah->NO_KWITANSI ?: '...../KTKBM.Tgl/'.date('Y') }}</span>
      </div>

      <!-- TABEL ISI KWITANSI -->
      <table class="kwitansi-table">
        <tr>
          <td class="field-label">Telah terima dari</td>
          <td class="colon">:</td>
          <td><strong>{{ strtoupper($upah->NAMA_CLIENT ?: 'PT. BERKAH MAKMUR SEJAHTERA') }}</strong></td>
        </tr>
        <tr>
          <td class="field-label">Banyaknya Uang</td>
          <td class="colon">:</td>
          <td>
            <div class="terbilang-box">{{ strtoupper($upah->TERBILANG ?: 'NOL RUPIAH') }}</div>
          </td>
        </tr>
        <tr>
          <td class="field-label">Untuk membayar Upah kegiatan Bongkar / Muat barang dari kapal</td>
          <td class="colon">:</td>
          <td><strong>{{ strtoupper($upah->NAMA_KAPAL ?: 'BG. SATRIA LAUT 346') }}</strong></td>
        </tr>
        <tr>
          <td class="field-label">Tanggal Kegiatan</td>
          <td class="colon">:</td>
          <td>{{ date('d F Y', strtotime($upah->TGL_KEGIATAN_START ?? date('Y-m-d'))) }} &nbsp; s/d &nbsp; {{ date('d F Y', strtotime($upah->TGL_KEGIATAN_END ?? date('Y-m-d'))) }}</td>
        </tr>
        <tr>
          <td class="field-label">Jenis barang bongkar/muat</td>
          <td class="colon">:</td>
          <td><strong>{{ strtoupper($upah->KOMODITAS ?: 'B. BATU BARA') }}</strong></td>
        </tr>
        <tr>
          <td class="field-label">Jumlah Volume / berat barang</td>
          <td class="colon">:</td>
          <td><strong>{{ number_format(floatval($upah->TOTAL_VOLUME), 3, ',', '.') }} TON</strong></td>
        </tr>
        <tr>
          <td colspan="3" style="padding-top:6px;">
            Dengan rincian bagian-bagian upah sebagai berikut :
            <div class="breakdown-list">
              <div>- Bagian Upah TKBM ( borongan / harian ) + 0 Jam lembur &nbsp;: &nbsp;<strong>Rp {{ ribuan($upah->ALOKASI_BURUH) }}</strong></div>
              <div>- Bagian Koperasi TKBM ( dana Adm. + Kesra TKBM ) &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: &nbsp;<strong>Rp {{ ribuan($upah->ALOKASI_KOPERASI) }}</strong></div>
            </div>
          </td>
        </tr>
      </table>

      <!-- FOOTER: REKENING & TANDA TANGAN -->
      <div class="bottom-grid">
        <div class="bank-info">
          <div><strong>NO REK: {{ $upah->REKENING_BANK ?: '(BCA) 6100091044 / (MANDIRI) 1780068844444 AN. ERNAWATI/THOHIR' }}</strong></div>
          <div class="note-ceq">Pembayaran dengan Ceq dianggap syah jika dapat diuangkan</div>

          <div class="nominal-badge">
            Jumlah Rp. &nbsp;{{ ribuan($upah->GRAND_TOTAL) }}
          </div>
        </div>

        <div class="signature-area">
          <div class="sign-date">Gresik, &nbsp;{{ date('d F Y', strtotime($upah->TGL_KEGIATAN_END ?? date('Y-m-d'))) }}</div>
          <div class="sign-role">
            KOPERASI TKBM PELABUHAN GRESIK<br>
            SIE KOORDINATOR SEKTOR UTARA / SELATAN
          </div>

          @if ($upah->STATUS === 'paid')
            <div class="round-stamp-lunas">
              <span>KTKBM PELABUHAN GRESIK</span>
              <span style="font-size:13px; font-weight:bold; margin:2px 0;">LUNAS</span>
              <span>Rp {{ ribuan($upah->GRAND_TOTAL) }}</span>
            </div>
          @endif

          <div class="sign-name">{{ $upah->KOORDINATOR_NAMA ?: 'H. M THOHIR SE' }}</div>
          <div class="sign-sp">20/K.KTKBM.X/Gsk/{{ date('Y') }}</div>
        </div>
      </div>

    </section>
  </div>

</body>
</html>

