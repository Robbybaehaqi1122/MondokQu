<?php

namespace App\Modules\Payment\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PaymentAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PaymentAccountController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = auth()->user()->tenant_id;

        $accounts = PaymentAccount::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->orderBy('sort_order')
            ->orderBy('type')
            ->get();

        return view('santri.payments.accounts', compact('accounts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = auth()->user()->tenant_id;
        if (! $tenantId) {
            return back()->with('error', 'Akses ditolak.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:bank,e_wallet,qris'],
            'account_name' => ['nullable', 'string', 'max:255'],
            'account_number' => ['nullable', 'string', 'max:100'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'qris_image' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        if ($request->hasFile('qris_image')) {
            $validated['qris_image'] = $request->file('qris_image')->store('payment-qris', 'public');
        }

        PaymentAccount::withoutTenantScope()->create([
            'tenant_id' => $tenantId,
            'created_by' => auth()->id(),
            ...$validated,
        ]);

        return redirect()->route('santri.payments.accounts.index')
            ->with('success', 'Akun pembayaran berhasil ditambahkan.');
    }

    public function update(Request $request, PaymentAccount $paymentAccount): RedirectResponse
    {
        if ($paymentAccount->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:bank,e_wallet,qris'],
            'account_name' => ['nullable', 'string', 'max:255'],
            'account_number' => ['nullable', 'string', 'max:100'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'qris_image' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        if ($request->hasFile('qris_image')) {
            if ($paymentAccount->qris_image) {
                Storage::disk('public')->delete($paymentAccount->qris_image);
            }
            $validated['qris_image'] = $request->file('qris_image')->store('payment-qris', 'public');
        }

        $paymentAccount->update($validated);

        return redirect()->route('santri.payments.accounts.index')
            ->with('success', 'Akun pembayaran berhasil diperbarui.');
    }

    public function destroy(PaymentAccount $paymentAccount): RedirectResponse
    {
        if ($paymentAccount->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        if ($paymentAccount->qris_image) {
            Storage::disk('public')->delete($paymentAccount->qris_image);
        }

        $paymentAccount->delete();

        return redirect()->route('santri.payments.accounts.index')
            ->with('success', 'Akun pembayaran berhasil dihapus.');
    }
}
