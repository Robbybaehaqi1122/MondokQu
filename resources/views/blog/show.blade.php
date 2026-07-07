@extends('layouts-public.app')

@section('title', $blog->title.' - Blog MondokQu')

@php
    $themeColor = config('app.tenant_theme_color', '#0d9488');
    $currentUser = auth()->user();
@endphp

@section('content')
    <section class="section-padding" style="padding-top: 8rem;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <a href="{{ route('blog.index') }}" class="btn btn-sm btn-outline-secondary mb-4">
                        <i class="ti ti-arrow-left"></i> Semua Artikel
                    </a>

                    <article>
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <small class="text-secondary">{{ $blog->published_at?->translatedFormat('d F Y') }}</small>
                            <span class="text-secondary">&middot;</span>
                            <small class="text-secondary">{{ $blog->getReadingTime() }}</small>
                            @if ($blog->category)
                                <span class="text-secondary">&middot;</span>
                                <span class="badge" style="background: color-mix(in srgb, {{ $themeColor }} 15%, white); color: {{ $themeColor }}; font-size: 0.75rem;">{{ $blog->category->name }}</span>
                            @endif
                            @if ($blog->author)
                                <span class="text-secondary">&middot;</span>
                                <small class="text-secondary">Oleh {{ $blog->author->name }}</small>
                            @endif
                        </div>

                        <h1 class="display-6 fw-bold mb-4" style="color: var(--c-ink);">{{ $blog->title }}</h1>

                        @if ($blog->featured_image)
                            <div class="mb-4" style="border-radius: 16px; overflow: hidden;">
                                <img src="{{ asset('storage/'.$blog->featured_image) }}" alt="{{ $blog->title }}" class="w-100" style="max-height: 400px; object-fit: cover;">
                            </div>
                        @endif

                        @if ($blog->excerpt)
                            <p class="fs-5 text-secondary mb-4 fst-italic">{{ $blog->excerpt }}</p>
                        @endif

                        <div class="blog-content fs-5" style="line-height: 1.8; color: var(--c-ink);">
                            {!! $blog->content !!}
                        </div>
                    </article>

                    @if ($recentBlogs->isNotEmpty())
                        <hr class="my-5">
                        <h4 class="fw-bold mb-4">Artikel Lainnya</h4>
                        <div class="row g-3">
                            @foreach ($recentBlogs as $recent)
                                <div class="col-md-4">
                                    <a href="{{ route('blog.show', $recent->slug) }}" class="text-decoration-none">
                                        <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; overflow: hidden;">
                                            @if ($recent->featured_image)
                                                <div style="height: 120px; overflow: hidden;">
                                                    <img src="{{ asset('storage/'.$recent->featured_image) }}" alt="{{ $recent->title }}" class="w-100 h-100" style="object-fit: cover;">
                                                </div>
                                            @endif
                                            <div class="card-body p-3">
                                                <h6 class="fw-bold mb-1" style="color: var(--c-ink);">{{ $recent->title }}</h6>
                                                <small class="text-secondary">{{ $recent->published_at?->translatedFormat('d M Y') }}</small>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
