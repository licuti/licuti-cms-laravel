<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\Language\LanguageDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Language\StoreLanguageRequest;
use App\Http\Requests\Admin\Language\UpdateLanguageRequest;
use App\Models\Language;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
use App\Services\Admin\Language\LanguageService;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function __construct(
        protected LanguageRepositoryInterface $languageRepository,
        protected LanguageService $languageService
    ) {}

    public function index()
    {
        $languages = $this->languageRepository->getPaginatedLanguages(20);
        return view('admin.languages.index', compact('languages'));
    }

    public function create()
    {
        $isoLanguages = config('iso_languages', []);
        return view('admin.languages.form', compact('isoLanguages'));
    }

    public function store(StoreLanguageRequest $request)
    {
        $dto = LanguageDTO::fromRequest($request);
        $language = $this->languageService->createLanguage($dto);

        if ($request->input('submit_action') === 'save_and_edit') {
            return redirect()->route('admin.languages.edit', $language->code)
                ->with('success', __('Ngôn ngữ đã được thêm thành công.'));
        }

        return redirect()->route('admin.languages.index')
            ->with('success', __('Ngôn ngữ đã được thêm thành công.'));
    }

    public function edit(Language $language)
    {
        $isoLanguages = config('iso_languages', []);
        return view('admin.languages.form', compact('language', 'isoLanguages'));
    }

    public function update(UpdateLanguageRequest $request, Language $language)
    {
        $dto = LanguageDTO::fromRequest($request);
        $updatedLanguage = $this->languageService->updateLanguage($language, $dto);

        if ($request->input('submit_action') === 'save_and_edit') {
            return redirect()->route('admin.languages.edit', $updatedLanguage->code)
                ->with('success', __('Ngôn ngữ đã được cập nhật thành công.'));
        }

        return redirect()->route('admin.languages.index')
            ->with('success', __('Ngôn ngữ đã được cập nhật thành công.'));
    }

    public function destroy(Language $language)
    {
        $this->languageService->deleteLanguage($language);
        return redirect()->route('admin.languages.index')
            ->with('success', __('Ngôn ngữ đã được xóa thành công.'));
    }
}
