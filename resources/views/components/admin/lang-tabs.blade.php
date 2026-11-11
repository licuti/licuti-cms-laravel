@props(['activeLanguages' => [], 'defaultLocale' => null])

@php
    $defaultLocale = $defaultLocale ?? app()->getLocale();
@endphp

<ul class="lang-tabs" role="tablist" aria-label="{{ __('Ngôn ngữ nội dung') }}">
    @foreach($activeLanguages as $lang)
        @php $isActive = $lang->code === $defaultLocale; @endphp
        <li class="lang-tabs-item" role="presentation">
            <button
                type="button"
                role="tab"
                data-lang-tab="{{ $lang->code }}"
                aria-selected="{{ $isActive ? 'true' : 'false' }}"
                class="lang-tabs-btn {{ $isActive ? 'is-active' : '' }}"
            >
                <span class="lang-tabs-flag" aria-hidden="true">
                    <img
                        src="{{ $lang->flag_url }}"
                        alt=""
                        width="22"
                        height="22"
                        loading="lazy"
                        onerror="this.parentElement.classList.add('is-fallback'); this.remove();"
                    >
                    <span class="lang-tabs-flag-text">{{ strtoupper($lang->code) }}</span>
                </span>
                <span class="lang-tabs-name">{{ $lang->native_name ?: $lang->name }}</span>
            </button>
        </li>
    @endforeach
</ul>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var tabButtons = document.querySelectorAll('.lang-tabs-btn');
        var panels = document.querySelectorAll('.lang-panel');
        if (!tabButtons.length) return;

        function activateLang(code) {
            tabButtons.forEach(function (btn) {
                var isActive = btn.getAttribute('data-lang-tab') === code;
                btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
                btn.classList.toggle('is-active', isActive);
            });
            panels.forEach(function (panel) {
                panel.classList.toggle('d-none', panel.getAttribute('data-lang-panel') !== code);
            });
        }

        tabButtons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                activateLang(btn.getAttribute('data-lang-tab'));
            });
        });
    });
</script>
@endpush
