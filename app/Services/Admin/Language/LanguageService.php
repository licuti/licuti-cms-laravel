<?php

namespace App\Services\Admin\Language;

use App\DTOs\Language\LanguageDTO;
use App\Models\Language;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Exceptions\LanguageException;

class LanguageService
{
    public function __construct(
        protected LanguageRepositoryInterface $languageRepository
    ) {}

    public function createLanguage(LanguageDTO $dto): Language
    {
        DB::beginTransaction();
        try {
            if ($dto->is_default) {
                // Remove default from others
                Language::where('is_default', true)->update(['is_default' => false]);
            }

            $language = $this->languageRepository->create($dto->toArray());

            $this->languageRepository->clearCache();
            DB::commit();

            return $language;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateLanguage(Language $language, LanguageDTO $dto): Language
    {
        DB::beginTransaction();
        try {
            // Cannot deactivate the default language
            if ($language->is_default && !$dto->is_active) {
                throw new LanguageException(__('Cannot deactivate the default language.'));
            }
            
            // If setting this one to default, remove from others
            if ($dto->is_default && !$language->is_default) {
                Language::where('id', '!=', $language->id)
                        ->where('is_default', true)
                        ->update(['is_default' => false]);
            }
            
            // If unsetting this one from default, ensure there's another default?
            // Actually, we should probably just prevent unsetting default directly, 
            // the admin should set another one to default instead.
            if ($language->is_default && !$dto->is_default) {
                throw new LanguageException(__('You cannot unset the default language directly. Set another language as default instead.'));
            }

            $language = $this->languageRepository->update($language->id, $dto->toArray());

            $this->languageRepository->clearCache();
            DB::commit();

            return $language;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteLanguage(Language $language): bool
    {
        if ($language->is_default) {
            throw new LanguageException(__('Cannot delete the default language.'));
        }

        DB::beginTransaction();
        try {
            $result = $this->languageRepository->delete($language->id);
            $this->languageRepository->clearCache();
            DB::commit();

            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
