<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Product module configuration
    |--------------------------------------------------------------------------
    */

    // Số tổ hợp biến thể tối đa được sinh từ cartesian product
    // các attribute is_variation. Giới hạn chống nổ UI/DB.
    'max_combos' => env('PRODUCT_MAX_COMBOS', 100),

    // Prefix SKU tự sinh khi product không có SKU (PRD-XXXXXXXX)
    'sku_prefix' => env('PRODUCT_SKU_PREFIX', 'PRD-'),

];
