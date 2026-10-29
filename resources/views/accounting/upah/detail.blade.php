@extends('layouts.app')

@section('title', $title ?? 'Detail Upah & Kwitansi TKBM')
@section('title_header', $title_header ?? 'Detail Upah & Kwitansi TKBM')
@section('title_parent', $title_parent ?? 'Upah & Kwitansi TKBM')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <div>
          <h5 class="card-title mb-0">
            <i class="fal fa-receipt text-primary me-2"></i> {{ $upah->NO_KWITANSI ?: $upah->NO_DOKUMEN }}
          </h5>
          <small class="text-muted">Nomor Dokumen Internal: <strong>{{ $upah->NO_DOKUMEN }}</strong> | Ref. WO: <strong>{{ $upah->NO_WORK_ORDER }}</strong></small>
        </div>
        <div class="d-flex gap-2">
          <a href="{{ url('accounting/upah') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fal fa-arrow-left me-1"></i> Kembali
          </a>

          <!-- DROPDOWN CETAK BERKAS RESMI (3 DOKUMEN) -->
          <div class="btn-group">
            <button type="button" class="btn btn-sm btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="fal fa-print me-1"></i> Cetak Berkas Resmi
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow">
              <li><h6 class="dropdown-header">Pilih Formulir Cetak:</h6></li>
              <li>
                <a class="dropdown-item" href="{{ url('accounting/upah/print_perhitungan/' . $upah->ID_UPAH_TKBM) }}" target="_blank">
                  <i class="fal fa-file-invoice text-primary me-2"></i> 1. Lembar Perhitungan Upah TKBM
                </a>
              </li>
              <li>
                <a class="dropdown-item" href="{{ url('accounting/upah/print_tally/' . $upah->ID_UPAH_TKBM) }}" target="_blank">
                  <i class="fal fa-truck text-info me-2"></i> 2. Rekap Tally Truk & Tonase
                </a>
              </li>
              <li>
                <a class="dropdown-item" href="{{ url('accounting/upah/print_kwitansi/' . $upah->ID_UPAH_TKBM) }}" target="_blank">
                  <i class="fal fa-receipt text-success me-2"></i> 3. Kwitansi Resmi K.TKBM Pelabuhan Gresik
                </a>
              </li>
            </ul>
          </div>

          @if (!in_array($upah->STATUS, ['paid', 'cancelled']) && !empty($permission_update))
            <a href="{{ url('accounting/upah/edit/' . $upah->ID_UPAH_TKBM) }}" class="btn btn-sm btn-warning">
              <i class="fal fa-pen me-1"></i> Edit
            </a>
            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modal_bayar">
              <i class="fal fa-check-circle me-1"></i> Konfirmasi Lunas
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger" id="btn_cancel_doc">
              <i class="fal fa-ban me-1"></i> Batalkan Dokumen
            </button>
          @endif

          @if (empty($upah->REF_INVOICE_ID))
            <button type="button" class="btn btn-sm btn-outline-primary" id="btn_bridge_invoice">
              <i class="fal fa-file-invoice me-1"></i> Generate Invoice Komersial
            </button>
          @else
            <a href="{{ url('accounting/invoice/detail/' . $upah->REF_INVOICE_ID) }}" class="btn btn-sm btn-outline-info">
              <i class="fal fa-external-link me-1"></i> Lihat Invoice Komersial
            </a>
          @endif
        </div>
      </div>

      <div class="card-body">
        <!-- STATUS WATERMARK / BADGE -->
        <div class="d-flex justify-content-between align-items-center mb-4 p-3 rounded {{ $upah->STATUS === 'paid' ? 'bg-success-subtle border border-success' : 'bg-light' }}">
          <div>
            <span class="fs-12 text-muted text-uppercase d-block">Status Pembayaran</span>
            @if ($upah->STATUS === 'paid')
              <span class="badge bg-success fs-14"><i class="fal fa-check-double me-1"></i> LUNAS</span>
              <span class="text-success ms-2 fw-medium">Tgl Pelunasan: {{ $upah->TGL_BAYAR_INDO ?? '-' }}</span>
            @elseif ($upah->STATUS === 'published')
              <span class="badge bg-info fs-14">TERBIT (MENUNGGU PEMBAYARAN)</span>
            @elseif ($upah->STATUS === 'draft')
              <span class="badge bg-secondary fs-14">DRAFT</span>
            @else
              <span class="badge bg-dark fs-14">{{ strtoupper($upah->STATUS) }}</span>
            @endif
          </div>
          <div class="text-end">
            <span class="fs-12 text-muted text-uppercase d-block">Total Nilai Tagihan Upah</span>
            <h3 class="mb-0 text-primary fw-bold">Rp {{ ribuan($upah->GRAND_TOTAL) }}</h3>
          </div>
        </div>

        <!-- INFO HEADER GRID -->
        <div class="row g-3 mb-4">
          <div class="col-md-4">
            <div class="p-3 border rounded h-100 bg-white">
              <h6 class="fw-bold text-muted mb-2"><i class="fal fa-building me-1"></i> Informasi PBM / Client</h6>
              <h5 class="mb-1 text-dark">{{ $upah->NAMA_CLIENT ?? '-' }}</h5>
              <p class="text-muted fs-12 mb-1">{!! nl2br(e($upah->ALAMAT_CLIENT ?? '-')) !!}</p>
              <p class="text-muted fs-12 mb-0">Kontak: {{ $upah->KONTAK_CLIENT ?? '-' }}</p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="p-3 border rounded h-100 bg-white">
              <h6 class="fw-bold text-muted mb-2"><i class="fal fa-ship me-1"></i> Kapal & Lokasi Dermaga</h6>
              <h5 class="mb-1 text-primary">{{ $upah->NAMA_KAPAL ?? '-' }}</h5>
              <p class="text-muted fs-12 mb-1">Dermaga: <strong>{{ $upah->NAMA_DERMAGA ?? '-' }}</strong> ({{ $upah->NAMA_PELABUHAN ?? 'Pelabuhan Gresik' }})</p>
              <p class="text-muted fs-12 mb-0">Komoditas: <strong>{{ $upah->KOMODITAS ?? '-' }}</strong></p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="p-3 border rounded h-100 bg-white">
              <h6 class="fw-bold text-muted mb-2"><i class="fal fa-calendar-alt me-1"></i> Periode & Rekap Fisik</h6>
              <p class="mb-1 fs-13">Periode: <strong>{{ $upah->TGL_START_INDO ?? '-' }} s/d {{ $upah->TGL_END_INDO ?? '-' }}</strong></p>
              <p class="mb-1 fs-13">Total Ritase Truk: <span class="badge bg-primary fs-12">{{ intval($upah->TOTAL_TRUCK) }} Truck</span></p>
              <p class="mb-0 fs-13">Total Volume Muatan: <strong class="text-success">{{ number_format(floatval($upah->TOTAL_VOLUME), 3, ',', '.') }} Ton (MT)</strong></p>
            </div>
          </div>
        </div>

        <!-- TABEL RINCIAN PERHITUNGAN UPAH -->
        <h6 class="fw-bold text-dark mb-2"><i class="fal fa-list-alt me-1"></i> Rincian Perhitungan Shift, Gang, & Komponen Upah</h6>
        <div class="table-responsive mb-4">
          <table class="table table-bordered table-sm align-middle mb-0">
            <thead class="table-dark text-center fs-12">
              <tr>
                <th width="4%">No</th>
                <th>Tanggal</th>
                <th>Jam Kerja</th>
                <th>Gang</th>
                <th>Harian</th>
                <th>Truk</th>
                <th>Borongan (Ton)</th>
                <th>Upah (Rp)</th>
                <th>Administrasi (Rp)</th>
                <th>Toeslag (Rp)</th>
                <th>Lembur (Rp)</th>
                <th>Jumlah Upah (Rp)</th>
                <th>Keterangan</th>
              </tr>
            </thead>
            <tbody class="fs-13">
              @php $no = 1; @endphp
              @foreach ($details as $d)
                <tr>
                  <td class="text-center">{{ $no++ }}</td>
                  <td class="text-center">{{ date('d/m/Y', strtotime($d->TGL_KERJA)) }}</td>
                  <td class="text-center">{{ $d->JAM_KERJA }}</td>
                  <td class="text-center">{{ $d->JUMLAH_GANG }}</td>
                  <td class="text-center">{{ $d->JUMLAH_HARIAN ?: '-' }}</td>
                  <td class="text-center">{{ $d->JUMLAH_TRUCK }}</td>
                  <td class="text-end">{{ number_format(floatval($d->VOLUME_BORONGAN), 3, ',', '.') }}</td>
                  <td class="text-end">{{ ribuan($d->UPAH_NOMINAL) }}</td>
                  <td class="text-end">{{ ribuan($d->ADM_NOMINAL) }}</td>
                  <td class="text-end">{{ ribuan($d->TOESLAG_NOMINAL) }}</td>
                  <td class="text-end">{{ ribuan($d->LEMBUR_NOMINAL) }}</td>
                  <td class="text-end fw-bold">Rp {{ ribuan($d->JUMLAH_UPAH) }}</td>
                  <td>{{ $d->KETERANGAN ?: '-' }}</td>
                </tr>
              @endforeach
            </tbody>
            <tfoot class="table-light fw-bold fs-12">
              <tr>
                <td colspan="3" class="text-center">TOTAL KESELURUHAN</td>
                <td class="text-center">{{ $details ? array_sum(array_column($details, 'JUMLAH_GANG')) : 0 }} Gang</td>
                <td class="text-center">{{ $details ? array_sum(array_column($details, 'JUMLAH_HARIAN')) : 0 }} Org</td>
                <td class="text-center">{{ $upah->TOTAL_TRUCK }} Truck</td>
                <td class="text-end">{{ number_format(floatval($upah->TOTAL_VOLUME), 3, ',', '.') }} Ton</td>
                <td class="text-end">Rp {{ ribuan($upah->SUBTOTAL_UPAH) }}</td>
                <td class="text-end">Rp {{ ribuan($upah->TOTAL_ADM) }}</td>
                <td class="text-end">Rp {{ ribuan($upah->TOTAL_TOESLAG) }}</td>
                <td class="text-end">Rp {{ ribuan($upah->TOTAL_LEMBUR) }}</td>
                <td class="text-end text-primary fs-14">Rp {{ ribuan($upah->GRAND_TOTAL) }}</td>
                <td></td>
              </tr>
            </tfoot>
          </table>
        </div>

        <!-- KALIMAT TERBILANG & ALOKASI PEMBAGIAN -->
        <div class="row g-3">
          <div class="col-md-7">
            <div class="p-3 border rounded bg-light mb-3">
              <span class="text-muted fs-12 d-block">TERBILANG:</span>
              <p class="fw-bold fst-italic text-dark mb-0">{{ $upah->TERBILANG }}</p>
            </div>

            <div class="p-3 border rounded bg-white">
              <span class="text-muted fs-12 d-block mb-1">Pejabat Pengesah / Sie Koordinator:</span>
              <strong>{{ $upah->KOORDINATOR_NAMA }}</strong> (Unit Usaha Pengerah Jasa TKBM Sektor Utara / Selatan)
              <hr class="my-2">
              <span class="text-muted fs-12 d-block mb-1">Rekening Penampung Koperasi:</span>
              <strong>{{ $upah->REKENING_BANK }}</strong>
            </div>
          </div>

          <div class="col-md-5">
            <div class="card border border-primary">
              <div class="card-header bg-primary-subtle py-2">
                <h6 class="card-title mb-0 fs-13 fw-bold text-primary"><i class="fal fa-pie-chart me-1"></i> Rincian Pembagian Alokasi Dana Kwitansi</h6>
              </div>
              <div class="card-body p-3">
                <table class="table table-sm table-borderless mb-2 fs-13">
                  <tr>
                    <td class="text-muted">Bagian Upah TKBM (Hak Buruh)</td>
                    <td class="text-end fw-bold text-dark">Rp {{ ribuan($upah->ALOKASI_BURUH) }}</td>
                  </tr>
                  <tr>
                    <td class="text-muted">Bagian Koperasi (Adm + Kesra)</td>
                    <td class="text-end fw-bold text-dark">Rp {{ ribuan($upah->ALOKASI_KOPERASI) }}</td>
                  </tr>
                  <tr class="border-top">
                    <td class="fw-bold fs-14">TOTAL KWITANSI</td>
                    <td class="text-end fw-bold fs-15 text-success">Rp {{ ribuan($upah->GRAND_TOTAL) }}</td>
                  </tr>
                </table>
                @if (!empty($upah->BUKTI_BAYAR))
                  <div class="mt-2 text-center">
                    <a href="{{ asset($upah->BUKTI_BAYAR) }}" target="_blank" class="btn btn-sm btn-outline-success w-100">
                      <i class="fal fa-image me-1"></i> Lihat Bukti Pembayaran / Transfer
                    </a>
                  </div>
                @endif
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<!-- MODAL KONFIRMASI PEMBAYARAN LUNAS -->
<div class="modal fade" id="modal_bayar" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="form_konfirmasi_bayar" enctype="multipart/form-data">
        <input type="hidden" name="{{ $csrf_name }}" id="{{ $csrf_name }}" value="{{ $csrf_token }}">
        <input type="hidden" name="id_upah_tkbm" value="{{ $upah->ID_UPAH_TKBM }}">

        <div class="modal-header bg-success text-white">
          <h5 class="modal-title"><i class="fal fa-check-circle me-1"></i> Konfirmasi Pembayaran LUNAS</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="mb-3 fs-13 text-muted">
            Tandai dokumen <strong>{{ $upah->NO_KWITANSI }}</strong> sebagai <strong>LUNAS</strong> setelah pembayaran dari PBM diterima.
          </p>
          <div class="mb-3">
            <label class="form-label required">Tanggal Pembayaran</label>
            <input type="date" class="form-control" name="tanggal_bayar" required value="{{ date('Y-m-d') }}">
          </div>
          <div class="mb-3">
            <label class="form-label">Upload Bukti Transfer / Kwitansi Cap Lunas <span class="text-muted">(Opsional)</span></label>
            <input type="file" class="form-control" name="bukti_bayar" accept="image/*,.pdf">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-success" id="btn_submit_bayar">
            <i class="fal fa-save me-1"></i> Simpan Status LUNAS
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  const upahRoute = "{{ url('accounting/upah') }}/";
  const upahId = "{{ $upah->ID_UPAH_TKBM }}";

  $('#form_konfirmasi_bayar').on('submit', function(e) {
    e.preventDefault();
    let formData = new FormData(this);
    $('#btn_submit_bayar').prop('disabled', true).html('<i class="fal fa-spinner fa-spin me-1"></i> Menyimpan...');

    $.ajax({
      url: upahRoute + 'update_payment',
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'JSON',
      success: function(res) {
        $('#btn_submit_bayar').prop('disabled', false).html('<i class="fal fa-save me-1"></i> Simpan Status LUNAS');
        if (res.status) {
          Swal.fire({
            icon: 'success',
            title: 'Lunas!',
            text: res.msg,
            timer: 1500,
            showConfirmButton: false
          }).then(() => {
            window.location.reload();
          });
        } else {
          Swal.fire('Gagal!', res.msg, 'error');
        }
      },
      error: function() {
        $('#btn_submit_bayar').prop('disabled', false).html('<i class="fal fa-save me-1"></i> Simpan Status LUNAS');
        Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
      }
    });
  });

  $('#btn_bridge_invoice').on('click', function() {
    Swal.fire({
      title: 'Generate Invoice Komersial?',
      text: 'Sistem akan membuat draft faktur komersial di modul Accounting > Invoice untuk dokumen ini.',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Ya, Buat Invoice',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {
        let data = { id_upah_tkbm: upahId };
        data[csrf_name] = $('#' + csrf_name).val();
        $.post(upahRoute + 'bridge_invoice', data, function(res) {
          if (res.status) {
            Swal.fire({
              icon: 'success',
              title: 'Berhasil!',
              text: res.msg,
              confirmButtonText: 'Buka Invoice'
            }).then(() => {
              window.location.href = res.invoice_url;
            });
          } else {
            Swal.fire('Gagal!', res.msg, 'error');
          }
        }, 'json');
      }
    });
  });

  $('#btn_cancel_doc').on('click', function() {
    Swal.fire({
      title: 'Batalkan Dokumen?',
      text: 'Dokumen ini akan diubah statusnya menjadi Dibatalkan (Cancelled).',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      confirmButtonText: 'Ya, Batalkan!',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {
        let data = { id_upah_tkbm: upahId };
        data[csrf_name] = $('#' + csrf_name).val();
        $.post(upahRoute + 'cancel', data, function(res) {
          if (res.status) {
            Swal.fire({
              icon: 'success',
              title: 'Dibatalkan!',
              text: res.msg,
              timer: 1500,
              showConfirmButton: false
            }).then(() => {
              window.location.reload();
            });
          } else {
            Swal.fire('Gagal!', res.msg, 'error');
          }
        }, 'json');
      }
    });
  });
</script>
@endpush

