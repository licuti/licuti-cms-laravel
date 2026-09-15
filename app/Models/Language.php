<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'native_name',
        'flag',
        'is_default',
        'is_active',
        'display_order',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    /**
     * Trả về cột sẽ dùng để Route Model Binding (thay vì ID)
     */
    public function getRouteKeyName(): string
    {
        return 'code';
    }

    /**
     * Lấy các ngôn ngữ đang hoạt động.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Sắp xếp theo order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order', 'asc')->orderBy('name', 'asc');
    }

    /**
     * Accessor lấy URL cờ quốc gia (Hybrid Approach)
     */
    public function getFlagUrlAttribute(): string
    {
        if ($this->flag) {
            // Nếu đã lưu là URL hợp lệ thì dùng luôn
            if (str_starts_with($this->flag, 'http') || str_starts_with($this->flag, '/')) {
                return $this->flag;
            }
            // Nếu chứa URL do Media upload (ví dụ: storage/media...) nhưng chưa có /
            return url($this->flag);
        }

        // Mapping ngôn ngữ -> quốc gia cho CDN flag
        $countryCode = match (strtolower($this->code)) {
            'en' => 'gb',
            'vi' => 'vn',
            'ja' => 'jp',
            'ko' => 'kr',
            'zh' => 'cn',
            default => strtolower($this->code),
        };

        return "https://flagcdn.com/48x36/{$countryCode}.png";
    }
}
