@extends('theme::layouts.app')

@section('title', 'Bài viết')

@section('content')
<section class="journal-hero">
    <div class="site-shell journal-hero__inner">
        <p class="eyebrow">LICUTI / JOURNAL</p>
        @php($heroTitle = setting('hero_title', 'Những ý tưởng đáng để lưu lại.'))
        @php($heroParts = preg_split('/\s+/', trim($heroTitle), 2))
        <h1>{{ $heroParts[0] ?? '' }}<br><em>{{ $heroParts[1] ?? '' }}</em></h1>
        <p class="journal-hero__intro">{{ setting('hero_description', 'Góc nhìn, câu chuyện và những điều hữu ích được tuyển chọn cho hành trình kinh doanh số.') }}</p>
    </div>
</section>

<section class="site-shell section-block" aria-labelledby="latest-heading">
    <div class="section-heading">
        <div>
            <p class="eyebrow">MỚI NHẤT</p>
            <h2 id="latest-heading">Từ tòa soạn</h2>
        </div>
        <span class="section-heading__count">{{ $posts->total() }} bài viết</span>
    </div>

    @if($posts->isNotEmpty())
        @php($blogLayout = in_array($themeSettings['blog_layout'] ?? 'grid', ['grid', 'list'], true) ? $themeSettings['blog_layout'] : 'grid')
        @php($gridColumns = max(2, min(4, (int) ($themeSettings['grid_columns'] ?? 3))))
        <div class="post-grid post-grid--{{ $blogLayout }}" style="--grid-columns: {{ $gridColumns }};">
            @foreach($posts as $post)
                @php($translation = $post->translationFor($locale, config('app.fallback_locale', 'vi')))
                <article class="post-card">
                    <a class="post-card__image" href="{{ route('storefront.posts.show', $translation?->slug) }}">
                        @if($post->thumbnail_url)
                            <img src="{{ $post->thumbnail_url }}" alt="{{ $translation?->title }}" loading="lazy">
                        @else
                            <span class="post-card__image-placeholder">LICUTI</span>
                        @endif
                    </a>
                    <div class="post-card__body">
                        <div class="post-card__meta">
                            <span>{{ $post->published_at?->format('d.m.Y') }}</span>
                            @if($post->categories->isNotEmpty())
                                <span>{{ $post->categories->first()->name }}</span>
                            @endif
                            @if(!empty($themeSettings['show_reading_time']))
                                @php($wordCount = count(preg_split('/\s+/u', trim(strip_tags((string) ($translation?->content ?? ''))), -1, PREG_SPLIT_NO_EMPTY) ?: []))
                                <span>{{ max(1, (int) ceil($wordCount / 200)) }} phút đọc</span>
                            @endif
                        </div>
                        <h3><a href="{{ route('storefront.posts.show', $translation?->slug) }}">{{ $translation?->title }}</a></h3>
                        @if($translation?->excerpt)
                            <p>{{ $translation->excerpt }}</p>
                        @endif
                        <a class="text-link" href="{{ route('storefront.posts.show', $translation?->slug) }}">Đọc bài viết <span aria-hidden="true">↗</span></a>
                    </div>
                </article>
            @endforeach
        </div>

        @if($posts->hasPages())
            <nav class="pagination-wrap" aria-label="Phân trang bài viết">
                {{ $posts->links('theme::partials.pagination') }}
            </nav>
        @endif
    @else
        <div class="empty-state">
            <span class="empty-state__mark">—</span>
            <h2>Chưa có bài viết được xuất bản</h2>
            <p>Các bài viết mới sẽ xuất hiện tại đây.</p>
        </div>
    @endif
</section>
@endsection
