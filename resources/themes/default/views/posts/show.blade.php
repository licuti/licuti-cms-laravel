@extends('theme::layouts.app')

@section('title', $translation?->title ?? 'Bài viết')

@section('content')
<article class="post-detail">
    <header class="post-detail__hero site-shell">
        <a class="back-link" href="{{ route('storefront.posts.index') }}">← Tất cả bài viết</a>
        <div class="post-detail__meta">
            <span>{{ $post->published_at?->format('d.m.Y') }}</span>
            <span>{{ $post->view_count }} lượt đọc</span>
            @if(!empty($themeSettings['show_reading_time']))
                @php($wordCount = count(preg_split('/\s+/u', trim(strip_tags((string) ($translation?->content ?? ''))), -1, PREG_SPLIT_NO_EMPTY) ?: []))
                <span>{{ max(1, (int) ceil($wordCount / 200)) }} phút đọc</span>
            @endif
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

    <div class="site-shell post-detail__layout {{ ($themeSettings['sidebar_layout'] ?? 'right') === 'none' ? 'post-detail__layout--no-sidebar' : '' }}">
        <div class="post-detail__main">
            <div class="post-detail__content">{!! $translation?->content !!}</div>
            @if(!empty($themeSettings['show_social_share']))
                @php($shareUrl = urlencode(request()->fullUrl()))
                @php($shareTitle = urlencode((string) ($translation?->title ?? '')))
                <div class="post-detail__share" aria-label="Chia sẻ bài viết">
                    <span class="eyebrow">CHIA SẺ</span>
                    <div class="post-detail__share-links">
                        <a class="share-link" href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" target="_blank" rel="noopener noreferrer">Facebook</a>
                        <a class="share-link" href="https://twitter.com/intent/tweet?url={{ $shareUrl }}&text={{ $shareTitle }}" target="_blank" rel="noopener noreferrer">X</a>
                        <a class="share-link" href="https://www.linkedin.com/sharing/share-offsite/?url={{ $shareUrl }}" target="_blank" rel="noopener noreferrer">LinkedIn</a>
                    </div>
                </div>
            @endif
        </div>
        @if(($themeSettings['sidebar_layout'] ?? 'right') !== 'none')
        <aside class="post-detail__aside">
            @if(!empty($themeSettings['show_author_box']))
                <p class="eyebrow">VỀ BÀI VIẾT</p>
                <p>Được viết bởi <strong>{{ $post->author?->name ?? 'Licuti Editorial' }}</strong>.</p>
            @endif
            @if($post->categories->isNotEmpty())
                <p class="post-detail__category">{{ $post->categories->pluck('name')->join(' · ') }}</p>
            @endif
        </aside>
        @endif
    </div>

    @if(($relatedPosts ?? collect())->isNotEmpty())
        <section class="site-shell related-posts" aria-labelledby="related-heading">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">ĐỌC TIẾP</p>
                    <h2 id="related-heading">Bài viết liên quan</h2>
                </div>
            </div>
            <div class="post-grid" style="--grid-columns: 3;">
                @foreach($relatedPosts as $related)
                    @php($relatedTranslation = $related->translationFor($locale, config('app.fallback_locale', 'vi')))
                    <article class="post-card">
                        <a class="post-card__image" href="{{ route('storefront.posts.show', $relatedTranslation?->slug) }}">
                            @if($related->thumbnail_url)
                                <img src="{{ $related->thumbnail_url }}" alt="{{ $relatedTranslation?->title }}" loading="lazy">
                            @else
                                <span class="post-card__image-placeholder">LICUTI</span>
                            @endif
                        </a>
                        <div class="post-card__body">
                            <div class="post-card__meta">
                                <span>{{ $related->published_at?->format('d.m.Y') }}</span>
                            </div>
                            <h3><a href="{{ route('storefront.posts.show', $relatedTranslation?->slug) }}">{{ $relatedTranslation?->title }}</a></h3>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif
</article>
@endsection
