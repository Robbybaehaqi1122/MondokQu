<style>
    .preview-card {
        opacity: 0;
        transform: translateY(20px);
        animation: fadeInUp 0.5s ease forwards;
    }
    .preview-card:nth-child(1) { animation-delay: 0.1s; }
    .preview-card:nth-child(2) { animation-delay: 0.2s; }
    .preview-card:nth-child(3) { animation-delay: 0.3s; }
    .preview-section {
        opacity: 0;
        transform: translateY(20px);
        animation: fadeInUp 0.5s ease forwards;
    }
    .preview-section:nth-child(1) { animation-delay: 0.4s; }
    .preview-section:nth-child(2) { animation-delay: 0.5s; }
    @keyframes fadeInUp {
        to { opacity: 1; transform: translateY(0); }
    }
    .count-up {
        display: inline-block;
    }
</style>

<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">Preview Import Santri</h2>
            <div class="text-secondary mt-1">Tinjau data sebelum diimport ke sistem.</div>
        </div>
    </x-slot>

    <div class="row row-cards mb-3">
        <div class="col-md-4 preview-card">
            <div class="card">
                <div class="card-body text-center">
                    <div class="h1 mb-1 text-success"><span class="count-up" data-count="{{ $validCount }}">0</span></div>
                    <div class="text-secondary">Data Valid</div>
                </div>
            </div>
        </div>
        <div class="col-md-4 preview-card">
            <div class="card">
                <div class="card-body text-center">
                    <div class="h1 mb-1 {{ $errorCount > 0 ? 'text-danger' : 'text-secondary' }}"><span class="count-up" data-count="{{ $errorCount }}">0</span></div>
                    <div class="text-secondary">Data Error</div>
                </div>
            </div>
        </div>
        <div class="col-md-4 preview-card">
            <div class="card">
                <div class="card-body text-center">
                    <div class="h1 mb-1"><span class="count-up" data-count="{{ $totalRows }}">0</span></div>
                    <div class="text-secondary">Total Baris</div>
                </div>
            </div>
        </div>
    </div>

    @if ($errorRows->isNotEmpty())
        <div class="card mb-3 preview-section">
            <div class="card-header">
                <h3 class="card-title text-danger">Data Error ({{ $errorCount }})</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th class="w-1">Baris</th>
                            <th>NIS</th>
                            <th>Nama</th>
                            <th>Tempat Lahir</th>
                            <th>Jenis Kelamin</th>
                            <th>Error</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($errorRows as $error)
                            <tr>
                                <td>{{ $error['row'] }}</td>
                                <td>{{ $error['data']['nis'] ?? '-' }}</td>
                                <td>{{ $error['data']['full_name'] ?? '-' }}</td>
                                <td>{{ $error['data']['birth_place'] ?? '-' }}</td>
                                <td>{{ $error['data']['gender'] ?? '-' }}</td>
                                <td>
                                    <ul class="mb-0 ps-3">
                                        @foreach ($error['errors'] as $message)
                                            <li class="text-danger">{{ $message }}</li>
                                        @endforeach
                                    </ul>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($validRows->isNotEmpty())
        <div class="card mb-3 preview-section">
            <div class="card-header">
                <h3 class="card-title text-success">Data Valid ({{ $validCount }})</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th class="w-1">#</th>
                            <th>NIS</th>
                            <th>Nama Lengkap</th>
                            <th>Jenis Kelamin</th>
                            <th>Tempat / Tgl Lahir</th>
                            <th>Alamat</th>
                            <th>Ayah</th>
                            <th>Ibu</th>
                            <th>No. HP Wali</th>
                            <th>Kamar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($validRows as $index => $valid)
                            <tr>
                                <td>{{ $valid['row'] }}</td>
                                <td>{{ $valid['data']['nis'] ?? '-' }}</td>
                                <td>{{ $valid['data']['full_name'] ?? '-' }}</td>
                                <td>{{ $valid['data']['gender'] ?? '-' }}</td>
                                <td>{{ $valid['data']['birth_place'] ?? '-' }}, {{ $valid['data']['birth_date'] ?? '-' }}</td>
                                <td>{{ Str::limit($valid['data']['address'] ?? '-', 30) }}</td>
                                <td>{{ $valid['data']['father_name'] ?? '-' }}</td>
                                <td>{{ $valid['data']['mother_name'] ?? '-' }}</td>
                                <td>{{ $valid['data']['guardian_phone_number'] ?? '-' }}</td>
                                <td>{{ $valid['data']['room_name'] ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="card preview-section">
        <div class="card-body d-flex flex-wrap gap-2">
            <a href="{{ route('santri.import.index') }}" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-left me-1"></i>
                Kembali
            </a>

            @if ($validCount > 0)
                <form method="POST" action="{{ route('santri.import.process') }}" class="ms-auto">
                    @csrf
                    <input type="hidden" name="preview_key" value="{{ $previewKey }}">
                    <button type="submit" class="btn btn-primary" id="import-btn">
                        <span id="import-btn-icon"><i class="ti ti-upload me-1"></i></span>
                        <span class="spinner-border spinner-border-sm me-1" id="import-spinner" role="status" hidden></span>
                        <span id="import-btn-text">Import {{ number_format($validCount) }} Data Valid</span>
                    </button>
                </form>
            @endif
        </div>
    </div>
</x-app-layout>

<script>
    function animateCountUp(el) {
        const target = parseInt(el.dataset.count);
        const duration = 600;
        const steps = 20;
        const increment = target / steps;
        let current = 0;
        let step = 0;
        const timer = setInterval(() => {
            step++;
            current = Math.min(Math.round(increment * step), target);
            el.textContent = current.toLocaleString('id-ID');
            if (step >= steps) {
                el.textContent = target.toLocaleString('id-ID');
                clearInterval(timer);
            }
        }, duration / steps);
    }

    document.querySelectorAll('.count-up').forEach(el => {
        if (parseInt(el.dataset.count) > 0) {
            animateCountUp(el);
        } else {
            el.textContent = '0';
        }
    });

    document.querySelector('form[action*="import/process"]')?.addEventListener('submit', function() {
        setTimeout(() => {
            const btn = document.getElementById('import-btn');
            if (btn) {
                btn.disabled = true;
                document.getElementById('import-btn-icon').hidden = true;
                document.getElementById('import-spinner').hidden = false;
                document.getElementById('import-btn-text').textContent = 'Mengimport...';
            }
        }, 50);
    });
</script>
