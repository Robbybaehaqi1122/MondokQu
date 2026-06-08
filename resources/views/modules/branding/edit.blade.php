<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h2 class="page-title">Pengaturan Branding</h2>
                <div class="text-secondary mt-1">{{ $tenant->name }}</div>
            </div>
        </div>
    </x-slot>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('branding.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Informasi Pondok</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Pondok <span class="text-danger">*</span></label>
                            <input type="text" name="ponpes_name" class="form-control @error('ponpes_name') is-invalid @enderror"
                                value="{{ old('ponpes_name', $settings['ponpes_name'] ?? $tenant->name) }}" required>
                            @error('ponpes_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea name="ponpes_address" class="form-control @error('ponpes_address') is-invalid @enderror" rows="2">{{ old('ponpes_address', $settings['ponpes_address'] ?? '') }}</textarea>
                            @error('ponpes_address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nomor Telepon</label>
                                <input type="text" name="ponpes_phone" class="form-control @error('ponpes_phone') is-invalid @enderror"
                                    value="{{ old('ponpes_phone', $settings['ponpes_phone'] ?? '') }}">
                                @error('ponpes_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="ponpes_email" class="form-control @error('ponpes_email') is-invalid @enderror"
                                    value="{{ old('ponpes_email', $settings['ponpes_email'] ?? '') }}">
                                @error('ponpes_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Website</label>
                            <input type="url" name="website" class="form-control @error('website') is-invalid @enderror"
                                value="{{ old('website', $settings['website'] ?? '') }}" placeholder="https://example.com">
                            @error('website') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Tema & Tampilan</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Warna Tema (Primary)</label>
                            <div class="input-group">
                                <input type="color" name="theme_color" class="form-control form-control-color"
                                    value="{{ old('theme_color', $settings['theme_color'] ?? '#0d9488') }}" style="width: 60px;">
                                <input type="text" name="theme_color_text" class="form-control"
                                    value="{{ old('theme_color', $settings['theme_color'] ?? '#0d9488') }}"
                                    maxlength="7" pattern="^#[a-fA-F0-9]{6}$"
                                    oninput="this.previousElementSibling.value = this.value">
                            </div>
                            @error('theme_color') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            <div class="mt-2 d-flex gap-1 flex-wrap">
                                @php
                                    $presets = ['#0d9488', '#059669', '#0891b2', '#4f46e5', '#7c3aed', '#db2777', '#ea580c', '#ca8a04', '#475569', '#206bc4'];
                                @endphp
                                @foreach ($presets as $preset)
                                    <button type="button" class="btn p-0 border-0 rounded-circle color-preset"
                                        data-color="{{ $preset }}"
                                        style="width: 28px; height: 28px; background: {{ $preset }}; cursor: pointer;"
                                        title="{{ $preset }}"></button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Dokumen & Kop Surat</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Footer untuk Invoice / Dokumen</label>
                            <textarea name="invoice_footer" class="form-control @error('invoice_footer') is-invalid @enderror" rows="2"
                                placeholder="Terima kasih atas kepercayaan Anda...">{{ old('invoice_footer', $settings['invoice_footer'] ?? '') }}</textarea>
                            @error('invoice_footer') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Logo Pondok</h3>
                    </div>
                    <div class="card-body text-center">
                        @php
                            $logoUrl = !empty($settings['logo_path']) ? asset('storage/'.$settings['logo_path']) : null;
                        @endphp
                        @if ($logoUrl)
                            <img src="{{ $logoUrl }}" alt="Logo" class="img-fluid mb-3" style="max-height: 120px;">
                            <form action="{{ route('branding.remove-logo') }}" method="POST" class="mb-3">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger btn-sm"
                                    onclick="return confirm('Hapus logo?')">
                                    <i class="ti ti-trash me-1"></i> Hapus Logo
                                </button>
                            </form>
                        @else
                            <div class="text-secondary mb-3">
                                <i class="ti ti-photo fs-1"></i>
                                <p class="mt-2">Belum ada logo</p>
                            </div>
                        @endif
                        <input type="file" name="logo" class="form-control @error('logo') is-invalid @enderror" accept="image/png,image/jpeg,image/svg+xml">
                        <div class="form-text">PNG, JPG, atau SVG. Maks 2MB.</div>
                        @error('logo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Favicon</h3>
                    </div>
                    <div class="card-body text-center">
                        @php
                            $faviconUrl = !empty($settings['favicon_path']) ? asset('storage/'.$settings['favicon_path']) : null;
                        @endphp
                        @if ($faviconUrl)
                            <img src="{{ $faviconUrl }}" alt="Favicon" class="mb-3" style="max-height: 48px;">
                            <form action="{{ route('branding.remove-favicon') }}" method="POST" class="mb-3">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger btn-sm"
                                    onclick="return confirm('Hapus favicon?')">
                                    <i class="ti ti-trash me-1"></i> Hapus Favicon
                                </button>
                            </form>
                        @else
                            <div class="text-secondary mb-3">
                                <i class="ti ti-brush fs-1"></i>
                                <p class="mt-2">Favicon default</p>
                            </div>
                        @endif
                        <input type="file" name="favicon" class="form-control @error('favicon') is-invalid @enderror" accept="image/png,image/x-icon,image/svg+xml">
                        <div class="form-text">PNG, ICO, atau SVG. Maks 1MB.</div>
                        @error('favicon') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-device-floppy me-1"></i> Simpan Pengaturan
                </button>
            </div>
        </div>
    </form>

    @push('scripts')
    <script>
        const colorInput = document.querySelector('[name="theme_color"]');
        const textInput = document.querySelector('[name="theme_color_text"]');
        if (colorInput && textInput) {
            colorInput.addEventListener('input', () => { textInput.value = colorInput.value; });
            textInput.addEventListener('input', () => {
                if (/^#[a-fA-F0-9]{6}$/.test(textInput.value)) {
                    colorInput.value = textInput.value;
                }
            });
        }
        document.querySelectorAll('.color-preset').forEach(btn => {
            btn.addEventListener('click', () => {
                const color = btn.dataset.color;
                colorInput.value = color;
                textInput.value = color;
            });
        });
    </script>
    @endpush
</x-app-layout>
