<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class EmailController extends Controller
{
    protected string $permissionRead   = 'read-master-email-template';
    protected string $permissionCreate = 'create-master-email-template';
    protected string $permissionUpdate = 'update-master-email-template';
    protected string $permissionDelete = 'delete-master-email-template';

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

        ActivityLogService::log('Lihat List Email Template', 'view', 'master');

        $categories = EmailTemplate::select('CATEGORY')
            ->whereNotNull('CATEGORY')
            ->where('CATEGORY', '!=', '')
            ->distinct()
            ->pluck('CATEGORY')
            ->toArray();

        return view('master.email_template.index', [
            'title' => 'Email Templates',
            'title_parent' => 'Master',
            'categories' => $categories,
            'can_create' => $this->hasPerm($this->permissionCreate),
            'can_update' => $this->hasPerm($this->permissionUpdate),
            'can_delete' => $this->hasPerm($this->permissionDelete),
        ]);
    }

    public function table(Request $request): JsonResponse
    {
        if (!$this->hasPerm($this->permissionRead)) {
            return $this->jsonResponse(false, 'Unauthorized');
        }

        $query = EmailTemplate::query();

        if ($request->filled('category')) {
            $query->where('CATEGORY', $request->input('category'));
        }

        if ($request->filled('is_active')) {
            $query->where('IS_ACTIVE', (int)$request->input('is_active'));
        }

        $templates = $query->orderBy('TEMPLATE_NAME', 'asc')->get();

        $canUpdate = $this->hasPerm($this->permissionUpdate);
        $canDelete = $this->hasPerm($this->permissionDelete);
        $canCreate = $this->hasPerm($this->permissionCreate);

        $data = [];
        $no = 1;

        foreach ($templates as $v) {
            $status = $v->IS_ACTIVE == 1
                ? '<span class="badge bg-success">Active</span>'
                : '<span class="badge bg-secondary">Inactive</span>';

            $category = !empty($v->CATEGORY)
                ? '<span class="badge bg-info">' . ucfirst(e($v->CATEGORY)) . '</span>'
                : '-';

            $action = '<div class="btn-group">';
            $action .= '<button type="button" class="btn btn-sm btn-info btn-icon waves-effect me-1 btn-preview"
                            data-bs-toggle="tooltip" title="Preview Template" data-id="' . $v->ID_TEMPLATE . '">
                        <i class="fal fa-eye"></i></button>';

            if ($canUpdate) {
                $action .= '<button type="button" class="btn btn-sm btn-success btn-icon waves-effect me-1 btn-edit"
                                data-bs-toggle="tooltip" title="Edit Template" data-id="' . $v->ID_TEMPLATE . '">
                            <i class="fal fa-edit"></i></button>';
            }

            if ($canCreate) {
                $action .= '<button type="button" class="btn btn-sm btn-warning btn-icon waves-effect me-1 btn-duplicate"
                                data-bs-toggle="tooltip" title="Duplicate Template" data-id="' . $v->ID_TEMPLATE . '">
                            <i class="fal fa-copy"></i></button>';
            }

            if ($canDelete) {
                $action .= '<button type="button" class="btn btn-sm btn-danger btn-icon waves-effect btn-delete"
                                data-bs-toggle="tooltip" title="Delete Template" data-id="' . $v->ID_TEMPLATE . '">
                            <i class="fal fa-trash"></i></button>';
            }
            $action .= '</div>';

            $subjectTruncated = mb_strlen($v->SUBJECT) > 50 ? mb_substr($v->SUBJECT, 0, 50) . '...' : $v->SUBJECT;

            $data[] = [
                $no++,
                e($v->TEMPLATE_NAME),
                '<code>' . e($v->TEMPLATE_SLUG) . '</code>',
                e($subjectTruncated),
                $category,
                $status,
                $action,
            ];
        }

        return response()->json([
            'status' => true,
            'dataTables' => $data,
            'csrf_token_name' => '_token',
            'csrf_token_hash' => csrf_token(),
        ]);
    }

    public function create()
    {
        if (!$this->hasPerm($this->permissionCreate)) {
            abort(403, 'Akses tidak diizinkan');
        }

        $categories = EmailTemplate::select('CATEGORY')
            ->whereNotNull('CATEGORY')
            ->where('CATEGORY', '!=', '')
            ->distinct()
            ->pluck('CATEGORY')
            ->toArray();

        return view('master.email_template._form', [
            'title' => 'Create Email Template',
            'categories' => $categories,
            'template' => null,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        if (!$this->hasPerm($this->permissionCreate)) {
            return $this->jsonResponse(false, 'Anda tidak memiliki hak akses');
        }

        $request->validate([
            'template_name' => 'required|string|max:150',
            'template_slug' => 'required|string|max:100|unique:s_email_templates,TEMPLATE_SLUG',
            'subject' => 'required|string|max:255',
            'email_body' => 'required|string',
        ]);

        $variables = $request->input('variables');
        if (is_string($variables)) {
            $variables = array_map('trim', explode(',', $variables));
        }

        $template = EmailTemplate::create([
            'TEMPLATE_NAME' => $request->input('template_name'),
            'TEMPLATE_SLUG' => $request->input('template_slug'),
            'SUBJECT' => $request->input('subject'),
            'EMAIL_BODY' => $request->input('email_body'),
            'VARIABLES' => is_array($variables) ? json_encode(array_values(array_filter($variables))) : null,
            'DESCRIPTION' => $request->input('description'),
            'CATEGORY' => $request->input('category'),
            'IS_ACTIVE' => $request->has('is_active') ? 1 : 0,
            'CREATED_BY' => auth()->id(),
        ]);

        ActivityLogService::log("Membuat template email: {$template->TEMPLATE_NAME}", 'create', 'master', [
            'template_id' => $template->ID_TEMPLATE,
        ]);

        return $this->jsonResponse(true, 'Template berhasil dibuat', ['id' => $template->ID_TEMPLATE]);
    }

    public function edit(Request $request)
    {
        if (!$this->hasPerm($this->permissionUpdate)) {
            abort(403, 'Akses tidak diizinkan');
        }

        $id = $request->input('id');
        $template = EmailTemplate::findOrFail($id);

        $categories = EmailTemplate::select('CATEGORY')
            ->whereNotNull('CATEGORY')
            ->where('CATEGORY', '!=', '')
            ->distinct()
            ->pluck('CATEGORY')
            ->toArray();

        return view('master.email_template._form', [
            'title' => 'Edit Email Template',
            'categories' => $categories,
            'template' => $template,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        if (!$this->hasPerm($this->permissionUpdate)) {
            return $this->jsonResponse(false, 'Anda tidak memiliki hak akses');
        }

        $id = $request->input('id');
        $template = EmailTemplate::findOrFail($id);

        $request->validate([
            'template_name' => 'required|string|max:150',
            'template_slug' => 'required|string|max:100|unique:s_email_templates,TEMPLATE_SLUG,' . $id . ',ID_TEMPLATE',
            'subject' => 'required|string|max:255',
            'email_body' => 'required|string',
        ]);

        $variables = $request->input('variables');
        if (is_string($variables)) {
            $variables = array_map('trim', explode(',', $variables));
        }

        $template->update([
            'TEMPLATE_NAME' => $request->input('template_name'),
            'TEMPLATE_SLUG' => $request->input('template_slug'),
            'SUBJECT' => $request->input('subject'),
            'EMAIL_BODY' => $request->input('email_body'),
            'VARIABLES' => is_array($variables) ? json_encode(array_values(array_filter($variables))) : null,
            'DESCRIPTION' => $request->input('description'),
            'CATEGORY' => $request->input('category'),
            'IS_ACTIVE' => $request->has('is_active') ? 1 : 0,
            'UPDATED_BY' => auth()->id(),
        ]);

        ActivityLogService::log("Update template email: {$template->TEMPLATE_NAME}", 'update', 'master', [
            'template_id' => $template->ID_TEMPLATE,
        ]);

        return $this->jsonResponse(true, 'Template berhasil diupdate');
    }

    public function delete(Request $request): JsonResponse
    {
        if (!$this->hasPerm($this->permissionDelete)) {
            return $this->jsonResponse(false, 'Anda tidak memiliki hak akses');
        }

        $id = $request->input('id');
        $template = EmailTemplate::findOrFail($id);

        $name = $template->TEMPLATE_NAME;
        $template->delete();

        ActivityLogService::log("Hapus template email: {$name}", 'delete', 'master', [
            'template_id' => $id,
        ]);

        return $this->jsonResponse(true, 'Template berhasil dihapus');
    }

    public function preview(Request $request)
    {
        $id = $request->input('id');
        $template = EmailTemplate::findOrFail($id);

        return view('master.email_template._preview', [
            'template' => $template,
        ]);
    }

    public function duplicate(Request $request): JsonResponse
    {
        if (!$this->hasPerm($this->permissionCreate)) {
            return $this->jsonResponse(false, 'Anda tidak memiliki hak akses');
        }

        $id = $request->input('id');
        $original = EmailTemplate::findOrFail($id);

        $newSlug = $original->TEMPLATE_SLUG . '_copy_' . time();

        $copy = EmailTemplate::create([
            'TEMPLATE_NAME' => $original->TEMPLATE_NAME . ' (Copy)',
            'TEMPLATE_SLUG' => $newSlug,
            'SUBJECT' => $original->SUBJECT,
            'EMAIL_BODY' => $original->EMAIL_BODY,
            'VARIABLES' => is_array($original->VARIABLES) ? json_encode($original->VARIABLES) : $original->VARIABLES,
            'DESCRIPTION' => $original->DESCRIPTION,
            'CATEGORY' => $original->CATEGORY,
            'IS_ACTIVE' => 0,
            'CREATED_BY' => auth()->id(),
        ]);

        ActivityLogService::log("Duplicate template email ID: {$id}", 'create', 'master', [
            'new_template_id' => $copy->ID_TEMPLATE,
        ]);

        return $this->jsonResponse(true, 'Template berhasil diduplikasi', ['id' => $copy->ID_TEMPLATE]);
    }
}
