<?php

namespace App\Policies;

use App\Models\SantriPayment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SantriPaymentPolicy
{
    public function viewAny(User $user): Response
    {
        if (! $user->isSuperAdmin() && ! $user->tenant_id) {
            return Response::deny('Akun Anda belum terhubung ke tenant pondok.');
        }

        return $user->can('view pembayaran')
            ? Response::allow()
            : Response::deny('Anda tidak memiliki akses untuk melihat pembayaran.');
    }

    public function view(User $user, SantriPayment $payment): Response
    {
        if (! $user->can('view pembayaran')) {
            return Response::deny('Anda tidak memiliki akses ke detail pembayaran.');
        }

        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $payment->tenant_id || $user->tenant_id !== $payment->tenant_id)) {
            return Response::deny('Anda tidak dapat mengakses pembayaran dari tenant pondok lain.');
        }

        return Response::allow();
    }

    public function create(User $user): Response
    {
        if (! $user->tenant_id) {
            return Response::deny('Akun Anda belum terhubung ke tenant pondok.');
        }

        return $user->can('create pembayaran')
            ? Response::allow()
            : Response::deny('Anda tidak memiliki akses untuk mencatat pembayaran.');
    }

    public function update(User $user, SantriPayment $payment): Response
    {
        if (! $user->can('edit historical pembayaran')) {
            return Response::deny('Anda tidak memiliki akses untuk mengubah pembayaran.');
        }

        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $payment->tenant_id || $user->tenant_id !== $payment->tenant_id)) {
            return Response::deny('Anda tidak dapat mengubah pembayaran dari tenant pondok lain.');
        }

        return Response::allow();
    }

    public function delete(User $user, SantriPayment $payment): Response
    {
        if (! $user->can('edit historical pembayaran')) {
            return Response::deny('Anda tidak memiliki akses untuk menghapus pembayaran.');
        }

        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $payment->tenant_id || $user->tenant_id !== $payment->tenant_id)) {
            return Response::deny('Anda tidak dapat menghapus pembayaran dari tenant pondok lain.');
        }

        return Response::allow();
    }
}
