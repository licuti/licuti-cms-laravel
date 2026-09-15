<?php

namespace App\Repositories;

use App\Models\Language;
use App\Repositories\Interfaces\LanguageRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class LanguageRepository extends BaseRepository implements LanguageRepositoryInterface
{
    const CACHE_KEY_ACTIVE = 'languages_active';

    public function __construct(Language $model)
    {
        parent::__construct($model);
    }

    public function getActiveLanguages()
    {
        return Cache::rememberForever(self::CACHE_KEY_ACTIVE, function () {
            return $this->model->active()->ordered()->get();
        });
    }

    public function clearCache()
    {
        Cache::forget(self::CACHE_KEY_ACTIVE);
    }

    public function getPaginatedLanguages(int $perPage = 20)
    {
        return $this->model->ordered()->paginate($perPage);
    }
}
