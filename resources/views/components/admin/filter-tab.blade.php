@props([
    'href'        => '#',
    'active'      => false,
    'count'       => null,
    'activeColor' => 'blue',
])

<li class="nav-item">
    <a href="{{ $href }}"
       {{ $attributes->merge(['class' => 'nav-link p-0 ' . ($active ? 'active' : 'text-body-secondary')]) }}>
        {{ $slot }}

        @if($count !== null && ($active || $count > 0))
            <span class="badge ms-1 {{ $active ? 'text-bg-primary' : 'text-bg-secondary' }}">
                {{ $count }}
            </span>
        @endif
    </a>
</li>