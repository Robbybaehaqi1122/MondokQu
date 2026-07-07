<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('pengaturan.blog.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="ti ti-arrow-left"></i> Kembali
            </a>
            <h2 class="page-title mb-0">Buat Blog Baru</h2>
        </div>
    </x-slot>

    <div class="row">
        <div class="col-lg-9">
            <form action="{{ route('pengaturan.blog.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="card">
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label required">Judul Blog</label>
                            <input type="text" name="title" class="form-control form-control-lg @error('title') is-invalid @enderror" value="{{ old('title') }}" required>
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Slug</label>
                                <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug') }}" placeholder="Otomatis dari judul">
                                <div class="form-text">URL blog. Kosongkan untuk generate otomatis.</div>
                                @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kategori</label>
                                <select name="category_id" class="form-select @error('category_id') is-invalid @enderror">
                                    <option value="">Tanpa Kategori</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}" @selected(old('category_id') == $cat->id)>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">Konten</label>
                            <textarea name="content" class="form-control @error('content') is-invalid @enderror" rows="16" id="blog-editor">{{ old('content') }}</textarea>
                            @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Ringkasan (Excerpt)</label>
                            <textarea name="excerpt" class="form-control @error('excerpt') is-invalid @enderror" rows="3">{{ old('excerpt') }}</textarea>
                            <div class="form-text">Ringkasan singkat yang muncul di halaman daftar blog.</div>
                            @error('excerpt')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Gambar Featured</label>
                            <input type="file" name="featured_image" class="form-control @error('featured_image') is-invalid @enderror" accept="image/jpg,image/jpeg,image/png,image/webp">
                            <div class="form-text">Format: JPG, PNG, WebP. Maks: 2MB</div>
                            @error('featured_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-check form-switch mb-0">
                            <input type="hidden" name="is_published" value="0">
                            <input type="checkbox" name="is_published" class="form-check-input" value="1" @checked(old('is_published')) id="is_published">
                            <label class="form-check-label" for="is_published">Publikasikan langsung</label>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mb-5">
                    <a href="{{ route('pengaturan.blog.index') }}" class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan Blog</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
    tinymce.init({
        selector: '#blog-editor',
        height: 500,
        menubar: true,
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'help', 'wordcount'
        ],
        toolbar: 'undo redo | blocks | ' +
            'bold italic strikethrough underline | alignleft aligncenter alignright alignjustify | ' +
            'bullist numlist outdent indent | link image media | ' +
            'removeformat fullscreen code | help',
        branding: false,
        promotion: false,
        content_style: 'body { font-family: Inter, sans-serif; font-size: 16px; line-height: 1.8; }',
    });
    </script>
    @endpush
</x-app-layout>
