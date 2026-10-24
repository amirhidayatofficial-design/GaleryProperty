<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VerifyEmailController extends Controller
{
    /**
     * Verifikasi email dari tautan bertanda tangan, tanpa harus login lebih dulu.
     * Berhasil diverifikasi = status akun berpindah dari pending ke active.
     */
    public function __invoke(Request $request, string $id, string $hash): RedirectResponse|View
    {
        $user = User::findOrFail($id);

        abort_unless(hash_equals(sha1($user->getEmailForVerification()), $hash), 403, 'Tautan verifikasi tidak valid.');

        if (! $user->hasVerifiedEmail() && $user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        // Dibuka di browser tempat pengguna sedang login → langsung ke dashboard
        if ($request->user()?->is($user) && ! $user->isSuspended()) {
            return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
        }

        return view('auth.email-verified', compact('user'));
    }
}
