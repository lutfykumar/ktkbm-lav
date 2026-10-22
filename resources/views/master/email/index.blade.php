@extends('layouts.app')

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <table id="crudTable" class="table table-hover table-nowrap mb-0" style="width: 100%;">
          <thead>
            <tr>
              <th class="table_no">No</th>
              <th>Nama</th>
              <th>Subject</th>
              <th>Pesan</th>
              <th class="table_action">Opsi</th>
            </tr>
          </thead>
          <tbody>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<script src="<?= base_url('assets/libs/@ckeditor/ckeditor5-build-classic/build/ckeditor.js'); ?>"></script>
<script>
  const route_link = ip_server + 'master/email/';

  $(document).ready(function() {
    var table = $('#crudTable').DataTable({
      responsive: true,
      processing: true,
      serverSide: false,
      ajax: {
        url: route_link + 'table',
        type: 'GET',
        dataType: "JSON",
        timeout: 10000,
        dataSrc: "",
      },
      columnDefs: [{
        targets: [1, -1],
        responsivePriority: 1,
      }, {
        targets: [0, -1],
        className: 'text-center',
        orderable: false,
        searchable: false
      }, {
        targets: 1,
        className: 'fw-bold'
      }],
      drawCallback: function() {
        $('[data-bs-toggle="tooltip"]').tooltip();
      },
    });
  });
  $(document).on('click', '.btn-edit', function(e) {
    e.preventDefault();
    var btn = $(this);
    var id = btn.data('id');
    var title = btn.attr('data-bs-original-title');
    var modal = $('#modal_center_lg');
    var m_title = modal.find('#m_title');
    var m_body = modal.find('#m_body');
    var action_url = route_link + 'edit';
    btn.addClass('disabled');
    m_body.html('');
    $.ajax({
      type: "POST",
      url: action_url,
      dataType: "html",
      data: {
        id: id
      },
      success: function(res) {
        m_title.text(title);
        m_body.html(res);
        $('[data-bs-toggle="tooltip"]').tooltip('hide');
        btn.removeClass('disabled');
        modal.modal({
          backdrop: 'static',
          keyboard: false
        }).modal('show');
      },
    });
  });
  $(document).on('click', '.btn-show', function(e) {
    e.preventDefault();
    var btn = $(this);
    var id = btn.data('id');
    var title = btn.attr('data-bs-original-title');
    var modal = $('#modal_center_lg');
    var m_title = modal.find('#m_title');
    var m_body = modal.find('#m_body');
    var action_url = route_link + 'show';
    btn.addClass('disabled');
    m_body.html('');
    $.ajax({
      type: "POST",
      url: action_url,
      dataType: "html",
      data: {
        id: id
      },
      success: function(res) {
        m_title.text(title);
        m_body.html(res);
        $('[data-bs-toggle="tooltip"]').tooltip('hide');
        btn.removeClass('disabled');
        modal.modal({
          backdrop: 'static',
          keyboard: false
        }).modal('show');
      },
    });
  });
</script>

@endsection

