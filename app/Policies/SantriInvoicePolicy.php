<?php

namespace App\Policies;

use App\Models\SantriInvoice;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SantriInvoicePolicy
{
    public function viewAny(User $user): Response
    {
        if (! $user->isSuperAdmin() && ! $user->tenant_id) {
            return Response::deny('Akun Anda belum terhubung ke tenant pondok.');
        }

        return $user->can('view pembayaran')
            ? Response::allow()
            : Response::deny('Anda tidak memiliki akses untuk melihat tagihan.');
    }

    public function view(User $user, SantriInvoice $invoice): Response
    {
        if (! $user->can('view pembayaran')) {
            return Response::deny('Anda tidak memiliki akses ke detail tagihan.');
        }

        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $invoice->tenant_id || $user->tenant_id !== $invoice->tenant_id)) {
            return Response::deny('Anda tidak dapat mengakses tagihan dari tenant pondok lain.');
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
            : Response::deny('Anda tidak memiliki akses untuk membuat tagihan.');
    }

    public function update(User $user, SantriInvoice $invoice): Response
    {
        if (! $user->can('update pembayaran')) {
            return Response::deny('Anda tidak memiliki akses untuk mengubah tagihan.');
        }

        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $invoice->tenant_id || $user->tenant_id !== $invoice->tenant_id)) {
            return Response::deny('Anda tidak dapat mengubah tagihan dari tenant pondok lain.');
        }

        return Response::allow();
    }

    public function delete(User $user, SantriInvoice $invoice): Response
    {
        if (! $user->can('update pembayaran')) {
            return Response::deny('Anda tidak memiliki akses untuk menghapus tagihan.');
        }

        if (! $user->isSuperAdmin() && (! $user->tenant_id || ! $invoice->tenant_id || $user->tenant_id !== $invoice->tenant_id)) {
            return Response::deny('Anda tidak dapat menghapus tagihan dari tenant pondok lain.');
        }

        return Response::allow();
    }
}
