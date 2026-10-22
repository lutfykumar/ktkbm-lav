<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationsController extends BaseApiController
{
    /**
     * GET /api/v1/notifications
     */
    public function index(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->input('page', 1));
        $perPage = min(50, max(1, (int) $request->input('per_page', 20)));
        $offset = ($page - 1) * $perPage;

        $userId = $this->userId($request);

        $query = DB::table('t_notifications')->where('user_id', $userId);
        $total = $query->count();

        $rows = (clone $query)->orderBy('created_at', 'DESC')
            ->limit($perPage)
            ->offset($offset)
            ->get();

        $notifications = $rows->map(function ($item) {
            return [
                'id'         => (int) $item->id,
                'title'      => $item->title,
                'body'       => $item->body,
                'type'       => $item->type,
                'ref_id'     => $item->ref_id,
                'is_read'    => (bool) $item->is_read,
                'created_at' => $item->created_at,
                'read_at'    => $item->read_at,
            ];
        });

        return $this->jsonSuccess('Notifications retrieved', $notifications, 200, [
            'page'      => $page,
            'per_page'  => $perPage,
            'total'     => $total,
            'last_page' => max(1, (int) ceil($total / $perPage)),
        ]);
    }

    /**
     * GET /api/v1/notifications/unread-count
     */
    public function unread_count(Request $request): JsonResponse
    {
        $count = DB::table('t_notifications')
            ->where('user_id', $this->userId($request))
            ->where('is_read', 0)
            ->count();

        return $this->jsonSuccess('Unread count', ['count' => $count]);
    }

    /**
     * POST /api/v1/notifications/{id}/read
     */
    public function mark_read(Request $request, $id = null): JsonResponse
    {
        if (empty($id) || !is_numeric($id)) {
            return $this->jsonError('Invalid notification ID', 400);
        }

        $affected = DB::table('t_notifications')
            ->where('id', (int) $id)
            ->where('user_id', $this->userId($request))
            ->where('is_read', 0)
            ->update([
                'is_read' => 1,
                'read_at' => date('Y-m-d H:i:s'),
            ]);

        if ($affected > 0) {
            return $this->jsonSuccess('Notification marked as read');
        }

        return $this->jsonError('Notification not found or already read', 404);
    }

    /**
     * POST /api/v1/notifications/read-all
     */
    public function mark_all_read(Request $request): JsonResponse
    {
        $affected = DB::table('t_notifications')
            ->where('user_id', $this->userId($request))
            ->where('is_read', 0)
            ->update([
                'is_read' => 1,
                'read_at' => date('Y-m-d H:i:s'),
            ]);

        return $this->jsonSuccess('All notifications marked as read', [
            'affected' => $affected,
        ]);
    }
}
