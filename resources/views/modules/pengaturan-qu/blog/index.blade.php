<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="page-title mb-0">Blog</h2>
            <a href="{{ route('pengaturan.blog.create') }}" class="btn btn-primary">
                <i class="ti ti-plus"></i> Buat Blog Baru
            </a>
        </div>
    </x-slot>

    <div class="card">
        <div class="card-body">
            <form method="GET" class="mb-3">
                <div class="input-group">
                    <input type="text" name="q" class="form-control" placeholder="Cari blog..." value="{{ $search }}">
                    <button class="btn btn-outline-secondary" type="submit">
                        <i class="ti ti-search"></i> Cari
                    </button>
                    @if ($search)
                        <a href="{{ route('pengaturan.blog.index') }}" class="btn btn-outline-danger">
                            <i class="ti ti-x"></i>
                        </a>
                    @endif
                </div>
            </form>

            @if ($blogs->isEmpty())
                <div class="empty">
                    <div class="empty-icon">
                        <i class="ti ti-article-off fs-1"></i>
                    </div>
                    <p class="empty-title">Belum ada blog</p>
                    <p class="empty-subtitle text-secondary">Buat blog pertama untuk pondok Anda.</p>
                    <a href="{{ route('pengaturan.blog.create') }}" class="btn btn-primary">
                        <i class="ti ti-plus"></i> Buat Blog
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-vcenter">
                        <thead>
                            <tr>
                                <th>Judul</th>
                                <th>Penulis</th>
                                <th>Kategori</th>
                                <th>Status</th>
                                <th>Dibuat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($blogs as $blog)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            @if ($blog->featured_image)
                                                <img src="{{ asset('storage/'.$blog->featured_image) }}" alt="{{ $blog->title }}" class="rounded" width="40" height="40" style="object-fit: cover;">
                                            @else
                                                <span class="avatar avatar-sm bg-secondary-lt">
                                                    <i class="ti ti-article"></i>
                                                </span>
                                            @endif
                                            <div class="fw-medium text-truncate" style="max-width: 300px;">{{ $blog->title }}</div>
                                        </div>
                                    </td>
                                    <td>{{ $blog->author?->name ?? '-' }}</td>
                                    <td>
                                        @if ($blog->category)
                                            <span class="badge bg-secondary-lt">{{ $blog->category->name }}</span>
                                        @else
                                            <span class="text-secondary">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($blog->is_published)
                                            <span class="badge bg-success">Published</span>
                                        @else
                                            <span class="badge bg-warning">Draft</span>
                                        @endif
                                    </td>
                                    <td>{{ $blog->created_at->translatedFormat('d M Y') }}</td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('pengaturan.blog.edit', $blog) }}" class="btn btn-sm btn-outline-secondary">
                                                <i class="ti ti-edit"></i>
                                            </a>
                                            <form action="{{ route('pengaturan.blog.destroy', $blog) }}" method="POST" onsubmit="return confirm('Hapus blog ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $blogs->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
