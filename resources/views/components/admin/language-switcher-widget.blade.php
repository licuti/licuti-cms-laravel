@props([
    'languages',
    'currentLocale',
    'translations' => collect(),
    'routePrefix',
    'uuid' => null,
    'isEdit' => false,
])

<x-admin.card title="{{ __('Ngôn ngữ') }}" class="shadow-sm">
    <div class="card-body gap-y-4 d-flex flex-column">
        @php
            $currentLangObj = $languages->firstWhere('code', $currentLocale);
        @endphp
        @if($currentLangObj)
            <div class="d-flex align-items-center gap-3 pb-3 border-bottom">
                <div class="d-flex align-items-center justify-content-center rounded-circle text-primary" style="width:2rem;height:2rem;background:rgba(37,99,235,0.1);">
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/></svg>
                </div>
                <div>
                    <span class="d-block small text-body-secondary">{{ __('Hiện tại:') }}</span>
                    <div class="fw-semibold d-flex align-items-center gap-2 small">
                        @if($currentLangObj->flag_url)
                            <img src="{{ $currentLangObj->flag_url }}" alt="" class="rounded shadow-sm" style="width:1rem;height:auto;">
                        @endif
                        {{ $currentLangObj->native_name ?: $currentLangObj->name }}
                    </div>
                </div>
            </div>
        @endif

        <div class="gap-y-3 d-flex flex-column">
            <span class="small fw-bold text-uppercase text-body-tertiary" style="font-size:0.625rem;letter-spacing:0.1em;">{{ __('Bản dịch khác') }}</span>
            <div class="gap-y-1 d-flex flex-column">
                @foreach($languages->where('code', '!=', $currentLocale) as $lang)
                    @php
                        $hasTrans = $isEdit && $translations->contains('locale', $lang->code);
                        $routeParams = $isEdit ? ['uuid' => $uuid, 'lang' => $lang->code] : ['lang' => $lang->code];
                        $route = $isEdit ? route($routePrefix . '.edit', $routeParams) : route($routePrefix . '.create', $routeParams);
                    @endphp
                    <div class="d-flex align-items-center justify-content-between p-2 rounded transition-all" style="cursor:pointer;" onmouseover="this.classList.add('bg-body-tertiary')" onmouseout="this.classList.remove('bg-body-tertiary')">
                        <div class="d-flex align-items-center gap-2 small fw-medium">
                            @if($lang->flag_url)
                                <img src="{{ $lang->flag_url }}" alt="" class="rounded shadow-sm" style="width:1rem;height:auto;">
                            @endif
                            {{ $lang->native_name ?: $lang->name }}
                        </div>
                        
                        <a href="{{ $route }}" class="d-flex align-items-center justify-content-center rounded border shadow-sm bg-body text-decoration-none transition-all" style="width:1.75rem;height:1.75rem;" onmouseover="this.classList.add('border-primary')" onmouseout="this.classList.remove('border-primary')">
                            @if($hasTrans)
                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="text-primary" title="{{ __('Sửa bản dịch') }}"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            @else
                                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="text-body-secondary" title="{{ __('Thêm bản dịch') }}"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            @endif
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-admin.card>
