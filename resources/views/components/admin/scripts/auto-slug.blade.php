@props(['isEdit' => false])

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const isEdit = {{ $isEdit ? 'true' : 'false' }};
        const nameInputs = document.querySelectorAll('.seo-source-name');
        
        nameInputs.forEach(nameInput => {
            // Tìm container chứa bộ input này. Ưu tiên closest lang-panel nếu có đa ngôn ngữ.
            const container = nameInput.closest('.lang-panel') || document;

            // Nếu phần tử cha là document, ta sẽ chỉ lấy phần tử đầu tiên match. 
            // Nếu có đa ngôn ngữ thì query Selector sẽ chạy trong context của .lang-panel đó.
            let langCode = null;
            const langCodeMatch = nameInput.name.match(/\[([a-zA-Z-]{2,5})\]/);
            if (langCodeMatch) {
                langCode = langCodeMatch[1];
            }

            let slugInput, metaTitleInput, excerptInput, metaDescInput;

            // Hỗ trợ cả trường hợp mảng đa ngôn ngữ và form không đa ngôn ngữ
            if (langCode) {
                slugInput = container.querySelector(`.seo-source-slug[name="translations[${langCode}][slug]"]`) || container.querySelector(`.seo-source-slug`);
                metaTitleInput = container.querySelector(`.seo-meta-title[name="translations[${langCode}][meta_title]"]`) || container.querySelector(`.seo-meta-title`);
                excerptInput = container.querySelector(`textarea[name="translations[${langCode}][excerpt]"].seo-source-excerpt`) || container.querySelector(`.seo-source-excerpt`);
                metaDescInput = container.querySelector(`.seo-meta-description[name="translations[${langCode}][meta_description]"]`) || container.querySelector(`.seo-meta-description`);
            } else {
                slugInput = container.querySelector('.seo-source-slug');
                metaTitleInput = container.querySelector('.seo-meta-title');
                excerptInput = container.querySelector('.seo-source-excerpt');
                metaDescInput = container.querySelector('.seo-meta-description');
            }
            
            let isProgrammatic = false;

            // Xử lý tự động sinh Slug từ Tiêu đề
            if (slugInput) {
                let isSlugManuallyEdited = isEdit || slugInput.value.trim() !== '';

                slugInput.addEventListener('input', () => {
                    if (!isProgrammatic) {
                        isSlugManuallyEdited = true;
                    }
                    if (slugInput.value.trim() === '') {
                        isSlugManuallyEdited = false;
                    }
                });

                nameInput.addEventListener('input', function() {
                    if (!isEdit && !isSlugManuallyEdited) {
                        let slug = this.value.toLowerCase().trim();
                        slug = slug.normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                                   .replace(/\u0111/g, 'd').replace(/\u0110/g, 'd')
                                   .replace(/[^a-z0-9-]/g, '-')
                                   .replace(/-+/g, '-')
                                   .replace(/^-|-$/g, '');
                        isProgrammatic = true;
                        slugInput.value = slug;
                        slugInput.dispatchEvent(new Event('input', { bubbles: true }));
                        isProgrammatic = false;
                    }
                });
            }

            // Xử lý tự động điền Meta Title từ Tiêu đề bài viết
            if (metaTitleInput) {
                let isMetaTitleManuallyEdited = isEdit || metaTitleInput.value.trim() !== '';

                metaTitleInput.addEventListener('input', () => {
                    if (!isProgrammatic) {
                        isMetaTitleManuallyEdited = true;
                    }
                    if (metaTitleInput.value.trim() === '') {
                        isMetaTitleManuallyEdited = false;
                    }
                });

                nameInput.addEventListener('input', function() {
                    if (!isEdit && !isMetaTitleManuallyEdited) {
                        isProgrammatic = true;
                        metaTitleInput.value = this.value;
                        metaTitleInput.dispatchEvent(new Event('input', { bubbles: true }));
                        isProgrammatic = false;
                    }
                });
            }

            // Xử lý tự động điền Meta Description từ Tóm tắt (Excerpt)
            if (excerptInput && metaDescInput) {
                let isMetaDescManuallyEdited = isEdit || metaDescInput.value.trim() !== '';

                metaDescInput.addEventListener('input', () => {
                    if (!isProgrammatic) {
                        isMetaDescManuallyEdited = true;
                    }
                    if (metaDescInput.value.trim() === '') {
                        isMetaDescManuallyEdited = false;
                    }
                });

                excerptInput.addEventListener('input', function() {
                    if (!isEdit && !isMetaDescManuallyEdited) {
                        isProgrammatic = true;
                        metaDescInput.value = this.value;
                        metaDescInput.dispatchEvent(new Event('input', { bubbles: true }));
                        isProgrammatic = false;
                    }
                });
            }
        });
    });
</script>
@endpush
