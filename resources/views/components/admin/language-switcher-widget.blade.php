@props([
    'languages',
    'currentLocale',
    'translations' => collect(),
    'routePrefix',
    'uuid' => null,
    'isEdit' => false,
])

@php
    $current = $languages->firstWhere('code', $currentLocale);
    $others = $languages->where('code', '!=', $currentLocale);
    $translated = $isEdit ? $translations->pluck('locale') : collect();
    $done = $languages->filter(fn ($language) => $translated->contains($language->code))->count();
@endphp

<x-admin.card :title="__('Ngôn ngữ')">
    @if($isEdit)
        <x-slot:action>
            <span class="small text-body-secondary">{{ $done }}/{{ $languages->count() }}</span>
        </x-slot:action>
    @endif

    <div class="d-flex flex-column gap-2">
        @if($current)
            <div class="d-flex align-items-center gap-2">
                @if($current->flag_url)
                    <img src="{{ $current->flag_url }}" alt="" class="rounded-circle border object-fit-cover flex-shrink-0" style="width:1.75rem;height:1.75rem;">
                @else
                    <span class="rounded-circle border d-inline-flex align-items-center justify-content-center small text-body-secondary flex-shrink-0" style="width:1.75rem;height:1.75rem;">{{ strtoupper($current->code) }}</span>
                @endif
                <span class="fw-semibold small">{{ $current->native_name ?: $current->name }}</span>
                <span class="badge bg-primary-subtle text-primary-emphasis ms-auto">{{ __('Đang sửa') }}</span>
            </div>
        @endif

        @if($isEdit && $others->isNotEmpty())
            <div class="list-group list-group-flush">
                @foreach($others as $lang)
                    @php $hasTrans = $translated->contains($lang->code); @endphp
                    <a href="{{ route($routePrefix . '.edit', ['uuid' => $uuid, 'lang' => $lang->code]) }}"
                       data-lang-switch
                       class="list-group-item list-group-item-action d-flex align-items-center gap-2 px-0">
                        @if($lang->flag_url)
                            <img src="{{ $lang->flag_url }}" alt="" class="rounded-circle border object-fit-cover flex-shrink-0" style="width:1.75rem;height:1.75rem;">
                        @else
                            <span class="rounded-circle border d-inline-flex align-items-center justify-content-center small text-body-secondary flex-shrink-0" style="width:1.75rem;height:1.75rem;">{{ strtoupper($lang->code) }}</span>
                        @endif
                        <span class="small fw-medium flex-grow-1">{{ $lang->native_name ?: $lang->name }}</span>
                        <span class="btn btn-sm {{ $hasTrans ? 'btn-outline-secondary' : 'btn-outline-primary' }} rounded-circle p-0 d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width:1.75rem;height:1.75rem;">
                            @if($hasTrans)
                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            @else
                                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            @endif
                        </span>
                    </a>
                @endforeach
            </div>
        @elseif(!$isEdit)
            <p class="small text-body-secondary mb-0">{{ __('Nội dung mới được lưu bằng ngôn ngữ mặc định. Sau khi lưu, bạn có thể thêm bản dịch cho ngôn ngữ khác.') }}</p>
        @endif
    </div>
</x-admin.card>
