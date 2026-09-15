@foreach($nodes as $node)
    @php
        $val = $node->{$valueField};
        $label = $node->{$labelField} ?? $val;
        $isChecked = in_array($val, $selectedArray);
        $nodeId = $inputId . '-node-' . $val;
        $hasChildren = isset($node->_children) && $node->_children->isNotEmpty();
    @endphp
    <li class="my-1">
        <div class="form-check d-flex align-items-center mb-0">
            <input 
                class="form-check-input mt-0 me-2" 
                type="{{ $type }}" 
                name="{{ $inputName }}" 
                id="{{ $nodeId }}" 
                value="{{ $val }}" 
                {{ $isChecked ? 'checked' : '' }}
                {{ $inputAttributes ?? '' }}
            >
            <label class="form-check-label small user-select-none" for="{{ $nodeId }}">
                {{ $label }}
            </label>
        </div>
        
        @if($hasChildren)
            <ul class="list-unstyled mb-0 ps-3 border-start ms-2 mt-1 border-secondary-subtle">
                @include('components.admin.partials.tree-node', [
                    'nodes'           => $node->_children, 
                    'inputName'       => $inputName, 
                    'inputId'         => $inputId,
                    'inputAttributes' => $inputAttributes ?? null,
                    'type'            => $type,
                    'selectedArray'   => $selectedArray,
                    'valueField'      => $valueField,
                    'labelField'      => $labelField,
                    'depth'           => $depth + 1
                ])
            </ul>
        @endif
    </li>
@endforeach
