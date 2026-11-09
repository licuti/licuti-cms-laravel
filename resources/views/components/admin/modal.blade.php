@props([
    'id',
    'title' => '',
    'maxWidth' => 'lg'
])

@php
    $sizeMap = [
        'sm'         => 'modal-sm',
        'modal-sm'   => 'modal-sm',
        'md'         => '',
        'modal-md'   => '',
        'lg'         => 'modal-lg',
        'modal-lg'   => 'modal-lg',
        'xl'         => 'modal-xl',
        'modal-xl'   => 'modal-xl',
        'fullscreen' => 'modal-fullscreen',
    ];
    $sizeClass = $sizeMap[$maxWidth] ?? (str_starts_with((string)$maxWidth, 'modal-') ? $maxWidth : 'modal-lg');
@endphp

<div id="{{ $id }}" class="modal fade" tabindex="-1" aria-labelledby="modal-title-{{ $id }}" aria-hidden="true">
    <div class="modal-dialog {{ $sizeClass }} modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modal-title-{{ $id }}">{{ $title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body custom-scrollbar">
                {{ $slot }}
            </div>

            @if(isset($footer))
                <div class="modal-footer bg-body-tertiary border-top">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>

@pushOnce('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Bridge functions for backward compatibility
        window.openModal = function(id) {
            var el = document.getElementById(id);
            if (el) {
                var modal = bootstrap.Modal.getOrCreateInstance(el);
                modal.show();
            }
        };

        window.closeModal = function(id) {
            var el = document.getElementById(id);
            if (el) {
                var modal = bootstrap.Modal.getInstance(el);
                if (modal) modal.hide();
            }
        };
    });
</script>
@endPushOnce
