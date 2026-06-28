<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VerifyEmailController extends Controller
{
    /**
     * Mark the selected user's email address as verified.
     */
    public function __invoke(Request $request, int $id, string $hash): RedirectResponse
    {
        $user = User::query()->findOrFail($id);

        $expectedHash = sha1($user->getEmailForVerification());

        if (! hash_equals((string) $hash, $expectedHash)) {
            Log::warning('Verifikasi email gagal: hash tidak cocok', [
                'user_id' => $id,
                'user_email' => $user->email,
                'hash_dari_url' => $hash,
                'hash_diharapkan' => $expectedHash,
            ]);

            return redirect()
                ->route('login')
                ->with('status', 'Link verifikasi email tidak valid. Silakan minta tautan baru.');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()
                ->route('login')
                ->with('status', 'Email ini sudah terverifikasi. Silakan login.');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect()
            ->route('login')
            ->with('status', 'Email berhasil diverifikasi. Silakan login ke akun Anda.');
    }
}
