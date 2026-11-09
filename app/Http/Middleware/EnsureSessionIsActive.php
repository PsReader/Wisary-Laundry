<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSessionIsActive
{
    /**
     * Catatan aktivitas terakhir pengguna.
     * Jika idle melebihi batas (default 30 menit), paksa logout.
     *
     * Stempel waktu disimpan sebagai unix timestamp agar bebas dari ambiguitas zona waktu.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            // Akun yang dinonaktifkan harus langsung kehilangan akses, termasuk
            // sesi lama dan sesi yang dipulihkan lewat cookie "remember me".
            if (! $request->user()->is_active) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->with('status', 'Akun Anda sudah dinonaktifkan.');
            }

            $lastActivity = $request->session()->get('last_activity_at');
            $idleLimit = (int) config('app.idle_timeout', 30);

            if ($lastActivity && (time() - (int) $lastActivity) > ($idleLimit * 60)) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->with('status', 'Sesi Anda telah berakhir karena tidak aktif selama '.$idleLimit.' menit. Silakan masuk kembali.');
            }

            $request->session()->put('last_activity_at', time());
        }

        return $next($request);
    }
}
