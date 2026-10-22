<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Pelabuhan;
use App\Services\WorkorderService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class WorkorderReportController extends Controller
{
    protected string $slug = 'report-work-order';
    protected string $permissionRead;
    protected string $permissionCreate;
    protected WorkorderService $workorderService;

    public function __construct(WorkorderService $workorderService)
    {
        $this->workorderService = $workorderService;
        $this->permissionRead   = 'read-' . $this->slug;
        $this->permissionCreate = 'create-' . $this->slug;
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

        $canCreate = $this->hasPerm($this->permissionCreate);

        return view('report.workorder', [
            'title' => 'Report Work Order',
            'title_header' => 'Report',
            'title_parent' => 'Work Order',
            'permission_create' => $canCreate ? 1 : 0,
        ]);
    }

    public function getReportData(Request $request): JsonResponse
    {
        if (!$this->hasPerm($this->permissionRead)) {
            return $this->jsonResponse(false, 'Akses tidak diizinkan');
        }

        try {
            $tStart    = $request->input('tgl_start', '');
            $tEnd      = $request->input('tgl_end', '');
            $pelabuhan = $request->input('pelabuhan', '');
            $client    = $request->input('client', '');
            $pengawas  = $request->input('pengawas', '');

            $results = $this->workorderService->tableList($tStart, $tEnd, $pelabuhan, $client, 'done', 0, $pengawas);

            $periode = '-';
            if (!empty($tStart) && !empty($tEnd)) {
                $periode = date('d/m/Y', strtotime($tStart)) . ' - ' . date('d/m/Y', strtotime($tEnd));
            } elseif (!empty($tStart)) {
                $periode = date('d/m/Y', strtotime($tStart)) . ' - Sekarang';
            }

            $hasPelabuhanFilter = !empty($pelabuhan);
            $hasClientFilter = !empty($client);
            $documents = [];

            foreach ($results as $v) {
                $woId = $v->ID_WORK_ORDER;
                $details = $this->workorderService->getWorkOrderDetails($woId);
                $alatList = $this->workorderService->getWoAlat($woId);
                $alatNames = [];
                foreach ($alatList as $a) {
                    $alatNames[] = e($a->NAMA ?? $a->NAMA_ALAT ?? '');
                }

                $totalGg = count($details);
                $kapalLine1 = e($v->NAMA_KAPAL ?? '-');
                $kapalLine2 = '(' . e($v->NAMA_CLIENT ?? '-') . ') ' . e($v->NAMA_DERMAGA ?? '-');
                $kapalLine3 = $totalGg . ' GG';
                if (!empty($v->NAMA_AKTIFITAS)) {
                    $satuan = !empty($v->NAMA_SATUAN) ? ' (' . e($v->NAMA_SATUAN) . ')' : '';
                    $kapalLine3 .= ', ' . e($v->NAMA_AKTIFITAS) . $satuan;
                }
                $kapalLine4 = !empty($alatNames) ? 'VIA ' . implode(', ', array_filter($alatNames)) : '';

                $detailRows = [];
                foreach ($details as $d) {
                    $detailRows[] = [
                        'tanggal'  => !empty($d->CREATED_AT) ? date('d/m/Y', strtotime($d->CREATED_AT)) : '-',
                        'nama_krk' => e($d->NAMA_KRK ?? '-'),
                        'hasil'    => !empty($d->HASIL) ? number_format($d->HASIL, 0, ',', '.') . ' <span class="text-muted">(' . e($v->NAMA_SATUAN ?? '-') . ')</span>' : '-',
                        'ransum'   => !empty($d->RANSUM) ? number_format($d->RANSUM, 0, ',', '.') . ' Bungkus' : '-',
                        'catatan'  => e($d->CATATAN ?? '-'),
                    ];
                }

                $woRow = [
                    'wo_id'       => $woId,
                    'kapal_line1' => $kapalLine1,
                    'kapal_line2' => $kapalLine2,
                    'kapal_line3' => $kapalLine3,
                    'kapal_line4' => $kapalLine4,
                    'details'     => $detailRows,
                    'total_gg'    => $totalGg,
                    'pelabuhan'   => e($v->NAMA_PELABUHAN ?? '-'),
                    'client'      => e($v->NAMA_CLIENT ?? '-'),
                    'pengawas'    => e($v->PENGAWAS_USER ?? '-'),
                    'tgl'         => $v->CREATED_AT ?? '-',
                ];

                $groupPelabuhan = $v->NAMA_PELABUHAN ?? 'Lainnya';
                $groupPengawas  = $v->PENGAWAS_USER ?? 'Tanpa Pengawas';
                $groupClient    = ($hasPelabuhanFilter && $hasClientFilter) ? ($v->NAMA_CLIENT ?? 'Lainnya') : '';
                $docKey = $groupPelabuhan . '||' . $groupClient . '||' . $groupPengawas;

                if (!isset($documents[$docKey])) {
                    $documents[$docKey] = [
                        'pelabuhan' => e($groupPelabuhan),
                        'client'    => $groupClient ? e($groupClient) : '',
                        'pengawas'  => e($groupPengawas),
                        'rows'      => [],
                        'total_gg'  => 0,
                    ];
                }

                $documents[$docKey]['rows'][] = $woRow;
                $documents[$docKey]['total_gg'] += $totalGg;
            }

            return $this->jsonResponse(true, '', [
                'documents' => array_values($documents),
                'total_wo'  => count($results),
                'periode'   => $periode,
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse(false, 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    public function getKegiatanKapal(Request $request): JsonResponse
    {
        if (!$this->hasPerm($this->permissionRead)) {
            return $this->jsonResponse(false, 'Akses tidak diizinkan');
        }

        try {
            $pelabuhanId = $request->input('pelabuhan');
            $bulanTahun  = $request->input('bulan_tahun', date('Y-m'));

            if (empty($pelabuhanId) || !is_numeric($pelabuhanId)) {
                return $this->jsonResponse(false, 'Pelabuhan wajib dipilih');
            }

            $pelabuhan = Pelabuhan::find($pelabuhanId);
            $pelabuhanNama = $pelabuhan ? strtoupper($pelabuhan->NAMA) : 'PELABUHAN';

            $blnNum = date('m', strtotime($bulanTahun . '-01'));
            $thnNum = date('Y', strtotime($bulanTahun . '-01'));
            $namaBulanArr = [
                '01' => 'JANUARI', '02' => 'FEBRUARI', '03' => 'MARET', '04' => 'APRIL',
                '05' => 'MEI', '06' => 'JUNI', '07' => 'JULI', '08' => 'AGUSTUS',
                '09' => 'SEPTEMBER', '10' => 'OKTOBER', '11' => 'NOVEMBER', '12' => 'DESEMBER'
            ];
            $headerPeriode = ($namaBulanArr[$blnNum] ?? 'BULAN') . ' ' . $thnNum;

            $results = $this->workorderService->getKegiatanKapalReport($pelabuhanId, $bulanTahun);

            $rows = [];
            $summaryPbm = [];
            $summaryKomoditas = [];
            $grandTotalParty = 0;
            $no = 1;

            foreach ($results as $v) {
                $party = floatval($v->PARTY);
                $grandTotalParty += $party;

                $kegiatanStr = '-';
                if (!empty($v->TGL_MULAI_KEGIATAN) && !empty($v->TGL_SELESAI_KEGIATAN)) {
                    $kegiatanStr = date('d/m', strtotime($v->TGL_MULAI_KEGIATAN)) . ' - ' . date('d/m', strtotime($v->TGL_SELESAI_KEGIATAN));
                } elseif (!empty($v->TGL_MULAI_KEGIATAN)) {
                    $kegiatanStr = date('d/m', strtotime($v->TGL_MULAI_KEGIATAN));
                } elseif (!empty($v->TGL_WO)) {
                    $kegiatanStr = date('d/m', strtotime($v->TGL_WO));
                }

                $bongkarVal = ($v->TIPE_AKTIFITAS !== 'muat') ? e($v->NAMA_AKTIFITAS ?? '') : '';
                $muatVal    = ($v->TIPE_AKTIFITAS === 'muat') ? e($v->NAMA_AKTIFITAS ?? '') : '';
                $pbmName    = e($v->NAMA_PBM ?? '-');
                $komoditasName = e($v->NAMA_AKTIFITAS ?? '-');

                $rows[] = [
                    'no'        => $no++,
                    'kapal'     => e($v->NAMA_KAPAL ?? '-'),
                    'pbm'       => $pbmName,
                    'no_inv'    => !empty($v->NO_INVOICE) ? e($v->NO_INVOICE) : '-',
                    'bongkar'   => $bongkarVal,
                    'muat'      => $muatVal,
                    'party'     => number_format($party, 2, ',', '.'),
                    'party_raw' => $party,
                    'kegiatan'  => $kegiatanStr,
                ];

                $summaryPbm[$pbmName] = ($summaryPbm[$pbmName] ?? 0) + 1;
                $summaryKomoditas[$komoditasName] = ($summaryKomoditas[$komoditasName] ?? 0) + $party;
            }

            arsort($summaryPbm);
            $pbmTable = [];
            $pbmChartLabels = [];
            $pbmChartSeries = [];
            $pbmNo = 1;
            foreach ($summaryPbm as $pbmKey => $pbmCnt) {
                $pbmTable[] = [
                    'no'    => $pbmNo++,
                    'pbm'   => $pbmKey,
                    'total' => $pbmCnt,
                ];
                $pbmChartLabels[] = $pbmKey;
                $pbmChartSeries[] = (int)$pbmCnt;
            }

            arsort($summaryKomoditas);
            $komoditasTable = [];
            $komoditasChartLabels = [];
            $komoditasChartSeries = [];
            $komoditasNo = 1;
            foreach ($summaryKomoditas as $komKey => $komTotal) {
                $komoditasTable[] = [
                    'no'        => $komoditasNo++,
                    'komoditas' => $komKey,
                    'total'     => number_format($komTotal, 2, ',', '.'),
                    'total_raw' => $komTotal,
                ];
                $komoditasChartLabels[] = $komKey;
                $komoditasChartSeries[] = round($komTotal, 2);
            }

            $payload = [
                'header_title'           => 'KEGIATAN KAPAL PLB ' . $pelabuhanNama,
                'header_periode'         => 'BULAN : ' . $headerPeriode,
                'pelabuhan_nama'         => $pelabuhanNama,
                'rows'                   => $rows,
                'total_kapal'            => count($rows),
                'grand_total_party'      => number_format($grandTotalParty, 2, ',', '.'),
                'summary_pbm'            => $pbmTable,
                'summary_komoditas'      => $komoditasTable,
                'pbm_chart_labels'       => $pbmChartLabels,
                'pbm_chart_series'       => $pbmChartSeries,
                'komoditas_chart_labels' => $komoditasChartLabels,
                'komoditas_chart_series' => $komoditasChartSeries,
            ];
            $payload['data'] = $payload;

            return $this->jsonResponse(true, '', $payload);
        } catch (\Exception $e) {
            return $this->jsonResponse(false, 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }
}
