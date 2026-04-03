<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">Dashboard Pengurus</h2>
            <div class="text-secondary mt-1">Operasional santri, kamar, dan izin.</div>
        </div>
    </x-slot>

    <div class="row row-cards">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">Tugas Pengurus</h3>
                    <p class="text-secondary mb-0">Pengurus dapat input data santri, mengatur kamar, dan mengelola izin.</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <a href="{{ route('pengurus.santri') }}" class="btn btn-primary">Buka Data Santri</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
