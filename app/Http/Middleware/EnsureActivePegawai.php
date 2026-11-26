<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureActivePegawai
{
    public function handle(Request $request, Closure $next): Response
    {
        $pegawai = $request->user()?->pegawai()->first();

        if ($pegawai && $pegawai->status_pegawai !== 'Aktif') {
            $status = $pegawai->status_pegawai;

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Akun tidak aktif karena status kepegawaian saat ini: ' . $status . '. Silakan hubungi admin kepegawaian.',
            ]);
        }

        return $next($request);
    }
}
