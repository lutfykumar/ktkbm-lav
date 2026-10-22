@php
$wo = $workorder;
$is_done = ($wo->STATUS === 'done');
@endphp

<div class="card border border-success">
  <div class="card-header bg-success-subtle">
    <div class="d-flex align-items-center justify-content-between">
      <div>
        <h5 class="card-title mb-1"><i class="fal fa-clipboard-check me-2"></i>Daftar Laporan Aktifitas</h5>
        <p class="text-muted mb-0 fs-12">Work Order: <strong>{{ $wo->NO_WORK_ORDER }}</strong></p>
      </div>
      <div class="d-flex gap-2">
        @if(!empty($permission_create) && !$is_done)
          <button type="button" class="btn btn-sm btn-primary btn-open-form" data-wo-id="{{ $wo->ID_WORK_ORDER }}">
            <i class="fal fa-plus me-1"></i> Tambah Laporan
          </button>
        @endif
        <button type="button" class="btn btn-sm btn-outline-secondary btn-back-to-cards">
          <i class="fal fa-arrow-left me-1"></i> Kembali
        </button>
      </div>
    </div>
  </div>
  <div class="card-body">
    <!-- WO Info Header -->
    <div class="row mb-3">
      <div class="col-md-3">
        <small class="text-muted d-block">Kapal</small>
        <strong>{{ $wo->NAMA_KAPAL ?: '-' }}</strong>
      </div>
      <div class="col-md-3">
        <small class="text-muted d-block">Client</small>
        <strong>{{ $wo->NAMA_CLIENT ?: '-' }}</strong>
      </div>
      <div class="col-md-3">
        <small class="text-muted d-block">Dermaga</small>
        <strong>{{ $wo->NAMA_DERMAGA ?: '-' }}</strong>
      </div>
      <div class="col-md-3">
        <small class="text-muted d-block">Aktifitas</small>
        <strong>{{ $wo->NAMA_AKTIFITAS ?: '-' }}</strong>
        @if(!empty($wo->NAMA_SATUAN))
          <span class="text-muted">({{ $wo->NAMA_SATUAN }})</span>
        @endif
      </div>
    </div>
    <hr>

    <!-- Detail Table -->
    @if(empty($details))
      <div class="alert alert-warning">Belum ada laporan untuk work order ini</div>
    @else
      <div class="table-responsive">
        <table class="table table-hover table-nowrap align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th class="text-center" style="width: 40px;">No</th>
              <th>Nama KRK</th>
              <th>Hasil</th>
              <th>Ransum</th>
              <th>Catatan</th>
              <th>Foto Before</th>
              <th>Foto After</th>
              <th class="text-center" style="width: 100px;">Dibuat</th>
              @if(!empty($permission_delete) && !$is_done)
                <th class="text-center" style="width: 60px;">Aksi</th>
              @endif
            </tr>
          </thead>
          <tbody>
            @php $no = 1; @endphp
            @foreach($details as $row)
              <tr id="detail-row-{{ $row->ID_WORK_ORDER_DETAIL }}">
                <td class="text-center">{{ $no++ }}</td>
                <td class="fw-medium">{{ $row->NAMA_KRK ?: '-' }}</td>
                <td>{{ !empty($row->HASIL) ? number_format($row->HASIL) : '-' }} <span class="text-muted">({{ $wo->NAMA_SATUAN }})</span></td>
                <td>{{ !empty($row->RANSUM) ? number_format($row->RANSUM) . ' Bungkus' : '-' }}</td>
                <td>
                  @if(!empty($row->CATATAN))
                    <span class="text-truncate d-inline-block" style="max-width: 200px;" title="{{ $row->CATATAN }}">
                      {{ $row->CATATAN }}
                    </span>
                  @else
                    -
                  @endif
                </td>
                <td>
                  @if(!empty($row->FOTO_KEGIATAN_BEFORE))
                    <a href="{{ asset($row->FOTO_KEGIATAN_BEFORE) }}" target="_blank">
                      <img src="{{ asset($row->FOTO_KEGIATAN_BEFORE) }}" class="img-thumbnail" style="max-height: 50px;" alt="Before">
                    </a>
                  @else
                    -
                  @endif
                </td>
                <td>
                  @if(!empty($row->FOTO_KEGIATAN_AFTER))
                    <a href="{{ asset($row->FOTO_KEGIATAN_AFTER) }}" target="_blank">
                      <img src="{{ asset($row->FOTO_KEGIATAN_AFTER) }}" class="img-thumbnail" style="max-height: 50px;" alt="After">
                    </a>
                  @else
                    -
                  @endif
                </td>
                <td class="text-center fs-12">
                  {{ $row->CREATED_USER ?: '-' }}
                  <br>
                  <small class="text-muted">{{ !empty($row->CREATED_AT) ? date('d/m/Y H:i', strtotime($row->CREATED_AT)) : '-' }}</small>
                </td>
                @if(!empty($permission_delete) && !$is_done)
                  <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger btn-icon btn-delete-detail"
                      data-id="{{ $row->ID_WORK_ORDER_DETAIL }}"
                      title="Hapus laporan">
                      <i class="fal fa-trash"></i>
                    </button>
                  </td>
                @endif
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>
</div>

<script>
  const listDetailRoute = "{{ url('activity') }}/";

  $(document).on('click', '.btn-delete-detail', function(e) {
    e.preventDefault();
    let btn = $(this);
    let id = btn.data('id');

    Swal.fire({
      title: 'Hapus Laporan?',
      text: 'Yakin ingin menghapus laporan ini?',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      confirmButtonText: 'Ya, Hapus!'
    }).then((result) => {
      if (result.isConfirmed) {
        btn.addClass('disabled');
        $.ajax({
          type: "POST",
          url: listDetailRoute + 'delete_detail',
          dataType: "JSON",
          data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            id: id
          },
          success: function(res) {
            if (res.status) {
              Swal.fire('Terhapus!', res.msg, 'success');
              $('#detail-row-' + id).fadeOut(300, function() {
                $(this).remove();
              });
            } else {
              Swal.fire('Gagal!', res.msg, 'error');
            }
            btn.removeClass('disabled');
          },
          error: function() {
            Swal.fire('Error!', 'Terjadi kesalahan sistem', 'error');
            btn.removeClass('disabled');
          }
        });
      }
    });
  });

  $(document).on('click', '.btn-open-form', function() {
    let woId = $(this).data('wo-id');
    load_form(woId);
  });
</script>
