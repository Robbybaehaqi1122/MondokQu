<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Services\UserAvatarUploader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Throwable;

class ProfileController extends Controller
{
    public function __construct(
        protected UserAvatarUploader $userAvatarUploader
    ) {}

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();
        $previousAvatarPath = $user->avatar_path;
        $newAvatarPath = $request->file('avatar')
            ? $this->userAvatarUploader->store($request->file('avatar'))
            : null;
        $avatarPath = $newAvatarPath ?? $previousAvatarPath;

        $emailChanged = false;

        try {
            DB::transaction(function () use ($user, $validated, $avatarPath, &$emailChanged): void {
                $user->fill([
                    'name' => $validated['name'],
                    'username' => $validated['username'],
                    'email' => $validated['email'],
                    'phone_number' => $validated['phone_number'] ?? null,
                    'avatar_path' => $avatarPath,
                ]);

                if ($user->isDirty('email')) {
                    $user->email_verified_at = null;
                }

                $user->save();

                $emailChanged = $user->wasChanged('email');
            });
        } catch (Throwable $exception) {
            $this->userAvatarUploader->deleteIfManaged($newAvatarPath);

            throw $exception;
        }

        if ($emailChanged) {
            $user->sendEmailVerificationNotification();
        }

        if ($previousAvatarPath && $previousAvatarPath !== $avatarPath) {
            $this->userAvatarUploader->deleteIfManaged($previousAvatarPath);
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
