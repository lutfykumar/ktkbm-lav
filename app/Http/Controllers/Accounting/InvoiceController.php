<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Workorder;
use App\Services\InvoiceService;
use App\Services\WorkorderService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class InvoiceController extends Controller
{
    protected string $slug = 'accounting-invoice';
    protected string $permissionRead;
    protected string $permissionCreate;
    protected string $permissionUpdate;
    protected string $permissionDelete;
    protected InvoiceService $invoiceService;
    protected WorkorderService $workorderService;

    public function __construct(InvoiceService $invoiceService, WorkorderService $workorderService)
    {
        $this->invoiceService = $invoiceService;
        $this->workorderService = $workorderService;
        $this->permissionRead   = 'read-' . $this->slug;
        $this->permissionCreate = 'create-' . $this->slug;
        $this->permissionUpdate = 'update-' . $this->slug;
        $this->permissionDelete = 'delete-' . $this->slug;
    }

    private function hasPerm(string $permission): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        if ($user->group_id === 1) return true;
        return Gate::allows($permission);
    }

    private function jsonResponse(bool $status, string $msg, array $extra = []): JsonResponse
    {
        return response()->json(array_merge([
            'status' => $status,
            'msg' => $msg,
            'csrf_token_name' => '_token',
            'csrf_token_hash' => csrf_token(),
        ], $extra));
    }

    public function index()
    {
        if (!$this->hasPerm($this->permissionRead)) {
            abort(403, 'Akses tidak diizinkan');
        }

        return view('accounting.invoice.index', [
            'title' => 'Invoice',
            'title_header' => 'Invoice',
            'title_parent' => 'Accounting',
            'permission_create' => $this->hasPerm($this->permissionCreate) ? 1 : 0,
            'permission_update' => $this->hasPerm($this->permissionUpdate) ? 1 : 0,
            'permission_delete' => $this->hasPerm($this->permissionDelete) ? 1 : 0,
            'stats' => $this->invoiceService->dashboardStats(),
        ]);
    }

    public function table(Request $request)
    {
        if (!$this->hasPerm($this->permissionRead)) {
            return response()->json([]);
        }

        $tStart      = $request->input('tgl_start', '');
        $tEnd        = $request->input('tgl_end', '');
        $client      = $request->input('client', '');
        $status      = $request->input('status', '');
        $showDeleted = (int)$request->input('show_deleted', 0);

        $results = $this->invoiceService->tableList($tStart, $tEnd, $client, $status, $showDeleted);
        $data = [];

        foreach ($results as $v) {
            $action = $this->generateActionButtons($v, (bool)$showDeleted);

            $statusBadge = match (strtolower($v->STATUS ?? '')) {
                'draft' => '<span class="badge bg-secondary">Draft</span>',
                'sent' => '<span class="badge bg-info">Sent</span>',
                'paid' => '<span class="badge bg-success">Paid</span>',
                'overdue' => '<span class="badge bg-danger">Overdue</span>',
                'cancelled' => '<span class="badge bg-dark">Cancelled</span>',
                default => '<span class="badge bg-secondary">' . ucfirst(e($v->STATUS ?? '')) . '</span>',
            };

            $data[] = [
                !empty($v->NO_INVOICE) ? $v->NO_INVOICE : '-',
                !empty($v->NAMA_CLIENT) ? $v->NAMA_CLIENT : '-',
                !empty($v->NO_WORK_ORDER) ? $v->NO_WORK_ORDER : '-',
                ribuan($v->TOTAL),
                $statusBadge,
                $v->TGL_INVOICE_FORMAT ?? '-',
                !empty($action) ? $action : '-',
            ];
        }

        return response()->json($data);
    }

    private function generateActionButtons($data, bool $showDeleted = false): string
    {
        $btnId   = $data->ID_INVOICE;
        $btnName = $data->NO_INVOICE;
        $action   = '<div class="btn-group">';

        if ($showDeleted) {
            if ($this->hasPerm($this->permissionUpdate)) {
                $action .= '<button type="button" class="btn btn-sm btn-success btn-icon waves-effect btn-restore"
                                data-bs-toggle="tooltip" title="Restore" data-id="' . $btnId . '">
                            <i class="fal fa-trash-restore"></i></button>';
            }
            $action .= '</div>';
            return $action;
        }

        if ($this->hasPerm($this->permissionRead)) {
            $action .= '<a href="' . url('accounting/invoice/detail/' . $btnId) . '" class="btn btn-sm btn-info btn-icon waves-effect"
                          data-bs-toggle="tooltip" title="Detail"><i class="fal fa-eye"></i></a>';

            $action .= '<a href="' . url('accounting/invoice/print_invoice/' . $btnId) . '" target="_blank" class="btn btn-sm btn-secondary btn-icon waves-effect"
                          data-bs-toggle="tooltip" title="Cetak"><i class="fal fa-print"></i></a>';
        }

        if ($this->hasPerm($this->permissionUpdate) && $data->STATUS === 'draft') {
            $action .= '<a href="' . url('accounting/invoice/edit/' . $btnId) . '" class="btn btn-sm btn-warning btn-icon waves-effect"
                          data-bs-toggle="tooltip" title="Edit"><i class="fal fa-pen"></i></a>';
        }

        if ($this->hasPerm($this->permissionDelete) && $data->STATUS === 'draft') {
            $action .= '<button type="button" class="btn btn-sm btn-danger btn-icon waves-effect btn-delete"
                            data-bs-toggle="tooltip" title="Hapus" data-id="' . $btnId . '" data-title="' . e($btnName) . '">
                        <i class="fal fa-trash-alt"></i></button>';
        }

        $action .= '</div>';
        return $action;
    }

    public function create($wo_id = null)
    {
        if (!$this->hasPerm($this->permissionCreate)) {
            abort(403, 'Akses tidak diizinkan');
        }

        $wo = null;
        if (!empty($wo_id)) {
            $wo = $this->workorderService->getWorkorder((int)$wo_id);
        }

        return view('accounting.invoice.form', [
            'wo' => $wo,
            'mode' => 'create',
            'title' => 'Buat Invoice',
            'title_header' => 'Buat Invoice Baru',
            'title_parent' => 'Invoice',
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        if (!$this->hasPerm($this->permissionCreate)) {
            return $this->jsonResponse(false, 'Anda tidak memiliki hak akses');
        }

        $idWorkOrder = $request->input('id_work_order');
        $tglInvoice   = $request->input('tanggal_invoice');
        $tglTempo     = $request->input('tanggal_jatuh_tempo');
        $pajakPersen  = floatval($request->input('pajak_persen', 11));
        $catatan       = $request->input('catatan');
        $statusInput  = $request->input('status', 'draft');

        $deskripsi     = $request->input('deskripsi', []);
        $jumlah        = $request->input('jumlah', []);
        $satuan        = $request->input('satuan', []);
        $hargaSatuan  = $request->input('harga_satuan', []);

        if (empty($idWorkOrder) || empty($tglInvoice) || empty($tglTempo)) {
            return $this->jsonResponse(false, 'Semua field wajib diisi');
        }

        $wo = $this->workorderService->getWorkorder((int)$idWorkOrder);
        if (!$wo) {
            return $this->jsonResponse(false, 'Data Work Order tidak ditemukan');
        }

        $items = [];
        $subtotal = 0;
        if (is_array($deskripsi)) {
            for ($i = 0; $i < count($deskripsi); $i++) {
                if (empty($deskripsi[$i])) continue;
                $qty = floatval($jumlah[$i] ?? 0);
                $price = floatval($hargaSatuan[$i] ?? 0);
                $sub = $qty * $price;
                $subtotal += $sub;

                $items[] = [
                    'DESKRIPSI' => $deskripsi[$i],
                    'JUMLAH' => $qty,
                    'SATUAN' => $satuan[$i] ?? '',
                    'HARGA_SATUAN' => $price,
                ];
            }
        }

        if (empty($items)) {
            return $this->jsonResponse(false, 'Minimal harus ada 1 item invoice');
        }

        $pajakNominal = ($subtotal * $pajakPersen) / 100;
        $total = $subtotal + $pajakNominal;
        $userId = auth()->id();

        $data = [
            'ID_CLIENT' => $wo->ID_CLIENT,
            'ID_PELABUHAN' => $wo->ID_PELABUHAN,
            'TANGGAL_INVOICE' => $tglInvoice,
            'TANGGAL_JATUH_TEMPO' => $tglTempo,
            'SUBTOTAL' => $subtotal,
            'PAJAK_PERSEN' => $pajakPersen,
            'PAJAK_NOMINAL' => $pajakNominal,
            'TOTAL' => $total,
            'STATUS' => in_array($statusInput, ['draft', 'sent']) ? $statusInput : 'draft',
            'CATATAN' => $catatan,
            'CREATED_BY' => $userId,
            'UPDATED_BY' => $userId,
        ];

        try {
            DB::beginTransaction();
            $invoiceId = $this->invoiceService->createFromWo($idWorkOrder, $items, $data);
            DB::commit();

            return $this->jsonResponse(true, 'Invoice berhasil dibuat', ['id' => $invoiceId]);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->jsonResponse(false, 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    public function detail($id)
    {
        if (!$this->hasPerm($this->permissionRead)) {
            abort(403, 'Akses tidak diizinkan');
        }

        $invoice = $this->invoiceService->getInvoice((int)$id);
        if (!$invoice) {
            return redirect('accounting/invoice');
        }

        $items = $this->invoiceService->getInvoiceItems((int)$id);

        return view('accounting.invoice.detail', [
            'invoice' => $invoice,
            'items' => $items,
            'title' => 'Detail Invoice ' . $invoice->NO_INVOICE,
            'title_header' => 'Detail Invoice ' . $invoice->NO_INVOICE,
            'title_parent' => 'Invoice',
        ]);
    }

    public function edit($id)
    {
        if (!$this->hasPerm($this->permissionUpdate)) {
            abort(403, 'Akses tidak diizinkan');
        }

        $invoice = $this->invoiceService->getInvoice((int)$id);
        if (!$invoice || $invoice->STATUS !== 'draft') {
            return redirect('accounting/invoice');
        }

        $items = $this->invoiceService->getInvoiceItems((int)$id);
        $wo = $this->workorderService->getWorkorder((int)$invoice->ID_WORK_ORDER);

        return view('accounting.invoice.form', [
            'invoice' => $invoice,
            'items' => $items,
            'wo' => $wo,
            'mode' => 'edit',
            'title' => 'Edit Invoice ' . $invoice->NO_INVOICE,
            'title_header' => 'Edit Invoice ' . $invoice->NO_INVOICE,
            'title_parent' => 'Invoice',
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        if (!$this->hasPerm($this->permissionUpdate)) {
            return $this->jsonResponse(false, 'Anda tidak memiliki hak akses');
        }

        $idInvoice    = $request->input('id_invoice');
        $tglInvoice   = $request->input('tanggal_invoice');
        $tglTempo     = $request->input('tanggal_jatuh_tempo');
        $pajakPersen  = floatval($request->input('pajak_persen', 11));
        $catatan       = $request->input('catatan');
        $statusUpdate = $request->input('status', 'draft');

        $deskripsi     = $request->input('deskripsi', []);
        $jumlah        = $request->input('jumlah', []);
        $satuan        = $request->input('satuan', []);
        $hargaSatuan  = $request->input('harga_satuan', []);

        $invoice = $this->invoiceService->getInvoice((int)$idInvoice);
        if (!$invoice || $invoice->STATUS !== 'draft') {
            return $this->jsonResponse(false, 'Invoice tidak ditemukan atau tidak dapat diedit');
        }

        $items = [];
        $subtotal = 0;
        if (is_array($deskripsi)) {
            for ($i = 0; $i < count($deskripsi); $i++) {
                if (empty($deskripsi[$i])) continue;
                $qty = floatval($jumlah[$i] ?? 0);
                $price = floatval($hargaSatuan[$i] ?? 0);
                $sub = $qty * $price;
                $subtotal += $sub;

                $items[] = [
                    'DESKRIPSI' => $deskripsi[$i],
                    'JUMLAH' => $qty,
                    'SATUAN' => $satuan[$i] ?? '',
                    'HARGA_SATUAN' => $price,
                ];
            }
        }

        $pajakNominal = ($subtotal * $pajakPersen) / 100;
        $total = $subtotal + $pajakNominal;

        $data = [
            'TANGGAL_INVOICE' => $tglInvoice,
            'TANGGAL_JATUH_TEMPO' => $tglTempo,
            'SUBTOTAL' => $subtotal,
            'PAJAK_PERSEN' => $pajakPersen,
            'PAJAK_NOMINAL' => $pajakNominal,
            'TOTAL' => $total,
            'STATUS' => in_array($statusUpdate, ['draft', 'sent']) ? $statusUpdate : 'draft',
            'CATATAN' => $catatan,
            'UPDATED_BY' => auth()->id(),
            'UPDATED_AT' => date('Y-m-d H:i:s'),
        ];

        try {
            DB::beginTransaction();
            DB::table('t_invoice')->where('ID_INVOICE', $idInvoice)->update($data);
            $this->invoiceService->deleteItems((int)$idInvoice);
            $this->invoiceService->insertItems((int)$idInvoice, $items);
            DB::commit();

            return $this->jsonResponse(true, 'Invoice berhasil diupdate');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->jsonResponse(false, 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    public function delete(Request $request): JsonResponse
    {
        if (!$this->hasPerm($this->permissionDelete)) {
            return $this->jsonResponse(false, 'Anda tidak memiliki hak akses');
        }

        $id = $request->input('id');
        $invoice = Invoice::find($id);

        if (!$invoice || $invoice->STATUS !== 'draft') {
            return $this->jsonResponse(false, 'Hanya invoice draft yang bisa dihapus');
        }

        $invoice->delete();
        InvoiceItem::where('ID_INVOICE', $id)->delete();

        return $this->jsonResponse(true, 'Data berhasil dihapus');
    }

    public function restore(Request $request): JsonResponse
    {
        if (!$this->hasPerm($this->permissionUpdate)) {
            return $this->jsonResponse(false, 'Anda tidak memiliki hak akses');
        }

        $id = $request->input('id');
        $invoice = Invoice::onlyTrashed()->find($id);
        if (!$invoice) {
            return $this->jsonResponse(false, 'Data tidak ditemukan');
        }

        $invoice->restore();
        InvoiceItem::onlyTrashed()->where('ID_INVOICE', $id)->restore();

        return $this->jsonResponse(true, 'Data berhasil dipulihkan');
    }

    public function updatePayment(Request $request): JsonResponse
    {
        if (!$this->hasPerm($this->permissionUpdate)) {
            return $this->jsonResponse(false, 'Anda tidak memiliki hak akses');
        }

        $id = $request->input('id_invoice');
        $tglBayar = $request->input('tanggal_bayar');
        $metode = $request->input('metode_bayar');

        $invoice = Invoice::find($id);
        if (!$invoice || in_array($invoice->STATUS, ['draft', 'cancelled', 'paid'])) {
            return $this->jsonResponse(false, 'Invoice tidak valid untuk pembayaran');
        }

        $buktiPath = null;
        if ($request->hasFile('bukti_bayar')) {
            $file = $request->file('bukti_bayar');
            $year = date('Y');
            $month = date('m');
            $uploadPath = public_path("filemanager/invoice/{$year}/{$month}");
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }
            $filename = uniqid('pay_') . '.' . $file->getClientOriginalExtension();
            $file->move($uploadPath, $filename);
            $buktiPath = "filemanager/invoice/{$year}/{$month}/{$filename}";
        }

        $updateData = [
            'TANGGAL_BAYAR' => $tglBayar,
            'METODE_BAYAR' => $metode,
            'STATUS' => 'paid',
            'UPDATED_BY' => auth()->id(),
            'UPDATED_AT' => now(),
        ];

        if ($buktiPath) {
            $updateData['BUKTI_BAYAR'] = $buktiPath;
        }

        $invoice->update($updateData);

        return $this->jsonResponse(true, 'Pembayaran berhasil dicatat');
    }

    public function cancel(Request $request): JsonResponse
    {
        if (!$this->hasPerm($this->permissionUpdate)) {
            return $this->jsonResponse(false, 'Anda tidak memiliki hak akses');
        }

        $id = $request->input('id');
        $invoice = Invoice::find($id);

        if (!$invoice || in_array($invoice->STATUS, ['paid', 'cancelled'])) {
            return $this->jsonResponse(false, 'Invoice tidak dapat dibatalkan');
        }

        $invoice->update([
            'STATUS' => 'cancelled',
            'UPDATED_BY' => auth()->id(),
            'UPDATED_AT' => now(),
        ]);

        return $this->jsonResponse(true, 'Invoice berhasil dibatalkan');
    }

    public function printInvoice($id)
    {
        if (!$this->hasPerm($this->permissionRead)) {
            abort(403, 'Akses ditolak');
        }

        $invoice = $this->invoiceService->getInvoice((int)$id);
        if (!$invoice) {
            abort(404, 'Invoice tidak ditemukan');
        }

        $items = $this->invoiceService->getInvoiceItems((int)$id);

        return view('accounting.invoice.print', [
            'invoice' => $invoice,
            'items' => $items,
            'title' => 'Print Invoice ' . $invoice->NO_INVOICE,
        ]);
    }
}
