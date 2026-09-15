@props(['activeLanguages', 'defaultLocale'])

<ul class="nav border-bottom" role="tablist">
    @foreach($activeLanguages as $lang)
        <li class="nav-item">
            <button
                type="button"
                role="tab"
                data-lang-tab="{{ $lang->code }}"
                aria-selected="{{ $lang->code === $defaultLocale ? 'true' : 'false' }}"
                class="lang-tab-btn nav-link d-inline-flex align-items-center gap-2 fw-medium small
                    {{ $lang->code === $defaultLocale ? 'active' : 'text-body-secondary' }}"
            >
                @if($lang->flag_url ?? null)
                    <img src="{{ $lang->flag_url }}" alt="" class="" style="width:1.25rem;height:auto;">
                @endif
                <span>{{ $lang->native_name ?: $lang->name }}</span>
                <span class="badge text-bg-secondary small">{{ strtoupper($lang->code) }}</span>
            </button>
        </li>
    @endforeach
</ul>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var tabButtons = document.querySelectorAll('.lang-tab-btn');
        var panels = document.querySelectorAll('.lang-panel');
        if (!tabButtons.length) return;

        function activateLang(code) {
            tabButtons.forEach(function (btn) {
                var isActive = btn.getAttribute('data-lang-tab') === code;
                btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
                btn.classList.toggle('active', isActive);
                btn.classList.toggle('text-body-secondary', !isActive);
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
