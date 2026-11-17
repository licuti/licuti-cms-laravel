@props([
    'lang' => null,
    'seo'  => null,
    'showHeader' => true,
])

@php
    $langCode = $lang?->code ?? app()->getLocale();
    
    // Fallbacks from old input or DB
    $metaTitle       = old("translations.{$langCode}.meta_title", $seo?->meta_title ?? '');
    $metaDescription = old("translations.{$langCode}.meta_description", $seo?->meta_description ?? '');

    $ogTitle         = old("translations.{$langCode}.og_title", $seo?->og_title ?? '');
    $ogDescription   = old("translations.{$langCode}.og_description", $seo?->og_description ?? '');
    $ogImage         = old("translations.{$langCode}.og_image", $seo?->og_image ?? '');
    
    $canonicalUrl    = old("translations.{$langCode}.canonical_url", $seo?->canonical_url ?? '');
    $robotsIndex     = old("translations.{$langCode}.robots_index", $seo?->robots_index ?? true);
    $robotsFollow    = old("translations.{$langCode}.robots_follow", $seo?->robots_follow ?? true);
    $schemaType      = old("translations.{$langCode}.schema_type", $seo?->schema_type ?? '');
    $focusKeyword    = old("translations.{$langCode}.focus_keyword", $seo?->focus_keyword ?? '');

    // Preview URL — dùng chung một nguồn cho cả Google & Social
    $previewBaseUrl = rtrim(config('app.url'), '/');
    $previewHost    = parse_url($previewBaseUrl, PHP_URL_HOST) ?: request()->getHost();
@endphp

<div {{ $attributes->merge(['class' => 'seo-meta-container']) }}
    data-lang="{{ $langCode }}"
    data-fallback-title="{{ __('Tiêu đề trang sẽ hiển thị ở đây') }}"
    data-fallback-desc="{{ __('Vui lòng nhập mô tả ngắn (Meta description) để tối ưu hóa SEO. Một mô tả tốt sẽ thu hút người dùng nhấp vào liên kết của bạn.') }}"
    data-placeholder-title="{{ __('Nhập tiêu đề SEO...') }}"
    data-placeholder-desc="{{ __('Nhập mô tả ngắn cho SEO...') }}"
