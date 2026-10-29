<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\UpahTkbm;
use App\Models\UpahTkbmDetail;
use App\Models\Workorder;
use Illuminate\Support\Facades\DB;

class UpahTkbmService
{
    /**
     * Generate Nomor Dokumen Internal: TKBM-UPH-YYMM-XXXX
     */
    public function generateNoDokumen(?string $tgl = null): string
    {
        $f_tgl = !empty($tgl) ? $tgl : date('Y-m-d');
        $ym = date('ym', strtotime($f_tgl));
        $prefix = 'TKBM-UPH-' . $ym . '-';

        $last = DB::table('t_upah_tkbm')
            ->where('NO_DOKUMEN', 'LIKE', $prefix . '%')
            ->orderByDesc('ID_UPAH_TKBM')
            ->value('NO_DOKUMEN');

        if (!empty($last)) {
            $seq = intval(substr($last, -4)) + 1;
        } else {
            $seq = 1;
        }

        return $prefix . sprintf('%04d', $seq);
    }

    /**
     * Generate Nomor Kwitansi Resmi Dinas: {Urutan}/KTKBM.Tgl/{Tahun}
     */
    public function generateNoKwitansi(?string $tgl = null): string
    {
        $f_tgl = !empty($tgl) ? $tgl : date('Y-m-d');
        $year = date('Y', strtotime($f_tgl));
        $suffix = '/KTKBM.Tgl/' . $year;

        $last = DB::table('t_upah_tkbm')
            ->where('NO_KWITANSI', 'LIKE', '%' . $suffix)
            ->orderByDesc('ID_UPAH_TKBM')
            ->value('NO_KWITANSI');

        if (!empty($last)) {
            $parts = explode('/', $last);
            $seq = intval($parts[0]) + 1;
        } else {
            // Default awal nomor dinas jika belum ada di sistem
            $seq = 1739;
        }

        return $seq . $suffix;
    }

    /**
     * Helper Terbilang Huruf Kapital Bahasa Indonesia
     */
    public function terbilangRupiah(float $angka): string
    {
        $nominal = round($angka);
        if ($nominal <= 0) {
            return 'NOL RUPIAH';
        }

        $teks = trim(terbilang($nominal));
        return strtoupper($teks) . ' RUPIAH';
    }

