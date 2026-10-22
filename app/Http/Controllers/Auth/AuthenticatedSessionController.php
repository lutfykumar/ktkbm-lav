<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        try {
            $request->authenticate();

            $request->session()->regenerate();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => true,
                    'link_tujuan' => route('dashboard'),
                    'msg' => 'Berhasil masuk. Mohon tunggu...'
                ]);
            }

            return redirect()->intended(route('dashboard'));
        } catch (ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                $firstError = collect($e->errors())->flatten()->first() ?: 'Email atau Password salah.';
                return response()->json([
                    'status' => false,
                    'msg' => $firstError
                ]);
            }

            throw $e;
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
