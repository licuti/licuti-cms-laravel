<?php

namespace App\Http\Requests\Admin\Post;

class UpdatePostRequest extends StorePostRequest
{
    protected function permission(): ?string
    {
        return 'posts.update';
    }

    /** Khi sửa, tiêu đề bắt buộc thuộc về ngôn ngữ đang soạn (?lang=), không phải mặc định. */
    protected function titleLocale(string $defaultLocale): string
    {
        return $this->contentLocale();
    }
}
