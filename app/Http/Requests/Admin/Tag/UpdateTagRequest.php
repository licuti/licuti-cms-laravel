<?php

namespace App\Http\Requests\Admin\Tag;

class UpdateTagRequest extends StoreTagRequest
{
    protected function permission(): ?string
    {
        return 'tags.update';
    }

    // Dùng chung rules với StoreTagRequest
}
