@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        function initTinyMCE() {
            var isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';

            tinymce.init({
                selector: '.tinymce-editor',
                height: 480,
                menubar: false,
                branding: false,
                promotion: false,
                toolbar_mode: 'wrap',
                skin: isDark ? 'oxide-dark' : 'oxide',
                content_css: isDark ? 'dark' : 'default',
                plugins: [
                    'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                    'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                    'insertdatetime', 'media', 'table', 'help', 'wordcount'
                ],
                toolbar: [
                    'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | forecolor backcolor',
                    'alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media table | removeformat code fullscreen'
                ],
                content_style: 'body { font-family: "Outfit", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; font-size: 15px; line-height: 1.6; }',
                setup: function(editor) {
                    editor.on('change', function() {
                        editor.save();
                    });
                }
            });
        }

        initTinyMCE();

        // Tự động đổi theme TinyMCE tức thì khi bấm nút Dark/Light mode không cần F5
        var currentTheme = document.documentElement.getAttribute('data-bs-theme');
        var themeObserver = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.attributeName === 'data-bs-theme') {
                    var newTheme = document.documentElement.getAttribute('data-bs-theme');
                    if (newTheme !== currentTheme) {
                        currentTheme = newTheme;
                        if (typeof tinymce !== 'undefined') {
                            tinymce.triggerSave();
                            tinymce.remove('.tinymce-editor');
                            initTinyMCE();
                        }
                    }
                }
            });
        });
        themeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['data-bs-theme'] });

        // Đảm bảo dữ liệu TinyMCE được đồng bộ về textarea khi submit form
        var postForm = document.getElementById('post-form');
        if (postForm) {
            postForm.addEventListener('submit', function() {
                if (typeof tinymce !== 'undefined') {
                    tinymce.triggerSave();
                }
            });
        }
    });
</script>
@endpush
