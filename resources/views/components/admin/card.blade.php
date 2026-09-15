@props(['title' => null])

<div {{ $attributes->merge(['class' => 'card border shadow-sm']) }}>
    @if(isset($title) || isset($action))
        <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between py-3">
            @if(isset($title))
                <h5 class="card-title mb-0 fw-bold fs-6">{{ $title }}</h5>
            @endif
            @if(isset($action))
                <div>{{ $action }}</div>
            @endif
        </div>
    @endif

    <div class="card-body">
        {{ $slot }}
    </div>
</div>