>
    @if($showHeader)
    <div class="d-flex align-items-center gap-2 mb-3">
        <span class="seo-section-icon d-inline-flex align-items-center justify-content-center rounded text-bg-primary bg-opacity-10 text-primary" style="width: 2rem; height: 2rem;">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </span>
        <div>
            <h5 class="fw-semibold mb-0 fs-6">{{ __('Cấu hình SEO') }}</h5>
            <p class="text-body-secondary small mb-0">{{ __('Tối ưu hiển thị trên Google và mạng xã hội.') }}</p>
        </div>
    </div>
    @endif
    
    {{-- Tabs Nav --}}
    <ul class="nav nav-pills seo-tabs d-flex flex-wrap gap-1 p-1 mb-3 rounded border bg-body-tertiary" id="seoTabs-{{ $langCode }}" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active w-100 py-1 px-2 small fw-medium d-inline-flex align-items-center justify-content-center" id="seo-google-tab-{{ $langCode }}" data-bs-toggle="pill" data-bs-target="#seo-google-{{ $langCode }}" type="button" role="tab" aria-controls="seo-google-{{ $langCode }}" aria-selected="true">
                <svg class="me-1 shrink-0" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12.48 10.92v3.28h7.84c-.24 1.84-.853 3.187-1.787 4.133-1.147 1.147-2.933 2.4-6.053 2.4-4.827 0-8.6-3.893-8.6-8.72s3.773-8.72 8.6-8.72c2.6 0 4.507 1.027 5.907 2.347l2.307-2.307C18.747 1.44 16.133 0 12.48 0 5.867 0 .307 5.387.307 12s5.56 12 12.173 12c3.573 0 6.267-1.173 8.373-3.36 2.16-2.16 2.84-5.213 2.84-7.667 0-.76-.053-1.467-.173-2.053H12.48z"/></svg>
                <span>{{ __('Google') }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link w-100 py-1 px-2 small fw-medium d-inline-flex align-items-center justify-content-center" id="seo-social-tab-{{ $langCode }}" data-bs-toggle="pill" data-bs-target="#seo-social-{{ $langCode }}" type="button" role="tab" aria-controls="seo-social-{{ $langCode }}" aria-selected="false">
                <svg class="me-1 shrink-0" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                <span>{{ __('Facebook') }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link w-100 py-1 px-2 small fw-medium d-inline-flex align-items-center justify-content-center" id="seo-advanced-tab-{{ $langCode }}" data-bs-toggle="pill" data-bs-target="#seo-advanced-{{ $langCode }}" type="button" role="tab" aria-controls="seo-advanced-{{ $langCode }}" aria-selected="false">
                <svg class="me-1 shrink-0" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>{{ __('Nâng cao') }}</span>
            </button>
        </li>
    </ul>

    {{-- Khung xem trước (Google / Facebook) --}}
    <div class="d-flex flex-column gap-3 mb-4 seo-preview-col">

        {{-- Google Preview Card --}}
        <x-admin.card class="bg-body-tertiary preview-card google-preview-card" data-lang="{{ $langCode }}">
            <h6 class="small fw-bold text-uppercase text-body-secondary mb-3 d-flex justify-content-between align-items-center">
                <span class="d-inline-flex align-items-center">
                    <svg class="text-primary me-1 shrink-0" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12.48 10.92v3.28h7.84c-.24 1.84-.853 3.187-1.787 4.133-1.147 1.147-2.933 2.4-6.053 2.4-4.827 0-8.6-3.893-8.6-8.72s3.773-8.72 8.6-8.72c2.6 0 4.507 1.027 5.907 2.347l2.307-2.307C18.747 1.44 16.133 0 12.48 0 5.867 0 .307 5.387.307 12s5.56 12 12.173 12c3.573 0 6.267-1.173 8.373-3.36 2.16-2.16 2.84-5.213 2.84-7.667 0-.76-.053-1.467-.173-2.053H12.48z"/></svg>
                    <span>{{ __('Xem trước Google') }}</span>
                </span>
                <div class="btn-group btn-group-sm seo-preview-toggle">
                    <button type="button" class="btn btn-outline-secondary active btn-preview-desktop d-inline-flex align-items-center" title="Desktop" aria-pressed="true" aria-label="{{ __('Xem trước dạng desktop') }}">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-preview-mobile d-inline-flex align-items-center" title="Mobile" aria-pressed="false" aria-label="{{ __('Xem trước dạng mobile') }}">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </button>
                </div>
            </h6>

            <div class="google-preview-container desktop-view bg-white border rounded shadow-sm p-3 mx-auto" data-bs-theme="light" style="transition: max-width 0.3s ease; max-width: 600px; width: 100%;">
                <div class="d-flex gap-2 mb-1">
                    <div class="rounded-circle bg-body-secondary d-flex align-items-center justify-content-center shrink-0" style="width: 28px; height: 28px;">
                        <svg width="16" height="16" class="text-body-tertiary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                    </div>
                    <div class="d-flex flex-column justify-content-center" style="min-width: 0;">
                        <span class="small fw-medium text-body lh-1">{{ config('app.name') }}</span>
                        <span class="small text-body-secondary lh-1 text-truncate" style="font-size: 0.75rem;">
                            {{ $previewBaseUrl }}/<span class="seo-preview-slug">...</span>
                        </span>
                    </div>
                </div>
                <div class="fs-5 text-primary mb-1 fw-medium seo-preview-title seo-clamp-2" style="line-height: 1.3;">
                    {{ $metaTitle ?: __('Tiêu đề trang sẽ hiển thị ở đây') }}
                </div>
                <div class="small text-body-secondary seo-preview-description seo-clamp-2">
                    {{ $metaDescription ?: __('Vui lòng nhập mô tả ngắn (Meta description) để tối ưu hóa SEO. Một mô tả tốt sẽ thu hút người dùng nhấp vào liên kết của bạn.') }}
                </div>
            </div>
        </x-admin.card>

        {{-- Social Preview Card (Hidden initially, toggled via JS) --}}
        <x-admin.card class="bg-body-tertiary preview-card social-preview-card d-none" data-lang="{{ $langCode }}">
            <h6 class="small fw-bold text-uppercase text-body-secondary mb-3 d-inline-flex align-items-center">
                <svg class="text-primary me-1 shrink-0" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                <span>{{ __('Xem trước Facebook') }}</span>
            </h6>

            <div class="seo-preview-surface social-preview border rounded shadow-sm overflow-hidden" data-bs-theme="light">
                <div class="seo-preview-og-image seo-og-image bg-body-secondary d-flex align-items-center justify-content-center text-body-tertiary">
                    <svg width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div class="p-3 bg-body-tertiary border-top">
                    <div class="text-body-secondary text-uppercase mb-1 seo-og-domain">{{ $previewHost }}</div>
                    <div class="fw-bold text-body seo-preview-og-title seo-clamp-2">
                        {{ $ogTitle ?: ($metaTitle ?: __('Tiêu đề trang sẽ hiển thị ở đây')) }}
                    </div>
                    <div class="small text-body-secondary mt-1 seo-preview-og-description seo-clamp-1">
                        {{ $ogDescription ?: ($metaDescription ?: __('Mô tả ngắn của trang sẽ xuất hiện tại đây...')) }}
                    </div>
                </div>
            </div>
        </x-admin.card>
    </div>

    {{-- Form nhập liệu SEO --}}
    <div class="seo-fields">
        {{-- Tabs Content --}}
            <div class="tab-content" id="seoTabsContent-{{ $langCode }}">
                
                {{-- TAB: GOOGLE SEARCH --}}
                <div class="tab-pane fade show active" id="seo-google-{{ $langCode }}" role="tabpanel" aria-labelledby="seo-google-tab-{{ $langCode }}">
                    <x-admin.form-group description="{{ __('Từ khóa chính của nội dung, dùng để đánh giá mức độ tối ưu SEO.') }}">
                        <x-slot:label>{{ __('Từ khóa chính (Focus keyword)') }}</x-slot:label>
                        <x-admin.input 
                            type="text" 
                            name="translations[{{ $langCode }}][focus_keyword]" 
                            size="sm" 
                            value="{{ $focusKeyword }}" 
                            class="seo-focus-keyword"
                            data-lang="{{ $langCode }}"
                            placeholder="{{ __('Ví dụ: tối ưu SEO') }}" 
                        />
                    </x-admin.form-group>

                    <x-admin.form-group>
                        <x-slot:label>{{ __('Tiêu đề SEO (Meta Title)') }}</x-slot:label>
                        <x-admin.input 
                            type="text" 
                            name="translations[{{ $langCode }}][meta_title]" 
                            size="sm" 
                            value="{{ $metaTitle }}" 
                            class="seo-meta-title"
                            data-lang="{{ $langCode }}"
                            maxlength="60"
                            placeholder="{{ __('Nhập tiêu đề SEO...') }}" 
                        />
                        <div class="progress mt-2 bg-body-secondary" style="height: 3px;" aria-hidden="true">
                            <div class="progress-bar bg-secondary seo-progress-title" role="progressbar" style="width: 0%;"></div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <small class="text-body-secondary">{{ __('Khuyên dùng từ 40 - 60 ký tự.') }}</small>
                            <small class="fw-semibold text-body-secondary font-monospace seo-counter-title" data-lang="{{ $langCode }}">0/60</small>
                        </div>
                    </x-admin.form-group>
                    
                    <x-admin.form-group>
                        <x-slot:label>{{ __('Mô tả SEO (Meta Description)') }}</x-slot:label>
                        <x-admin.textarea 
                            name="translations[{{ $langCode }}][meta_description]" 
                            size="sm"
                            rows="3"
                            class="seo-meta-description"
                            data-lang="{{ $langCode }}"
                            maxlength="160"
                            placeholder="{{ __('Nhập mô tả ngắn cho SEO...') }}"
                        >{{ $metaDescription }}</x-admin.textarea>
                        <div class="progress mt-2 bg-body-secondary" style="height: 3px;" aria-hidden="true">
                            <div class="progress-bar bg-secondary seo-progress-description" role="progressbar" style="width: 0%;"></div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <small class="text-body-secondary">{{ __('Khuyên dùng từ 120 đến 160 ký tự.') }}</small>
                            <small class="fw-semibold text-body-secondary font-monospace seo-counter-description" data-lang="{{ $langCode }}">0/160</small>
                        </div>
                    </x-admin.form-group>
                </div>

                {{-- TAB: MẠNG XÃ HỘI (SOCIAL) --}}
                <div class="tab-pane fade" id="seo-social-{{ $langCode }}" role="tabpanel" aria-labelledby="seo-social-tab-{{ $langCode }}">
                    <div class="alert alert-info mb-3 py-2 small d-flex align-items-center">
                        <svg class="me-2 shrink-0" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ __('Mặc định sẽ sử dụng thông tin của Google SEO nếu bạn để trống các ô dưới đây.') }}</span>
                    </div>
                    <x-admin.form-group description="{{ __('Tiêu đề hiển thị khi chia sẻ lên Facebook, Zalo, Twitter...') }}">
                        <x-slot:label>{{ __('Tiêu đề chia sẻ (OG Title)') }}</x-slot:label>
                        <x-admin.input 
                            type="text" 
                            name="translations[{{ $langCode }}][og_title]" 
                            size="sm"
                            value="{{ $ogTitle }}" 
                            class="seo-og-title"
                            data-lang="{{ $langCode }}"
                        />
                    </x-admin.form-group>

                    <x-admin.form-group description="{{ __('Mô tả hiển thị khi chia sẻ.') }}">
                        <x-slot:label>{{ __('Mô tả chia sẻ (OG Description)') }}</x-slot:label>
                        <x-admin.textarea 
                            name="translations[{{ $langCode }}][og_description]" 
                            size="sm"
                            rows="3"
                            class="seo-og-description"
                            data-lang="{{ $langCode }}"
                        >{{ $ogDescription }}</x-admin.textarea>
                    </x-admin.form-group>

                    <x-admin.form-group description="{{ __('Đường dẫn ảnh bìa khi chia sẻ. Kích thước khuyên dùng: 1200x630px.') }}">
                        <x-slot:label>{{ __('Ảnh chia sẻ (OG Image)') }}</x-slot:label>
                        <x-admin.image-upload
                            name="translations[{{ $langCode }}][og_image]"
                            :current="$ogImage"
                            return="url"
                            input-class="seo-og-image-input"
                        />
                    </x-admin.form-group>
                </div>

                {{-- TAB: NÂNG CAO --}}
                <div class="tab-pane fade" id="seo-advanced-{{ $langCode }}" role="tabpanel" aria-labelledby="seo-advanced-tab-{{ $langCode }}">
                    <input type="hidden" name="translations[{{ $langCode }}][robots_index]" value="0">
                    <x-admin.toggle 
                        name="translations[{{ $langCode }}][robots_index]" 
                        :checked="$robotsIndex"
                        value="1"
                        label="{{ __('Cho phép Index (Lập chỉ mục)') }}" 
                        description="{{ __('Bật để Google có thể tìm thấy bài này.') }}"
                        color="green"
                    />
                    <input type="hidden" name="translations[{{ $langCode }}][robots_follow]" value="0">
                    <x-admin.toggle 
                        name="translations[{{ $langCode }}][robots_follow]" 
                        :checked="$robotsFollow"
                        value="1"
                        label="{{ __('Cho phép Follow link') }}" 
                        description="{{ __('Bật để Bot đi theo các link trong bài.') }}"
                        color="blue"
                    />

                    <x-admin.form-group class="mt-3" description="{{ __('URL gốc của nội dung này. Giúp tránh lỗi trùng lặp nội dung nếu copy từ nguồn khác. Để trống mặc định là link hiện tại.') }}">
                        <x-slot:label>{{ __('Canonical URL') }}</x-slot:label>
                        <x-admin.input 
                            type="url" 
                            name="translations[{{ $langCode }}][canonical_url]" 
                            size="sm"
                            value="{{ $canonicalUrl }}" 
                            placeholder="https://..." 
                        />
                    </x-admin.form-group>

                    <x-admin.form-group description="{{ __('Định dạng dữ liệu có cấu trúc giúp Google hiểu nội dung trang tốt hơn (Rich Snippets).') }}">
                        <x-slot:label>{{ __('Dữ liệu có cấu trúc (Schema)') }}</x-slot:label>
                        <x-admin.select name="translations[{{ $langCode }}][schema_type]" size="sm">
                            <option value=""  @selected($schemaType == '')>{{ __('Mặc định (Không thiết lập)') }}</option>
                            <option value="Article" @selected($schemaType == 'Article')>{{ __('Article (Bài viết chung)') }}</option>
                            <option value="NewsArticle" @selected($schemaType == 'NewsArticle')>{{ __('NewsArticle (Bài báo/Tin tức)') }}</option>
                            <option value="BlogPosting" @selected($schemaType == 'BlogPosting')>{{ __('BlogPosting (Bài viết Blog)') }}</option>
                            <option value="Product" @selected($schemaType == 'Product')>{{ __('Product (Sản phẩm)') }}</option>
                            <option value="FAQPage" @selected($schemaType == 'FAQPage')>{{ __('FAQPage (Trang câu hỏi thường gặp)') }}</option>
                        </x-admin.select>
                    </x-admin.form-group>
                </div>
            </div>
        </div>
</div>

