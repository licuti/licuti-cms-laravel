@extends('theme::layouts.app')

@section('title', $translation?->title ?? 'Bài viết')

@section('content')
<article class="post-detail">
    <header class="post-detail__hero site-shell">
        <a class="back-link" href="{{ route('storefront.posts.index') }}">← Tất cả bài viết</a>
        <div class="post-detail__meta">
            <span>{{ $post->published_at?->format('d.m.Y') }}</span>
            <span>{{ $post->view_count }} lượt đọc</span>
        </div>
        <h1>{{ $translation?->title }}</h1>
        @if($translation?->excerpt)
            <p class="post-detail__excerpt">{{ $translation->excerpt }}</p>
        @endif
    </header>

    @if($post->image_url)
        <div class="site-shell post-detail__cover">
            <img src="{{ $post->image_url }}" alt="{{ $translation?->title }}">
        </div>
    @endif

    <div class="site-shell post-detail__layout">
        <div class="post-detail__content">{!! $translation?->content !!}</div>
        <aside class="post-detail__aside">
            <p class="eyebrow">VỀ BÀI VIẾT</p>
            <p>Được viết bởi <strong>{{ $post->author?->name ?? 'Licuti Editorial' }}</strong>.</p>
            @if($post->categories->isNotEmpty())
                <p class="post-detail__category">{{ $post->categories->pluck('name')->join(' · ') }}</p>
            @endif
        </aside>
    </div>
</article>
@endsection
