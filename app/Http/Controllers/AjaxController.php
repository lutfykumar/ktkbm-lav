<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AjaxController extends Controller
{
    /**
     * Search menu items by keyword (topbar live search)
     */
    public function searchMenu(Request $request)
    {
        $keywords = $request->input('keywords', '');
        if (empty($keywords)) {
            return response()->json(['status' => false]);
        }

        $menus = DB::table('s_menu')
            ->whereNotNull('HEADER_ID')
            ->whereNotNull('LINK')
            ->where('TAMPIL', 1)
            ->where('NAMA', 'like', "%{$keywords}%")
            ->orderBy('URUT', 'ASC')
            ->limit(5)
            ->get();

        $menuHtml = '';
        foreach ($menus as $v) {
            $menuHtml .= '<a href="' . url($v->LINK) . '" class="dropdown-item notify-item">
                <i class="fal ' . $v->ICON . ' align-middle fs-18 text-muted me-2"></i>
                <span>' . e($v->NAMA) . '</span>
            </a>';
        }

        if (!empty($menuHtml)) {
            return response()->json([
                'status' => true,
                'menu' => $menuHtml,
            ]);
        }

        return response()->json(['status' => false]);
    }

    public function getAllRoles(Request $request)
    {
        $s = $request->input('search');
        $query = DB::table('s_groups')->whereNull('DELETED_AT');
        if (!empty($s)) {
            $query->where('name', 'like', "%{$s}%");
        }
        $data = $query->select('id', 'name as text')->get();
        return response()->json($data);
    }

    public function getAllPelabuhan(Request $request)
    {
        $s = $request->input('search');
        $query = DB::table('m_pelabuhan')->whereNull('DELETED_AT')->where('STATUS', 1);
        if (!empty($s)) {
            $query->where('NAMA', 'like', "%{$s}%");
        }
        $data = $query->select('ID_PELABUHAN as id', 'NAMA as text')->get();
        return response()->json($data);
    }

    public function getAllKapal(Request $request)
    {
        $s = $request->input('search');
        $query = DB::table('m_kapal')->whereNull('DELETED_AT')->where('STATUS', 1);
        if (!empty($s)) {
            $query->where('NAMA', 'like', "%{$s}%");
        }
        $data = $query->select('ID_KAPAL as id', 'NAMA as text')->get();
        return response()->json($data);
    }

    public function getAllAlat(Request $request)
    {
        $s = $request->input('search');
        $query = DB::table('m_alat')->whereNull('DELETED_AT')->where('STATUS', 1);
        if (!empty($s)) {
            $query->where('NAMA', 'like', "%{$s}%");
        }
        $data = $query->select('ID_ALAT as id', 'NAMA as text')->get();
        return response()->json($data);
    }

    public function getAllAktifitas(Request $request)
    {
        $s = $request->input('search');
        $query = DB::table('m_aktifitas as a')
            ->leftJoin('m_satuan as b', 'a.ID_SATUAN', '=', 'b.ID_SATUAN')
            ->whereNull('a.DELETED_AT')
            ->where('a.STATUS', 1);
        if (!empty($s)) {
            $query->where('a.NAMA', 'like', "%{$s}%");
        }
        $rows = $query->select('a.ID_AKTIFITAS', 'a.NAMA', 'b.NAMA as NAMA_SATUAN')->get();
        $arr = [];
        foreach ($rows as $v) {
            $satuan = $v->NAMA_SATUAN ? ' - (' . $v->NAMA_SATUAN . ')' : '';
            $arr[] = [
                'id' => $v->ID_AKTIFITAS,
                'text' => $v->NAMA . $satuan,
            ];
        }
        return response()->json($arr);
    }

    public function getAllDermaga(Request $request)
    {
        $s = $request->input('search');
        $query = DB::table('m_dermaga as a')
            ->leftJoin('m_pelabuhan as b', 'a.ID_PELABUHAN', '=', 'b.ID_PELABUHAN')
            ->whereNull('a.DELETED_AT')
            ->where('a.STATUS', 1);
        if (!empty($s)) {
            $query->where('a.NAMA', 'like', "%{$s}%");
        }
        $rows = $query->select('a.ID_DERMAGA', 'a.NAMA', 'a.ID_PELABUHAN', 'b.NAMA as NAMA_PELABUHAN')->get();

        $grouped = [];
        foreach ($rows as $v) {
            $pelabuhanName = $v->NAMA_PELABUHAN ?: 'Tanpa Pelabuhan';
            $pelabuhanId = $v->ID_PELABUHAN ?: 0;
            if (!isset($grouped[$pelabuhanId])) {
                $grouped[$pelabuhanId] = [
                    'text' => $pelabuhanName,
                    'children' => []
                ];
            }
            $grouped[$pelabuhanId]['children'][] = [
                'id' => $v->ID_DERMAGA,
                'text' => $v->NAMA
            ];
        }

        return response()->json(array_values($grouped));
    }

    public function getAllJenisSatuan(Request $request)
    {
        $s = $request->input('search');
        $query = DB::table('m_satuan')->whereNull('DELETED_AT')->where('STATUS', 1);
        if (!empty($s)) {
            $query->where('NAMA', 'like', "%{$s}%");
        }
        $data = $query->select('ID_SATUAN as id', 'NAMA as text')->get();
        return response()->json($data);
    }

    public function getAllClient(Request $request)
    {
        $s = $request->input('search');
        $query = DB::table('m_client')->whereNull('DELETED_AT')->where('STATUS', 1);
        if (!empty($s)) {
            $query->where('NAMA', 'like', "%{$s}%");
        }
        $data = $query->select('ID_CLIENT as id', 'NAMA as text')->get();
        return response()->json($data);
    }

    public function getAllUsersByRole(Request $request)
    {
        $s = $request->input('search');
        $roleId = $request->input('role_id');

        $query = DB::table('s_users as u')
            ->join('s_users_groups as ug', 'ug.user_id', '=', 'u.id')
            ->whereNull('u.DELETED_AT')
            ->where('u.active', 1);

        if (!empty($roleId)) {
            $query->where('ug.group_id', $roleId);
        }
        if (!empty($s)) {
            $query->where('u.username', 'like', "%{$s}%");
        }

        $data = $query->select('u.id', 'u.username as text')->distinct()->get();
        return response()->json($data);
    }

    public function getAllHeader(Request $request)
    {
        $s = $request->input('search');
        $query = DB::table('s_menu')
            ->whereNull('HEADER_ID')
            ->whereNull('PARENT_ID')
            ->where('TAMPIL', 1);
        if (!empty($s)) {
            $query->where('NAMA', 'like', "%{$s}%");
        }
        $data = $query->orderBy('URUT', 'ASC')->select('ID_MENU as id', 'NAMA as text')->get();
        return response()->json($data);
    }

    public function getAllParent(Request $request)
    {
        $s = $request->input('search');
        $query = DB::table('s_menu')
            ->whereNotNull('HEADER_ID')
            ->whereNull('PARENT_ID')
            ->where('TAMPIL', 1);
        if (!empty($s)) {
            $query->where('NAMA', 'like', "%{$s}%");
        }
        $data = $query->orderBy('URUT', 'ASC')->select('ID_MENU as id', 'NAMA as text')->get();
        return response()->json($data);
    }

    public function getAllKrk(Request $request)
    {
        $s = $request->input('search');
        $query = DB::table('m_krk')->whereNull('DELETED_AT')->where('STATUS', 1);
        if (!empty($s)) {
            $query->where('NAMA', 'like', "%{$s}%");
        }
        $data = $query->select('ID_KRK as id', 'NAMA as text')->get();
        return response()->json($data);
    }

    public function getAllCatatan(Request $request)
    {
        $s = $request->input('search');
        $query = DB::table('m_catatan')->whereNull('DELETED_AT')->where('STATUS', 1);
        if (!empty($s)) {
            $query->where('NAMA', 'like', "%{$s}%");
        }
        $data = $query->select('ID_CATATAN as id', 'NAMA as text')->get();
        return response()->json($data);
    }

    public function storeKrk(Request $request)
    {
        $nama = trim($request->input('nama', ''));
        if (empty($nama)) {
            return response()->json(['id' => null, 'msg' => 'Nama KRK harus diisi']);
        }

        $id = DB::table('m_krk')->insertGetId([
            'NAMA' => $nama,
            'STATUS' => 1,
            'CREATED_BY' => auth()->id(),
            'UPDATED_BY' => auth()->id(),
            'CREATED_AT' => now(),
            'UPDATED_AT' => now(),
        ]);

        return response()->json(['id' => $id, 'text' => $nama]);
    }

    public function getAllWorkorderDone(Request $request)
    {
        $search = $request->input('search');
        $query = DB::table('t_work_order as a')
            ->leftJoin('m_pelabuhan as c', 'a.ID_PELABUHAN', '=', 'c.ID_PELABUHAN')
            ->leftJoin('m_client as d', 'a.ID_CLIENT', '=', 'd.ID_CLIENT')
            ->leftJoin('m_kapal as e', 'a.ID_KAPAL', '=', 'e.ID_KAPAL')
            ->whereNull('a.DELETED_AT')
            ->whereRaw("LOWER(a.STATUS) != 'hide'");

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('a.NO_WORK_ORDER', 'like', "%{$search}%")
                  ->orWhere('d.NAMA', 'like', "%{$search}%")
                  ->orWhere('e.NAMA', 'like', "%{$search}%");
            });
        }

        $items = $query->orderByDesc('a.ID_WORK_ORDER')
            ->limit(50)
            ->select([
                'a.ID_WORK_ORDER',
                'a.NO_WORK_ORDER',
                'a.STATUS',
                'd.NAMA as NAMA_CLIENT',
                'e.NAMA as NAMA_KAPAL',
                'c.NAMA as NAMA_PELABUHAN',
            ])
            ->get();

        return response()->json($items);
    }
}
