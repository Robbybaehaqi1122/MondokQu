<?php

namespace App\Providers;

use App\Models\ActivityLog;
use App\Models\AttendanceActivity;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Communication;
use App\Models\LeaveRequest;
use App\Models\MataPelajaran;
use App\Models\NilaiSantri;
use App\Models\Pelanggaran;
use App\Models\PelanggaranKategori;
use App\Models\Room;
use App\Models\Santri;
use App\Models\SantriInvoice;
use App\Models\SantriPayment;
use App\Models\TahfidzSession;
use App\Models\User;
use App\Policies\ActivityLogPolicy;
use App\Policies\AttendanceActivityPolicy;
use App\Policies\AttendanceRecordPolicy;
use App\Policies\AttendanceSessionPolicy;
use App\Policies\CommunicationPolicy;
use App\Policies\LeaveRequestPolicy;
use App\Policies\MataPelajaranPolicy;
use App\Policies\NilaiSantriPolicy;
use App\Policies\PelanggaranKategoriPolicy;
use App\Policies\PelanggaranPolicy;
use App\Policies\RoomPolicy;
use App\Policies\SantriInvoicePolicy;
use App\Policies\SantriPaymentPolicy;
use App\Policies\SantriPolicy;
use App\Policies\TahfidzSessionPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->isProduction()) {
            URL::forceScheme('https');
        }

        Gate::before(function ($user, $ability) {
            if ($user instanceof User && $user->isSuperAdmin()) {
                return true;
            }
        });

        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Santri::class, SantriPolicy::class);
        Gate::policy(Room::class, RoomPolicy::class);
        Gate::policy(SantriInvoice::class, SantriInvoicePolicy::class);
        Gate::policy(SantriPayment::class, SantriPaymentPolicy::class);
        Gate::policy(LeaveRequest::class, LeaveRequestPolicy::class);
        Gate::policy(AttendanceSession::class, AttendanceSessionPolicy::class);
        Gate::policy(AttendanceActivity::class, AttendanceActivityPolicy::class);
        Gate::policy(AttendanceRecord::class, AttendanceRecordPolicy::class);
        Gate::policy(ActivityLog::class, ActivityLogPolicy::class);
        Gate::policy(NilaiSantri::class, NilaiSantriPolicy::class);
        Gate::policy(MataPelajaran::class, MataPelajaranPolicy::class);
        Gate::policy(Pelanggaran::class, PelanggaranPolicy::class);
        Gate::policy(PelanggaranKategori::class, PelanggaranKategoriPolicy::class);
        Gate::policy(Communication::class, CommunicationPolicy::class);
        Gate::policy(TahfidzSession::class, TahfidzSessionPolicy::class);
    }
}
