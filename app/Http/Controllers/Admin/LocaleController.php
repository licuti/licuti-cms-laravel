<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Services\Shared\Language\LanguageResolver;

class LocaleController extends Controller
{
    public function __construct(private LanguageResolver $languageResolver)
    {
    }

    public function switch(Request $request): RedirectResponse
    {
        $code = $request->input('locale');
        
        $activeLanguages = $this->languageResolver->getActiveLanguages();
        
        if ($activeLanguages->contains('code', $code)) {
            session(['admin_content_locale' => $code]);
        }
        
        return back();
    }
}
