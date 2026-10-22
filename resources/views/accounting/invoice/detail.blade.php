@extends('layouts.app')

@section('title', $title ?? 'Detail Invoice')
@section('title_header', $title_header ?? 'Invoice')
@section('title_parent', $title_parent ?? 'Accounting')

@php
$statusBadge = match (strtolower($invoice->STATUS ?? '')) {
    'draft' => 'bg-secondary',
    'sent' => 'bg-info',
    'paid' => 'bg-success',
    'overdue' => 'bg-danger',
    'cancelled' => 'bg-dark',
    default => 'bg-secondary',
};
@endphp

@section('content')
<div class="row">
  <div class="col-md-8">
    
    <!-- HEADER INVOICE -->
    <div class="card mb-3">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h4 class="fw-bold mb-1">{{ $invoice->NO_INVOICE }}</h4>
            <span class="badge {{ $statusBadge }} fs-6">{{ ucfirst($invoice->STATUS) }}</span>
          </div>
          <div class="text-end">
            <p class="text-muted mb-0">Total Tagihan</p>
            <h3 class="fw-bold text-success mb-0">Rp {{ ribuan($invoice->TOTAL) }}</h3>
          </div>
        </div>
        <hr>
        <div class="row text-muted text-sm mt-3">
          <div class="col-sm-3">
            <strong>Tanggal Invoice</strong><br>
            {{ $invoice->TGL_INVOICE_FORMAT }}
          </div>
          <div class="col-sm-3">
            <strong>Jatuh Tempo</strong><br>
            <span class="{{ (strtotime($invoice->TANGGAL_JATUH_TEMPO) < time() && $invoice->STATUS != 'paid') ? 'text-danger fw-bold' : '' }}">
              {{ $invoice->TGL_JATUH_TEMPO_FORMAT }}
            </span>
          </div>
          <div class="col-sm-6">
            <strong>Client</strong><br>
            {{ $invoice->NAMA_CLIENT }}
            <br><small>{{ $invoice->ALAMAT_CLIENT }}</small>
          </div>
        </div>
      </div>
    </div>

    <!-- ITEM TABLE -->
    <div class="card mb-3">
      <div class="card-header bg-light">
        <h5 class="card-title mb-0"><i class="fal fa-list-ol me-1"></i> Rincian Tagihan</h5>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th width="5%" class="text-center">No</th>
                <th>Deskripsi</th>
                <th width="10%" class="text-center">Qty</th>
                <th width="10%">Satuan</th>
                <th width="20%" class="text-end">Harga</th>
                <th width="20%" class="text-end">Subtotal</th>
              </tr>
            </thead>
            <tbody>
              @php $no = 1; @endphp
              @foreach ($items as $item)
              <tr>
                <td class="text-center">{{ $no++ }}</td>
                <td>{{ $item->DESKRIPSI }}</td>
                <td class="text-center">{{ $item->JUMLAH }}</td>
                <td>{{ $item->SATUAN }}</td>
                <td class="text-end">{{ ribuan($item->HARGA_SATUAN) }}</td>
                <td class="text-end fw-bold">{{ ribuan($item->SUBTOTAL) }}</td>
              </tr>
              @endforeach
            </tbody>
            <tfoot class="table-light">
              <tr>
                <td colspan="5" class="text-end fw-bold">Subtotal</td>
                <td class="text-end fw-bold">{{ ribuan($invoice->SUBTOTAL) }}</td>
              </tr>
              <tr>
                <td colspan="5" class="text-end fw-bold">PPN ({{ floatval($invoice->PAJAK_PERSEN) }}%)</td>
                <td class="text-end fw-bold">{{ ribuan($invoice->PAJAK_NOMINAL) }}</td>
              </tr>
              <tr>
                <td colspan="5" class="text-end fw-bold fs-5">TOTAL</td>
                <td class="text-end fw-bold fs-5 text-success">Rp {{ ribuan($invoice->TOTAL) }}</td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>

  </div>
  
  <div class="col-md-4">
    
    <!-- ACTION BUTTONS -->
    <div class="card mb-3">
      <div class="card-body">
        <div class="d-grid gap-2">
          @if ($invoice->STATUS == 'draft')
            <a href="{{ url('accounting/invoice/edit/' . $invoice->ID_INVOICE) }}" class="btn btn-warning"><i class="fal fa-pen me-1"></i> Edit Invoice</a>
            <button class="btn btn-info" id="btn_mark_sent"><i class="fal fa-paper-plane me-1"></i> Tandai Sudah Dikirim (Sent)</button>
          @endif

          @if (in_array($invoice->STATUS, ['sent', 'overdue']))
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modal_bayar"><i class="fal fa-money-bill-wave me-1"></i> Input Pembayaran</button>
          @endif

          <a href="{{ url('accounting/invoice/print_invoice/' . $invoice->ID_INVOICE) }}" target="_blank" class="btn btn-secondary"><i class="fal fa-print me-1"></i> Cetak Invoice</a>

          @if (in_array($invoice->STATUS, ['draft', 'sent', 'overdue']))
            <button class="btn btn-danger" id="btn_cancel"><i class="fal fa-times-circle me-1"></i> Batalkan Invoice</button>
          @endif

          <a href="{{ url('accounting/invoice') }}" class="btn btn-light mt-2"><i class="fal fa-arrow-left me-1"></i> Kembali ke List</a>
        </div>
      </div>
    </div>

    <!-- WO INFO -->
    <div class="card mb-3">
      <div class="card-header bg-light">
        <h5 class="card-title mb-0"><i class="fal fa-info-circle me-1"></i> Referensi Work Order</h5>
      </div>
      <div class="card-body">
        <table class="table table-sm table-borderless mb-0">
          <tr><td class="text-muted" width="40%">No WO</td><td>: <a href="{{ url('workorder') }}" target="_blank" class="fw-bold">{{ $invoice->NO_WORK_ORDER }}</a></td></tr>
          <tr><td class="text-muted">Status WO</td><td>: {{ strtoupper($invoice->STATUS_WO ?? '') }}</td></tr>
          <tr><td class="text-muted">Pelabuhan</td><td>: {{ $invoice->NAMA_PELABUHAN }}</td></tr>
          <tr><td class="text-muted">Dermaga</td><td>: {{ $invoice->NAMA_DERMAGA }}</td></tr>
          <tr><td class="text-muted">Kapal</td><td>: {{ $invoice->NAMA_KAPAL }}</td></tr>
          <tr><td class="text-muted">Aktifitas</td><td>: {{ $invoice->NAMA_AKTIFITAS }}</td></tr>
          <tr><td class="text-muted">Volume</td><td>: {{ floatval($invoice->VOLUME_WO) . ' ' . ($invoice->NAMA_SATUAN ?? '') }}</td></tr>
        </table>
      </div>
    </div>

    <!-- PAYMENT INFO -->
    @if ($invoice->STATUS == 'paid')
    <div class="card border-success border-2 shadow-sm mb-3">
      <div class="card-header bg-success text-white">
        <h5 class="card-title mb-0 text-white"><i class="fal fa-check-circle me-1"></i> Info Pembayaran</h5>
      </div>
      <div class="card-body">
        <table class="table table-sm table-borderless mb-0">
          <tr><td class="text-muted" width="40%">Tgl Bayar</td><td>: <strong>{{ $invoice->TGL_BAYAR_FORMAT }}</strong></td></tr>
          <tr><td class="text-muted">Metode</td><td>: <strong>{{ $invoice->METODE_BAYAR }}</strong></td></tr>
          @if (!empty($invoice->BUKTI_BAYAR))
          <tr><td colspan="2" class="pt-3">
            <a href="{{ asset($invoice->BUKTI_BAYAR) }}" target="_blank" class="btn btn-sm btn-outline-success w-100"><i class="fal fa-file-image me-1"></i> Lihat Bukti Pembayaran</a>
          </td></tr>
          @endif
        </table>
      </div>
    </div>
    @endif

  </div>
