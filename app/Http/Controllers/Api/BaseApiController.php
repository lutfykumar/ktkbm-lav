<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BaseApiController extends Controller
{
    /**
     * Send JSON success response
     */
    protected function jsonSuccess(string $message, mixed $data = null, int $statusCode = 200, array $meta = []): JsonResponse
    {
        $response = [
            'status'  => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $statusCode, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Send JSON error response
     */
    protected function jsonError(string $message, int $statusCode = 400, mixed $errors = null): JsonResponse
    {
        $response = [
            'status'  => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Get authenticated user ID
     */
    protected function userId(Request $request): int
    {
        if ($request->user()) {
            return (int) $request->user()->id;
        }

        $apiUser = $request->attributes->get('api_user');
        if ($apiUser && isset($apiUser->user_id)) {
            return (int) $apiUser->user_id;
        }

        return 0;
    }

    /**
     * Check if authenticated user is admin/management (type_auth = 1 or group 1,2,3)
     */
    protected function isAdmin(Request $request): bool
    {
        $apiUser = $request->attributes->get('api_user');
        if ($apiUser && isset($apiUser->type_auth)) {
            return (int) $apiUser->type_auth === 1;
        }

        $user = $request->user();
        if ($user) {
            return in_array((int) ($user->group_id ?? 0), [1, 2, 3]);
        }

        return false;
    }
}
