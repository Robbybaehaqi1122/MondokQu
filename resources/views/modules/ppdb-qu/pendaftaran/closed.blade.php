<x-guest-layout>
    @php
        $ponpesName = $tenant?->settings['ponpes_name'] ?? $tenant?->name ?? null;
        $ponpesLogo = isset($tenant?->settings['logo_path']) ? asset('storage/'.$tenant->settings['logo_path']) : null;
        $brandName = $ponpesName ? "PPDB Online - {$ponpesName}" : 'PPDB Online';
    @endphp

    <div class="text-center">
        @if ($ponpesLogo)
            <img src="{{ $ponpesLogo }}" alt="{{ $ponpesName }}" class="img-fluid mb-3" style="max-height: 64px;">
        @endif
        <h2 class="mb-1">Pendaftaran Ditutup</h2>
        <p class="text-secondary mb-0">{{ $brandName }}</p>
    </div>

    <div class="alert alert-warning mt-4 text-center" role="alert">
        <i class="ti ti-alert-triangle fs-2 mb-2 d-block"></i>
        <h5 class="alert-heading">Gelombang {{ $selectedGelombang->nama }} sudah tidak menerima pendaftaran.</h5>
        <p class="mb-0">Periode pendaftaran: {{ $selectedGelombang->tanggal_mulai->translatedFormat('d M Y') }} - {{ $selectedGelombang->tanggal_selesai->translatedFormat('d M Y') }}</p>
        @if ($selectedGelombang->tanggal_selesai < now())
            <p class="mt-2 mb-0">Pendaftaran telah berakhir pada {{ $selectedGelombang->tanggal_selesai->translatedFormat('d M Y') }}.</p>
        @elseif ($selectedGelombang->tanggal_mulai > now())
            <p class="mt-2 mb-0">Pendaftaran akan dibuka pada {{ $selectedGelombang->tanggal_mulai->translatedFormat('d M Y') }}.</p>
        @else
            <p class="mt-2 mb-0">Gelombang ini sudah tidak aktif.</p>
        @endif
    </div>

    <div class="text-center mt-3">
        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">Kembali</a>
    </div>
</x-guest-layout>
