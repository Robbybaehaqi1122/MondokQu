<?php

namespace App\Modules\Branding\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TenantBrandingController extends Controller
{
    public function edit(Request $request): View
    {
        $currentUser = $request->user();
        $tenant = $currentUser->tenant;

        if (! $tenant) abort(403);

        return view('modules.branding.edit', [
            'tenant' => $tenant,
            'settings' => $tenant->settings ?? [],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $currentUser = $request->user();
        $tenant = $currentUser->tenant;

        if (! $tenant) abort(403);

        $validated = $request->validate([
            'ponpes_name' => ['required', 'string', 'max:255'],
            'ponpes_address' => ['nullable', 'string', 'max:500'],
            'ponpes_phone' => ['nullable', 'string', 'max:30'],
            'ponpes_email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'theme_color' => ['required', 'string', 'regex:/^#[a-fA-F0-9]{6}$/'],
            'invoice_footer' => ['nullable', 'string', 'max:500'],
        ]);

        $settings = $tenant->settings ?? [];

        foreach (['ponpes_name', 'ponpes_address', 'ponpes_phone', 'ponpes_email', 'website', 'theme_color', 'invoice_footer'] as $field) {
            $settings[$field] = $validated[$field] ?? '';
        }

        if ($request->hasFile('logo')) {
            $request->validate(['logo' => ['image', 'mimes:png,jpg,jpeg,svg', 'max:2048']]);
            if (! empty($settings['logo_path'])) {
                Storage::disk('public')->delete($settings['logo_path']);
            }
            $settings['logo_path'] = $request->file('logo')->store('tenant-branding/'.$tenant->id, 'public');
        }

        if ($request->hasFile('favicon')) {
            $request->validate(['favicon' => ['image', 'mimes:png,ico,svg', 'max:1024']]);
            if (! empty($settings['favicon_path'])) {
                Storage::disk('public')->delete($settings['favicon_path']);
            }
            $settings['favicon_path'] = $request->file('favicon')->store('tenant-branding/'.$tenant->id, 'public');
        }

        $tenant->settings = $settings;
        $tenant->save();

        return redirect()->route('branding.edit')
            ->with('success', 'Pengaturan branding berhasil disimpan.');
    }

    public function removeLogo(Request $request): RedirectResponse
    {
        $currentUser = $request->user();
        $tenant = $currentUser->tenant;
        if (! $tenant) abort(403);

        $settings = $tenant->settings ?? [];
        if (! empty($settings['logo_path'])) {
            Storage::disk('public')->delete($settings['logo_path']);
        }
        unset($settings['logo_path']);
        $tenant->settings = $settings;
        $tenant->save();

        return redirect()->route('branding.edit')->with('success', 'Logo berhasil dihapus.');
    }

    public function removeFavicon(Request $request): RedirectResponse
    {
        $currentUser = $request->user();
        $tenant = $currentUser->tenant;
        if (! $tenant) abort(403);

        $settings = $tenant->settings ?? [];
        if (! empty($settings['favicon_path'])) {
            Storage::disk('public')->delete($settings['favicon_path']);
        }
        unset($settings['favicon_path']);
        $tenant->settings = $settings;
        $tenant->save();

        return redirect()->route('branding.edit')->with('success', 'Favicon berhasil dihapus.');
    }
}
