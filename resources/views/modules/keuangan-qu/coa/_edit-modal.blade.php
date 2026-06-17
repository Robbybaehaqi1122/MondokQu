<div class="modal modal-blur fade" id="editCoaModal{{ $account->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('keuangan.coa.update', $account) }}">
                @csrf @method('PATCH')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Akun: {{ $account->code }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required">Kode Akun</label>
                            <input type="text" name="code" class="form-control" value="{{ old('code', $account->code) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Tipe</label>
                            <select name="type" class="form-select" required>
                                @foreach ($account::getTypes() as $t)
                                    <option value="{{ $t }}" @selected(old('type', $account->type) === $t)>{{ ucfirst($t) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label required">Nama Akun</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $account->name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Saldo Normal</label>
                            <select name="normal_balance" class="form-select" required>
                                <option value="debit" @selected(old('normal_balance', $account->normal_balance) === 'debit')>Debit</option>
                                <option value="kredit" @selected(old('normal_balance', $account->normal_balance) === 'kredit')>Kredit</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="description" class="form-control" rows="2">{{ old('description', $account->description) }}</textarea>
                        </div>
                        <div class="col-md-12">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" class="form-check-input" value="1" @checked($account->is_active)>
                                <label class="form-check-label">Akun Aktif</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
