<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'group',
        'type',
        'label',
        'description',
        'is_translatable'
    ];

    protected $casts = [
        'is_translatable' => 'boolean',
    ];

    public function scopeByGroup($query, string $group)
    {
        return $query->where('group', $group);
    }
}
