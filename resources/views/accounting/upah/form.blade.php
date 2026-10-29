@extends('layouts.app')

@section('title', $title ?? 'Form Perhitungan Upah TKBM')
@section('title_header', $title_header ?? 'Form Perhitungan Upah TKBM')
@section('title_parent', $title_parent ?? 'Upah & Kwitansi TKBM')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
          <i class="fal fa-file-invoice-dollar me-2 text-primary"></i> {{ $title_header ?? 'Form Perhitungan Upah & Kwitansi' }}
        </h5>
        <a href="{{ url('accounting/upah') }}" class="btn btn-sm btn-outline-secondary">
          <i class="fal fa-arrow-left me-1"></i> Kembali ke Daftar
        </a>
      </div>
      <div class="card-body">
        <form id="form_upah" class="needs-validation" novalidate>
          <input type="hidden" name="{{ $csrf_name }}" id="{{ $csrf_name }}" value="{{ $csrf_token }}">
          @if ($mode == 'edit')
            <input type="hidden" name="id_upah_tkbm" value="{{ $upah->ID_UPAH_TKBM }}">
          @endif

          <!-- SECTION 1: PEMILIHAN WORK ORDER & IDENTITAS -->
          <div class="border rounded p-3 mb-4 bg-light-subtle">
            <h6 class="fw-bold mb-3 text-primary"><i class="fal fa-ship me-1"></i> 1. Identitas Pekerjaan & Dokumen</h6>
            <div class="row g-3">
              <div class="col-md-6 col-sm-12">
                <label class="form-label required">Pilih Work Order (Status Done / Review)</label>
                @if ($mode == 'create')
                  <select class="form-select select2" name="id_work_order" id="id_work_order" required>
                    <option value="">-- Pilih Work Order Selesai --</option>
                    @foreach ($completedWorkorders as $w)
                      <option value="{{ $w->ID_WORK_ORDER }}"
                        data-client="{{ $w->client->NAMA ?? '-' }}"
                        data-kapal="{{ $w->kapal->NAMA ?? '-' }}"
                        data-dermaga="{{ $w->dermaga->NAMA ?? '-' }}"
                        data-komoditas="{{ $w->aktifitas->NAMA ?? '-' }}"
                        {{ (!empty($wo) && $wo->ID_WORK_ORDER == $w->ID_WORK_ORDER) ? 'selected' : '' }}>
                        {{ $w->NO_WORK_ORDER }} — {{ $w->kapal->NAMA ?? '-' }} ({{ $w->client->NAMA ?? '-' }})
                      </option>
                    @endforeach
                  </select>
                @else
                  <input type="hidden" name="id_work_order" value="{{ $upah->ID_WORK_ORDER }}">
                  <input type="text" class="form-control bg-light" readonly
                    value="{{ $upah->NO_WORK_ORDER }} — {{ $upah->NAMA_KAPAL }} ({{ $upah->NAMA_CLIENT }})">
                @endif
              </div>

              <div class="col-md-3 col-sm-6">
                <label class="form-label required">Nomor Dokumen Internal</label>
                <input type="text" class="form-control" name="no_dokumen" id="no_dokumen" required
                  value="{{ $mode == 'edit' ? $upah->NO_DOKUMEN : $autoNoDokumen }}">
              </div>

              <div class="col-md-3 col-sm-6">
                <label class="form-label required">Nomor Kwitansi Resmi</label>
                <input type="text" class="form-control fw-bold" name="no_kwitansi" id="no_kwitansi" required
                  placeholder="e.g. 1738/KTKBM.Tgl/2026"
                  value="{{ $mode == 'edit' ? $upah->NO_KWITANSI : $autoNoKwitansi }}">
              </div>

              <div class="col-md-3 col-sm-6">
                <label class="form-label required">Tanggal Mulai Kegiatan</label>
                <input type="date" class="form-control" name="tgl_kegiatan_start" id="tgl_kegiatan_start" required
                  value="{{ $mode == 'edit' ? $upah->TGL_KEGIATAN_START : ($pulled['tgl_start'] ?? date('Y-m-d')) }}">
              </div>

              <div class="col-md-3 col-sm-6">
                <label class="form-label required">Tanggal Selesai Kegiatan</label>
                <input type="date" class="form-control" name="tgl_kegiatan_end" id="tgl_kegiatan_end" required
                  value="{{ $mode == 'edit' ? $upah->TGL_KEGIATAN_END : ($pulled['tgl_end'] ?? date('Y-m-d')) }}">
              </div>

              <div class="col-md-3 col-sm-6">
                <label class="form-label required">Komoditas / Muatan</label>
                <input type="text" class="form-control" name="komoditas" id="komoditas" required
                  placeholder="e.g. B. BATU BARA atau BONGKAR ZA"
                  value="{{ $mode == 'edit' ? $upah->KOMODITAS : ($wo->NAMA_AKTIFITAS ?? 'B. BATU BARA') }}">
              </div>

              <div class="col-md-3 col-sm-6">
                <label class="form-label required">Status Dokumen</label>
                <select class="form-select" name="status" id="status" required>
                  <option value="draft" {{ ($mode == 'edit' && $upah->STATUS == 'draft') ? 'selected' : '' }}>Draft</option>
                  <option value="published" {{ ($mode == 'edit' && $upah->STATUS == 'published') ? 'selected' : 'selected' }}>Terbit (Siap Ditagihkan)</option>
                  @if ($mode == 'edit')
                    <option value="paid" {{ $upah->STATUS == 'paid' ? 'selected' : '' }}>Lunas</option>
                    <option value="cancelled" {{ $upah->STATUS == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                  @endif
                </select>
              </div>

              <div class="col-md-6 col-sm-12">
                <label class="form-label">Info Rekening Bank (Dicetak di Kwitansi)</label>
                <input type="text" class="form-control" name="rekening_bank" id="rekening_bank"
                  value="{{ $mode == 'edit' ? $upah->REKENING_BANK : '(BCA) 6100091044 / (MANDIRI) 1780068844444 AN. ERNAWATI/THOHIR' }}">
              </div>

              <div class="col-md-6 col-sm-12">
                <label class="form-label">Nama Pejabat / Koordinator Sektor</label>
                <input type="text" class="form-control" name="koordinator_nama" id="koordinator_nama"
                  value="{{ $mode == 'edit' ? $upah->KOORDINATOR_NAMA : 'H. M. THOHIR, SE' }}">
              </div>
            </div>
          </div>

          <!-- SECTION 2: TABEL RINCIAN PERHITUNGAN SHIFT / GANG -->
          <div class="border rounded p-3 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <div>
                <h6 class="fw-bold mb-0 text-primary"><i class="fal fa-table me-1"></i> 2. Rincian Harian Shift, Gang, & Komponen Upah</h6>
                <small class="text-muted">Kalkulasi baris = Upah + Administrasi + Toeslag + Lembur</small>
              </div>
              <button type="button" class="btn btn-sm btn-success" id="btn_add_row">
                <i class="fal fa-plus me-1"></i> Tambah Baris Shift
              </button>
            </div>

            <div class="table-responsive">
              <table class="table table-bordered table-sm align-middle mb-0" id="table_upah_details">
                <thead class="table-dark text-center fs-12">
                  <tr>
                    <th width="11%">Tanggal</th>
                    <th width="9%">Jam Kerja</th>
                    <th width="6%">Gang</th>
                    <th width="6%">Harian</th>
                    <th width="7%">Truk (Rit)</th>
                    <th width="9%">Borongan (Ton)</th>
                    <th width="11%">Upah (Rp)</th>
                    <th width="9%">Adm (Rp)</th>
                    <th width="9%">Toeslag (Rp)</th>
                    <th width="9%">Lembur (Rp)</th>
                    <th width="11%">Jumlah (Rp)</th>
                    <th width="14%">Keterangan</th>
                    <th width="4%"></th>
                  </tr>
                </thead>
                <tbody id="upah_rows_body">
                  @if ($mode == 'edit' && !empty($details))
                    @foreach ($details as $row)
                      <tr class="upah-row">
                        <td><input type="date" class="form-control form-control-sm" name="tgl_kerja[]" required value="{{ $row->TGL_KERJA }}"></td>
                        <td>
                          <select class="form-select form-select-sm" name="jam_kerja[]">
                            <option value="07 - 19" {{ $row->JAM_KERJA == '07 - 19' ? 'selected' : '' }}>07 - 19 (Siang)</option>
                            <option value="19 - 07" {{ $row->JAM_KERJA == '19 - 07' ? 'selected' : '' }}>19 - 07 (Malam)</option>
                          </select>
                        </td>
                        <td><input type="number" min="1" step="1" class="form-control form-control-sm text-center calc-trigger gang-field" name="jumlah_gang[]" required value="{{ $row->JUMLAH_GANG }}"></td>
                        <td><input type="number" min="0" step="1" class="form-control form-control-sm text-center calc-trigger harian-field" name="jumlah_harian[]" value="{{ $row->JUMLAH_HARIAN }}"></td>
                        <td><input type="number" min="0" step="1" class="form-control form-control-sm text-center calc-trigger truck-field" name="jumlah_truck[]" value="{{ $row->JUMLAH_TRUCK }}"></td>
                        <td><input type="number" min="0" step="0.001" class="form-control form-control-sm text-end calc-trigger vol-field" name="volume_borongan[]" required value="{{ floatval($row->VOLUME_BORONGAN) }}"></td>
                        <td><input type="number" min="0" step="1" class="form-control form-control-sm text-end calc-trigger upah-field" name="upah_nominal[]" required value="{{ floatval($row->UPAH_NOMINAL) }}"></td>
                        <td><input type="number" min="0" step="1" class="form-control form-control-sm text-end calc-trigger adm-field" name="adm_nominal[]" value="{{ floatval($row->ADM_NOMINAL) }}"></td>
                        <td><input type="number" min="0" step="1" class="form-control form-control-sm text-end calc-trigger toeslag-field" name="toeslag_nominal[]" value="{{ floatval($row->TOESLAG_NOMINAL) }}"></td>
                        <td><input type="number" min="0" step="1" class="form-control form-control-sm text-end calc-trigger lembur-field" name="lembur_nominal[]" value="{{ floatval($row->LEMBUR_NOMINAL) }}"></td>
                        <td class="text-end fw-bold row-total">Rp {{ ribuan($row->JUMLAH_UPAH) }}</td>
                        <td><input type="text" class="form-control form-control-sm" name="keterangan[]" placeholder="Catatan shift" value="{{ $row->KETERANGAN }}"></td>
                        <td class="text-center">
                          <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row p-1"><i class="fal fa-trash-alt"></i></button>
                        </td>
                      </tr>
                    @endforeach
                  @elseif ($mode == 'create' && !empty($pulled['suggested_rows']))
                    @foreach ($pulled['suggested_rows'] as $row)
                      <tr class="upah-row">
                        <td><input type="date" class="form-control form-control-sm" name="tgl_kerja[]" required value="{{ $row['tgl_kerja'] }}"></td>
                        <td>
                          <select class="form-select form-select-sm" name="jam_kerja[]">
                            <option value="07 - 19" {{ $row['jam_kerja'] == '07 - 19' ? 'selected' : '' }}>07 - 19 (Siang)</option>
                            <option value="19 - 07" {{ $row['jam_kerja'] == '19 - 07' ? 'selected' : '' }}>19 - 07 (Malam)</option>
                          </select>
                        </td>
                        <td><input type="number" min="1" step="1" class="form-control form-control-sm text-center calc-trigger gang-field" name="jumlah_gang[]" required value="{{ $row['jumlah_gang'] }}"></td>
                        <td><input type="number" min="0" step="1" class="form-control form-control-sm text-center calc-trigger harian-field" name="jumlah_harian[]" value="{{ $row['jumlah_harian'] ?? 0 }}"></td>
                        <td><input type="number" min="0" step="1" class="form-control form-control-sm text-center calc-trigger truck-field" name="jumlah_truck[]" value="{{ $row['jumlah_truck'] }}"></td>
                        <td><input type="number" min="0" step="0.001" class="form-control form-control-sm text-end calc-trigger vol-field" name="volume_borongan[]" required value="{{ $row['volume_borongan'] }}"></td>
                        <td><input type="number" min="0" step="1" class="form-control form-control-sm text-end calc-trigger upah-field" name="upah_nominal[]" required value="0"></td>
                        <td><input type="number" min="0" step="1" class="form-control form-control-sm text-end calc-trigger adm-field" name="adm_nominal[]" value="0"></td>
                        <td><input type="number" min="0" step="1" class="form-control form-control-sm text-end calc-trigger toeslag-field" name="toeslag_nominal[]" value="0"></td>
                        <td><input type="number" min="0" step="1" class="form-control form-control-sm text-end calc-trigger lembur-field" name="lembur_nominal[]" value="0"></td>
                        <td class="text-end fw-bold row-total">Rp 0</td>
                        <td><input type="text" class="form-control form-control-sm" name="keterangan[]" placeholder="Catatan shift" value="{{ $row['keterangan'] }}"></td>
                        <td class="text-center">
                          <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row p-1"><i class="fal fa-trash-alt"></i></button>
                        </td>
                      </tr>
                    @endforeach
                  @endif
                </tbody>
                <tfoot class="table-light fw-bold fs-12">
                  <tr>
                    <td colspan="2" class="text-center">TOTAL KESELURUHAN</td>
                    <td class="text-center" id="sum_gang">0 Gang</td>
                    <td class="text-center" id="sum_harian">0 Org</td>
                    <td class="text-center" id="sum_truck">0 Rit</td>
                    <td class="text-end" id="sum_volume">0,000 Ton</td>
                    <td class="text-end" id="sum_upah">Rp 0</td>
                    <td class="text-end" id="sum_adm">Rp 0</td>
                    <td class="text-end" id="sum_toeslag">Rp 0</td>
                    <td class="text-end" id="sum_lembur">Rp 0</td>
                    <td class="text-end text-primary fs-14" id="sum_grand_total">Rp 0</td>
                    <td colspan="2"></td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>

          <!-- SECTION 3: RINGKASAN PEMBAGIAN HAK BURUH VS KOPERASI -->
          <div class="row mb-4">
            <div class="col-md-6 col-sm-12">
              <div class="card border border-info mb-3">
                <div class="card-header bg-info-subtle py-2">
                  <h6 class="card-title mb-0 fs-13 fw-bold text-info"><i class="fal fa-file-certificate me-1"></i> Kalimat Terbilang Rupiah</h6>
                </div>
                <div class="card-body">
                  <p class="fst-italic fw-medium text-dark mb-0" id="terbilang_display">
                    {{ $mode == 'edit' ? $upah->TERBILANG : 'NOL RUPIAH' }}
                  </p>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label">Catatan Tambahan</label>
                <textarea class="form-control" name="catatan" rows="3" placeholder="Tulis catatan internal atau instruksi pembayaran...">{{ $mode == 'edit' ? $upah->CATATAN : '' }}</textarea>
              </div>
            </div>

            <div class="col-md-6 col-sm-12">
              <div class="card border border-primary">
                <div class="card-header bg-primary-subtle py-2">
                  <h6 class="card-title mb-0 fs-13 fw-bold text-primary"><i class="fal fa-balance-scale me-1"></i> Rincian Pembagian Alokasi (Dicetak di Kwitansi)</h6>
                </div>
                <div class="card-body">
                  <div class="mb-3">
                    <label class="form-label required">Bagian Upah TKBM (Hak Regu Buruh / Borongan + Lembur)</label>
                    <div class="input-group">
                      <span class="input-group-text">Rp</span>
                      <input type="number" min="0" step="1" class="form-control fw-bold text-end" name="alokasi_buruh" id="alokasi_buruh" required
                        value="{{ $mode == 'edit' ? floatval($upah->ALOKASI_BURUH) : 0 }}">
                    </div>
                    <small class="text-muted">Hak upah kerja yang diteruskan ke mandor/gang buruh.</small>
                  </div>

                  <div class="mb-3">
                    <label class="form-label required">Bagian Koperasi TKBM (Dana Adm. + Kesra Koperasi)</label>
                    <div class="input-group">
                      <span class="input-group-text">Rp</span>
                      <input type="number" min="0" step="1" class="form-control fw-bold text-end" name="alokasi_koperasi" id="alokasi_koperasi" required
                        value="{{ $mode == 'edit' ? floatval($upah->ALOKASI_KOPERASI) : 0 }}">
                    </div>
                    <small class="text-muted">Hak potongan biaya administrasi dinas dan kesejahteraan anggota.</small>
                  </div>

                  <hr class="my-2">
                  <div class="d-flex justify-content-between align-items-center">
                    <span class="fw-bold fs-14">TOTAL YANG HARUS DIBAYAR:</span>
                    <span class="fw-bold fs-16 text-success" id="grand_total_badge">Rp 0</span>
                  </div>
                  <div id="alokasi_balance_badge" class="badge bg-success-subtle text-success border border-success p-2 mt-2 w-100 fs-12 text-center">
                    <i class="fal fa-check-circle me-1"></i> Alokasi Seimbang: Buruh + Koperasi = Total Tagihan
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- ACTIONS -->
          <div class="d-flex justify-content-end gap-2">
            <a href="{{ url('accounting/upah') }}" class="btn btn-light">Batal</a>
            <button type="submit" class="btn btn-primary" id="btn_submit">
              <i class="fal fa-save me-1"></i> Simpan Dokumen Upah & Kwitansi
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  const mode = "{{ $mode }}";
  const upahRoute = "{{ url('accounting/upah') }}/";

  $(document).ready(function() {
    if (mode === 'create') {
      $('#id_work_order').select2({
        theme: 'bootstrap-5',
        placeholder: '-- Pilih Work Order --',
        width: '100%'
      });

      // Event listener saat memilih WO di create mode
      $('#id_work_order').on('change', function() {
        let woId = $(this).val();
        if (woId) {
          load_wo_data(woId);
        }
      });
    }

    recalculate();
  });

  function load_wo_data(woId) {
    Swal.fire({
      title: 'Memuat Data Lapangan...',
      text: 'Mengambil riwayat shift dan tonase dari Work Order...',
      allowOutsideClick: false,
      didOpen: () => { Swal.showLoading(); }
    });

    let data = { wo_id: woId };
    data[csrf_name] = $('#' + csrf_name).val();

    $.post(upahRoute + 'pull_wo_data', data, function(res) {
      Swal.close();
      if (res.status) {
        let d = res;
        $('#tgl_kegiatan_start').val(d.tgl_start);
        $('#tgl_kegiatan_end').val(d.tgl_end);
        if (d.workorder && d.workorder.NAMA_AKTIFITAS) {
          $('#komoditas').val(d.workorder.NAMA_AKTIFITAS);
        }

        // Render rows
        let html = '';
        if (d.suggested_rows && d.suggested_rows.length > 0) {
          d.suggested_rows.forEach(function(r) {
            html += generate_row_html(r.tgl_kerja, r.jam_kerja, r.jumlah_gang, r.jumlah_harian || 0, r.jumlah_truck, r.volume_borongan, 0, 0, 0, 0, r.keterangan);
          });
        } else {
          html = generate_row_html(d.tgl_start, '07 - 19', 1, 0, 0, 0, 0, 0, 0, 0, 'Kegiatan Lapangan');
        }
        $('#upah_rows_body').html(html);
        recalculate();
      } else {
        Swal.fire('Gagal!', res.msg, 'error');
      }
    }, 'json').fail(function() {
      Swal.close();
      Swal.fire('Error', 'Gagal menghubungi server', 'error');
    });
  }

  function generate_row_html(tgl = '', jam = '07 - 19', gang = 1, harian = 0, truck = 0, vol = 0, upah = 0, adm = 0, toeslag = 0, lembur = 0, ket = '') {
    let jam07 = (jam === '07 - 19') ? 'selected' : '';
    let jam19 = (jam === '19 - 07') ? 'selected' : '';
    let subtotal = parseFloat(upah) + parseFloat(adm) + parseFloat(toeslag) + parseFloat(lembur);

    return `
      <tr class="upah-row">
        <td><input type="date" class="form-control form-control-sm" name="tgl_kerja[]" required value="${tgl}"></td>
        <td>
          <select class="form-select form-select-sm" name="jam_kerja[]">
            <option value="07 - 19" ${jam07}>07 - 19 (Siang)</option>
            <option value="19 - 07" ${jam19}>19 - 07 (Malam)</option>
          </select>
        </td>
        <td><input type="number" min="1" step="1" class="form-control form-control-sm text-center calc-trigger gang-field" name="jumlah_gang[]" required value="${gang}"></td>
        <td><input type="number" min="0" step="1" class="form-control form-control-sm text-center calc-trigger harian-field" name="jumlah_harian[]" value="${harian}"></td>
        <td><input type="number" min="0" step="1" class="form-control form-control-sm text-center calc-trigger truck-field" name="jumlah_truck[]" value="${truck}"></td>
        <td><input type="number" min="0" step="0.001" class="form-control form-control-sm text-end calc-trigger vol-field" name="volume_borongan[]" required value="${vol}"></td>
        <td><input type="number" min="0" step="1" class="form-control form-control-sm text-end calc-trigger upah-field" name="upah_nominal[]" required value="${upah}"></td>
        <td><input type="number" min="0" step="1" class="form-control form-control-sm text-end calc-trigger adm-field" name="adm_nominal[]" value="${adm}"></td>
        <td><input type="number" min="0" step="1" class="form-control form-control-sm text-end calc-trigger toeslag-field" name="toeslag_nominal[]" value="${toeslag}"></td>
        <td><input type="number" min="0" step="1" class="form-control form-control-sm text-end calc-trigger lembur-field" name="lembur_nominal[]" value="${lembur}"></td>
        <td class="text-end fw-bold row-total">Rp ${number_format(subtotal)}</td>
        <td><input type="text" class="form-control form-control-sm" name="keterangan[]" placeholder="Catatan shift" value="${ket}"></td>
        <td class="text-center">
          <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row p-1"><i class="fal fa-trash-alt"></i></button>
        </td>
      </tr>
    `;
  }

  $('#btn_add_row').on('click', function() {
    let lastDate = $('#upah_rows_body tr:last-child input[name="tgl_kerja[]"]').val() || $('#tgl_kegiatan_start').val() || '';
    $('#upah_rows_body').append(generate_row_html(lastDate, '07 - 19', 1, 0, 0, 0, 0, 0, 0, 0, ''));
    recalculate();
  });

  $(document).on('click', '.btn-remove-row', function() {
    if ($('#upah_rows_body tr').length > 1) {
      $(this).closest('tr').remove();
      recalculate();
    } else {
      toast_warning('Minimal satu baris kegiatan harus diisi');
    }
  });

  $(document).on('input change', '.calc-trigger', function() {
    recalculate();
  });

  $(document).on('input change', '#alokasi_buruh, #alokasi_koperasi', function() {
    check_allocation_balance();
  });

  function check_allocation_balance() {
    let buruh = parseFloat($('#alokasi_buruh').val()) || 0;
    let koperasi = parseFloat($('#alokasi_koperasi').val()) || 0;
    let grandTotal = parseFloat($('#sum_grand_total').text().replace(/[^\d]/g, '')) || 0;
    let diff = Math.abs((buruh + koperasi) - grandTotal);
    let $badge = $('#alokasi_balance_badge');

    if (diff > 1) {
      $badge.removeClass('bg-success-subtle text-success border-success')
            .addClass('bg-danger-subtle text-danger border-danger')
            .html(`<i class="fal fa-exclamation-triangle me-1"></i> Alokasi Tidak Seimbang! Selisih: Rp ${number_format(diff)} (Buruh + Koperasi ≠ Total Tagihan)`);
    } else {
      $badge.removeClass('bg-danger-subtle text-danger border-danger')
            .addClass('bg-success-subtle text-success border-success')
            .html(`<i class="fal fa-check-circle me-1"></i> Alokasi Seimbang: Buruh + Koperasi = Total Tagihan`);
    }
  }

  function recalculate() {
    let sumGang = 0;
    let sumHarian = 0;
    let sumTruck = 0;
    let sumVol = 0;
    let sumUpah = 0;
    let sumAdm = 0;
    let sumToeslag = 0;
    let sumLembur = 0;
    let grandTotal = 0;

    $('.upah-row').each(function() {
      let gang = parseInt($(this).find('.gang-field').val()) || 0;
      let harian = parseInt($(this).find('.harian-field').val()) || 0;
      let truck = parseInt($(this).find('.truck-field').val()) || 0;
      let vol = parseFloat($(this).find('.vol-field').val()) || 0;
      let upah = parseFloat($(this).find('.upah-field').val()) || 0;
      let adm = parseFloat($(this).find('.adm-field').val()) || 0;
      let toeslag = parseFloat($(this).find('.toeslag-field').val()) || 0;
      let lembur = parseFloat($(this).find('.lembur-field').val()) || 0;

      let rowTotal = upah + adm + toeslag + lembur;
      $(this).find('.row-total').text('Rp ' + number_format(rowTotal));

      sumGang += gang;
      sumHarian += harian;
      sumTruck += truck;
      sumVol += vol;
      sumUpah += upah;
      sumAdm += adm;
      sumToeslag += toeslag;
      sumLembur += lembur;
      grandTotal += rowTotal;
    });

    $('#sum_gang').text(sumGang + ' Gang');
    $('#sum_harian').text(sumHarian + ' Org');
    $('#sum_truck').text(sumTruck + ' Rit');
    $('#sum_volume').text(sumVol.toLocaleString('id-ID', { minimumFractionDigits: 3, maximumFractionDigits: 3 }) + ' Ton');
    $('#sum_upah').text('Rp ' + number_format(sumUpah));
    $('#sum_adm').text('Rp ' + number_format(sumAdm));
    $('#sum_toeslag').text('Rp ' + number_format(sumToeslag));
    $('#sum_lembur').text('Rp ' + number_format(sumLembur));
    $('#sum_grand_total').text('Rp ' + number_format(grandTotal));
    $('#grand_total_badge').text('Rp ' + number_format(grandTotal));

    // Auto-update alokasi jika masih default atau saat create
    if (mode === 'create') {
      let alokasiBuruh = sumUpah + sumLembur;
      let alokasiKoperasi = grandTotal - alokasiBuruh;
      $('#alokasi_buruh').val(alokasiBuruh);
      $('#alokasi_koperasi').val(alokasiKoperasi);
    }

    check_allocation_balance();

    if (grandTotal > 0) {
      $('#terbilang_display').text(terbilang_js(grandTotal).toUpperCase() + ' RUPIAH');
    } else {
      $('#terbilang_display').text('NOL RUPIAH');
    }
  }

  function number_format(num) {
    return Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
  }

  function terbilang_js(a) {
    var bilangan = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];
    var kalimat = "";
    var bagi = 0;

    a = Math.floor(Math.abs(a));
    if (a < 12) {
      kalimat = " " + bilangan[a];
    } else if (a < 20) {
      kalimat = terbilang_js(a - 10) + " Belas";
    } else if (a < 100) {
      bagi = Math.floor(a / 10);
      kalimat = terbilang_js(bagi) + " Puluh" + terbilang_js(a % 10);
    } else if (a < 200) {
      kalimat = " Seratus" + terbilang_js(a - 100);
    } else if (a < 1000) {
      bagi = Math.floor(a / 100);
      kalimat = terbilang_js(bagi) + " Ratus" + terbilang_js(a % 100);
    } else if (a < 2000) {
      kalimat = " Seribu" + terbilang_js(a - 1000);
    } else if (a < 1000000) {
      bagi = Math.floor(a / 1000);
      kalimat = terbilang_js(bagi) + " Ribu" + terbilang_js(a % 1000);
    } else if (a < 1000000000) {
      bagi = Math.floor(a / 1000000);
      kalimat = terbilang_js(bagi) + " Juta" + terbilang_js(a % 1000000);
    } else if (a < 1000000000000) {
      bagi = Math.floor(a / 1000000000);
      kalimat = terbilang_js(bagi) + " Miliar" + terbilang_js(a % 1000000000);
    } else if (a < 1000000000000000) {
      bagi = Math.floor(a / 1000000000000);
      kalimat = terbilang_js(bagi) + " Triliun" + terbilang_js(a % 1000000000000);
    }
    return kalimat.trim();
  }

  // Form Submission
  $('#form_upah').on('submit', function(e) {
    e.preventDefault();

    if ($('.upah-row').length === 0) {
      toast_warning('Minimal satu baris rincian kegiatan harus diisi');
      return;
    }

    let buruh = parseFloat($('#alokasi_buruh').val()) || 0;
    let koperasi = parseFloat($('#alokasi_koperasi').val()) || 0;
    let total = parseFloat($('#sum_grand_total').text().replace(/[^\d]/g, '')) || 0;
    if (Math.abs((buruh + koperasi) - total) > 1) {
      Swal.fire({
        icon: 'warning',
        title: 'Alokasi Dana Tidak Seimbang!',
        text: 'Total Bagian Buruh (Rp ' + number_format(buruh) + ') + Bagian Koperasi (Rp ' + number_format(koperasi) + ') harus sama dengan Total Tagihan (Rp ' + number_format(total) + '). Selisih: Rp ' + number_format(Math.abs((buruh + koperasi) - total))
      });
      return;
    }

    let url = (mode === 'create') ? (upahRoute + 'store') : (upahRoute + 'update');
    let formData = $(this).serialize();

    $('#btn_submit').prop('disabled', true).html('<i class="fal fa-spinner fa-spin me-1"></i> Menyimpan...');

    $.post(url, formData, function(res) {
      $('#btn_submit').prop('disabled', false).html('<i class="fal fa-save me-1"></i> Simpan Dokumen Upah & Kwitansi');
      if (res.status) {
        Swal.fire({
          icon: 'success',
          title: 'Berhasil!',
          text: res.msg,
          timer: 1500,
          showConfirmButton: false
        }).then(() => {
          let targetUrl = (mode === 'create' && res.id) ? (upahRoute + 'detail/' + res.id) : "{{ url('accounting/upah') }}";
          window.location.href = targetUrl;
        });
      } else {
        Swal.fire('Gagal!', res.msg, 'error');
      }
    }, 'json').fail(function() {
      $('#btn_submit').prop('disabled', false).html('<i class="fal fa-save me-1"></i> Simpan Dokumen Upah & Kwitansi');
      Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
    });
  });
</script>
@endpush

