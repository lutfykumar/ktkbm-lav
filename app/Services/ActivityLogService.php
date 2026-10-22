<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class ActivityLogService
{
    /**
     * Log user activity to s_user_activity_logs
     */
    public static function log(
        string $description,
        string $type = 'view',
        string $category = 'general',
        array $options = []
    ): void {
        try {
            $user = auth()->user();
            $userId = $user?->id;
            $username = $user?->username ?? $user?->email ?? 'Guest';
            $sessionId = session()->getId() ?: 'cli_or_api_' . uniqid();

            $ipAddress = Request::ip() ?? '127.0.0.1';
            $userAgent = Request::header('User-Agent') ?? 'Unknown';
            $requestUri = Request::getRequestUri() ?? '/';
            $requestMethod = Request::method() ?? 'CLI';

            $routeAction = Request::route() ? Request::route()->getActionName() : '';
            $controller = '';
            $method = '';
            if (str_contains($routeAction, '@')) {
                [$controller, $method] = explode('@', $routeAction);
                $controller = class_basename($controller);
            }

            $affectedRecordId = $options['affected_record_id'] ?? null;
            $affectedTable = $options['affected_table'] ?? null;
            $oldValues = isset($options['old_values']) ? json_encode($options['old_values']) : null;
            $newValues = isset($options['new_values']) ? json_encode($options['new_values']) : null;
            $status = $options['status'] ?? 'success';

            DB::table('s_user_activity_logs')->insert([
                'session_id' => substr($sessionId, 0, 128),
                'REQUEST_ID' => uniqid('req_'),
                'user_id' => $userId,
                'username' => substr($username, 0, 100),
                'activity_type' => in_array($type, [
                    'login','logout','view','create','update','delete','download','upload','export','import','security','error'
                ]) ? $type : 'view',
                'activity_category' => substr($category, 0, 50),
                'activity_description' => $description,
                'activity_status' => in_array($status, ['success','failed','warning']) ? $status : 'success',
                'ip_address' => substr($ipAddress, 0, 45),
                'user_agent' => substr($userAgent, 0, 500),
                'device_type' => 'desktop',
                'request_method' => substr($requestMethod, 0, 10),
                'request_uri' => substr($requestUri, 0, 255),
                'controller' => substr($controller, 0, 100),
                'method' => substr($method, 0, 100),
                'affected_record_id' => $affectedRecordId ? (string)$affectedRecordId : null,
                'affected_table' => $affectedTable,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Silently ignore activity logging errors so main execution is never broken
            logger()->warning('Failed to write activity log: ' . $e->getMessage());
        }
    }
}
