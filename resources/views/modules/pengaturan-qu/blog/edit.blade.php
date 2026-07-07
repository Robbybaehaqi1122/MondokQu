<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('pengaturan.blog.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="ti ti-arrow-left"></i> Kembali
            </a>
            <h2 class="page-title mb-0">Edit Blog</h2>
        </div>
    </x-slot>

    <div class="row">
        <div class="col-lg-9">
            <form action="{{ route('pengaturan.blog.update', $blog) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="card">
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label required">Judul Blog</label>
                            <input type="text" name="title" class="form-control form-control-lg @error('title') is-invalid @enderror" value="{{ old('title', $blog->title) }}" required>
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Slug</label>
                                <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $blog->slug) }}" placeholder="Otomatis dari judul">
                                <div class="form-text">URL blog. Kosongkan untuk generate otomatis.</div>
                                @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kategori</label>
                                <select name="category_id" class="form-select @error('category_id') is-invalid @enderror">
                                    <option value="">Tanpa Kategori</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}" @selected(old('category_id', $blog->category_id) == $cat->id)>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">Konten</label>
                            <textarea name="content" id="content-input" class="form-control @error('content') is-invalid @enderror" rows="16">{{ old('content', $blog->content) }}</textarea>
                            @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Ringkasan (Excerpt)</label>
                            <textarea name="excerpt" class="form-control @error('excerpt') is-invalid @enderror" rows="3">{{ old('excerpt', $blog->excerpt) }}</textarea>
                            <div class="form-text">Ringkasan singkat yang muncul di halaman daftar blog.</div>
                            @error('excerpt')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Gambar Featured</label>
                            @if ($blog->featured_image)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/'.$blog->featured_image) }}" alt="{{ $blog->title }}" class="rounded" style="max-height: 150px;">
                                </div>
                            @endif
                            <input type="file" name="featured_image" class="form-control @error('featured_image') is-invalid @enderror" accept="image/jpg,image/jpeg,image/png,image/webp">
                            <div class="form-text">Format: JPG, PNG, WebP. Maks: 2MB. Upload ulang jika ingin mengganti.</div>
                            @error('featured_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-check form-switch mb-0">
                            <input type="hidden" name="is_published" value="0">
                            <input type="checkbox" name="is_published" class="form-check-input" value="1" @checked(old('is_published', $blog->is_published)) id="is_published">
                            <label class="form-check-label" for="is_published">Publikasikan</label>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mb-5">
                    <a href="{{ route('pengaturan.blog.index') }}" class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">Update Blog</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.ckeditor.com/4.24.0/full/ckeditor.js"></script>
    <script>
    CKEDITOR.replace('content-input', {
        height: 500,
        removePlugins: 'exportpdf',
        toolbar: [
            { name: 'document', items: ['Source', '-', 'Preview', 'Print'] },
            { name: 'clipboard', items: ['Undo', 'Redo', '-', 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord'] },
            '/',
            { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat'] },
            { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'] },
            { name: 'links', items: ['Link', 'Unlink', 'Anchor'] },
            { name: 'insert', items: ['Image', 'Table', 'HorizontalRule', 'SpecialChar', 'PageBreak'] },
            '/',
            { name: 'styles', items: ['Format', 'Font', 'FontSize'] },
            { name: 'colors', items: ['TextColor', 'BGColor'] },
            { name: 'tools', items: ['Maximize', 'ShowBlocks'] },
        ],
        format_tags: 'p;h1;h2;h3;h4;pre',
        allowedContent: true,
    });
    </script>
    @endpush
</x-app-layout>
