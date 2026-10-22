function toast_error(pesan) {
  Toastify({
    newWindow: true,
    text: pesan,
    gravity: 'top',
    position: 'right',
    className: 'bg-danger rounded',
    stopOnFocus: true,
    offset: {
      x: 10, // horizontal axis - can be a number or a string indicating unity. eg: '2em'
      y: 10, // vertical axis - can be a number or a string indicating unity. eg: '2em'
    },
    duration: 5000,
    close: true,
    // style: {
    //   background: "linear-gradient(to right, #0AB39C, #405189)"
    // },
  }).showToast();
}

function toast_warning(pesan) {
  Toastify({
    newWindow: true,
    text: pesan,
    gravity: 'top',
    position: 'right',
    className: 'bg-warning rounded',
    stopOnFocus: true,
    offset: {
      x: 10, // horizontal axis - can be a number or a string indicating unity. eg: '2em'
      y: 10, // vertical axis - can be a number or a string indicating unity. eg: '2em'
    },
    duration: 5000,
    close: true,
    // style: {
    //   background: "linear-gradient(to right, #0AB39C, #405189)"
    // },
  }).showToast();
}

function toast_success(pesan) {
  Toastify({
    newWindow: true,
    text: pesan,
    gravity: 'top',
    position: 'right',
    className: 'bg-success rounded',
    stopOnFocus: true,
    offset: {
      x: 10, // horizontal axis - can be a number or a string indicating unity. eg: '2em'
      y: 10, // vertical axis - can be a number or a string indicating unity. eg: '2em'
    },
    duration: 5000,
    close: true,
    style: {
      background: 'linear-gradient(to right, #0AB39C, #405189)',
    },
  }).showToast();
}

$(function () {
  // $('.btn-popup-top').tooltip({
  //   placement: 'top'
  // })

  // Konfigurasi default jQuery Validator - pastikan library dimuat terlebih dahulu
  if (typeof $.validator !== 'undefined') {
    $.validator.setDefaults({
      errorElement: 'div',
      errorPlacement: function (error, element) {
        error.addClass('invalid-feedback');
        element.closest('.fm').append(error);
      },
      highlight: function (element, errorClass, validClass) {
        $(element).addClass('is-invalid');
      },
      unhighlight: function (element, errorClass, validClass) {
        $(element).removeClass('is-invalid');
      },
      lang: 'id',
    });
  }
});

// Konfigurasi default DataTables - pastikan library dimuat terlebih dahulu
if (typeof $.fn.dataTable !== 'undefined') {
  $.extend(true, $.fn.dataTable.defaults, {
    fixedHeader: true,
    ordering: true,
    searching: true,
    responsive: true,
    language: {
      emptyTable: 'Data tidak ditemukan',
      info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
      infoEmpty: 'Menampilkan 0 sampai 0 dari 0 data',
      infoFiltered: '(Pencarian dari _MAX_ total data)',
      infoPostFix: '',
      thousands: '.',
      lengthMenu: '_MENU_ Data',
      search: 'Pencarian: ',
      zeroRecords: 'Data tidak ditemukan',
      paginate: {
        first: 'Awal',
        last: 'Akhir',
        next: '>',
        previous: '<',
      },
      aria: {
        sortAscending: ': activate to sort column ascending',
        sortDescending: ': activate to sort column descending',
      },
      buttons: {
        copy: 'Copy',
        excel: 'Excel',
        csv: 'CSV',
        pdf: 'PDF',
        print: 'Print',
        colvis: 'Tampil Column',
      },
    },
    pageLength: 10,
    lengthMenu: [
      [10, 25, 50, 100],
      [10, 25, 50, 100],
    ],
    order: [[0, 'asc']],
  });
}

// Ekstensi jQuery Validator - pastikan library dimuat terlebih dahulu
if (typeof $.validator !== 'undefined') {
  $.extend($.validator.prototype, {
    checkForm: function () {
      this.prepareForm();
      for (var i = 0, elements = (this.currentElements = this.elements()); elements[i]; i++) {
        if (this.findByName(elements[i].name).length != undefined && this.findByName(elements[i].name).length > 1) {
          for (var cnt = 0; cnt < this.findByName(elements[i].name).length; cnt++) {
            this.check(this.findByName(elements[i].name)[cnt]);
          }
        } else {
          this.check(elements[i]);
        }
      }
      return this.valid();
    },
  });
}

