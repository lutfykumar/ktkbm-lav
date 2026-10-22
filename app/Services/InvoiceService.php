<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Invoice;
use App\Models\InvoiceItem;

class InvoiceService
{
    /**
     * Get DataTable listing for invoices
     */
    public function tableList(
        ?string $tStart = '',
        ?string $tEnd = '',
        $client = '',
        ?string $status = '',
        int $showDeleted = 0
    ): array {
        $query = DB::table('t_invoice as a')
            ->leftJoin('m_client as b', 'a.ID_CLIENT', '=', 'b.ID_CLIENT')
            ->leftJoin('m_pelabuhan as c', 'a.ID_PELABUHAN', '=', 'c.ID_PELABUHAN')
            ->leftJoin('t_work_order as d', 'a.ID_WORK_ORDER', '=', 'd.ID_WORK_ORDER')
            ->leftJoin('m_kapal as e', 'd.ID_KAPAL', '=', 'e.ID_KAPAL')
            ->leftJoin('s_users as f', 'a.CREATED_BY', '=', 'f.id');

        if (!empty($tStart) && !empty($tEnd)) {
            $query->whereBetween(DB::raw('DATE(a.TANGGAL_INVOICE)'), [$tStart, $tEnd]);
        }
        if (!empty($client)) {
            $query->where('a.ID_CLIENT', $client);
        }
        if (!empty($status)) {
            $query->where('a.STATUS', $status);
        }

        if ($showDeleted == 1) {
            $query->whereNotNull('a.DELETED_AT');
        } else {
            $query->whereNull('a.DELETED_AT');
        }

        return $query->select([
            'a.*',
            DB::raw("DATE_FORMAT(a.TANGGAL_INVOICE, '%d %b %Y') as TGL_INVOICE_FORMAT"),
            DB::raw("DATE_FORMAT(a.TANGGAL_JATUH_TEMPO, '%d %b %Y') as TGL_JATUH_TEMPO_FORMAT"),
            DB::raw("DATE_FORMAT(a.CREATED_AT, '%Y-%m-%d %H:%i') as CREATED_AT_FORMAT"),
            'b.NAMA as NAMA_CLIENT',
            'c.NAMA as NAMA_PELABUHAN',
            'd.NO_WORK_ORDER',
            'd.ID_KAPAL',
            'e.NAMA as NAMA_KAPAL',
            'f.username as CREATED_USER'
        ])
        ->orderByDesc('a.CREATED_AT')
        ->get()
        ->toArray();
    }

    /**
     * Get single invoice with relations
     */
    public function getInvoice(int $id): ?object
    {
        return DB::table('t_invoice as a')
            ->leftJoin('m_client as b', 'a.ID_CLIENT', '=', 'b.ID_CLIENT')
            ->leftJoin('m_pelabuhan as c', 'a.ID_PELABUHAN', '=', 'c.ID_PELABUHAN')
            ->leftJoin('t_work_order as d', 'a.ID_WORK_ORDER', '=', 'd.ID_WORK_ORDER')
            ->leftJoin('m_kapal as e', 'd.ID_KAPAL', '=', 'e.ID_KAPAL')
            ->leftJoin('m_dermaga as f', 'd.ID_DERMAGA', '=', 'f.ID_DERMAGA')
            ->leftJoin('m_aktifitas as g', 'd.ID_AKTIFITAS', '=', 'g.ID_AKTIFITAS')
            ->leftJoin('m_satuan as h', 'd.ID_SATUAN', '=', 'h.ID_SATUAN')
            ->leftJoin('s_users as i', 'a.CREATED_BY', '=', 'i.id')
            ->leftJoin('s_users as j', 'a.UPDATED_BY', '=', 'j.id')
            ->where('a.ID_INVOICE', $id)
            ->select([
                'a.*',
                DB::raw("DATE_FORMAT(a.TANGGAL_INVOICE, '%d %b %Y') AS TGL_INVOICE_FORMAT"),
                DB::raw("DATE_FORMAT(a.TANGGAL_JATUH_TEMPO, '%d %b %Y') AS TGL_JATUH_TEMPO_FORMAT"),
                DB::raw("DATE_FORMAT(a.TANGGAL_BAYAR, '%d %b %Y') AS TGL_BAYAR_FORMAT"),
                DB::raw("DATE_FORMAT(a.CREATED_AT, '%d %b %Y %H:%i') AS CREATED_AT_FORMAT"),
                DB::raw("DATE_FORMAT(a.UPDATED_AT, '%d %b %Y %H:%i') AS UPDATED_AT_FORMAT"),
                'b.NAMA AS NAMA_CLIENT',
                'b.ALAMAT AS ALAMAT_CLIENT',
                'b.KONTAK AS KONTAK_CLIENT',
                'c.NAMA AS NAMA_PELABUHAN',
                'd.NO_WORK_ORDER',
                'd.STATUS AS STATUS_WO',
                'd.KETERANGAN AS KETERANGAN_WO',
                'd.VOLUME AS VOLUME_WO',
                'd.ID_KAPAL',
                'd.ID_DERMAGA',
                'd.ID_AKTIFITAS',
                'd.ID_SATUAN',
                'e.NAMA AS NAMA_KAPAL',
                'f.NAMA AS NAMA_DERMAGA',
                'g.NAMA AS NAMA_AKTIFITAS',
                'h.NAMA AS NAMA_SATUAN',
                'i.username AS CREATED_USER',
                'j.username AS UPDATED_USER'
            ])
            ->first();
    }