</div>

<!-- MODAL BAYAR -->
<div class="modal fade" id="modal_bayar" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Konfirmasi Pembayaran</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="form_bayar" enctype="multipart/form-data">
          <input type="hidden" name="{{ $csrf_name }}" id="csrf_bayar" value="{{ $csrf_token }}">
          <input type="hidden" name="id_invoice" value="{{ $invoice->ID_INVOICE }}">
          
          <div class="mb-3">
            <label class="form-label required">Tanggal Bayar</label>
            <input type="date" class="form-control" name="tanggal_bayar" required value="{{ date('Y-m-d') }}">
          </div>
          
          <div class="mb-3">
            <label class="form-label required">Metode Pembayaran</label>
            <select class="form-select" name="metode_bayar" required>
              <option value="Transfer Bank">Transfer Bank</option>
              <option value="Cash">Cash / Tunai</option>
              <option value="Giro / Cek">Giro / Cek</option>
            </select>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Upload Bukti Bayar <small class="text-muted">(Opsional, max 5MB)</small></label>
            <input type="file" class="form-control" name="bukti_bayar" accept=".jpg,.jpeg,.png,.pdf">
          </div>
          
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-success" id="btn_submit_bayar"><i class="fal fa-save me-1"></i> Simpan Pembayaran</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {

  // Tandai Terkirim
  $('#btn_mark_sent').click(function(e) {
    e.preventDefault();
    if (confirm('Tandai invoice ini sebagai Sent (Sudah Dikirim)?')) {
      let postData = { id_invoice: {{ $invoice->ID_INVOICE }}, status: 'sent', [csrf_name]: $('#csrf_bayar').val() };
      $.post(ip_server + 'accounting/invoice/update', postData, function(res) {
        if (res.status) {
          toast_success('Status invoice diperbarui menjadi Sent');
          setTimeout(() => location.reload(), 1000);
        } else {
          toast_error(res.msg || 'Terjadi kesalahan');
        }
      }, 'json');
    }
  });

  // Cancel Invoice
  $('#btn_cancel').click(function(e) {
    e.preventDefault();
    if (confirm('Apakah Anda yakin ingin MEMBATALKAN invoice ini? Aksi ini tidak dapat dikembalikan.')) {
      let postData = { id: {{ $invoice->ID_INVOICE }}, [csrf_name]: $('#csrf_bayar').val() };
      $.post(ip_server + 'accounting/invoice/cancel', postData, function(res) {
        if (res.status) {
          toast_success('Invoice berhasil dibatalkan');
          setTimeout(() => location.reload(), 1000);
        } else {
          toast_error(res.msg || 'Gagal membatalkan invoice');
        }
      }, 'json');
    }
  });

  // Submit Payment
  $('#btn_submit_bayar').click(function() {
    let form = $('#form_bayar')[0];
    if (!form.checkValidity()) {
      $('#form_bayar').addClass('was-validated');
      return;
    }

    let formData = new FormData(form);
    let btn = $(this);
    btn.prop('disabled', true).html('<i class="fal fa-spinner fa-spin me-1"></i> Menyimpan...');

    $.ajax({
      url: ip_server + 'accounting/invoice/update_payment',
      type: 'POST',
      data: formData,
      contentType: false,
      processData: false,
      dataType: 'JSON',
      success: function(res) {
        if (res.csrf_token_name && res.csrf_token_hash) {
          $('#csrf_bayar').val(res.csrf_token_hash);
        }
        if (res.status) {
          $('#modal_bayar').modal('hide');
          toast_success(res.msg);
          setTimeout(() => location.reload(), 1000);
        } else {
          toast_error(res.msg);
          btn.prop('disabled', false).html('<i class="fal fa-save me-1"></i> Simpan Pembayaran');
        }
      },
      error: function() {
        btn.prop('disabled', false).html('<i class="fal fa-save me-1"></i> Simpan Pembayaran');
        toast_error('Terjadi kesalahan koneksi');
      }
    });
  });

});
</script>
@endpush
