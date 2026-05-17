<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Santri;
use App\Http\Requests\Admin\StoreSantriRequest;
use App\Http\Requests\Admin\UpdateSantriRequest;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SantriController extends Controller
{
    public function __construct(
        protected ActivityLogger $activityLogger
    ) {}

    public function index(Request $request): View
    {
        $search = $request->string('search')->trim();

        $santris = Santri::query()
            ->when($search->isNotEmpty(), function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('nis', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('modules.operational.santri.index', compact('santris', 'search'));
    }

    public function store(StoreSantriRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('santri-photos', 'public');
        }

        $santri = Santri::create($data);

        $this->activityLogger->log(
            action: 'santri_created',
            actor: $request->user(),
            target: $santri,
            description: "Menambahkan santri baru bernama {$santri->name}.",
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()->route('admin.santri.index')
            ->with('success', 'Data santri berhasil ditambahkan.');
    }

    public function show(Santri $santri): View
    {
        return view('modules.operational.santri.show', compact('santri'));
    }

    public function update(UpdateSantriRequest $request, Santri $santri): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            // Hapus foto lama jika ada
            if ($santri->photo_path) {
                Storage::disk('public')->delete($santri->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('santri-photos', 'public');
        }

        $santri->update($data);

        $this->activityLogger->log(
            action: 'santri_updated',
            actor: $request->user(),
            target: $santri,
            description: "Memperbarui data santri {$santri->name}.",
            properties: ['changes' => $santri->getChanges()],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return back()->with('success', 'Data santri berhasil diperbarui.');
    }

    public function destroy(Request $request, Santri $santri): RedirectResponse
    {
        $santriName = $santri->name;

        // Hapus file fisik
        if ($santri->photo_path) {
            Storage::disk('public')->delete($santri->photo_path);
        }

        $santri->delete();

        $this->activityLogger->log(
            action: 'santri_deleted',
            actor: $request->user(),
            target: null, // target null karena record sudah dihapus
            description: "Menghapus data santri {$santriName}.",
            properties: ['name' => $santriName, 'nis' => $santri->nis],
            ipAddress: $request->ip(),
            userAgent: $request->userAgent()
        );

        return redirect()->route('admin.santri.index')
            ->with('success', "Data santri {$santriName} berhasil dihapus.");
    }
}
