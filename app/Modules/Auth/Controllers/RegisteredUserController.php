<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Modules\Auth\Actions\SendEmailVerificationNotificationAction;
use App\Modules\Auth\Requests\StoreUserRequest;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    public function __construct(
        protected SendEmailVerificationNotificationAction $sendVerificationNotification
    ) {}

    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('modules.auth.register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = DB::transaction(function () use ($validated) {
            $tenant = Tenant::query()->create([
                'name' => 'Pondok ' . $validated['name'],
                'slug' => Str::slug('pondok-' . $validated['name'] . '-' . now()->timestamp),
                'contact_email' => $validated['email'],
                'contact_phone_number' => $validated['phone_number'] ?? null,
                'subscription_plan' => config('saas.default_plan', 'trial'),
                'subscription_status' => Tenant::SUBSCRIPTION_TRIAL,
                'trial_ends_at' => now()->addDays(config('saas.trial_days', 15)),
                'subscription_starts_at' => now(),
                'subscription_ends_at' => null,
                'grace_ends_at' => null,
                'owner_id' => null,
            ]);

            $user = User::create([
                'tenant_id' => $tenant->id,
                'name' => $validated['name'],
                'username' => $validated['username'],
                'email' => $validated['email'],
                'phone_number' => $validated['phone_number'] ?? null,
                'password' => Hash::make($validated['password']),
                'status' => User::STATUS_ACTIVE,
                'password_change_required' => false, // Publik registrasi tidak perlu force change
            ]);

            Role::findOrCreate('Admin', 'web');
            $user->syncRoles(['Admin']);

            $tenant->update(['owner_id' => $user->id]);

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        // Kirim email verifikasi jika mailer aktif
        $this->sendVerificationNotification->handle($user);

        return redirect(route('dashboard', absolute: false))
            ->with('success', 'Akun berhasil dibuat. Trial 15 hari Anda sudah aktif.');
    }
}
