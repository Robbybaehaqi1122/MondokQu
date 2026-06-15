<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h2 class="page-title">Nilai Sikap: {{ $santri->full_name }}</h2>
                <div class="text-secondary mt-1">Semester {{ $semester }}</div>
            </div>
            <a href="{{ route('akademik.attitude.index') }}" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </x-slot>

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form action="{{ route('akademik.attitude.store') }}" method="POST">
        @csrf
        <input type="hidden" name="santri_id" value="{{ $santri->id }}">
        <input type="hidden" name="semester" value="{{ $semester }}">

        @foreach ($allAspects as $aspect => $aspectNames)
            <div class="card mb-3">
                <div class="card-header">
                    <h3 class="card-title">
                        @if ($aspect === 'spiritual')
                            <span class="text-primary">Sikap Spiritual</span>
                        @else
                            <span class="text-success">Sikap Sosial</span>
                        @endif
                    </h3>
                </div>
                <div class="table-responsive">
                    <table class="table table-vcenter card-table">
                        <thead>
                            <tr>
                                <th>Aspek</th>
                                <th style="width:150px">Predikat</th>
                                <th>Deskripsi / Uraian</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($aspectNames as $name)
                                @php
                                    $key = $aspect.'::'.$name;
                                    $grade = $existing->get($key);
                                @endphp
                                <tr>
                                    <td class="fw-semibold">{{ $name }}</td>
                                    <td>
                                        <select name="grades[{{ $loop->index }}][predicate]"
                                            class="form-select form-select-sm">
                                            <option value="">-- Pilih --</option>
                                            @foreach ($predicates as $val => $label)
                                                <option value="{{ $val }}"
                                                    {{ $grade && $grade->predicate === $val ? 'selected' : '' }}>
                                                    {{ $val }} - {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="grades[{{ $loop->index }}][aspect]" value="{{ $aspect }}">
                                        <input type="hidden" name="grades[{{ $loop->index }}][aspect_name]" value="{{ $name }}">
                                    </td>
                                    <td>
                                        <textarea name="grades[{{ $loop->index }}][description]" class="form-control form-control-sm" rows="2" placeholder="Opsional">{{ $grade?->description ?? '' }}</textarea>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach

        <div class="card">
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-device-floppy me-1"></i> Simpan Nilai Sikap
                </button>
            </div>
        </div>
    </form>
</x-app-layout>
