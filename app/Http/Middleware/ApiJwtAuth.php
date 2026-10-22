<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\JwtService;
use Closure;
use Firebase\JWT\ExpiredException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiJwtAuth
{
    public function __construct(
        protected JwtService $jwtService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (empty($token)) {
            $header = $request->header('Authorization')
                ?: $request->server('HTTP_AUTHORIZATION')
                ?: $request->server('REDIRECT_HTTP_AUTHORIZATION');

            if (!empty($header) && preg_match('/Bearer\s(\S+)/', $header, $matches)) {
                $token = $matches[1];
            }
        }

        if (empty($token)) {
            return response()->json([
                'status'  => false,
                'message' => 'Token tidak ditemukan. Silakan login terlebih dahulu.',
            ], 401, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        try {
            $decoded = $this->jwtService->decodeAccessToken($token);
        } catch (ExpiredException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Token telah kedaluwarsa. Silakan refresh token.',
            ], 401, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Token tidak valid.',
            ], 401, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        $userId = $decoded->user_id ?? $decoded->sub ?? 0;
        $user = User::where('id', $userId)->where('active', 1)->first();

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'User tidak ditemukan atau tidak aktif.',
            ], 401, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        $request->setUserResolver(fn() => $user);
        $request->attributes->set('api_user', $decoded);

        return $next($request);
    }
}
