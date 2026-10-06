<?php

namespace App\Core\Traits;

use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use Illuminate\Validation\Rule;

/**
 * Rule chung chặn value trùng văn bản trong cùng (attribute, locale).
 *
 * Một attribute không nên có 2 giá trị cùng tên ở cùng ngôn ngữ (VD: 2 lần "Đỏ").
 * Check ở app-level vì DB chỉ unique khi có compound index (attribute_value_id, locale),
 * mà value id chưa biết trước khi insert.
 */
trait ValidatesAttributeValueUniqueness
{
    /**
     * Trả mảng rules unique cho từng locale có trong translations.
     *
     * @param  int  $attributeId  Attribute đang thêm/sửa value.
     * @param  int|null  $ignoreValueId  Bỏ qua chính value này khi update.
     */
    protected function valueUniquenessRules(int $attributeId, ?int $ignoreValueId = null): array
    {
        $rules = [];
        $translations = $this->input('translations', []);

        if (! is_array($translations)) {
            return $rules;
        }

        foreach (array_keys($translations) as $locale) {
            $value = $translations[$locale]['value'] ?? null;

            // Value rỗng đã bị chặn bởi rule nullable/required ở locale default.
            if (! is_string($value) || trim($value) === '') {
                continue;
            }

            $rules["translations.{$locale}.value"][] = Rule::unique('product_attribute_value_translations', 'value')
                ->where(fn ($q) => $q->where('locale', $locale)
                    ->whereIn('attribute_value_id', ProductAttributeValue::select('id')->where('attribute_id', $attributeId)))
                ->when($ignoreValueId, fn ($r) => $r->ignore($ignoreValueId, 'attribute_value_id'));
        }

        return $rules;
    }

    /**
     * Lấy attribute id từ route parameter `attribute_uuid`.
     */
    protected function resolveAttributeIdFromRoute(): ?int
    {
        $uuid = $this->route('attribute_uuid');

        if (! is_string($uuid)) {
            return null;
        }

        return ProductAttribute::where('uuid', $uuid)->value('id');
    }

    /**
     * Lấy value id đang sửa từ route parameter `uuid`.
     */
    protected function resolveValueIdFromRoute(): ?int
    {
        $uuid = $this->route('uuid');

        if (! is_string($uuid)) {
            return null;
        }

        return ProductAttributeValue::where('uuid', $uuid)->value('id');
    }
}
