<?php

namespace App\Repositories\Eloquent;

use App\Repositories\BaseRepository;

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

    /**
     * Lấy các ngôn ngữ đang hoạt động (có cache).
     *
     * Lưu ý: KHÔNG cache kết quả rỗng. Nếu bảng `languages` chưa có dòng
     * nào (vd: fresh install chưa seed, hoặc app boot trước khi dữ liệu được
     * tạo trong test), việc cache một collection rỗng vĩnh viễn sẽ "khóa"
     * cache — các ngôn ngữ thêm sau đó sẽ không hiển thị cho đến khi cache bị
     * xóa. clearCache() vẫn đảm bảo làm mới khi có thay đổi.
     */
    public function getActiveLanguages()
    {
        if (Cache::has(self::CACHE_KEY_ACTIVE)) {
            return Cache::get(self::CACHE_KEY_ACTIVE);
        }

        $languages = $this->model->active()->ordered()->get();

        if ($languages->isNotEmpty()) {
            Cache::forever(self::CACHE_KEY_ACTIVE, $languages);
        }

        return $languages;
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
