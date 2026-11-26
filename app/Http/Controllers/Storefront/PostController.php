<?php

namespace App\Http\Controllers\Storefront;

use App\Core\Base\BaseController;
use App\DTOs\Storefront\HomepageContext;
use App\DTOs\Storefront\PostContext;
use App\Repositories\Interfaces\PostRepositoryInterface;
use App\Services\Admin\Setting\SettingService;
use App\Services\Shared\Language\LanguageResolver;
use App\Services\Theme\ThemeManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PostController extends BaseController
{
    public function __construct(
        private readonly PostRepositoryInterface $postRepository,
        private readonly LanguageResolver $languageResolver,
        private readonly ThemeManager $themeManager,
    ) {}

    public function index(Request $request): View
    {
        [$locale, $fallbackLocale] = $this->locales();
        $themeSettings = $this->themeManager->settings();
        $postsPerPage = max(1, (int) setting('posts_per_page', 9));
        $posts = $this->postRepository->getPublished($postsPerPage, $locale, $fallbackLocale);
        $homepageContext = new HomepageContext(
            locale: $locale,
            fallbackLocale: $fallbackLocale,
            siteSettings: app(SettingService::class)->getAllCached(),
            themeSettings: $themeSettings,
        );

        return $this->themeManager->view('posts.index', [
            'context' => $homepageContext,
            'posts' => $posts,
            'locale' => $locale,
        ]);
    }

    public function show(string $slug): View
    {
        [$locale, $fallbackLocale] = $this->locales();
        $post = $this->postRepository->findPublishedBySlug($slug, $locale, $fallbackLocale);

        if (! $post) {
            throw new NotFoundHttpException;
        }

        $post->increment('view_count');
        $translation = $post->translationFor($locale, $fallbackLocale);
        $themeSettings = $this->themeManager->settings();
        $relatedLimit = max(0, min(6, (int) ($themeSettings['related_posts_count'] ?? 3)));
        $relatedPosts = $relatedLimit > 0
            ? $this->postRepository->getRelatedPublished($post, $relatedLimit, $locale, $fallbackLocale)
            : collect();

        $context = new PostContext(
            post: $post,
            translation: $translation,
            seo: $post->seoTranslations()->where('locale', $locale)->first(),
            locale: $locale,
            fallbackLocale: $fallbackLocale,
        );

        return $this->themeManager->view('posts.show', [
            'context' => $context,
            'post' => $post,
            'translation' => $translation,
            'relatedPosts' => $relatedPosts,
            'locale' => $locale,
        ]);
    }

    private function locales(): array
    {
        $current = $this->languageResolver->getCurrentLanguage();
        $fallback = $this->languageResolver->getDefaultLanguage();
        $locale = $current?->code ?? config('app.locale', 'vi');
        $fallbackLocale = $fallback?->code ?? config('app.fallback_locale', 'vi');

        return [$locale, $fallbackLocale];
    }
}
