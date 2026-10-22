<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterController extends BaseApiController
{
    // =========================================================================
    // PELABUHAN
    // =========================================================================

    public function pelabuhan(): JsonResponse
    {
        $rows = DB::table('m_pelabuhan')
            ->whereNull('DELETED_AT')
            ->orderBy('NAMA', 'ASC')
            ->get();

        $data = $rows->map(function ($row) {
            return [
                'id'     => (int) $row->ID_PELABUHAN,
                'nama'   => $row->NAMA,
                'alamat' => $row->ALAMAT ?? '',
            ];
        });

        return $this->jsonSuccess('Data pelabuhan berhasil dimuat', $data);
    }

    public function create_pelabuhan(Request $request): JsonResponse
    {
        $nama = trim($request->input('nama', ''));
        $alamat = trim($request->input('alamat', ''));

        if (empty($nama)) {
            return $this->jsonError('Nama pelabuhan wajib diisi.', 422);
        }

        $existing = DB::table('m_pelabuhan')
            ->whereRaw('LOWER(NAMA) = ?', [strtolower($nama)])
            ->whereNull('DELETED_AT')
            ->first();

        if ($existing) {
            return $this->jsonSuccess('Data pelabuhan sudah terdaftar (ditautkan otomatis)', [
                'id'     => (int) $existing->ID_PELABUHAN,
                'nama'   => $existing->NAMA,
                'alamat' => $existing->ALAMAT,
            ]);
        }

        $now = date('Y-m-d H:i:s');
        $userId = $this->userId($request);

        $newId = DB::table('m_pelabuhan')->insertGetId([
            'NAMA'       => $nama,
            'ALAMAT'     => !empty($alamat) ? $alamat : null,
            'STATUS'     => 1,
            'CREATED_BY' => $userId,
            'UPDATED_BY' => $userId,
            'CREATED_AT' => $now,
            'UPDATED_AT' => $now,
        ]);

        return $this->jsonSuccess('Data pelabuhan berhasil ditambahkan', [
            'id'     => (int) $newId,
            'nama'   => $nama,
            'alamat' => !empty($alamat) ? $alamat : null,
        ], 201);
    }

    public function update_pelabuhan(Request $request): JsonResponse
    {
        $id = $request->input('id');
        $nama = trim($request->input('nama', ''));
        $alamat = trim($request->input('alamat', ''));

        if (empty($id) || !is_numeric($id)) {
            return $this->jsonError('ID pelabuhan tidak valid.', 422);
        }
        if (empty($nama)) {
            return $this->jsonError('Nama pelabuhan wajib diisi.', 422);
        }

        $existing = DB::table('m_pelabuhan')->where('ID_PELABUHAN', $id)->whereNull('DELETED_AT')->first();
        if (!$existing) {
            return $this->jsonError('Data pelabuhan tidak ditemukan.', 404);
        }

        $duplicate = DB::table('m_pelabuhan')
            ->whereRaw('LOWER(NAMA) = ?', [strtolower($nama)])
            ->where('ID_PELABUHAN', '!=', $id)
            ->whereNull('DELETED_AT')
            ->first();

        if ($duplicate) {
            return $this->jsonError('Pelabuhan dengan nama tersebut sudah terdaftar.', 422);
        }

        $now = date('Y-m-d H:i:s');
        DB::table('m_pelabuhan')->where('ID_PELABUHAN', $id)->update([
            'NAMA'       => $nama,
            'ALAMAT'     => !empty($alamat) ? $alamat : null,
            'UPDATED_BY' => $this->userId($request),
            'UPDATED_AT' => $now,
        ]);

        return $this->jsonSuccess('Data pelabuhan berhasil diperbarui', [
            'id'     => (int) $id,
            'nama'   => $nama,
            'alamat' => !empty($alamat) ? $alamat : null,
        ]);
    }

    // =========================================================================
    // KRK
    // =========================================================================

    public function krk(): JsonResponse
    {
        $rows = DB::table('m_krk')
            ->whereNull('DELETED_AT')
            ->orderBy('NAMA', 'ASC')
            ->get();

        $data = $rows->map(function ($row) {
            return [
                'id'         => (int) $row->ID_KRK,
                'nama'       => $row->NAMA,
                'keterangan' => $row->KETERANGAN ?? '',
            ];
        });

        return $this->jsonSuccess('Data KRK berhasil dimuat', $data);
    }

    public function create_krk(Request $request): JsonResponse
    {
        $nama = trim($request->input('nama', ''));
        $keterangan = trim($request->input('keterangan', ''));

        if (empty($nama)) {
            return $this->jsonError('Nama KRK wajib diisi.', 422);
        }

        $existing = DB::table('m_krk')->whereRaw('LOWER(NAMA) = ?', [strtolower($nama)])->whereNull('DELETED_AT')->first();
        if ($existing) {
            return $this->jsonSuccess('Data KRK sudah terdaftar (ditautkan otomatis)', [
                'id'         => (int) $existing->ID_KRK,
                'nama'       => $existing->NAMA,
                'keterangan' => $existing->KETERANGAN,
            ]);
        }

        $now = date('Y-m-d H:i:s');
        $userId = $this->userId($request);

        $newId = DB::table('m_krk')->insertGetId([
            'NAMA'       => $nama,
            'KETERANGAN' => !empty($keterangan) ? $keterangan : null,
            'STATUS'     => 1,
            'CREATED_BY' => $userId,
            'UPDATED_BY' => $userId,
            'CREATED_AT' => $now,
            'UPDATED_AT' => $now,
        ]);

        return $this->jsonSuccess('Data KRK berhasil ditambahkan', [
            'id'         => (int) $newId,
            'nama'       => $nama,
            'keterangan' => !empty($keterangan) ? $keterangan : null,
        ], 201);
    }

    public function update_krk(Request $request): JsonResponse
    {
        $id = $request->input('id');
        $nama = trim($request->input('nama', ''));
        $keterangan = trim($request->input('keterangan', ''));

        if (empty($id) || !is_numeric($id)) {
            return $this->jsonError('ID KRK tidak valid.', 422);
        }
        if (empty($nama)) {
            return $this->jsonError('Nama KRK wajib diisi.', 422);
        }

        $existing = DB::table('m_krk')->where('ID_KRK', $id)->whereNull('DELETED_AT')->first();
        if (!$existing) {
            return $this->jsonError('Data KRK tidak ditemukan.', 404);
        }

        $duplicate = DB::table('m_krk')
            ->whereRaw('LOWER(NAMA) = ?', [strtolower($nama)])
            ->where('ID_KRK', '!=', $id)
            ->whereNull('DELETED_AT')
            ->first();

        if ($duplicate) {
            return $this->jsonError('KRK dengan nama tersebut sudah terdaftar.', 422);
        }

        $now = date('Y-m-d H:i:s');
        DB::table('m_krk')->where('ID_KRK', $id)->update([
            'NAMA'       => $nama,
            'KETERANGAN' => !empty($keterangan) ? $keterangan : null,
            'UPDATED_BY' => $this->userId($request),
            'UPDATED_AT' => $now,
        ]);

        return $this->jsonSuccess('Data KRK berhasil diperbarui', [
            'id'         => (int) $id,
            'nama'       => $nama,
            'keterangan' => !empty($keterangan) ? $keterangan : null,
        ]);
    }

    // =========================================================================
    // SATUAN
    // =========================================================================

    public function satuan(): JsonResponse
    {
        $rows = DB::table('m_satuan')->whereNull('DELETED_AT')->orderBy('NAMA', 'ASC')->get();

        $data = $rows->map(function ($row) {
            return [
                'id'   => (int) $row->ID_SATUAN,
                'nama' => $row->NAMA,
            ];
        });

        return $this->jsonSuccess('Data satuan berhasil dimuat', $data);
    }

    public function create_satuan(Request $request): JsonResponse
    {
        $nama = trim($request->input('nama', ''));
        if (empty($nama)) {
            return $this->jsonError('Nama satuan wajib diisi.', 422);
        }

        $existing = DB::table('m_satuan')->whereRaw('LOWER(NAMA) = ?', [strtolower($nama)])->whereNull('DELETED_AT')->first();
        if ($existing) {
            return $this->jsonSuccess('Data satuan sudah terdaftar (ditautkan otomatis)', [
                'id'   => (int) $existing->ID_SATUAN,
                'nama' => $existing->NAMA,
            ]);
        }

        $now = date('Y-m-d H:i:s');
        $newId = DB::table('m_satuan')->insertGetId([
            'NAMA'       => $nama,
            'STATUS'     => 1,
            'CREATED_BY' => $this->userId($request),
            'UPDATED_BY' => $this->userId($request),
            'CREATED_AT' => $now,
            'UPDATED_AT' => $now,
        ]);

        return $this->jsonSuccess('Data satuan berhasil ditambahkan', [
            'id'   => (int) $newId,
            'nama' => $nama,
        ], 201);
    }

    public function update_satuan(Request $request): JsonResponse
    {
        $id = $request->input('id');
        $nama = trim($request->input('nama', ''));

        if (empty($id) || !is_numeric($id)) {
            return $this->jsonError('ID satuan tidak valid.', 422);
        }
        if (empty($nama)) {
            return $this->jsonError('Nama satuan wajib diisi.', 422);
        }

        $existing = DB::table('m_satuan')->where('ID_SATUAN', $id)->whereNull('DELETED_AT')->first();
        if (!$existing) {
            return $this->jsonError('Data satuan tidak ditemukan.', 404);
        }

        $now = date('Y-m-d H:i:s');
        DB::table('m_satuan')->where('ID_SATUAN', $id)->update([
            'NAMA'       => $nama,
            'UPDATED_BY' => $this->userId($request),
            'UPDATED_AT' => $now,
        ]);

        return $this->jsonSuccess('Data satuan berhasil diperbarui', [
            'id'   => (int) $id,
            'nama' => $nama,
        ]);
    }

    // =========================================================================
    // CATATAN
    // =========================================================================

    public function catatan(): JsonResponse
    {
        $rows = DB::table('m_catatan')->whereNull('DELETED_AT')->orderBy('NAMA', 'ASC')->get();

        $data = $rows->map(function ($row) {
            return [
                'id'         => (int) $row->ID_CATATAN,
                'nama'       => $row->NAMA,
                'keterangan' => $row->KETERANGAN ?? '',
            ];
        });

        return $this->jsonSuccess('Data catatan berhasil dimuat', $data);
    }

    public function create_catatan(Request $request): JsonResponse
    {
        $nama = trim($request->input('nama', ''));
        $keterangan = trim($request->input('keterangan', ''));

        if (empty($nama)) {
            return $this->jsonError('Nama catatan wajib diisi.', 422);
        }

        $existing = DB::table('m_catatan')->whereRaw('LOWER(NAMA) = ?', [strtolower($nama)])->whereNull('DELETED_AT')->first();
        if ($existing) {
            return $this->jsonSuccess('Data catatan sudah terdaftar (ditautkan otomatis)', [
                'id'         => (int) $existing->ID_CATATAN,
                'nama'       => $existing->NAMA,
                'keterangan' => $existing->KETERANGAN,
            ]);
        }

        $now = date('Y-m-d H:i:s');
        $newId = DB::table('m_catatan')->insertGetId([
            'NAMA'       => $nama,
            'KETERANGAN' => !empty($keterangan) ? $keterangan : null,
            'STATUS'     => 1,
            'CREATED_BY' => $this->userId($request),
            'UPDATED_BY' => $this->userId($request),
            'CREATED_AT' => $now,
            'UPDATED_AT' => $now,
        ]);

        return $this->jsonSuccess('Data catatan berhasil ditambahkan', [
            'id'         => (int) $newId,
            'nama'       => $nama,
            'keterangan' => !empty($keterangan) ? $keterangan : null,
        ], 201);
    }

    public function update_catatan(Request $request): JsonResponse
    {
        $id = $request->input('id');
        $nama = trim($request->input('nama', ''));
        $keterangan = trim($request->input('keterangan', ''));

        if (empty($id) || !is_numeric($id)) {
            return $this->jsonError('ID catatan tidak valid.', 422);
        }
        if (empty($nama)) {
            return $this->jsonError('Nama catatan wajib diisi.', 422);
        }

        $existing = DB::table('m_catatan')->where('ID_CATATAN', $id)->whereNull('DELETED_AT')->first();
        if (!$existing) {
            return $this->jsonError('Data catatan tidak ditemukan.', 404);
        }

        $now = date('Y-m-d H:i:s');
        DB::table('m_catatan')->where('ID_CATATAN', $id)->update([
            'NAMA'       => $nama,
            'KETERANGAN' => !empty($keterangan) ? $keterangan : null,
            'UPDATED_BY' => $this->userId($request),
            'UPDATED_AT' => $now,
        ]);

        return $this->jsonSuccess('Data catatan berhasil diperbarui', [
            'id'         => (int) $id,
            'nama'       => $nama,
            'keterangan' => !empty($keterangan) ? $keterangan : null,
        ]);
    }

    // =========================================================================
    // KAPAL
    // =========================================================================

    public function kapal(): JsonResponse
    {
        $rows = DB::table('m_kapal')->whereNull('DELETED_AT')->orderBy('NAMA', 'ASC')->get();

        $data = $rows->map(function ($row) {
            return [
                'id'   => (int) $row->ID_KAPAL,
                'nama' => $row->NAMA,
            ];
        });

        return $this->jsonSuccess('Data kapal berhasil dimuat', $data);
    }

    public function create_kapal(Request $request): JsonResponse
    {
        $nama = trim($request->input('nama', ''));
        if (empty($nama)) {
            return $this->jsonError('Nama kapal wajib diisi.', 422);
        }

        $existing = DB::table('m_kapal')->whereRaw('LOWER(NAMA) = ?', [strtolower($nama)])->whereNull('DELETED_AT')->first();
        if ($existing) {
            return $this->jsonSuccess('Data kapal sudah terdaftar (ditautkan otomatis)', [
                'id'   => (int) $existing->ID_KAPAL,
                'nama' => $existing->NAMA,
            ]);
        }

        $now = date('Y-m-d H:i:s');
        $newId = DB::table('m_kapal')->insertGetId([
            'NAMA'       => $nama,
            'STATUS'     => 1,
            'CREATED_BY' => $this->userId($request),
            'UPDATED_BY' => $this->userId($request),
            'CREATED_AT' => $now,
            'UPDATED_AT' => $now,
        ]);

        return $this->jsonSuccess('Data kapal berhasil ditambahkan', [
            'id'   => (int) $newId,
            'nama' => $nama,
        ], 201);
    }

    public function update_kapal(Request $request): JsonResponse
    {
        $id = $request->input('id');
        $nama = trim($request->input('nama', ''));

        if (empty($id) || !is_numeric($id)) {
            return $this->jsonError('ID kapal tidak valid.', 422);
        }
        if (empty($nama)) {
            return $this->jsonError('Nama kapal wajib diisi.', 422);
        }

        $existing = DB::table('m_kapal')->where('ID_KAPAL', $id)->whereNull('DELETED_AT')->first();
        if (!$existing) {
            return $this->jsonError('Data kapal tidak ditemukan.', 404);
        }

        $now = date('Y-m-d H:i:s');
        DB::table('m_kapal')->where('ID_KAPAL', $id)->update([
            'NAMA'       => $nama,
            'UPDATED_BY' => $this->userId($request),
            'UPDATED_AT' => $now,
        ]);

        return $this->jsonSuccess('Data kapal berhasil diperbarui', [
            'id'   => (int) $id,
            'nama' => $nama,
        ]);
    }

    // =========================================================================
    // CLIENT
    // =========================================================================

    public function client(): JsonResponse
    {
        $rows = DB::table('m_client')->whereNull('DELETED_AT')->orderBy('NAMA', 'ASC')->get();

        $data = $rows->map(function ($row) {
            return [
                'id'      => (int) $row->ID_CLIENT,
                'nama'    => $row->NAMA,
                'short'   => $row->SHORT ?? '',
                'alamat'  => $row->ALAMAT ?? '',
                'email'   => $row->EMAIL ?? '',
                'no_telp' => $row->NO_TELP ?? '',
            ];
        });

        return $this->jsonSuccess('Data client berhasil dimuat', $data);
    }

    public function create_client(Request $request): JsonResponse
    {
        $nama = trim($request->input('nama', ''));
        $short = trim($request->input('short', ''));
        $alamat = trim($request->input('alamat', ''));
        $email = trim($request->input('email', ''));
        $noTelp = trim($request->input('no_telp', ''));

        if (empty($nama)) {
            return $this->jsonError('Nama client wajib diisi.', 422);
        }

        $existing = DB::table('m_client')->whereRaw('LOWER(NAMA) = ?', [strtolower($nama)])->whereNull('DELETED_AT')->first();
        if ($existing) {
            return $this->jsonSuccess('Data client sudah terdaftar (ditautkan otomatis)', [
                'id'      => (int) $existing->ID_CLIENT,
                'nama'    => $existing->NAMA,
                'short'   => $existing->SHORT,
                'alamat'  => $existing->ALAMAT,
                'email'   => $existing->EMAIL,
                'no_telp' => $existing->NO_TELP,
            ]);
        }

        $now = date('Y-m-d H:i:s');
        $newId = DB::table('m_client')->insertGetId([
            'NAMA'       => $nama,
            'SHORT'      => !empty($short) ? $short : null,
            'ALAMAT'     => !empty($alamat) ? $alamat : null,
            'EMAIL'      => !empty($email) ? $email : null,
            'NO_TELP'    => !empty($noTelp) ? $noTelp : null,
            'STATUS'     => 1,
            'CREATED_BY' => $this->userId($request),
            'UPDATED_BY' => $this->userId($request),
            'CREATED_AT' => $now,
            'UPDATED_AT' => $now,
        ]);

        return $this->jsonSuccess('Data client berhasil ditambahkan', [
            'id'      => (int) $newId,
            'nama'    => $nama,
            'short'   => !empty($short) ? $short : null,
            'alamat'  => !empty($alamat) ? $alamat : null,
            'email'   => !empty($email) ? $email : null,
            'no_telp' => !empty($noTelp) ? $noTelp : null,
        ], 201);
    }

    public function update_client(Request $request): JsonResponse
    {
        $id = $request->input('id');
        $nama = trim($request->input('nama', ''));
        $short = trim($request->input('short', ''));
        $alamat = trim($request->input('alamat', ''));
        $email = trim($request->input('email', ''));
        $noTelp = trim($request->input('no_telp', ''));

        if (empty($id) || !is_numeric($id)) {
            return $this->jsonError('ID client tidak valid.', 422);
        }
        if (empty($nama)) {
            return $this->jsonError('Nama client wajib diisi.', 422);
        }

        $existing = DB::table('m_client')->where('ID_CLIENT', $id)->whereNull('DELETED_AT')->first();
        if (!$existing) {
            return $this->jsonError('Data client tidak ditemukan.', 404);
        }

        $now = date('Y-m-d H:i:s');
        DB::table('m_client')->where('ID_CLIENT', $id)->update([
            'NAMA'       => $nama,
            'SHORT'      => !empty($short) ? $short : null,
            'ALAMAT'     => !empty($alamat) ? $alamat : null,
            'EMAIL'      => !empty($email) ? $email : null,
            'NO_TELP'    => !empty($noTelp) ? $noTelp : null,
            'UPDATED_BY' => $this->userId($request),
            'UPDATED_AT' => $now,
        ]);

        return $this->jsonSuccess('Data client berhasil diperbarui', [
            'id'      => (int) $id,
            'nama'    => $nama,
            'short'   => !empty($short) ? $short : null,
            'alamat'  => !empty($alamat) ? $alamat : null,
            'email'   => !empty($email) ? $email : null,
            'no_telp' => !empty($noTelp) ? $noTelp : null,
        ]);
    }

    // =========================================================================
    // DERMAGA
    // =========================================================================

    public function dermaga(): JsonResponse
    {
        $rows = DB::table('m_dermaga as a')
            ->leftJoin('m_pelabuhan as b', 'a.ID_PELABUHAN', '=', 'b.ID_PELABUHAN')
            ->whereNull('a.DELETED_AT')
            ->select('a.*', 'b.NAMA as PELABUHAN_NAME')
            ->orderBy('a.NAMA', 'ASC')
            ->get();

        $data = $rows->map(function ($row) {
            return [
                'id'             => (int) $row->ID_DERMAGA,
                'pelabuhan_id'   => !empty($row->ID_PELABUHAN) ? (int) $row->ID_PELABUHAN : null,
                'pelabuhan_name' => $row->PELABUHAN_NAME ?? '',
                'nama'           => $row->NAMA,
                'alamat'         => $row->ALAMAT ?? '',
            ];
        });

        return $this->jsonSuccess('Data dermaga berhasil dimuat', $data);
    }

    public function create_dermaga(Request $request): JsonResponse
    {
        $pelabuhanId = $request->input('pelabuhan_id');
        $nama = trim($request->input('nama', ''));
        $alamat = trim($request->input('alamat', ''));

        if (empty($nama)) {
            return $this->jsonError('Nama dermaga wajib diisi.', 422);
        }

        $existing = DB::table('m_dermaga')->whereRaw('LOWER(NAMA) = ?', [strtolower($nama)])->whereNull('DELETED_AT')->first();
        if ($existing) {
            return $this->jsonSuccess('Data dermaga sudah terdaftar (ditautkan otomatis)', [
                'id'           => (int) $existing->ID_DERMAGA,
                'pelabuhan_id' => !empty($existing->ID_PELABUHAN) ? (int) $existing->ID_PELABUHAN : null,
                'nama'         => $existing->NAMA,
                'alamat'       => $existing->ALAMAT,
            ]);
        }

        $now = date('Y-m-d H:i:s');
        $newId = DB::table('m_dermaga')->insertGetId([
            'ID_PELABUHAN' => !empty($pelabuhanId) ? (int) $pelabuhanId : null,
            'NAMA'         => $nama,
            'ALAMAT'       => !empty($alamat) ? $alamat : null,
            'STATUS'       => 1,
            'CREATED_BY'   => $this->userId($request),
            'UPDATED_BY'   => $this->userId($request),
            'CREATED_AT'   => $now,
            'UPDATED_AT'   => $now,
        ]);

        return $this->jsonSuccess('Data dermaga berhasil ditambahkan', [
            'id'           => (int) $newId,
            'pelabuhan_id' => !empty($pelabuhanId) ? (int) $pelabuhanId : null,
            'nama'         => $nama,
            'alamat'       => !empty($alamat) ? $alamat : null,
        ], 201);
    }

    public function update_dermaga(Request $request): JsonResponse
    {
        $id = $request->input('id');
        $pelabuhanId = $request->input('pelabuhan_id');
        $nama = trim($request->input('nama', ''));
        $alamat = trim($request->input('alamat', ''));

        if (empty($id) || !is_numeric($id)) {
            return $this->jsonError('ID dermaga tidak valid.', 422);
        }
        if (empty($nama)) {
            return $this->jsonError('Nama dermaga wajib diisi.', 422);
        }

        $existing = DB::table('m_dermaga')->where('ID_DERMAGA', $id)->whereNull('DELETED_AT')->first();
        if (!$existing) {
            return $this->jsonError('Data dermaga tidak ditemukan.', 404);
        }

        $now = date('Y-m-d H:i:s');
        DB::table('m_dermaga')->where('ID_DERMAGA', $id)->update([
            'ID_PELABUHAN' => !empty($pelabuhanId) ? (int) $pelabuhanId : null,
            'NAMA'         => $nama,
            'ALAMAT'       => !empty($alamat) ? $alamat : null,
            'UPDATED_BY'   => $this->userId($request),
            'UPDATED_AT'   => $now,
        ]);

        return $this->jsonSuccess('Data dermaga berhasil diperbarui', [
            'id'           => (int) $id,
            'pelabuhan_id' => !empty($pelabuhanId) ? (int) $pelabuhanId : null,
            'nama'         => $nama,
            'alamat'       => !empty($alamat) ? $alamat : null,
        ]);
    }

    // =========================================================================
    // ALAT
    // =========================================================================

    public function alat(): JsonResponse
    {
        $rows = DB::table('m_alat')->whereNull('DELETED_AT')->orderBy('NAMA', 'ASC')->get();

        $data = $rows->map(function ($row) {
            return [
                'id'   => (int) $row->ID_ALAT,
                'nama' => $row->NAMA,
            ];
        });

        return $this->jsonSuccess('Data alat berhasil dimuat', $data);
    }

    public function create_alat(Request $request): JsonResponse
    {
        $nama = trim($request->input('nama', ''));
        if (empty($nama)) {
            return $this->jsonError('Nama alat wajib diisi.', 422);
        }

        $existing = DB::table('m_alat')->whereRaw('LOWER(NAMA) = ?', [strtolower($nama)])->whereNull('DELETED_AT')->first();
        if ($existing) {
            return $this->jsonSuccess('Data alat sudah terdaftar (ditautkan otomatis)', [
                'id'   => (int) $existing->ID_ALAT,
                'nama' => $existing->NAMA,
            ]);
        }

        $now = date('Y-m-d H:i:s');
        $newId = DB::table('m_alat')->insertGetId([
            'NAMA'       => $nama,
            'STATUS'     => 1,
            'CREATED_BY' => $this->userId($request),
            'UPDATED_BY' => $this->userId($request),
            'CREATED_AT' => $now,
            'UPDATED_AT' => $now,
        ]);

        return $this->jsonSuccess('Data alat berhasil ditambahkan', [
            'id'   => (int) $newId,
            'nama' => $nama,
        ], 201);
    }

    public function update_alat(Request $request): JsonResponse
    {
        $id = $request->input('id');
        $nama = trim($request->input('nama', ''));

        if (empty($id) || !is_numeric($id)) {
            return $this->jsonError('ID alat tidak valid.', 422);
        }
        if (empty($nama)) {
            return $this->jsonError('Nama alat wajib diisi.', 422);
        }

        $existing = DB::table('m_alat')->where('ID_ALAT', $id)->whereNull('DELETED_AT')->first();
        if (!$existing) {
            return $this->jsonError('Data alat tidak ditemukan.', 404);
        }

        $now = date('Y-m-d H:i:s');
        DB::table('m_alat')->where('ID_ALAT', $id)->update([
            'NAMA'       => $nama,
            'UPDATED_BY' => $this->userId($request),
            'UPDATED_AT' => $now,
        ]);

        return $this->jsonSuccess('Data alat berhasil diperbarui', [
            'id'   => (int) $id,
            'nama' => $nama,
        ]);
    }

    // =========================================================================
    // AKTIFITAS
    // =========================================================================

    public function aktifitas(): JsonResponse
    {
        $rows = DB::table('m_aktifitas as a')
            ->leftJoin('m_satuan as b', 'a.ID_SATUAN', '=', 'b.ID_SATUAN')
            ->whereNull('a.DELETED_AT')
            ->select('a.*', 'b.NAMA as SATUAN_NAME')
            ->orderBy('a.NAMA', 'ASC')
            ->get();

        $data = $rows->map(function ($row) {
            return [
                'id'          => (int) $row->ID_AKTIFITAS,
                'satuan_id'   => !empty($row->ID_SATUAN) ? (int) $row->ID_SATUAN : null,
                'satuan_name' => $row->SATUAN_NAME ?? '',
                'nama'        => $row->NAMA,
                'tipe'        => $row->TIPE ?? '',
            ];
        });

        return $this->jsonSuccess('Data aktifitas berhasil dimuat', $data);
    }

    public function create_aktifitas(Request $request): JsonResponse
    {
        $satuanId = $request->input('satuan_id');
        $nama = trim($request->input('nama', ''));
        $tipe = trim($request->input('tipe', ''));

        if (empty($nama)) {
            return $this->jsonError('Nama aktifitas wajib diisi.', 422);
        }

        $existing = DB::table('m_aktifitas')->whereRaw('LOWER(NAMA) = ?', [strtolower($nama)])->whereNull('DELETED_AT')->first();
        if ($existing) {
            return $this->jsonSuccess('Data aktifitas sudah terdaftar (ditautkan otomatis)', [
                'id'        => (int) $existing->ID_AKTIFITAS,
                'satuan_id' => !empty($existing->ID_SATUAN) ? (int) $existing->ID_SATUAN : null,
                'nama'      => $existing->NAMA,
                'tipe'      => $existing->TIPE,
            ]);
        }

        $now = date('Y-m-d H:i:s');
        $newId = DB::table('m_aktifitas')->insertGetId([
            'ID_SATUAN'  => !empty($satuanId) ? (int) $satuanId : null,
            'NAMA'       => $nama,
            'TIPE'       => !empty($tipe) ? $tipe : 'bongkar',
            'STATUS'     => 1,
            'CREATED_BY' => $this->userId($request),
            'UPDATED_BY' => $this->userId($request),
            'CREATED_AT' => $now,
            'UPDATED_AT' => $now,
        ]);

        return $this->jsonSuccess('Data aktifitas berhasil ditambahkan', [
            'id'        => (int) $newId,
            'satuan_id' => !empty($satuanId) ? (int) $satuanId : null,
            'nama'      => $nama,
            'tipe'      => !empty($tipe) ? $tipe : 'bongkar',
        ], 201);
    }

    public function update_aktifitas(Request $request): JsonResponse
    {
        $id = $request->input('id');
        $satuanId = $request->input('satuan_id');
        $nama = trim($request->input('nama', ''));
        $tipe = trim($request->input('tipe', ''));

        if (empty($id) || !is_numeric($id)) {
            return $this->jsonError('ID aktifitas tidak valid.', 422);
        }
        if (empty($nama)) {
            return $this->jsonError('Nama aktifitas wajib diisi.', 422);
        }

        $existing = DB::table('m_aktifitas')->where('ID_AKTIFITAS', $id)->whereNull('DELETED_AT')->first();
        if (!$existing) {
            return $this->jsonError('Data aktifitas tidak ditemukan.', 404);
        }

        $now = date('Y-m-d H:i:s');
        DB::table('m_aktifitas')->where('ID_AKTIFITAS', $id)->update([
            'ID_SATUAN'  => !empty($satuanId) ? (int) $satuanId : null,
            'NAMA'       => $nama,
            'TIPE'       => !empty($tipe) ? $tipe : 'bongkar',
            'UPDATED_BY' => $this->userId($request),
            'UPDATED_AT' => $now,
        ]);

        return $this->jsonSuccess('Data aktifitas berhasil diperbarui', [
            'id'        => (int) $id,
            'satuan_id' => !empty($satuanId) ? (int) $satuanId : null,
            'nama'      => $nama,
            'tipe'      => !empty($tipe) ? $tipe : 'bongkar',
        ]);
    }
}
