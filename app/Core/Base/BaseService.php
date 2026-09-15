<?php

namespace App\Core\Base;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

abstract class BaseService
{
    /**
     * Thực thi một callable bên trong DB Transaction.
     * Nếu thành công, trả về kết quả; nếu lỗi, log và ném lại exception.
     */
    protected function handleTransaction(callable $callback): mixed
    {
        return DB::transaction($callback);
    }

    /**
     * Log lỗi theo chuẩn dự án và ném lại exception.
     */
    protected function handleException(Throwable $e, string $context = ''): never
    {
        Log::error($context ?: static::class . ' error', [
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'trace'   => $e->getTraceAsString(),
        ]);

        throw $e;
    }

    /**
     * Generate a unique slug for translation tables.
     */
    protected function generateUniqueSlug(
        string $translationTable,
        string $locale,
        ?string $slug,
        string $title,
        ?int $ignoreForeignId = null,
        string $foreignKey = 'post_id'
    ): string {
        $baseSlug = \Illuminate\Support\Str::slug($slug ?: $title);
        $uniqueSlug = $baseSlug;
        $counter = 1;

        while (true) {
            $query = DB::table($translationTable)
                ->where('locale', $locale)
                ->where('slug', $uniqueSlug);

            if ($ignoreForeignId) {
                $query->where($foreignKey, '!=', $ignoreForeignId);
            }

            if (!$query->exists()) {
                break;
            }

            $uniqueSlug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $uniqueSlug;
    }
}