$(document).ready(function () {
  $('.btn_batal_form').click(function () {
    let me = $(this);
    let form = $('.btn-form-accor');
    me.addClass('disabled');
    form.trigger('click');
    $('.ajax_select').each(function () {
      $(this).val('').trigger('change');
    });
    me.removeClass('disabled');
  });

  // Inisialisasi Select2 - pastikan library dimuat terlebih dahulu
  if (typeof $.fn.select2 !== 'undefined') {
    $('.select2').select2();
  }

  if ($('.filter_tgl').length) {
    let elementFilterPeriode = $('.filter_tgl');
    elementFilterPeriode.each(function () {
      let new_elemt_id = $(this).attr('id');

      // Inisialisasi DateRangePicker - pastikan library dimuat terlebih dahulu
      if (typeof $.fn.daterangepicker !== 'undefined') {
        $('#' + new_elemt_id).daterangepicker({
          autoUpdateInput: false,
          locale: {
            format: 'DD/MM/YYYY',
          },
        });
        $('#' + new_elemt_id).on('apply.daterangepicker', function (ev, picker) {
          $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
        });

        $('#' + new_elemt_id).on('cancel.daterangepicker', function (ev, picker) {
          $(this).val('');
        });
      }
    });
  }
});

// Konfigurasi default Select2 - pastikan library dimuat terlebih dahulu
if (typeof $.fn.select2 !== 'undefined') {
  $.fn.select2.defaults.set('language', 'id');
  $(document).on('select2:open', () => {
    document.querySelector('.select2-search__field').focus();
  });
}

// Generic function for all select2 data fetching
// Mengurangi duplikasi kode dengan membuat fungsi generik untuk semua select2 yang menggunakan AJAX
function initializeSelect2(element, endpoint, modalElement = '', extraData = {}) {
  // Pastikan ip_server didefinisikan, jika tidak gunakan fallback
  let baseUrl = typeof ip_server !== 'undefined' ? ip_server : '';
  let action_url = baseUrl + endpoint;
  let select2Options = {
    placeholder: 'Pilih salah satu',
    ajax: {
      dataType: 'JSON',
      type: 'POST',
      url: action_url,
      data: function (params) {
        return {
          search: params.term,
          ...extraData,
        };
      },
      processResults: function (data, page) {
        return {
          results: data,
        };
      },
      cache: false,
    },
  };

  if (modalElement !== '') {
    select2Options.dropdownParent = $(modalElement);
  }

  $(element).select2(select2Options);
}

function get_all_roles(element, modalElement = '') {
  initializeSelect2(element, 'ajax/get_all_roles', modalElement);
}

function get_all_client(element, modalElement = '') {
  initializeSelect2(element, 'ajax/get_all_client', modalElement);
}

function get_all_pelabuhan(element, modalElement = '') {
  initializeSelect2(element, 'ajax/get_all_pelabuhan', modalElement);
}
function get_all_kapal(element, modalElement = '') {
  initializeSelect2(element, 'ajax/get_all_kapal', modalElement);
}
function get_all_dermaga(element, modalElement = '') {
  initializeSelect2(element, 'ajax/get_all_dermaga', modalElement);
}
function get_all_alat(element, modalElement = '') {
  initializeSelect2(element, 'ajax/get_all_alat', modalElement);
}
function get_all_aktifitas(element, modalElement = '') {
  initializeSelect2(element, 'ajax/get_all_aktifitas', modalElement);
}
function get_all_jenis_satuan(element, modalElement = '') {
  initializeSelect2(element, 'ajax/get_all_jenis_satuan', modalElement);
}

function get_all_users_by_role(element, modalElement = '', role_id = '') {
  // Parameter 'kecuali' digunakan untuk exclude karyawan tertentu dari list
  initializeSelect2(element, 'ajax/get_all_users_by_role', modalElement, {
    role_id: role_id,
  });
}

function get_all_coa(element, modalElement = '') {
  // Chart of Accounts - daftar perkiraan Akuntansi
  initializeSelect2(element, 'ajax/get_all_coa', modalElement);
}

function get_all_project(element, modalElement = '') {
  // Daftar project yang tersedia
  initializeSelect2(element, 'ajax/get_all_project', modalElement);
}

