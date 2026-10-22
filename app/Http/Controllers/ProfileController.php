<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
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
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login');
        }

        ActivityLogService::log('Lihat Profile Anda', 'view', 'profile');

        $activities = DB::table('s_user_activity_logs')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(25)
            ->get();

        $workorders = DB::table('t_work_order as wo')
            ->leftJoin('m_dermaga as md', 'md.ID_DERMAGA', '=', 'wo.ID_DERMAGA')
            ->leftJoin('m_kapal as mk', 'mk.ID_KAPAL', '=', 'wo.ID_KAPAL')
            ->where(function ($q) use ($user) {
                $q->where('wo.ID_PENGAWAS', $user->id)
                  ->orWhere('wo.CREATED_BY', $user->id);
            })
            ->whereNull('wo.DELETED_AT')
            ->select('wo.*', 'md.NAMA as DERMAGA_NAMA', 'mk.NAMA as KAPAL_NAMA')
            ->orderBy('wo.CREATED_AT', 'desc')
            ->limit(15)
            ->get();

        return view('profile.index', [
            'title' => 'Profile',
            'title_parent' => 'Account',
            'user' => $user,
            'activities' => $activities,
            'workorders' => $workorders,
        ]);
    }

    public function edit()
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login');
        }

        return view('profile.edit', [
            'title' => 'Profile Edit',
            'title_parent' => 'Account',
            'user' => $user,
        ]);
    }

    public function store_info(Request $request): JsonResponse
    {
        $modelId = $request->input('model_id', auth()->id());
        $user = User::find($modelId);

        if (!$user) {
            return $this->jsonResponse(false, 'User tidak ditemukan');
        }

        // Only allow self or super-admin
        if (auth()->id() !== (int)$modelId && !in_array((int)auth()->user()->group_id, [1, 2])) {
            return $this->jsonResponse(false, 'Anda tidak memiliki hak akses');
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $oldData = $user->toArray();

        $user->username = $request->input('name');
        $user->phone = $request->input('phone');

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $uploadPath = public_path('filemanager/avatar');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            $filename = md5(time() . '_' . $file->getClientOriginalName()) . '.' . $file->getClientOriginalExtension();
            $file->move($uploadPath, $filename);

            // remove old avatar if exists and not default
            if ($user->avatar && $user->avatar !== 'default.jpg' && file_exists($uploadPath . '/' . $user->avatar)) {
                @unlink($uploadPath . '/' . $user->avatar);
            }

            $user->avatar = $filename;
        }

        $user->save();

        ActivityLogService::log('Update Profile Info', 'update', 'profile', [
            'affected_record_id' => $user->id,
            'affected_table' => 's_users',
            'old_values' => $oldData,
            'new_values' => $user->toArray(),
        ]);

        return $this->jsonResponse(true, 'Data Berhasil diupdate');
    }

    public function store_password(Request $request): JsonResponse
    {
        $modelId = $request->input('model_id', auth()->id());
        $user = User::find($modelId);

        if (!$user) {
            return $this->jsonResponse(false, 'User tidak ditemukan');
        }

        // Only allow self or super-admin
        if (auth()->id() !== (int)$modelId && !in_array((int)auth()->user()->group_id, [1, 2])) {
            return $this->jsonResponse(false, 'Anda tidak memiliki hak akses');
        }

        $oldPassword = $request->input('oldpasswordInput');
        $newPassword = $request->input('newpasswordInput');
        $confirmPassword = $request->input('confirmpasswordInput');

        if (empty($oldPassword) || empty($newPassword) || empty($confirmPassword)) {
            return $this->jsonResponse(false, 'Syarat tidak lengkap');
        }

        if ($newPassword !== $confirmPassword) {
            return $this->jsonResponse(false, 'Password baru dan konfirmasi password tidak sama');
        }

        // Verify old password
        if (!Hash::check($oldPassword, $user->password)) {
            return $this->jsonResponse(false, 'Password lama tidak sesuai');
        }

        $user->password = Hash::make($newPassword);
        $user->save();

        ActivityLogService::log('Update Profile Password', 'update', 'profile', [
            'affected_record_id' => $user->id,
            'affected_table' => 's_users',
        ]);

        return $this->jsonResponse(true, 'Password Berhasil diupdate');
    }

    public function detail($id)
    {
        if (empty($id)) {
            abort(404);
        }

        $user = User::with('groups')->findOrFail($id);

        ActivityLogService::log('Lihat Profile ' . $user->username, 'view', 'profile');

        $activities = DB::table('s_user_activity_logs')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(25)
            ->get();

        $workorders = DB::table('t_work_order as wo')
            ->leftJoin('m_dermaga as md', 'md.ID_DERMAGA', '=', 'wo.ID_DERMAGA')
            ->leftJoin('m_kapal as mk', 'mk.ID_KAPAL', '=', 'wo.ID_KAPAL')
            ->where(function ($q) use ($user) {
                $q->where('wo.ID_PENGAWAS', $user->id)
                  ->orWhere('wo.CREATED_BY', $user->id);
            })
            ->whereNull('wo.DELETED_AT')
            ->select('wo.*', 'md.NAMA as DERMAGA_NAMA', 'mk.NAMA as KAPAL_NAMA')
            ->orderBy('wo.CREATED_AT', 'desc')
            ->limit(15)
            ->get();

        return view('profile.detail', [
            'title' => 'Profile ' . $user->username,
            'title_parent' => 'Account',
            'user' => $user,
            'activities' => $activities,
            'workorders' => $workorders,
        ]);
    }
}
