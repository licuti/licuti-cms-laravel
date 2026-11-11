@props(['locales' => [], 'languages' => null, 'route' => null, 'routeParams' => []])

@php
    $languages = $languages ?? ($activeLanguages ?? collect());
    $locales = collect($locales);
@endphp

<div {{ $attributes->merge(['class' => 'd-flex align-items-center flex-wrap gap-1']) }}>
    @foreach($languages as $language)
        @php
            $hasTranslation = $locales->contains($language->code);
            $languageName = $language->native_name ?: $language->name;
            $label = $languageName . ($hasTranslation ? '' : ' — ' . __('chưa có bản dịch'));
            $url = $route ? route($route, $routeParams + ['lang' => $language->code]) : null;
        @endphp

        @if($url)
            <a href="{{ $url }}" data-bs-toggle="tooltip" title="{{ $label }}" aria-label="{{ $label }}" class="d-inline-flex flex-shrink-0 text-decoration-none">
        @else
            <span data-bs-toggle="tooltip" title="{{ $label }}" class="d-inline-flex flex-shrink-0">
        @endif

            @if($language->flag_url)
                <img src="{{ $language->flag_url }}" alt="{{ $languageName }}"
                     class="rounded-circle border object-fit-cover {{ $hasTranslation ? '' : 'opacity-50' }}"
                     style="width:1.25rem;height:1.25rem;">
            @else
                <span class="badge {{ $hasTranslation ? 'text-bg-primary' : 'text-bg-secondary opacity-75' }}">{{ strtoupper($language->code) }}</span>
            @endif

        @if($url)
            </a>
        @else
            </span>
        @endif
    @endforeach
</div>
