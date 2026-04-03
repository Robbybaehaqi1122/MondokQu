<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Actions\UpdatePasswordAction;
use App\Modules\Auth\Requests\UpdatePasswordRequest;
use Illuminate\Http\RedirectResponse;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(
        UpdatePasswordRequest $request,
        UpdatePasswordAction $updatePassword
    ): RedirectResponse {
        $updatePassword->handle($request->user(), $request->validated('password'));

        return back()->with('status', 'password-updated');
    }
}
