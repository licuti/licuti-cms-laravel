<?php

namespace App\Http\Requests\Admin\Category;

use App\Core\Traits\AuthorizesWithPermission;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends StoreCategoryRequest
{
    protected function permission(): ?string
    {
        return 'categories.update';
    }

    // Dùng chung rules với StoreCategoryRequest
}