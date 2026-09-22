<?php

namespace App\Http\Requests\Admin\Page;

class UpdatePageRequest extends StorePageRequest
{
    protected function permission(): ?string
    {
        return 'pages.update';
    }

    // Dùng chung rules với StorePageRequest
}