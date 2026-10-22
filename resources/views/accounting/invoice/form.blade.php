@extends('layouts.app')

@section('title', $title ?? 'Form Invoice')
@section('title_header', $title_header ?? 'Invoice')
@section('title_parent', $title_parent ?? 'Accounting')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header bg-light">
        <h5 class="card-title mb-0"><i class="fal fa-file-invoice me-1"></i> {{ $title_header ?? 'Form Invoice' }}</h5>
      </div>
      <div class="card-body">
        
        @if (empty($wo))
          <div class="alert alert-danger">Data Work Order tidak ditemukan.</div>
        @else
        
        <form id="form_invoice" class="needs-validation" novalidate>
          <input type="hidden" name="{{ $csrf_name }}" id="{{ $csrf_name }}" value="{{ $csrf_token }}">
          <input type="hidden" name="id_work_order" value="{{ $wo->ID_WORK_ORDER }}">
          @if ($mode == 'edit')
            <input type="hidden" name="id_invoice" value="{{ $invoice->ID_INVOICE }}">
          @endif

          <div class="row mb-4">
            <div class="col-md-6">
              <h6 class="fw-bold mb-3 text-muted">Informasi Work Order</h6>
              <table class="table table-sm table-borderless">
                <tr><td width="150" class="text-muted">No Work Order</td><td>: <strong>{{ $wo->NO_WORK_ORDER }}</strong></td></tr>
                <tr><td class="text-muted">Client</td><td>: {{ $wo->NAMA_CLIENT }}</td></tr>
                <tr><td class="text-muted">Pelabuhan</td><td>: {{ $wo->NAMA_PELABUHAN }}</td></tr>
                <tr><td class="text-muted">Dermaga</td><td>: {{ $wo->NAMA_DERMAGA }}</td></tr>
                <tr><td class="text-muted">Kapal</td><td>: {{ $wo->NAMA_KAPAL }}</td></tr>
                <tr><td class="text-muted">Aktifitas</td><td>: {{ $wo->NAMA_AKTIFITAS }}</td></tr>
              </table>
            </div>
            <div class="col-md-6">
              <h6 class="fw-bold mb-3 text-muted">Pengaturan Invoice</h6>
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label required">Tanggal Invoice</label>
                  <input type="date" class="form-control" name="tanggal_invoice" required 
                         value="{{ $mode == 'edit' ? $invoice->TANGGAL_INVOICE : date('Y-m-d') }}">
                </div>
                <div class="col-md-6">
                  <label class="form-label required">Jatuh Tempo</label>
                  <input type="date" class="form-control" name="tanggal_jatuh_tempo" required
                         value="{{ $mode == 'edit' ? $invoice->TANGGAL_JATUH_TEMPO : date('Y-m-d', strtotime('+14 days')) }}">
                </div>
                <div class="col-md-6">
                  <label class="form-label required">Pajak PPN (%)</label>
                  <input type="number" step="0.01" class="form-control calc-trigger" name="pajak_persen" id="pajak_persen" required
                         value="{{ $mode == 'edit' ? floatval($invoice->PAJAK_PERSEN) : 11 }}">
                </div>
                @if ($mode == 'edit')
                <div class="col-md-6">
                  <label class="form-label required">Status</label>
                  <select class="form-select" name="status" required>
                    <option value="draft" {{ $invoice->STATUS == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="sent" {{ $invoice->STATUS == 'sent' ? 'selected' : '' }}>Sent</option>
                  </select>
                </div>
                @else
                <div class="col-md-6">
                  <label class="form-label required">Status</label>
                  <select class="form-select" name="status" required>
                    <option value="draft" selected>Draft</option>
                    <option value="sent">Sent</option>
                  </select>
                </div>
                @endif
              </div>
            </div>
          </div>

          <h6 class="fw-bold mb-3 text-muted">Item Invoice</h6>
          <div class="table-responsive mb-3">
            <table class="table table-bordered align-middle" id="table_items">
              <thead class="table-light">
                <tr>
                  <th width="35%">Deskripsi</th>
                  <th width="15%">Jumlah</th>
                  <th width="15%">Satuan</th>
                  <th width="20%">Harga Satuan</th>
                  <th width="10%">Subtotal</th>
                  <th width="5%" class="text-center"><button type="button" class="btn btn-sm btn-success" id="btn_add_item"><i class="fal fa-plus"></i></button></th>
                </tr>
              </thead>
              <tbody>
                @if ($mode == 'edit' && !empty($items))
                  @foreach ($items as $idx => $item)
                  <tr>
                    <td><input type="text" class="form-control" name="deskripsi[]" required value="{{ $item->DESKRIPSI }}"></td>
                    <td><input type="number" step="0.01" class="form-control qty calc-trigger" name="jumlah[]" required value="{{ floatval($item->JUMLAH) }}"></td>
                    <td><input type="text" class="form-control" name="satuan[]" value="{{ $item->SATUAN }}"></td>
                    <td><input type="number" step="0.01" class="form-control price calc-trigger" name="harga_satuan[]" required value="{{ floatval($item->HARGA_SATUAN) }}"></td>
                    <td class="text-end fw-bold row-subtotal">{{ ribuan($item->SUBTOTAL) }}</td>
                    <td class="text-center"><button type="button" class="btn btn-sm btn-danger btn_remove_item"><i class="fal fa-trash"></i></button></td>
                  </tr>
                  @endforeach
                @else
                  <tr>
                    <td><input type="text" class="form-control" name="deskripsi[]" required placeholder="Contoh: Jasa Bongkar Muat"></td>
                    <td><input type="number" step="0.01" class="form-control qty calc-trigger" name="jumlah[]" required value="{{ floatval($wo->VOLUME) }}"></td>
                    <td><input type="text" class="form-control" name="satuan[]" value="{{ $wo->NAMA_SATUAN ?? '' }}"></td>
                    <td><input type="number" step="0.01" class="form-control price calc-trigger" name="harga_satuan[]" required value="0"></td>
                    <td class="text-end fw-bold row-subtotal">0</td>
                    <td class="text-center"><button type="button" class="btn btn-sm btn-danger btn_remove_item"><i class="fal fa-trash"></i></button></td>
                  </tr>
                @endif
              </tbody>
              <tfoot class="table-light">
                <tr>
                  <td colspan="4" class="text-end fw-bold">Subtotal</td>
                  <td class="text-end fw-bold" id="lbl_subtotal">0</td>
                  <td></td>
                </tr>
                <tr>
                  <td colspan="4" class="text-end fw-bold">PPN</td>
                  <td class="text-end fw-bold" id="lbl_ppn">0</td>
                  <td></td>
                </tr>
                <tr>
                  <td colspan="4" class="text-end fw-bold fs-5">TOTAL</td>
                  <td class="text-end fw-bold fs-5 text-success" id="lbl_total">0</td>
                  <td></td>
                </tr>
              </tfoot>
            </table>
          </div>

          <div class="mb-4">
            <label class="form-label">Catatan Tambahan</label>
            <textarea class="form-control" name="catatan" rows="3">{{ $mode == 'edit' ? $invoice->CATATAN : '' }}</textarea>
          </div>

          <div class="d-flex justify-content-between">
            <a href="{{ url('accounting/invoice') }}" class="btn btn-light"><i class="fal fa-arrow-left me-1"></i> Kembali</a>
            <button type="button" class="btn btn-primary" id="btn_save_invoice"><i class="fal fa-save me-1"></i> Simpan Invoice</button>
          </div>

        </form>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
  const mode = '{{ $mode }}';
  calculate_total();

  // Add Item Row
  $('#btn_add_item').click(function() {
    let row = `<tr>
      <td><input type="text" class="form-control" name="deskripsi[]" required></td>
      <td><input type="number" step="0.01" class="form-control qty calc-trigger" name="jumlah[]" required value="1"></td>
      <td><input type="text" class="form-control" name="satuan[]"></td>
      <td><input type="number" step="0.01" class="form-control price calc-trigger" name="harga_satuan[]" required value="0"></td>
      <td class="text-end fw-bold row-subtotal">0</td>
      <td class="text-center"><button type="button" class="btn btn-sm btn-danger btn_remove_item"><i class="fal fa-trash"></i></button></td>
    </tr>`;
    $('#table_items tbody').append(row);
    calculate_total();
  });

  // Remove Item Row
  $(document).on('click', '.btn_remove_item', function() {
    if ($('#table_items tbody tr').length > 1) {
      $(this).closest('tr').remove();
      calculate_total();
    } else {
      toast_warning('Minimal harus ada 1 item invoice');
    }
  });

  // Calculate Subtotal & Total
  $(document).on('input', '.calc-trigger', function() {
    calculate_total();
  });

  function calculate_total() {
    let subtotal = 0;
    $('#table_items tbody tr').each(function() {
      let qty = parseFloat($(this).find('.qty').val()) || 0;
      let price = parseFloat($(this).find('.price').val()) || 0;
      let row_sub = qty * price;
      subtotal += row_sub;
      $(this).find('.row-subtotal').text(new Intl.NumberFormat('id-ID').format(row_sub));
    });

    let pajak_persen = parseFloat($('#pajak_persen').val()) || 0;
    let ppn = (subtotal * pajak_persen) / 100;
    let grand_total = subtotal + ppn;

    $('#lbl_subtotal').text(new Intl.NumberFormat('id-ID').format(subtotal));
    $('#lbl_ppn').text(new Intl.NumberFormat('id-ID').format(ppn));
    $('#lbl_total').text(new Intl.NumberFormat('id-ID').format(grand_total));
  }

  // Save Invoice
  $('#btn_save_invoice').click(function(e) {
    e.preventDefault();
    let form = $('#form_invoice');
    let btn = $(this);

    if (form[0].checkValidity() === false) {
      form.addClass('was-validated');
      toast_warning('Mohon lengkapi semua field yang wajib diisi');
      return;
    }

    let url = mode == 'edit' ? ip_server + 'accounting/invoice/update' : ip_server + 'accounting/invoice/store';

    btn.prop('disabled', true).html('<i class="fal fa-spinner fa-spin me-1"></i> Menyimpan...');

    $.ajax({
      url: url,
      type: "POST",
      dataType: "JSON",
      data: form.serialize(),
      success: function(res) {
        if (res.csrf_token_name && res.csrf_token_hash) {
          $('input[name="' + res.csrf_token_name + '"]').val(res.csrf_token_hash);
        }
        if (res.status == true) {
          toast_success(res.msg);
          let redirect_id = res.id || $('input[name="id_invoice"]').val();
          setTimeout(() => {
            window.location.href = ip_server + 'accounting/invoice/detail/' + redirect_id;
          }, 1000);
        } else {
          toast_error(res.msg);
          btn.prop('disabled', false).html('<i class="fal fa-save me-1"></i> Simpan Invoice');
        }
      },
      error: function() {
        btn.prop('disabled', false).html('<i class="fal fa-save me-1"></i> Simpan Invoice');
        toast_error('Terjadi kesalahan koneksi');
      }
    });
  });
});
</script>
@endpush
