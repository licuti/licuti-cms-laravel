<footer class="site-footer" @if(!empty($themeSettings['footer_bg'])) style="background-color: var(--footer-bg);" @endif>
    <div class="site-shell site-footer__inner">
        <span>{{ setting('brand_label', 'Licuti Journal') }}</span>
        <span>{{ now()->year }} · Những câu chuyện đáng đọc</span>
    </div>
</footer>
