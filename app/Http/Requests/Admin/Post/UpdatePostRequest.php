<?php

namespace App\Http\Requests\Admin\Post;

class UpdatePostRequest extends StorePostRequest
{
    protected function permission(): ?string
    {
        return 'posts.update';
    }

    // Dùng chung rules với StorePostRequest
}
