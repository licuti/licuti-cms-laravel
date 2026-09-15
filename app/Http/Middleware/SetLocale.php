<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\Shared\Language\LanguageResolver;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = session('admin_content_locale');

        if (!$locale) {
            try {
                $resolver = app(LanguageResolver::class);
                $defaultLang = $resolver->getDefaultLanguage();
                $locale = $defaultLang ? $defaultLang->code : config('app.locale', 'vi');
            } catch (\Exception $e) {
                $locale = config('app.locale', 'vi');
            }
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
