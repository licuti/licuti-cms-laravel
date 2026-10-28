<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlashSaleTranslation extends Model
{
    use HasFactory;

    protected $table = 'flash_sale_translations';

    protected $fillable = [
        'flash_sale_id',
        'locale',
        'name',
        'description',
    ];

    public function flashSale(): BelongsTo
    {
        return $this->belongsTo(FlashSale::class);
    }
}
