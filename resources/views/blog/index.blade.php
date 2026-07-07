@extends('layouts-public.app')

@section('title', 'Blog - MondokQu')

@php
    $themeColor = config('app.tenant_theme_color', '#0d9488');
    $currentUser = auth()->user();
@endphp

@section('content')
    <section class="section-padding" style="padding-top: 8rem;">
        <div class="container">
            <div class="text-center mb-5">
                <span class="badge" style="background: color-mix(in srgb, {{ $themeColor }} 15%, white); color: {{ $themeColor }}; border: 1px solid color-mix(in srgb, {{ $themeColor }} 30%, transparent); padding: 0.4rem 1rem; border-radius: 100px; font-size: 0.8rem;">
                    Blog
                </span>
                <h1 class="display-5 fw-bold mt-3" style="color: var(--c-ink);">Artikel & Berita Terbaru</h1>
                <p class="text-secondary fs-5" style="max-width: 600px; margin: 0 auto;">
                    Informasi, tips, dan berita seputar pengelolaan pondok pesantren modern.
                </p>
            </div>

            @if ($blogs->isEmpty())
                <div class="text-center py-5">
                    <i class="ti ti-article-off fs-1 text-secondary"></i>
                    <p class="mt-3 text-secondary">Belum ada artikel blog. Kembali lagi nanti!</p>
                </div>
            @else
                <div class="row g-4">
                    @foreach ($blogs as $blog)
                        <div class="col-md-6 col-lg-4">
                            <a href="{{ route('blog.show', $blog->slug) }}" class="text-decoration-none">
                                <div class="card h-100 border-0 shadow-sm" style="border-radius: 16px; overflow: hidden; transition: transform 0.2s, box-shadow 0.2s;">
                                    <div style="height: 200px; background: #f0f0f0; overflow: hidden;">
                                        @if ($blog->featured_image)
                                            <img src="{{ asset('storage/'.$blog->featured_image) }}" alt="{{ $blog->title }}" class="w-100 h-100" style="object-fit: cover;">
                                        @else
                                            <div class="w-100 h-100 d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, color-mix(in srgb, {{ $themeColor }} 10%, white), color-mix(in srgb, {{ $themeColor }} 5%, white));">
                                                <i class="ti ti-article" style="font-size: 3rem; color: {{ $themeColor }};"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <small class="text-secondary">{{ $blog->published_at?->translatedFormat('d M Y') }}</small>
                                            <span class="text-secondary">&middot;</span>
                                            <small class="text-secondary">{{ $blog->getReadingTime() }}</small>
                                            @if ($blog->category)
                                                <span class="text-secondary">&middot;</span>
                                                <span class="badge" style="background: color-mix(in srgb, {{ $themeColor }} 15%, white); color: {{ $themeColor }}; font-size: 0.7rem;">{{ $blog->category->name }}</span>
                                            @endif
                                        </div>
                                        <h5 class="fw-bold mb-2" style="color: var(--c-ink);">{{ $blog->title }}</h5>
                                        <p class="text-secondary small mb-0">
                                            {{ $blog->getExcerptHtml() }}
                                        </p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>

                <div class="mt-5 d-flex justify-content-center">
                    {{ $blogs->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
