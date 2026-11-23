<?php

namespace Database\Seeders;

use App\Core\Enums\ContentStatus;
use App\Models\ContentBlock;
use Illuminate\Database\Seeder;

class ContentBlockSeeder extends Seeder
{
    public function run(): void
    {
        $blocks = [
            [
                'uuid' => '10000000-0000-4000-8000-000000000001',
                'status' => ContentStatus::PUBLISHED,
                'published_at' => now()->subDays(3),
                'translations' => [
                    'vi' => [
                        'title' => 'Hero giới thiệu Licuti',
                        'slug' => 'hero-gioi-thieu-licuti',
                        'excerpt' => 'Khối nội dung mẫu dùng cho hero trên trang chủ.',
                        'content' => '<section class="content-block content-block--hero"><p class="eyebrow">LICUTI CMS</p><h2>Xây storefront theo cách của riêng bạn.</h2><p>Nội dung này được quản lý từ module Khối nội dung và có thể tái sử dụng trong theme.</p><a href="/posts" class="btn btn-primary">Khám phá bài viết</a></section>',
                    ],
                    'en' => [
                        'title' => 'Licuti introduction hero',
                        'slug' => 'licuti-introduction-hero',
                        'excerpt' => 'A sample content block for the homepage hero.',
                        'content' => '<section class="content-block content-block--hero"><p class="eyebrow">LICUTI CMS</p><h2>Build your storefront your way.</h2><p>This content is managed from the Content Blocks module and can be reused by a theme.</p><a href="/posts" class="btn btn-primary">Explore the journal</a></section>',
                    ],
                ],
            ],
            [
                'uuid' => '10000000-0000-4000-8000-000000000002',
                'status' => ContentStatus::PUBLISHED,
                'published_at' => now()->subDay(),
                'translations' => [
                    'vi' => [
                        'title' => 'Ba giá trị của Licuti',
                        'slug' => 'ba-gia-tri-cua-licuti',
                        'excerpt' => 'Khối mẫu dạng ba cột để giới thiệu giá trị thương hiệu.',
                        'content' => '<section class="content-block content-block--values"><div class="row g-4"><div class="col-md-4"><h3>01. Linh hoạt</h3><p>Theme tự do trình bày dữ liệu chuẩn từ hệ thống.</p></div><div class="col-md-4"><h3>02. Rõ ràng</h3><p>Nội dung được quản lý tập trung, không rải trong template.</p></div><div class="col-md-4"><h3>03. Mở rộng</h3><p>Khối có thể được tái sử dụng khi xây Homepage Builder.</p></div></div></section>',
                    ],
                    'en' => [
                        'title' => 'Three Licuti values',
                        'slug' => 'three-licuti-values',
                        'excerpt' => 'A sample three-column block for brand values.',
                        'content' => '<section class="content-block content-block--values"><div class="row g-4"><div class="col-md-4"><h3>01. Flexible</h3><p>The theme is free to present standardized system data.</p></div><div class="col-md-4"><h3>02. Clear</h3><p>Content is managed centrally instead of being scattered in templates.</p></div><div class="col-md-4"><h3>03. Extensible</h3><p>Blocks can be reused when the Homepage Builder arrives.</p></div></div></section>',
                    ],
                ],
            ],
            [
                'uuid' => '10000000-0000-4000-8000-000000000003',
                'status' => ContentStatus::DRAFT,
                'published_at' => null,
                'translations' => [
                    'vi' => [
                        'title' => 'CTA đăng ký nhận tin',
                        'slug' => 'cta-dang-ky-nhan-tin',
                        'excerpt' => 'Khối nháp để thử nghiệm lời kêu gọi hành động.',
                        'content' => '<section class="content-block content-block--cta"><h2>Nhận những cập nhật mới nhất.</h2><p>Đăng ký để không bỏ lỡ các bài viết và tính năng mới của Licuti.</p><a href="/contact" class="btn btn-dark">Đăng ký nhận tin</a></section>',
                    ],
                    'en' => [
                        'title' => 'Newsletter signup CTA',
                        'slug' => 'newsletter-signup-cta',
                        'excerpt' => 'A draft block for testing a call to action.',
                        'content' => '<section class="content-block content-block--cta"><h2>Get the latest updates.</h2><p>Subscribe to follow new Licuti articles and features.</p><a href="/contact" class="btn btn-dark">Subscribe</a></section>',
                    ],
                ],
            ],
        ];

        foreach ($blocks as $data) {
            $translations = $data['translations'];
            unset($data['translations']);

            $block = ContentBlock::withTrashed()->firstOrNew(['uuid' => $data['uuid']]);
            $block->fill([
                'status' => $data['status'],
                'published_at' => $data['published_at'],
                'image' => null,
            ]);
            $block->deleted_at = null;
            $block->save();

            foreach ($translations as $locale => $translation) {
                $block->translations()->updateOrCreate(
                    ['locale' => $locale],
                    $translation,
                );
            }
        }
    }
}
