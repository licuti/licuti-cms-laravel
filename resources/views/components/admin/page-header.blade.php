@props([
    'title' => '',
    'subtitle' => null,
    'breadcrumbs' => []
])

<div {{ $attributes->merge(['class' => 'd-flex flex-wrap align-items-center justify-content-between gap-3 mb-3']) }}>
    <div>
        @if(!empty($breadcrumbs))
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 text-body-secondary small" style="--bs-breadcrumb-font-size: 0.75rem;">
                    @foreach($breadcrumbs as $bc)
                        @if($loop->last)
                            <li class="breadcrumb-item active fw-medium" aria-current="page">{{ $bc['label'] }}</li>
                        @else
                            <li class="breadcrumb-item"><a href="{{ $bc['url'] ?? '#' }}" class="text-decoration-none text-body-secondary hover-primary">{{ $bc['label'] }}</a></li>
                        @endif
                    @endforeach
                </ol>
            </nav>
        @endif
        <h2 class="h5 fw-bold mb-0">{{ $title }}</h2>
        @if($subtitle)
            <p class="text-body-secondary small mb-0">{{ $subtitle }}</p>
        @endif
    </div>

    @if(isset($actions) || $slot->isNotEmpty())
        <div class="d-flex align-items-center gap-2">
            {{ $actions ?? $slot }}
        </div>
    @endif
</div>