function get_all_berita_acara(element, modalElement = '') {
  // Daftar berita acara
  initializeSelect2(element, 'ajax/get_all_berita_acara', modalElement);
}

function get_all_faktur(element, modalElement = '') {
  // Daftar faktur yang tersedia
  initializeSelect2(element, 'ajax/get_all_faktur', modalElement);
}

function get_all_header(element, modalElement = '') {
  // Daftar faktur yang tersedia
  initializeSelect2(element, 'ajax/get_all_header', modalElement);
}
function get_all_parent(element, modalElement = '') {
  // Daftar faktur yang tersedia
  initializeSelect2(element, 'ajax/get_all_parent', modalElement);
}

function get_all_krk(element, modalElement = '') {
  // Daftar KRK dengan support tags (bisa input nama baru)
  let baseUrl = typeof ip_server !== 'undefined' ? ip_server : '';
  let action_url = baseUrl + 'ajax/get_all_krk';
  let select2Options = {
    placeholder: 'Pilih atau ketik nama KRK baru',
    tags: true,
    createTag: function (params) {
      let term = $.trim(params.term);
      if (term === '') return null;
      return { id: 'new:' + term, text: term, newTag: true };
    },
    ajax: {
      dataType: 'JSON',
      type: 'POST',
      url: action_url,
      data: function (params) {
        return { search: params.term };
      },
      processResults: function (data) {
        return { results: data };
      },
      cache: false,
    },
  };
  if (modalElement !== '') {
    select2Options.dropdownParent = $(modalElement);
  }
  $(element).select2(select2Options);
}

function get_all_catatan(element, modalElement = '') {
  // Daftar catatan untuk suggest input
  initializeSelect2(element, 'ajax/get_all_catatan', modalElement);
}

function stringToInteger(string) {
  var gh;
  if (typeof string === 'string') {
    var mystring = string.replace(/\./g, '');
    gh = parseFloat(mystring.replace(/Rp| /g, '').replace(/,/, '.'));
  } else if (typeof string === 'number') {
    gh = string;
  } else {
    gh = 0;
  }
  return gh;
}

function js_format_uang(numb) {
  // Format angka menjadi format currency Indonesia (Rupiah)
  let addOn = '';
  if (isNegative(numb)) {
    addOn = '-';
  }
  const format = numb.toString().split('').reverse().join('');
  const convert = format.match(/\d{1,3}/g);
  const rupiah = 'Rp. ' + addOn + convert.join('.').split('').reverse().join('');
  return rupiah;
}

function isNegative(num) {
  // Cek apakah angka negatif
  if (Math.sign(num) === -1) {
    return true;
  }
  return false;
}

// function hitung_nilai_dari_persen(awal, persen) {
//   // Helper function untuk menghitung nilai dari persentase (commented out)
//   const pr = parseInt(numb);
//   const format = pr.toString().split('').reverse().join('');
//   const convert = format.match(/\d{1,3}/g);
//   const rupiah = 'Rp. ' + convert.join('.').split('').reverse().join('')
//   return rupiah;
// }

function copyToClipboard(elem) {
  // Copy teks ke clipboard dengan fallback untuk browser lama
  var target = elem;
  var currentFocus = document.activeElement;

  target.focus();
  target.setSelectionRange(0, target.value.length);
  var succeed;
  try {
    succeed = document.execCommand('copy');
  } catch (e) {
    console.warn(e);
    succeed = false;
  }
  if (currentFocus && typeof currentFocus.focus === 'function') {
    currentFocus.focus();
  }
  if (succeed) {
    toast_success('Link Copy');
  }
  return succeed;
}

let js_today = () => {
  // Mendapatkan tanggal hari ini dalam format YYYY-MM-DD
  let today = new Date();
  let dd = String(today.getDate()).padStart(2, '0');
  let mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
  let yyyy = today.getFullYear();

  return yyyy + '-' + mm + '-' + dd;
};

function downloadFile(filename, link) {
  // Download file dari server menggunakan AJAX dan Blob
  $.ajax({
    url: link,
    method: 'GET',
    xhrFields: {
      responseType: 'blob',
    },
    success: function (data) {
      var a = document.createElement('a');
      var url = window.URL.createObjectURL(data);
      a.href = url;
      a.download = filename;
      document.body.append(a);
      a.click();
      a.remove();
      window.URL.revokeObjectURL(url);
    },
  });
}
