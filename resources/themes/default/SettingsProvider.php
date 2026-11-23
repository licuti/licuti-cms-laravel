<?php

use App\Contracts\Theme\ThemeSettingsProvider;
use App\Services\Theme\ThemeSettingsRegistry;

return new class implements ThemeSettingsProvider
{
    public function register(ThemeSettingsRegistry $registry): void
    {
        $registry
            ->section('branding', 'Nhận diện thương hiệu', 'Các thông tin nhận diện hiển thị trên storefront.')
            ->text('brand_label', 'Nhãn thương hiệu', 'Licuti Journal', 'Hiển thị trong header và footer.');

        $registry
            ->section('colors', 'Màu sắc', 'Màu nhấn của theme editorial.')
            ->color('accent_color', 'Màu nhấn', '#d9f05b');

        $registry
            ->section('homepage', 'Trang bài viết', 'Cấu hình phần giới thiệu của trang bài viết.')
            ->text('hero_title', 'Tiêu đề hero', 'Những ý tưởng đáng để lưu lại.')
            ->textarea('hero_description', 'Mô tả hero', 'Góc nhìn, câu chuyện và những điều hữu ích được tuyển chọn cho hành trình kinh doanh số.')
            ->number('posts_per_page', 'Số bài viết mỗi trang', 9, 3, 24);
    }
};