    /**
     * Get items of an invoice
     */
    public function getInvoiceItems(int $invoiceId): array
    {
        return DB::table('t_invoice_item')
            ->where('ID_INVOICE', $invoiceId)
            ->whereNull('DELETED_AT')
            ->orderBy('ID_INVOICE_ITEM', 'asc')
            ->get()
            ->toArray();
    }

    /**
     * Generate invoice number with sequence INV-YYYYMM-XXXX
     */
    public function generateNoInvoice(): string
    {
        $yearMonth = date('Ym');
        $prefix = 'INV-' . $yearMonth . '-';

        $lastNo = DB::table('t_invoice')
            ->where('NO_INVOICE', 'LIKE', $prefix . '%')
            ->orderByDesc('ID_INVOICE')
            ->value('NO_INVOICE');

        if (!empty($lastNo)) {
            $lastSeq = substr($lastNo, -4);
            $nextSeq = intval($lastSeq) + 1;
        } else {
            $nextSeq = 1;
        }

        return $prefix . sprintf('%04d', $nextSeq);
    }

    /**
     * Create invoice with items
     */
    public function createFromWo(int $woId, array $items = [], array $data = []): int
    {
        $now = date('Y-m-d H:i:s');
        $noInvoice = $this->generateNoInvoice();
        $data['NO_INVOICE'] = $noInvoice;
        $data['ID_WORK_ORDER'] = $woId;
        $data['CREATED_AT'] = $now;
        $data['UPDATED_AT'] = $now;

        $invoiceId = DB::table('t_invoice')->insertGetId($data);

        if (!empty($items)) {
            $this->insertItems($invoiceId, $items);
        }

        return $invoiceId;
    }

    /**
     * Insert batch items
     */
    public function insertItems(int $invoiceId, array $items = []): void
    {
        $now = date('Y-m-d H:i:s');
        foreach ($items as $item) {
            if (empty($item['DESKRIPSI'])) continue;
            $qty = floatval($item['JUMLAH'] ?? 0);
            $price = floatval($item['HARGA_SATUAN'] ?? 0);
            $subtotal = $qty * $price;

            DB::table('t_invoice_item')->insert([
                'ID_INVOICE' => $invoiceId,
                'DESKRIPSI' => $item['DESKRIPSI'],
                'JUMLAH' => $qty,
                'SATUAN' => $item['SATUAN'] ?? null,
                'HARGA_SATUAN' => $price,
                'SUBTOTAL' => $subtotal,
                'CREATED_AT' => $now,
            ]);
        }
    }

    /**
     * Soft delete items
     */
    public function deleteItems(int $invoiceId): void
    {
        DB::table('t_invoice_item')
            ->where('ID_INVOICE', $invoiceId)
            ->update(['DELETED_AT' => date('Y-m-d H:i:s')]);
    }

    /**
     * Update invoice status
     */
    public function updateStatus(int $id, string $status, array $extra = []): bool
    {
        $data = array_merge([
            'STATUS' => $status,
            'UPDATED_AT' => date('Y-m-d H:i:s'),
        ], $extra);

        return (bool)DB::table('t_invoice')->where('ID_INVOICE', $id)->update($data);
    }

    /**
     * Get dashboard stats for invoice index
     */
    public function dashboardStats(): array
    {
        $today = date('Y-m-d');
        $monthStart = date('Y-m-01');
        $monthEnd = date('Y-m-t');

        // Auto-mark overdue
        DB::table('t_invoice')
            ->where('STATUS', 'sent')
            ->where('TANGGAL_JATUH_TEMPO', '<', $today)
            ->whereNull('DELETED_AT')
            ->update([
                'STATUS' => 'overdue',
                'UPDATED_AT' => date('Y-m-d H:i:s')
            ]);

        $unpaid = DB::table('t_invoice')
            ->whereIn('STATUS', ['draft', 'sent', 'overdue'])
            ->whereNull('DELETED_AT')
            ->sum('TOTAL');

        $paid = DB::table('t_invoice')
            ->where('STATUS', 'paid')
            ->whereBetween('TANGGAL_BAYAR', [$monthStart, $monthEnd])
            ->whereNull('DELETED_AT')
            ->sum('TOTAL');

        $overdueCount = DB::table('t_invoice')
            ->where('STATUS', 'overdue')
            ->whereNull('DELETED_AT')
            ->count();

        return [
            'total_unpaid' => floatval($unpaid),
            'total_paid' => floatval($paid),
            'total_overdue' => intval($overdueCount),
        ];
    }
}
