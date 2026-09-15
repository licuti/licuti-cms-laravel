@props(['actions' => []])

<div {{ $attributes->merge(['class' => 'd-flex align-items-center gap-2 mt-1 small row-hover-actions']) }}>
    @forelse($actions as $index => $action)
        @php
            $colorClass = match($action['color'] ?? 'blue') {
                'red' => 'text-danger',
                'amber' => 'text-warning',
                'emerald', 'green' => 'text-success',
                default => 'text-primary',
            };
        @endphp

        @if(($action['method'] ?? 'GET') === 'GET')
            <a href="{{ $action['route'] }}" @class([$colorClass, 'fw-medium text-decoration-none', $action['class'] ?? ''])>
                {{ $action['label'] }}
            </a>
        @else
            @php
                $isConfirm = isset($action['confirm_text']);
                $formClass = $isConfirm ? trim(($action['class'] ?? '') . ' form-confirm') : ($action['class'] ?? '');
            @endphp
            <form action="{{ $action['route'] }}" method="POST" class="d-inline {{ $formClass }}"
                @if($isConfirm)
                    data-confirm-text="{{ $action['confirm_text'] }}"
                    @if(isset($action['confirm_title'])) data-confirm-title="{{ $action['confirm_title'] }}" @endif
                    @if(isset($action['confirm_btn'])) data-confirm-btn="{{ $action['confirm_btn'] }}" @endif
                    @if(isset($action['confirm_icon'])) data-confirm-icon="{{ $action['confirm_icon'] }}" @endif
                @endif
            >
                @csrf
                @if($action['method'] !== 'POST')
                    @method($action['method'])
                @endif
                <button type="submit" @class([$colorClass, 'fw-medium bg-transparent border-0 p-0 text-decoration-underline cursor-pointer'])>
                    {{ $action['label'] }}
                </button>
            </form>
        @endif
        
        @if($index < count($actions) - 1)
            <span class="text-body-tertiary">|</span>
        @endif
    @empty
        {{ $slot }}
    @endforelse
</div>
