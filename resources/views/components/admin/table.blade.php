@props([
    'head' => null,
    'paginator' => null,
    'paginationId' => null,
    'tbodyId' => null
])

<div class="card border shadow-sm overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            @if($head)
                <thead class="table-light">
                    <tr>
                        {{ $head }}
                    </tr>
                </thead>
            @endif
            <tbody @if($tbodyId) id="{{ $tbodyId }}" @endif>
                {{ $slot }}
            </tbody>
        </table>
    </div>
    
    @if($paginator && $paginator->hasPages())
        <div @if($paginationId) id="{{ $paginationId }}" @endif class="card-footer bg-transparent border-top d-flex align-items-center justify-content-between py-3">
            {{ $paginator->withQueryString()->links() }}
        </div>
    @endif
</div>
