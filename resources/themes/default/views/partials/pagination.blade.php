<div class="pagination">
    @if($paginator->onFirstPage())
        <span class="pagination__disabled">← Trước</span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}">← Trước</a>
    @endif

    <span class="pagination__current">Trang {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>

    @if($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}">Sau →</a>
    @else
        <span class="pagination__disabled">Sau →</span>
    @endif
</div>
