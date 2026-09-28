# Module: Tag

> ✅ **Hoàn thành (dùng chung cho Post + Product)** | Profile: **Simple** | Tầng 2 — CMS
> Route: `admin.tags.*` | Views: `resources/views/admin/tags/`
> Spec: [`07` §3.1](../07-development-process.md) (xem Tags trong module Posts)

## Hiện trạng (cập nhật 09/2026)

| Hạng mục | Trạng thái |
|---|---|
| Migration `tags` (uuid, name, slug, display_order) + `post_tag` (post_id, tag_id, unique) | ✅ Migration enhance `2026_09_17_080000` sửa schema stub cũ |
| Bảng pivot `product_tag` | ✅ Thêm trong Product Form Revamp P2 |
| Model `Tag` (`$fillable`, `HasUuid`, `posts()`, `products()`) | ✅ |
| FormRequest / DTO / Repository / Service / Controller / Views | ✅ |
| Test | ✅ 5 case PASS + test tích hợp trong `ProductCrudTest` |

## Tích hợp với Product (Product Form Revamp P2)

- Bảng pivot mới **`product_tag`** (`product_id` FK cascade, `tag_id` FK cascade, `unique(product_id, tag_id)`) — migration `2026_09_28_010000_enhance_products_for_shipping_taxonomy`.
- `Tag::products(): BelongsToMany` (pivot `product_tag`) song song với `Tag::posts()` (pivot `post_tag`).
- Form Product có card "Thẻ tag" ở sidebar: `<select multiple>` + **TomSelect** (`create: true`) — gõ để tìm tag sẵn có hoặc tạo tag mới ngay tại form.
- `ProductDTO::parseTagIds()` phân loại id số (tag sẵn có) vs text (tag mới); `ProductService::syncTags()` tạo tag mới (`Str::slug`, trùng slug thì dùng tag hiện có) rồi `sync()` pivot — chạy trong transaction của `create()`/`update()`.
- `ProductRepository::findByUuidWithRelations()` eager-load `tags`.

## Việc cần làm

- [ ] Frontend storefront dùng tag làm bộ lọc tìm kiếm sản phẩm.
- [ ] Kiểm tra ngược flow `PostService::syncTags()` (pivot `post_tag`) sau khi thêm `product_tag`.

Tham khảo: [`07` §3.1`](../07-development-process.md), [product.md](product.md), [post.md](post.md).