{{-- Recursive partial: render nested <option> cho tree select --}}
{{-- Props: nodes (Collection), depth (int), selectedValue (?int) --}}
@props(['nodes', 'depth' => 0, 'selectedValue' => null])

@foreach($nodes as $node)
    @php
        $nodeTitle = $node->title ?? ($node->translate(app()->getLocale())?->title ?? ('Trang #' . $node->id));
        $rawIndent = $depth > 0 ? str_repeat('&nbsp;&nbsp;&nbsp;', $depth) . '└ ' : '';
        $isSel = ((int) $selectedValue === (int) $node->id) ? 'selected' : '';
        $val = (int) $node->id;
    @endphp
    <option value="{{ $val }}" {{ $isSel }}>{!! $rawIndent !!}{{ $nodeTitle }}</option>
    @if(!empty($node->_children))
        @include('components.admin.partials.tree-select-options', [
            'nodes'         => $node->_children,
            'depth'         => $depth + 1,
            'selectedValue' => $selectedValue,
        ])
    @endif
@endforeach
