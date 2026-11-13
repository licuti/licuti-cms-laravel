<?php

return [
    /*
     * Disk dùng để dựng URL cho ảnh cũ lưu dạng đường dẫn trong cột `image`.
     * Ảnh mới nên lưu Media UUID (mỗi bản ghi Media có `disk` riêng).
     */
    'legacy_disk' => env('MEDIA_LEGACY_DISK', 'public'),
];
