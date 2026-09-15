@props([
    'items'   => [],
    'current' => null,
    'route'   => null,
])

<ul class="nav gap-3 mb-2">
    @if(!empty($items) && $route)
        @foreach($items as $item)
            <x-admin.filter-tab
                :href="route($route, array_merge(request()->except(['tab', 'page']), ['tab' => $item['key']]))"
                :active="$current === $item['key']"
                :count="$item['count'] ?? null"
                :active-color="$item['color'] ?? 'blue'"
            >
                {{ $item['label'] }}
            </x-admin.filter-tab>
        @endforeach
    @else
        {{ $slot }}
    @endif
</ul>