<?php

use App\Contracts\Theme\ThemeSettingsProvider;
use App\Services\Theme\ThemeSettingsRegistry;

return new class implements ThemeSettingsProvider
{
    public function register(ThemeSettingsRegistry $registry): void
    {
        // ─── Global: nội dung/nhận diện dùng chung cho mọi theme ─────────────
        $registry
            ->globalSection('branding', 'Nhận diện thương hiệu', 'Các thông tin nhận diện dùng chung cho storefront.')
            ->text('brand_label', 'Nhãn thương hiệu', 'Licuti Journal', 'Hiển thị trong header và footer.');

        $registry
            ->globalSection('homepage', 'Nội dung trang bài viết', 'Nội dung và phân trang dùng chung, không thay đổi khi đổi theme.')
            ->text('hero_title', 'Tiêu đề hero', 'Những ý tưởng đáng để lưu lại.')
            ->textarea('hero_description', 'Mô tả hero', 'Góc nhìn, câu chuyện và những điều hữu ích được tuyển chọn cho hành trình kinh doanh số.')
            ->number('posts_per_page', 'Số bài viết mỗi trang', 9, 3, 24);

        // ─── Common theme: theme nào cũng có thể hỗ trợ ─────────────────────
        // ─── 1. Màu sắc & Phong cách (Common Theme) ─────────────────────────
        $registry
            ->commonThemeSection('colors', 'Màu sắc', 'Tùy chỉnh bảng màu tổng thể của giao diện.')
            ->color('accent_color', 'Màu nhấn', '#d9f05b', 'Màu nút bấm và các chi tiết nổi bật.')
            ->color('header_bg', 'Màu nền Header', '#ffffff', 'Màu nền của thanh điều hướng trên cùng.')
            ->color('footer_bg', 'Màu nền Footer', '#111827', 'Màu nền của khu vực chân trang.');

        // ─── 2. Đầu trang (Header) ──────────────────────────────────────────
        $registry
            ->themeSpecificSection('header', 'Đầu trang (Header)', 'Cấu hình thanh điều hướng và thông báo đầu trang.')
            ->boolean('header_sticky', 'Cố định thanh điều hướng', true, 'Giữ header trên cùng màn hình khi cuộn trang.')
            ->boolean('show_announcement', 'Bật thanh thông báo đỉnh trang', false, 'Hiển thị dải băng thông báo nhỏ ở đầu trang.')
            ->text('announcement_text', 'Nội dung thông báo', 'Chào mừng bạn đến với Licuti CMS!', 'Nội dung hiển thị trên thanh thông báo.');

        // ─── 3. Danh sách bài viết (Blog Archive) ───────────────────────────
        $registry
            ->commonThemeSection('blog_archive', 'Danh sách bài viết', 'Bố cục trang danh sách tin tức và bài viết.')
            ->select('blog_layout', 'Bố cục hiển thị', [
                'grid' => 'Dạng lưới (Grid)',
                'list' => 'Dạng danh sách (List card)',
            ], 'grid', 'Kiểu sắp xếp thẻ bài viết trên trang danh sách.')
            ->number('grid_columns', 'Số cột hiển thị (Desktop)', 3, 2, 4, 'Số cột bài viết hiển thị trên màn hình máy tính.')
            ->boolean('show_reading_time', 'Hiển thị thời gian đọc ước tính', true, 'Tự động tính và hiển thị thời gian đọc trên thẻ bài viết.');

        // ─── 4. Chi tiết bài viết (Single Post) ─────────────────────────────
        $registry
            ->themeSpecificSection('single_post', 'Chi tiết bài viết', 'Cấu hình trải nghiệm đọc trang chi tiết bài viết.')
            ->select('sidebar_layout', 'Vị trí Sidebar', [
                'right' => 'Bên phải',
                'none' => 'Không dùng Sidebar (Tràn viền)',
            ], 'right', 'Vị trí hiển thị thanh bên chứa thông tin bổ sung.')
            ->boolean('show_author_box', 'Hiển thị hộp tác giả', true, 'Hiển thị avatar và tên tác giả trong thanh bên.')
            ->boolean('show_social_share', 'Hiển thị nút chia sẻ', true, 'Hiển thị các nút chia sẻ mạng xã hội.')
            ->number('related_posts_count', 'Số bài viết liên quan', 3, 0, 6, 'Số bài viết cùng chuyên mục hiển thị ở cuối trang.');

        // ─── 5. Nâng cao (Advanced) ─────────────────────────────────────────
        $registry
            ->themeSpecificSection('advanced', 'Nâng cao', 'Tùy biến mã CSS riêng cho theme.')
            ->textarea('custom_css', 'Custom CSS', '', 'Viết các đoạn CSS ghi đè nhanh cho giao diện.');
    }
};
