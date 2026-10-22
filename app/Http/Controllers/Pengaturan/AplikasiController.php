<?php

namespace App\Http\Controllers\Pengaturan;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use App\Services\AppSettingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class AplikasiController extends Controller
{
    protected string $permissionRead   = 'read-advanced-pengaturan-aplikasi';
    protected string $permissionUpdate = 'update-advanced-pengaturan-aplikasi';

    protected AppSettingService $settingService;

    public function __construct(AppSettingService $settingService)
    {
        $this->settingService = $settingService;
    }

    private function hasPerm(string $permission): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        if (in_array((int)$user->group_id, [1, 2])) return true;
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

        ActivityLogService::log('Lihat Halaman Pengaturan Aplikasi', 'view', 'pengaturan');

        return view('pengaturan.aplikasi.index', [
            'title' => 'Aplikasi',
            'title_parent' => 'Pengaturan',
        ]);
    }

    public function getSettings(): JsonResponse
    {
        if (!$this->hasPerm($this->permissionRead)) {
            return $this->jsonResponse(false, 'Anda tidak memiliki hak akses');
        }

        try {
            $settings = $this->settingService->getAllWithMetadata();

            $settingsArray = [];
            $metadataArray = [];

            foreach ($settings as $setting) {
                $settingsArray[$setting->key] = $setting->value;
                $metadataArray[$setting->key] = [
                    'input_type' => $setting->input_type,
                    'options' => $setting->options,
                    'validation_rules' => $setting->validation_rules,
                    'placeholder' => $setting->placeholder,
                    'help_text' => $setting->help_text,
                    'group' => $setting->group,
                    'description' => $setting->description,
                    'default_value' => $setting->default_value,
                ];
            }

            return response()->json([
                'status' => true,
                'msg' => 'Settings loaded successfully',
                'settings' => $settingsArray,
                'metadata' => $metadataArray,
                'csrf_token_name' => '_token',
                'csrf_token_hash' => csrf_token(),
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse(false, 'Gagal memuat pengaturan: ' . $e->getMessage());
        }
    }

    public function updateSettings(Request $request): JsonResponse
    {
        if (!$this->hasPerm($this->permissionUpdate)) {
            return $this->jsonResponse(false, 'Anda tidak memiliki hak akses');
        }

        $postData = $request->except(['_token', 'csrf_name']);
        if (empty($postData)) {
            return $this->jsonResponse(false, 'Tidak ada data pengaturan yang dikirim');
        }

        try {
            DB::beginTransaction();

            $updatedCount = 0;
            foreach ($postData as $key => $value) {
                $setting = DB::table('app_settings')->where('key', $key)->first();
                if ($setting) {
                    $valToSave = is_array($value) ? json_encode($value) : $value;
                    DB::table('app_settings')->where('key', $key)->update([
                        'value' => $valToSave,
                        'updated_at' => now(),
                    ]);
                    $updatedCount++;
                }
            }

            $this->settingService->clearCache();

            DB::commit();

            ActivityLogService::log("Mengupdate {$updatedCount} Pengaturan Aplikasi", 'update', 'pengaturan');

            return $this->jsonResponse(true, "Pengaturan berhasil diupdate ({$updatedCount} items)");
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->jsonResponse(false, 'Gagal mengupdate pengaturan: ' . $e->getMessage());
        }
    }

    public function resetSetting(Request $request): JsonResponse
    {
        if (!$this->hasPerm($this->permissionUpdate)) {
            return $this->jsonResponse(false, 'Anda tidak memiliki hak akses');
        }

        $key = $request->input('key');
        if (empty($key)) {
            return $this->jsonResponse(false, 'Setting key wajib diisi');
        }

        $setting = DB::table('app_settings')->where('key', $key)->first();
        if (!$setting) {
            return $this->jsonResponse(false, 'Setting tidak ditemukan');
        }

        try {
            DB::table('app_settings')->where('key', $key)->update([
                'value' => $setting->default_value,
                'updated_at' => now(),
            ]);

            $this->settingService->clearCache();

            ActivityLogService::log("Reset setting: {$key}", 'update', 'pengaturan');

            return $this->jsonResponse(true, 'Setting berhasil direset ke default');
        } catch (\Exception $e) {
            return $this->jsonResponse(false, 'Gagal reset setting: ' . $e->getMessage());
        }
    }

    public function uploadLogo(Request $request): JsonResponse
    {
        if (!$this->hasPerm($this->permissionUpdate)) {
            return $this->jsonResponse(false, 'Anda tidak memiliki hak akses');
        }

        $type = $request->input('type'); // 'logo_lg', 'logo_sm', 'favicon_png'
        if (!$request->hasFile('file')) {
            return $this->jsonResponse(false, 'Tidak ada file yang diunggah');
        }

        $file = $request->file('file');
        $uploadFolder = str_contains($type, 'favicon') ? 'assets/favicon' : 'assets/images';
        $fullPath = public_path($uploadFolder);

        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0777, true);
        }

        $ext = $file->getClientOriginalExtension();
        $filename = $type . '_' . time() . '.' . $ext;
        $file->move($fullPath, $filename);

        $relativePath = $uploadFolder . '/' . $filename;

        DB::table('app_settings')->where('key', $type)->update([
            'value' => $relativePath,
            'updated_at' => now(),
        ]);

        $this->settingService->clearCache();

        ActivityLogService::log("Upload asset {$type}", 'upload', 'pengaturan');

        return $this->jsonResponse(true, 'File uploaded successfully', ['file_path' => asset($relativePath)]);
    }

    public function exportSettings()
    {
        if (!$this->hasPerm($this->permissionRead)) {
            abort(403, 'Akses tidak diizinkan');
        }

        $settings = DB::table('app_settings')->get();
        $filename = 'app_settings_' . date('Ymd_His') . '.json';

        return response()->json($settings)
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function importSettings(Request $request): JsonResponse
    {
        if (!$this->hasPerm($this->permissionUpdate)) {
            return $this->jsonResponse(false, 'Anda tidak memiliki hak akses');
        }

        if (!$request->hasFile('file')) {
            return $this->jsonResponse(false, 'Tidak ada file yang diunggah');
        }

        $file = $request->file('file');
        $content = file_get_contents($file->getRealPath());
        $data = json_decode($content, true);

        if (!is_array($data)) {
            return $this->jsonResponse(false, 'Format file JSON tidak valid');
        }

        try {
            DB::beginTransaction();
            $count = 0;
            foreach ($data as $item) {
                if (!empty($item['key']) && isset($item['value'])) {
                    DB::table('app_settings')->where('key', $item['key'])->update([
                        'value' => $item['value'],
                        'updated_at' => now(),
                    ]);
                    $count++;
                }
            }
            $this->settingService->clearCache();
            DB::commit();

            return $this->jsonResponse(true, "Berhasil mengimpor {$count} pengaturan");
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->jsonResponse(false, 'Gagal mengimpor pengaturan: ' . $e->getMessage());
        }
    }
}
