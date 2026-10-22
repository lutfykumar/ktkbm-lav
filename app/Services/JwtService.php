<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Illuminate\Support\Facades\DB;

class JwtService
{
    protected string $secret;
    protected string $algorithm;
    protected string $issuer;
    protected int $accessExpiry;
    protected int $refreshExpiry;

    public function __construct()
    {
        $this->secret = config('jwt.secret', 'tKbM_4p1_s3cR3t_k3Y_2026!@#xQ9zW7vB5mN3pL1');
        $this->algorithm = config('jwt.algorithm', 'HS256');
        $this->issuer = config('jwt.issuer', 'tkbm-api');
        $this->accessExpiry = (int) config('jwt.access_expiry', 3600);
        $this->refreshExpiry = (int) config('jwt.refresh_expiry', 2592000);
    }

    public function getAccessExpiry(): int
    {
        return $this->accessExpiry;
    }

    public function getRefreshExpiry(): int
    {
        return $this->refreshExpiry;
    }

    /**
     * Generate JWT access token
     */
    public function generateAccessToken(object $user): string
    {
        $now = time();
        $payload = [
            'iss'       => $this->issuer,
            'iat'       => $now,
            'exp'       => $now + $this->accessExpiry,
            'sub'       => (int) $user->id,
            'user_id'   => (int) $user->id,
            'username'  => $user->username ?? '',
            'email'     => $user->email ?? '',
            'roles_id'  => isset($user->roles_id) ? (int) $user->roles_id : null,
            'type_auth' => isset($user->type_auth) ? (int) $user->type_auth : 0,
        ];

        return JWT::encode($payload, $this->secret, $this->algorithm);
    }

    /**
     * Generate refresh token and store in t_api_refresh_tokens
     */
    public function generateRefreshToken(int $userId, ?string $deviceName = null): string
    {
        $token = bin2hex(random_bytes(64));

        DB::table('t_api_refresh_tokens')->insert([
            'user_id'     => $userId,
            'token'       => hash('sha256', $token),
            'device_name' => $deviceName ?: 'Unknown Device',
            'expires_at'  => date('Y-m-d H:i:s', time() + $this->refreshExpiry),
            'created_at'  => date('Y-m-d H:i:s'),
        ]);

        return $token;
    }

    /**
     * Validate raw refresh token
     */
    public function validateRefreshToken(string $token): ?object
    {
        $hashed = hash('sha256', $token);

        return DB::table('t_api_refresh_tokens')
            ->where('token', $hashed)
            ->where('expires_at', '>', date('Y-m-d H:i:s'))
            ->first();
    }

    /**
     * Revoke single refresh token
     */
    public function revokeRefreshToken(string $token): void
    {
        $hashed = hash('sha256', $token);
        DB::table('t_api_refresh_tokens')->where('token', $hashed)->delete();
    }

    /**
     * Revoke all refresh tokens for a user
     */
    public function revokeAllRefreshTokens(int $userId): void
    {
        DB::table('t_api_refresh_tokens')->where('user_id', $userId)->delete();
    }

    /**
     * Clean up expired tokens
     */
    public function cleanupExpiredTokens(): void
    {
        DB::table('t_api_refresh_tokens')
            ->where('expires_at', '<', date('Y-m-d H:i:s'))
            ->delete();
    }

    /**
     * Decode JWT access token
     *
     * @throws ExpiredException
     * @throws \Exception
     */
    public function decodeAccessToken(string $token): object
    {
        return JWT::decode($token, new Key($this->secret, $this->algorithm));
    }
}
