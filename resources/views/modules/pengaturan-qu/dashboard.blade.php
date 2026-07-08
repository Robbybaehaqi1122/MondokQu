<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="page-title mb-0">Pengaturan</h2>
        </div>
    </x-slot>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card card-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="avatar avatar-lg bg-primary-lt">
                        <i class="ti ti-article fs-2"></i>
                    </span>
                    <div>
                        <div class="fs-1 fw-bold">{{ $blogCount }}</div>
                        <div class="text-secondary">Total Blog</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="avatar avatar-lg bg-success-lt">
                        <i class="ti ti-circle-check fs-2"></i>
                    </span>
                    <div>
                        <div class="fs-1 fw-bold">{{ $publishedCount }}</div>
                        <div class="text-secondary">Dipublikasikan</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="avatar avatar-lg bg-info-lt">
                        <i class="ti ti-settings fs-2"></i>
                    </span>
                    <div>
                        <div class="fs-1 fw-bold">{{ $blogCount - $publishedCount }}</div>
                        <div class="text-secondary">Draft</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Menu Pengaturan</h3>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <a href="{{ route('pengaturan.blog.index') }}" class="card card-link card-hover">
                                <div class="card-body text-center p-4">
                                    <span class="avatar avatar-lg mb-3 bg-primary-lt">
                                        <i class="ti ti-article fs-2"></i>
                                    </span>
                                    <h4>Blog</h4>
                                    <p class="text-secondary mb-0 small">Kelola artikel blog pondok</p>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="#" class="card card-link card-hover disabled" style="opacity: 0.5; pointer-events: none;">
                                <div class="card-body text-center p-4">
                                    <span class="avatar avatar-lg mb-3 bg-secondary-lt">
                                        <i class="ti ti-code fs-2"></i>
                                    </span>
                                    <h4>Halaman Statis</h4>
                                    <p class="text-secondary mb-0 small">Kelola konten halaman publik (segera)</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if ($latestBlogs->isNotEmpty())
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Blog Terbaru</h3>
                        <a href="{{ route('pengaturan.blog.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                    </div>
                    <div class="list-group list-group-flush">
                        @foreach ($latestBlogs as $blog)
                            <div class="list-group-item d-flex align-items-center gap-3">
                                @if ($blog->featured_image)
                                    <img src="{{ asset('storage/'.$blog->featured_image) }}" alt="{{ $blog->title }}" class="rounded" width="48" height="48" style="object-fit: cover;">
                                @else
                                    <span class="avatar avatar-md bg-secondary-lt">
                                        <i class="ti ti-article"></i>
                                    </span>
                                @endif
                                <div class="flex-grow-1 min-w-0">
                                    <div class="text-truncate fw-medium">{{ $blog->title }}</div>
                                    <div class="text-secondary small">
                                        {{ $blog->author?->name ?? '-' }}
                                        @if ($blog->is_published)
                                            &middot; {{ $blog->published_at?->translatedFormat('d M Y') }}
                                        @else
                                            &middot; <span class="badge bg-warning">Draft</span>
                                        @endif
                                    </div>
                                </div>
                                <a href="{{ route('pengaturan.blog.edit', $blog) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="ti ti-edit"></i>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