    /**
     * DataTables listing for Upah & Kwitansi TKBM
     */
    public function tableList(
        ?string $tStart = '',
        ?string $tEnd = '',
        $client = '',
        ?string $status = '',
        int $showDeleted = 0
    ): array {
        $query = DB::table('t_upah_tkbm as a')
            ->leftJoin('m_client as b', 'a.ID_CLIENT', '=', 'b.ID_CLIENT')
            ->leftJoin('m_kapal as c', 'a.ID_KAPAL', '=', 'c.ID_KAPAL')
            ->leftJoin('m_dermaga as d', 'a.ID_DERMAGA', '=', 'd.ID_DERMAGA')
            ->leftJoin('t_work_order as e', 'a.ID_WORK_ORDER', '=', 'e.ID_WORK_ORDER')
            ->leftJoin('s_users as f', 'a.CREATED_BY', '=', 'f.id');

        if (!empty($tStart) && !empty($tEnd)) {
            $query->whereBetween(DB::raw('DATE(a.CREATED_AT)'), [$tStart, $tEnd]);
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
            DB::raw("DATE_FORMAT(a.TGL_KEGIATAN_START, '%d %b %Y') as TGL_START_FORMAT"),
            DB::raw("DATE_FORMAT(a.TGL_KEGIATAN_END, '%d %b %Y') as TGL_END_FORMAT"),
            DB::raw("DATE_FORMAT(a.TGL_BAYAR, '%d %b %Y') as TGL_BAYAR_FORMAT"),
            DB::raw("DATE_FORMAT(a.CREATED_AT, '%Y-%m-%d %H:%i') as CREATED_AT_FORMAT"),
            'b.NAMA as NAMA_CLIENT',
            'c.NAMA as NAMA_KAPAL',
            'd.NAMA as NAMA_DERMAGA',
            'e.NO_WORK_ORDER',
            'f.username as CREATED_USER'
        ])
        ->orderByDesc('a.CREATED_AT')
        ->get()
        ->toArray();
    }

    /**
     * Dashboard stats for Upah TKBM
     */
    public function dashboardStats(): array
    {
        $base = DB::table('t_upah_tkbm')->whereNull('DELETED_AT');

        $totalDokumen = (clone $base)->count();
        $totalNominal = (clone $base)->whereIn('STATUS', ['published', 'paid'])->sum('GRAND_TOTAL');
        $totalLunas   = (clone $base)->where('STATUS', 'paid')->sum('GRAND_TOTAL');
        $totalPending = (clone $base)->whereIn('STATUS', ['draft', 'published'])->sum('GRAND_TOTAL');
        $countLunas   = (clone $base)->where('STATUS', 'paid')->count();
        $countPending = (clone $base)->whereIn('STATUS', ['draft', 'published'])->count();

        return [
            'total_dokumen' => $totalDokumen,
            'total_nominal' => floatval($totalNominal),
            'total_lunas'   => floatval($totalLunas),
            'total_pending' => floatval($totalPending),
            'count_lunas'   => $countLunas,
            'count_pending' => $countPending,
        ];
    }

    /**
     * Get single record with joins
     */
    public function getUpahTkbm(int $id): ?object
    {
        return DB::table('t_upah_tkbm as a')
            ->leftJoin('m_client as b', 'a.ID_CLIENT', '=', 'b.ID_CLIENT')
            ->leftJoin('m_kapal as c', 'a.ID_KAPAL', '=', 'c.ID_KAPAL')
            ->leftJoin('m_dermaga as d', 'a.ID_DERMAGA', '=', 'd.ID_DERMAGA')
            ->leftJoin('t_work_order as e', 'a.ID_WORK_ORDER', '=', 'e.ID_WORK_ORDER')
            ->leftJoin('m_pelabuhan as p', 'e.ID_PELABUHAN', '=', 'p.ID_PELABUHAN')
            ->leftJoin('s_users as f', 'a.CREATED_BY', '=', 'f.id')
            ->leftJoin('s_users as g', 'a.UPDATED_BY', '=', 'g.id')
            ->where('a.ID_UPAH_TKBM', $id)
            ->select([
                'a.*',
                'b.NAMA as NAMA_CLIENT',
                'b.ALAMAT as ALAMAT_CLIENT',
                'b.KONTAK as KONTAK_CLIENT',
                'c.NAMA as NAMA_KAPAL',
                'd.NAMA as NAMA_DERMAGA',
                'p.NAMA as NAMA_PELABUHAN',
                'e.NO_WORK_ORDER',
                'f.username as CREATED_USER',
                'g.username as UPDATED_USER',
                DB::raw("DATE_FORMAT(a.TGL_KEGIATAN_START, '%d %M %Y') as TGL_START_INDO"),
                DB::raw("DATE_FORMAT(a.TGL_KEGIATAN_END, '%d %M %Y') as TGL_END_INDO"),
                DB::raw("DATE_FORMAT(a.TGL_BAYAR, '%d %M %Y') as TGL_BAYAR_INDO"),
                DB::raw("DATE_FORMAT(a.CREATED_AT, '%d %M %Y') as TGL_BUAT_INDO"),
            ])
            ->first();
    }

    /**
     * Get details sorted by work date
     */
    public function getUpahTkbmDetails(int $upahId): array
    {
        return DB::table('t_upah_tkbm_detail')
            ->where('ID_UPAH_TKBM', $upahId)
            ->whereNull('DELETED_AT')
            ->orderBy('TGL_KERJA')
            ->orderBy('ID_UPAH_DETAIL')
            ->get()
            ->toArray();
    }

    /**
     * Pull data from Work Order to prepopulate form
     */
    public function pullFromWorkorder(int $woId): array
    {
        $wo = DB::table('t_work_order as a')
            ->leftJoin('m_client as b', 'a.ID_CLIENT', '=', 'b.ID_CLIENT')
            ->leftJoin('m_kapal as c', 'a.ID_KAPAL', '=', 'c.ID_KAPAL')
            ->leftJoin('m_dermaga as d', 'a.ID_DERMAGA', '=', 'd.ID_DERMAGA')
            ->leftJoin('m_aktifitas as e', 'a.ID_AKTIFITAS', '=', 'e.ID_AKTIFITAS')
            ->where('a.ID_WORK_ORDER', $woId)
            ->select([
                'a.*',
                'b.NAMA as NAMA_CLIENT',
                'c.NAMA as NAMA_KAPAL',
                'd.NAMA as NAMA_DERMAGA',
                'e.NAMA as NAMA_AKTIFITAS',
            ])
            ->first();

        if (!$wo) {
            return [];
        }

        $details = DB::table('t_work_order_detail as a')
            ->leftJoin('m_krk as b', 'a.ID_KRK', '=', 'b.ID_KRK')
            ->where('a.ID_WORK_ORDER', $woId)
            ->whereNull('a.DELETED_AT')
            ->select([
                'a.*',
                'b.NAMA as NAMA_KRK'
            ])
            ->orderBy('a.CREATED_AT')
            ->get();

        $rows = [];
        $totalTruck = 0;
        $totalVolume = 0;
        $tStart = null;
        $tEnd = null;

        foreach ($details as $d) {
            $date = date('Y-m-d', strtotime($d->CREATED_AT));
            $hour = intval(date('H', strtotime($d->CREATED_AT)));

            // Estimasi shift pelabuhan: Siang 07 - 19, Malam 19 - 07
            $jamKerja = ($hour >= 7 && $hour < 19) ? '07 - 19' : '19 - 07';

            $truck = intval($d->JUMLAH_TRUCK ?? 0);
            $hasil = floatval($d->HASIL ?? 0);

            $totalTruck += $truck;
            $totalVolume += $hasil;

            if ($tStart === null || $date < $tStart) $tStart = $date;
            if ($tEnd === null || $date > $tEnd) $tEnd = $date;

            $rows[] = [
                'tgl_kerja' => $date,
                'jam_kerja' => $jamKerja,
                'jumlah_gang' => 1,
                'jumlah_harian' => 0,
                'jumlah_truck' => $truck,
                'volume_borongan' => $hasil,
                'upah_nominal' => 0,
                'adm_nominal' => 0,
                'toeslag_nominal' => 0,
                'lembur_nominal' => 0,
                'jumlah_upah' => 0,
                'keterangan' => $d->NAMA_KRK ?? 'Kegiatan Lapangan',
            ];
        }

        return [
            'workorder' => $wo,
            'tgl_start' => $tStart ?: date('Y-m-d'),
            'tgl_end' => $tEnd ?: date('Y-m-d'),
            'total_truck' => $totalTruck,
            'total_volume' => $totalVolume,
            'suggested_rows' => $rows,
        ];
    }

    /**
     * Store Upah & Kwitansi TKBM
     */
    public function store(array $headerData, array $details, ?int $userId = null): int
    {
        return DB::transaction(function () use ($headerData, $details, $userId) {
            $now = date('Y-m-d H:i:s');

            if (empty($headerData['NO_DOKUMEN'])) {
                $headerData['NO_DOKUMEN'] = $this->generateNoDokumen($headerData['TGL_KEGIATAN_START'] ?? null);
            }
            if (empty($headerData['NO_KWITANSI'])) {
                $headerData['NO_KWITANSI'] = $this->generateNoKwitansi($headerData['TGL_KEGIATAN_START'] ?? null);
            }

            $grandTotal = floatval($headerData['GRAND_TOTAL'] ?? 0);
            $headerData['TERBILANG'] = $this->terbilangRupiah($grandTotal);
            $headerData['CREATED_BY'] = $userId;
            $headerData['UPDATED_BY'] = $userId;
            $headerData['CREATED_AT'] = $now;
            $headerData['UPDATED_AT'] = $now;

            $id = DB::table('t_upah_tkbm')->insertGetId($headerData);

            $this->insertDetails($id, $details);

            return $id;
        });
    }

    /**
     * Update Upah & Kwitansi TKBM
     */
    public function update(int $id, array $headerData, array $details, ?int $userId = null): bool
    {
        return DB::transaction(function () use ($id, $headerData, $details, $userId) {
            $now = date('Y-m-d H:i:s');

            $grandTotal = floatval($headerData['GRAND_TOTAL'] ?? 0);
            $headerData['TERBILANG'] = $this->terbilangRupiah($grandTotal);
            $headerData['UPDATED_BY'] = $userId;
            $headerData['UPDATED_AT'] = $now;

            DB::table('t_upah_tkbm')->where('ID_UPAH_TKBM', $id)->update($headerData);

            // Re-sync details: soft delete old, insert new
            DB::table('t_upah_tkbm_detail')->where('ID_UPAH_TKBM', $id)->delete();
            $this->insertDetails($id, $details);

            return true;
        });
    }

    /**
     * Insert detail rows
     */
    private function insertDetails(int $upahId, array $details): void
    {
        $now = date('Y-m-d H:i:s');
        $batch = [];

        foreach ($details as $d) {
            if (empty($d['tgl_kerja'])) continue;

            $gang = intval($d['jumlah_gang'] ?? 1);
            $harian = intval($d['jumlah_harian'] ?? 0);
            $truck = intval($d['jumlah_truck'] ?? 0);
            $vol = floatval($d['volume_borongan'] ?? 0);
            $upah = floatval($d['upah_nominal'] ?? 0);
            $adm = floatval($d['adm_nominal'] ?? 0);
            $toeslag = floatval($d['toeslag_nominal'] ?? 0);
            $lembur = floatval($d['lembur_nominal'] ?? 0);
            $total = floatval($d['jumlah_upah'] ?? ($upah + $adm + $toeslag + $lembur));

            $batch[] = [
                'ID_UPAH_TKBM' => $upahId,
                'TGL_KERJA' => $d['tgl_kerja'],
                'JAM_KERJA' => $d['jam_kerja'] ?? '07 - 19',
                'JUMLAH_GANG' => $gang,
                'JUMLAH_HARIAN' => $harian,
                'JUMLAH_TRUCK' => $truck,
                'VOLUME_BORONGAN' => $vol,
                'UPAH_NOMINAL' => $upah,
                'ADM_NOMINAL' => $adm,
                'TOESLAG_NOMINAL' => $toeslag,
                'LEMBUR_NOMINAL' => $lembur,
                'JUMLAH_UPAH' => $total,
                'KETERANGAN' => $d['keterangan'] ?? null,
                'CREATED_AT' => $now,
                'UPDATED_AT' => $now,
            ];
        }

        if (!empty($batch)) {
            DB::table('t_upah_tkbm_detail')->insert($batch);
        }
    }

    /**
     * Update payment settlement
     */
    public function updatePayment(int $id, string $tglBayar, ?string $buktiBayar = null, ?int $userId = null): bool
    {
        $update = [
            'STATUS' => 'paid',
            'TGL_BAYAR' => $tglBayar,
            'UPDATED_BY' => $userId,
            'UPDATED_AT' => date('Y-m-d H:i:s'),
        ];
        if (!empty($buktiBayar)) {
            $update['BUKTI_BAYAR'] = $buktiBayar;
        }

        return DB::table('t_upah_tkbm')->where('ID_UPAH_TKBM', $id)->update($update) > 0;
    }

    /**
     * Auto-bridge to commercial Invoice module
     */
    public function bridgeToInvoice(int $upahId, ?int $userId = null): int
    {
        $upah = $this->getUpahTkbm($upahId);
        if (!$upah) {
            throw new \Exception('Data Upah TKBM tidak ditemukan');
        }

        if (!empty($upah->REF_INVOICE_ID)) {
            return $upah->REF_INVOICE_ID;
        }

        return DB::transaction(function () use ($upah, $userId) {
            $invoiceService = app(InvoiceService::class);
            $noInvoice = $invoiceService->generateNoInvoice();
            $now = date('Y-m-d H:i:s');

            $invoiceData = [
                'NO_INVOICE' => $noInvoice,
                'ID_WORK_ORDER' => $upah->ID_WORK_ORDER,
                'ID_CLIENT' => $upah->ID_CLIENT,
                'ID_PELABUHAN' => DB::table('t_work_order')->where('ID_WORK_ORDER', $upah->ID_WORK_ORDER)->value('ID_PELABUHAN'),
                'TANGGAL_INVOICE' => $upah->TGL_KEGIATAN_END ?? date('Y-m-d'),
                'TANGGAL_JATUH_TEMPO' => date('Y-m-d', strtotime('+14 days')),
                'SUBTOTAL' => $upah->GRAND_TOTAL,
                'PAJAK_PERSEN' => 0,
                'PAJAK_NOMINAL' => 0,
                'TOTAL' => $upah->GRAND_TOTAL,
                'STATUS' => ($upah->STATUS === 'paid') ? 'paid' : 'sent',
                'CATATAN' => 'Tagihan Upah TKBM Kapal ' . ($upah->NAMA_KAPAL ?? '-') . ' (Ref: ' . $upah->NO_KWITANSI . ')',
                'CREATED_BY' => $userId,
                'UPDATED_BY' => $userId,
                'CREATED_AT' => $now,
                'UPDATED_AT' => $now,
            ];

            $invoiceId = DB::table('t_invoice')->insertGetId($invoiceData);

            // Add single consolidated item
            DB::table('t_invoice_item')->insert([
                'ID_INVOICE' => $invoiceId,
                'DESKRIPSI' => 'Jasa Upah TKBM Bongkar/Muat Kapal ' . ($upah->NAMA_KAPAL ?? '-') . ' (' . ($upah->KOMODITAS ?? '-') . ')',
                'JUMLAH' => $upah->TOTAL_VOLUME ?: 1,
                'SATUAN' => 'TON',
                'HARGA_SATUAN' => $upah->TOTAL_VOLUME > 0 ? ($upah->GRAND_TOTAL / $upah->TOTAL_VOLUME) : $upah->GRAND_TOTAL,
                'SUBTOTAL' => $upah->GRAND_TOTAL,
                'CREATED_AT' => $now,
            ]);

            // Link back to Upah TKBM
            DB::table('t_upah_tkbm')->where('ID_UPAH_TKBM', $upah->ID_UPAH_TKBM)->update([
                'REF_INVOICE_ID' => $invoiceId,
                'UPDATED_AT' => $now,
            ]);

            return $invoiceId;
        });
    }
}

