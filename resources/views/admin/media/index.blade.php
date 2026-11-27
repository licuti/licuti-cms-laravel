@extends('layouts.admin')
@section('title', 'Thư viện Media')

@section('content')
<div class="admin-media-page d-flex flex-column gap-4 pb-4 position-relative">
    <!-- Header & Actions -->
    <x-admin.page-header 
        title="Thư viện Media & Tệp tin"
        subtitle="Quản lý hình ảnh, tài liệu và tự động tạo thumbnail"
        :breadcrumbs="[
            ['label' => 'Bảng điều khiển', 'url' => '/admin/dashboard'], 
            ['label' => 'Thư viện Media']
        ]"
    >
        <x-admin.button id="btn-open-upload" variant="primary" class="flex-shrink-0">
            <svg width="16" height="16" class="me-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            <span>Tải tệp tin lên</span>
        </x-admin.button>
    </x-admin.page-header>

    <!-- Drag & Drop Upload Zone (Mặc định ẩn) -->
    <div id="upload-zone-wrapper" class="d-none">
        <x-admin.card class="media-upload-card p-4 border-primary-subtle bg-primary-subtle text-center position-relative">
            <input type="file" id="file-input" class="d-none" multiple>
            <div id="drop-zone" class="media-dropzone d-flex flex-column align-items-center gap-3 rounded p-4 border border-transparent">
                <div class="media-upload-icon rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center">
                    <svg width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                </div>
                <div>
                    <p class="fs-6 fw-bold mb-1">Kéo và thả tệp tin vào đây, hoặc <span class="text-primary text-decoration-underline">nhấp để chọn</span></p>
                    <p class="small text-body-secondary mb-0">Hỗ trợ tải lên nhiều file cùng lúc (Tối đa 10MB/tệp)</p>
                </div>
            </div>

            <div id="upload-progress-box" class="media-upload-progress d-none mx-auto mt-3">
                <div class="d-flex justify-content-between small fw-semibold text-primary mb-2">
                    <span id="progress-filename" class="text-truncate">Đang tải lên...</span>
                    <span id="progress-percent">0%</span>
                </div>
                <div class="progress" style="height: 0.5rem;">
                    <div id="progress-bar" class="progress-bar" role="progressbar" style="width: 0%"></div>
                </div>
            </div>

            <button type="button" id="btn-close-upload" class="btn-close position-absolute top-0 end-0 m-3" aria-label="Đóng vùng tải tệp"></button>
        </x-admin.card>
    </div>

    <!-- Filter Bar -->
    <x-admin.card class="p-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 small">
            <div class="d-flex align-items-center gap-2 fw-medium" id="folder-breadcrumb">
                <a href="#" class="text-decoration-none" data-folder-id="">Tất cả ảnh</a>
            </div>
            <div class="d-flex align-items-center gap-3 text-body-secondary fw-medium">
                <div id="folder-total-indicator">0 danh mục</div>
                <span class="rounded-circle bg-secondary-subtle" style="width: 0.25rem; height: 0.25rem;"></span>
                <div id="media-total-indicator">0 tệp tin</div>
            </div>
        </div>

        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 border-top pt-3 mt-3">
            <div class="media-toolbar-scroll d-flex flex-nowrap align-items-center gap-2 overflow-x-auto pb-1 pb-lg-0">
                <x-admin.button id="btn-create-folder" variant="outline-primary" class="flex-shrink-0">
                    <svg width="16" height="16" class="me-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 11h14a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2z"/></svg>
                    Tạo thư mục
                </x-admin.button>

                <div class="flex-shrink-0" style="width: 11.25rem;">
                    <x-admin.input type="text" id="media-keyword" placeholder="Tìm kiếm tên...">
                        <x-slot:prefix>
                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </x-slot:prefix>
                    </x-admin.input>
                </div>

                <div class="flex-shrink-0" style="width: 7.5rem;">
                    <x-admin.select id="media-type">
                        <option value="">Tất cả loại</option>
                        <option value="image">Hình ảnh</option>
                        <option value="document">Tài liệu</option>
                    </x-admin.select>
                </div>

                <div class="flex-shrink-0" style="width: 6.875rem;">
                    <x-admin.select id="media-sort">
                        <option value="desc">Mới nhất</option>
                        <option value="asc">Cũ nhất</option>
                    </x-admin.select>
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-end flex-shrink-0">
                <div class="media-view-toggle btn-group" role="group" aria-label="Kiểu hiển thị">
                    <button id="btn-view-grid" type="button" class="btn btn-sm btn-outline-primary active" title="Dạng lưới" aria-label="Dạng lưới" aria-pressed="true">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    </button>
                    <button id="btn-view-list" type="button" class="btn btn-sm btn-outline-primary" title="Dạng danh sách" aria-label="Dạng danh sách" aria-pressed="false">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </x-admin.card>

    <!-- Grid Container -->
    <div id="media-grid-container" class="media-grid-container position-relative bg-body rounded border p-3 d-flex flex-column gap-4">
        {{-- Dropzone Overlay for Grid (Kéo thả vào lưới) --}}
        <div id="media-grid-drop-overlay" class="position-absolute top-0 start-0 w-100 h-100 bg-primary-subtle rounded border border-2 border-primary d-none flex-column align-items-center justify-content-center pe-none" style="z-index: 20;">
            <svg width="64" height="64" class="text-primary mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
            <p class="fw-bold text-primary fs-5 mb-0">Thả file vào đây để tải lên</p>
        </div>

        <!-- Folders Section -->
        <div id="folder-section" class="d-none flex-column gap-3">
            <h3 class="small fw-semibold d-flex align-items-center gap-2 mb-0">
                <svg width="16" height="16" class="text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                Thư mục
            </h3>
            <div id="folder-grid" class="media-grid"></div>
            <hr class="border-secondary-subtle mb-0">
        </div>

        <!-- Media Section -->
        <div id="media-section" class="d-flex flex-column gap-3">
            <div id="media-grid" class="media-grid">
                <!-- Render via JS -->
                <div class="media-empty-state">
                    <span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>
                    Đang tải dữ liệu thư viện...
                </div>
            </div>
        </div>
        
        <!-- Load More / Loading Indicator -->
        <div id="media-load-more-container" class="py-4 text-center d-none mt-auto">
            <x-admin.button id="btn-load-more" variant="outline-primary" class="rounded-pill px-4">
                Tải thêm tệp tin
            </x-admin.button>
            <div id="media-loading-indicator" class="d-none small fw-medium text-body-secondary">
                <span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>
                Đang tải thêm...
            </div>
        </div>
    </div>
</div>

<!-- Floating Status Toast -->
<div id="media-grid-status" class="media-status-toast" role="status" aria-live="polite">
    <svg id="media-grid-icon-spinner" width="20" height="20" class="text-info" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
    <svg id="media-grid-icon-success" width="20" height="20" class="text-success d-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
    </svg>
    <span id="media-grid-status-text" class="small fw-semibold">Đang xử lý...</span>
</div>

<!-- Floating Bulk Action Bar -->
<div id="bulk-action-bar" class="media-bulk-bar">
    <div class="d-flex align-items-center gap-3 border-end pe-4">
        <div class="media-bulk-count" id="bulk-count">0</div>
        <span class="small fw-semibold">Tệp đang chọn</span>
    </div>
    <div class="d-flex align-items-center gap-2">
        <button id="btn-bulk-unselect" class="btn btn-sm btn-light">Hủy chọn</button>
        <button id="btn-bulk-delete" class="btn btn-sm btn-danger d-inline-flex align-items-center gap-1">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            Xóa hàng loạt
        </button>
    </div>
</div>

<!-- Modal: Xem chi tiết tệp -->
<x-admin.modal id="modal-media-detail" title="Chi tiết tệp tin">
    <div class="d-flex flex-column gap-4">
        <div id="detail-preview" class="media-detail-preview bg-body-tertiary rounded d-flex align-items-center justify-content-center overflow-hidden position-relative">
            <!-- Render via JS -->
        </div>
        <div class="d-flex flex-column gap-2 small text-body-secondary">
            <p class="mb-0"><strong>Tên tệp:</strong> <span id="detail-name" class="text-body"></span></p>
            <p class="mb-0"><strong>Kích thước:</strong> <span id="detail-size"></span></p>
            <p class="mb-0"><strong>Loại tệp (MIME):</strong> <span id="detail-mime"></span></p>
            <p class="mb-0"><strong>Ngày tải lên:</strong> <span id="detail-date"></span></p>
            <p class="mb-0"><strong>Đường dẫn gốc:</strong></p>
            <div class="d-flex align-items-center gap-2">
                <input type="text" id="detail-url" readonly class="form-control form-control-sm flex-grow-1 font-monospace">
                <button type="button" id="btn-copy-url" class="btn btn-sm btn-primary flex-shrink-0">Sao chép</button>
            </div>
        </div>
        <div class="pt-4 d-flex justify-content-between align-items-center border-top mt-4">
            <x-admin.button type="button" variant="danger" id="btn-delete-from-detail">
                <svg width="16" height="16" class="me-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Xóa file này
            </x-admin.button>
            <x-admin.button type="button" variant="secondary" onclick="closeModal('modal-media-detail')">Đóng</x-admin.button>
        </div>
    </div>
</x-admin.modal>

<!-- Modal: Tạo thư mục -->
<x-admin.modal id="modal-create-folder" title="Tạo thư mục mới">
    <form id="form-create-folder" class="d-flex flex-column gap-4">
        <div>
            <label class="form-label fw-medium mb-1">Tên thư mục</label>
            <x-admin.input type="text" id="folder-name" required placeholder="Nhập tên thư mục..." />
        </div>
        <div class="pt-4 d-flex justify-content-end gap-3 border-top mt-4">
            <x-admin.button type="button" variant="secondary" onclick="closeModal('modal-create-folder')">Hủy</x-admin.button>
            <x-admin.button type="submit" variant="primary" id="btn-submit-folder">Tạo mới</x-admin.button>
        </div>
    </form>
</x-admin.modal>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // ── Cấu hình ──────────────────────────────────────────────────────────
    const USE_INFINITE_SCROLL = true; // Đổi thành false nếu muốn dùng nút "Tải thêm"

    // ── Biến State ────────────────────────────────────────────────────────
    let mediaItemsMap = new Map();
    let foldersArray = [];
    let breadcrumbsArray = [];
    let currentFolderId = new URLSearchParams(window.location.search).get('folder') || '';
    let selectedUuids = new Set();
    let currentDetailMedia = null;
    let isListView = false;
    let currentPage = 1;
    let lastPage = 1;
    let currentTotal = 0;
    let isLoading = false;
    let isUploading = false;
    let gridStatusTimer = null;
    let searchTimeout = null;

    let lastCheckedItemIndex = -1;

    // ── DOM Elements ──────────────────────────────────────────────────────
    const gridEl              = document.getElementById('media-grid');
    const loadMoreContainer   = document.getElementById('media-load-more-container');
    const btnLoadMore         = document.getElementById('btn-load-more');
    const loadMoreSpinner     = document.getElementById('media-loading-indicator');
    
    // Filter
    const inputKeyword    = document.getElementById('media-keyword');
    const selectType      = document.getElementById('media-type');
    const selectSort      = document.getElementById('media-sort');
    const btnViewGrid     = document.getElementById('btn-view-grid');
    const btnViewList     = document.getElementById('btn-view-list');
    
    // Upload Top
    const btnOpenUpload   = document.getElementById('btn-open-upload');
    const btnCloseUpload  = document.getElementById('btn-close-upload');
    const uploadWrapper   = document.getElementById('upload-zone-wrapper');
    const dropZoneTop     = document.getElementById('drop-zone');
    const fileInput       = document.getElementById('file-input');
    const topProgBox      = document.getElementById('upload-progress-box');
    const topProgName     = document.getElementById('progress-filename');
    const topProgPct      = document.getElementById('progress-percent');
    const topProgBar      = document.getElementById('progress-bar');
    
    // Upload Grid Drop
    const gridContainer   = document.getElementById('media-grid-container');
    const gridDropOverlay = document.getElementById('media-grid-drop-overlay');

    // Bulk Action
    const btnBulkUnselect = document.getElementById('btn-bulk-unselect');
    const btnBulkDelete   = document.getElementById('btn-bulk-delete');

    // Detail Modal
    const detailName      = document.getElementById('detail-name');
    const detailSize      = document.getElementById('detail-size');
    const detailMime      = document.getElementById('detail-mime');
    const detailDate      = document.getElementById('detail-date');
    const detailUrl       = document.getElementById('detail-url');
    const detailPreview   = document.getElementById('detail-preview');
    const btnCopyUrl      = document.getElementById('btn-copy-url');
    const btnDeleteDetail = document.getElementById('btn-delete-from-detail');

    // ── Helpers ───────────────────────────────────────────────────────────
    function showGridStatus(text, type = 'success', duration = 2000) {
        const statusBox = document.getElementById('media-grid-status');
        const statusText = document.getElementById('media-grid-status-text');
        const iconSpinner = document.getElementById('media-grid-icon-spinner');
        const iconSuccess = document.getElementById('media-grid-icon-success');

        clearTimeout(gridStatusTimer);

        if (type === 'loading') {
            iconSpinner.classList.remove('d-none');
            iconSuccess.classList.add('d-none');
        } else {
            iconSpinner.classList.add('d-none');
            iconSuccess.classList.remove('d-none');
            if (type === 'error') iconSuccess.classList.replace('text-success', 'text-danger');
            else iconSuccess.classList.replace('text-danger', 'text-success');
        }
        
        statusText.textContent = text;
        statusBox.classList.add('is-visible');

        if (duration > 0) {
            gridStatusTimer = setTimeout(hideGridStatus, duration);
        }
    }

    function hideGridStatus() {
        const statusBox = document.getElementById('media-grid-status');
        statusBox.classList.remove('is-visible');
    }

    function updateBulkActionBar() {
        const count = selectedUuids.size;
        const bar = document.getElementById('bulk-action-bar');
        document.getElementById('bulk-count').textContent = count;

        if (count > 0) {
            bar.classList.add('is-visible');
        } else {
            bar.classList.remove('is-visible');
        }
    }

    function updateTotalIndicator() {
        const indicatorEl = document.getElementById('media-total-indicator');
        if (indicatorEl) {
            indicatorEl.innerHTML = `Hiển thị <strong>${mediaItemsMap.size}</strong> / ${currentTotal} tệp`;
        }
    }

    // ── Fetch Data ────────────────────────────────────────────────────────
    async function loadData(page = 1) {
        if (isLoading) return;
        isLoading = true;
        currentPage = page;

        if (page === 1) {
            mediaItemsMap.clear();
            foldersArray = [];
            gridEl.innerHTML = `<div class="media-empty-state"><span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Đang tải dữ liệu...</div>`;
            selectedUuids.clear();
            updateBulkActionBar();
            loadMoreContainer.classList.add('d-none');
            
            // Cập nhật URL parameter
            const newUrl = new URL(window.location);
            if (currentFolderId) newUrl.searchParams.set('folder', currentFolderId);
            else newUrl.searchParams.delete('folder');
            window.history.pushState({}, '', newUrl);
        } else {
            loadMoreContainer.classList.remove('d-none');
            btnLoadMore.classList.add('d-none');
            loadMoreSpinner.classList.remove('d-none');
        }

        const keyword = encodeURIComponent(inputKeyword.value.trim());
        const type    = encodeURIComponent(selectType.value);
        const sort    = encodeURIComponent(selectSort.value);

        try {
            const response = await fetch(`/admin/media?page=${page}&keyword=${keyword}&type=${type}&sort=${sort}&folder=${currentFolderId}`, {
                headers: { 'Accept': 'application/json' }
            });
            const res = await response.json();

            if (page === 1) {
                gridEl.innerHTML = '';
                if (res.folders) foldersArray = res.folders;
                if (res.breadcrumbs) breadcrumbsArray = res.breadcrumbs;
                renderBreadcrumbs();
            }
            
            const mediaData = res.media || res;
            lastPage = mediaData.last_page || (mediaData.meta ? mediaData.meta.last_page : 1);
            currentTotal = mediaData.total || (mediaData.meta ? mediaData.meta.total : 0);

            if (mediaData.data && Array.isArray(mediaData.data)) {
                mediaData.data.forEach(item => {
                    mediaItemsMap.set(item.id, item);
                });
            } else if (res.data && res.data.data) {
                res.data.data.forEach(item => {
                    mediaItemsMap.set(item.id, item);
                });
            }
            
            updateTotalIndicator();
            renderGrid();

            if (currentPage < lastPage) {
                loadMoreContainer.classList.remove('d-none');
                if (USE_INFINITE_SCROLL) {
                    btnLoadMore.classList.add('d-none');
                } else {
                    btnLoadMore.classList.remove('d-none');
                }
                loadMoreSpinner.classList.add('d-none');
            } else {
                loadMoreContainer.classList.add('d-none');
            }

        } catch (error) {
            console.error('Lỗi tải media:', error);
            if (page === 1) {
                gridEl.innerHTML = `<div class="media-empty-state text-danger fw-semibold">Có lỗi xảy ra khi tải dữ liệu!</div>`;
            }
        } finally {
            isLoading = false;
        }
    }

    function renderBreadcrumbs() {
        const bcContainer = document.getElementById('folder-breadcrumb');
        bcContainer.innerHTML = '';
        
        const rootLink = document.createElement('a');
        rootLink.href = 'javascript:void(0)';
        rootLink.className = 'text-decoration-none';
        rootLink.textContent = 'Tất cả ảnh';
        rootLink.addEventListener('click', () => { currentFolderId = ''; loadData(1); });
        bcContainer.appendChild(rootLink);

        breadcrumbsArray.forEach(bc => {
            const sep = document.createElement('span');
            sep.className = 'text-body-secondary mx-1';
            sep.textContent = '/';
            
            const link = document.createElement('a');
            link.href = 'javascript:void(0)';
            link.className = 'text-decoration-none';
            link.textContent = bc.name;
            link.addEventListener('click', () => { currentFolderId = bc.uuid; loadData(1); });
            
            bcContainer.appendChild(sep);
            bcContainer.appendChild(link);
        });
        
        document.getElementById('folder-total-indicator').textContent = `${foldersArray.length} danh mục`;
    }

    // ── Render ────────────────────────────────────────────────────────────
    function renderGrid() {
        const folderSection = document.getElementById('folder-section');
        const folderGrid = document.getElementById('folder-grid');
        
        if (currentPage === 1 && mediaItemsMap.size === 0 && foldersArray.length === 0) {
            folderSection.classList.add('d-none');
                folderSection.classList.remove('d-flex');
            gridEl.className = 'media-grid';
            gridEl.innerHTML = `<div class="media-empty-state">Thư viện trống. Hãy nhấp "Tải tệp tin lên" hoặc "Tạo thư mục".</div>`;
            return;
        }

        // Apply grid/list layout
        if (isListView) {
            gridEl.classList.add('media-grid-list');
            folderGrid.classList.add('media-grid-list');
        } else {
            gridEl.classList.remove('media-grid-list');
            folderGrid.classList.remove('media-grid-list');
        }

        if (currentPage === 1) {
            gridEl.innerHTML = '';
            if (mediaItemsMap.size === 0) {
                gridEl.innerHTML = '<div class="media-empty-state">Không tìm thấy tệp phù hợp.</div>';
            }
        }

        const df = document.createDocumentFragment();

        // Render Folders first (only on page 1)
        if (currentPage === 1) {
            folderGrid.innerHTML = '';
            if (foldersArray.length > 0) {
                folderSection.classList.remove('d-none');
                folderSection.classList.add('d-flex');
                foldersArray.forEach(folder => {
                    const el = document.createElement('div');
                    el.className = `media-folder-card ${isListView ? 'media-folder-card-list' : ''}`;
                    
                    const iconContainer = document.createElement('div');
                    iconContainer.className = 'media-folder-thumb';
                    
                    iconContainer.innerHTML = '<svg width="48" height="48" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/></svg>';
                    
                    if (!isListView) {
                        const text = document.createElement('div');
                        text.className = 'media-folder-name';
                        text.textContent = folder.name;
                        iconContainer.appendChild(text);
                    }
                    el.appendChild(iconContainer);

                    if (isListView) {
                        const textDiv = document.createElement('div');
                        textDiv.className = 'media-card-meta';
                        const folderName = document.createElement('p');
                        folderName.className = 'fw-bold small text-truncate mb-0';
                        folderName.textContent = folder.name;
                        textDiv.appendChild(folderName);
                        el.appendChild(textDiv);
                    }

                    // Delete button for folder
                    const delBtn = document.createElement('button');
                    delBtn.type = 'button';
                    delBtn.className = 'media-delete-action btn btn-sm btn-light';
                    delBtn.setAttribute('aria-label', `Xóa thư mục ${folder.name}`);
                    delBtn.innerHTML = `<svg width="16" height="16" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>`;
                    delBtn.addEventListener('click', async (e) => {
                        e.stopPropagation();
                        if (!confirm('Bạn có chắc muốn xóa thư mục này và TOÀN BỘ file bên trong? Không thể hoàn tác!')) return;
                        showGridStatus('Đang xóa thư mục...', 'loading', 0);
                        try {
                            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                            const res = await fetch(`/admin/media-folders/${folder.uuid}`, {
                                method: 'DELETE',
                                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
                            });
                            if(res.ok) { showGridStatus('Xóa thành công!', 'success'); loadData(1); }
                            else { showGridStatus('Lỗi khi xóa', 'error'); }
                        } catch(e) { showGridStatus('Lỗi mạng', 'error'); }
                    });
                    el.appendChild(delBtn);

                    // Double click to enter
                    el.addEventListener('dblclick', () => {
                        currentFolderId = folder.uuid;
                        loadData(1);
                    });
                    
                    folderGrid.appendChild(el);
                });
            } else {
                folderSection.classList.add('d-none');
                folderSection.classList.remove('d-flex');
            }
        }

        mediaItemsMap.forEach(item => {
            // Check if element already exists (in case of append)
            if (document.querySelector(`.media-card[data-id="${item.id}"]`)) return;

            const isImg = item.mime_type && item.mime_type.startsWith('image/');
            const thumbUrl = item.url || item.original_url || '';

            const el = document.createElement('div');
            el.className = `media-card ${isListView ? 'media-card-list' : ''}`;
            el.dataset.id = item.id;
            el.dataset.uuid = item.uuid;

            const imgContainer = document.createElement('div');
            imgContainer.className = `media-card-thumb ${isListView ? 'media-card-list-thumb' : ''}`;

            if (isImg && thumbUrl) {
                const img = document.createElement('img');
                img.src = thumbUrl;
                img.draggable = false;
                img.className = 'media-card-image';
                imgContainer.appendChild(img);
            } else {
                const docLabel = document.createElement('div');
                docLabel.className = 'position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center text-body-secondary fw-bold small';
                docLabel.textContent = 'FILE';
                imgContainer.appendChild(docLabel);
            }

            // Checkbox
            const checkDiv = document.createElement('div');
            const isChecked = selectedUuids.has(item.uuid);
            checkDiv.className = `media-check ${isChecked ? 'media-check-selected' : ''}`;
            checkDiv.setAttribute('role', 'checkbox');
            checkDiv.setAttribute('tabindex', '0');
            checkDiv.setAttribute('aria-label', `Chọn tệp ${item.file_name}`);
            checkDiv.setAttribute('aria-checked', isChecked ? 'true' : 'false');
            
            const checkSvg = document.createElement('div');
            checkSvg.innerHTML = `<svg width="14" height="14" class="text-white ${isChecked ? '' : 'd-none'}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>`;
            checkDiv.appendChild(checkSvg.firstChild);

            checkDiv.addEventListener('mousedown', (e) => {
                if (e.shiftKey) e.preventDefault();
            });

            checkDiv.addEventListener('click', (e) => {
                e.stopPropagation();
                
                const items = Array.from(document.querySelectorAll('.media-card'));
                const currentIndex = items.findIndex(el => el.dataset.id == item.id);
                
                if (e.shiftKey && lastCheckedItemIndex !== -1) {
                    const start = Math.min(lastCheckedItemIndex, currentIndex);
                    const end = Math.max(lastCheckedItemIndex, currentIndex);
                    
                    for (let i = start; i <= end; i++) {
                        const itUuid = items[i].dataset.uuid;
                        if (!selectedUuids.has(itUuid)) {
                            selectedUuids.add(itUuid);
                            const check = items[i].querySelector('.media-check');
                            check.classList.add('media-check-selected');
                            check.setAttribute('aria-checked', 'true');
                            check.querySelector('svg').classList.remove('d-none');
                        }
                    }
                } else {
                    const svg = checkDiv.querySelector('svg');
                    if (selectedUuids.has(item.uuid)) {
                        selectedUuids.delete(item.uuid);
                        checkDiv.classList.remove('media-check-selected');
                        checkDiv.setAttribute('aria-checked', 'false');
                        svg.classList.add('d-none');
                    } else {
                        selectedUuids.add(item.uuid);
                        checkDiv.classList.add('media-check-selected');
                        checkDiv.setAttribute('aria-checked', 'true');
                        svg.classList.remove('d-none');
                    }
                }
                
                lastCheckedItemIndex = currentIndex;
                updateBulkActionBar();
            });

            // Hover Overlay -> Open detail
            const overlayDiv = document.createElement('div');
            overlayDiv.className = 'media-card-overlay';
            overlayDiv.innerHTML = '<span class="media-card-overlay-label">Xem chi tiết</span>';
            
            overlayDiv.addEventListener('click', (e) => {
                if (e.target.closest('.media-check')) return;
                showDetailModal(item);
            });

            imgContainer.appendChild(checkDiv);
            imgContainer.appendChild(overlayDiv);
            el.appendChild(imgContainer);

            // Text Info (List view only)
            if (isListView) {
                const textDiv = document.createElement('div');
                textDiv.className = 'media-card-meta pe-none';
                const fileName = document.createElement('p');
                fileName.className = 'fw-bold small text-truncate mb-1';
                fileName.textContent = item.file_name;
                const fileMeta = document.createElement('div');
                fileMeta.className = 'd-flex align-items-center gap-3 small text-body-secondary';
                const fileSize = document.createElement('span');
                fileSize.textContent = item.human_readable_size || '';
                const fileDate = document.createElement('span');
                fileDate.textContent = item.created_at || '';
                fileMeta.append(fileSize, fileDate);
                textDiv.append(fileName, fileMeta);
                el.appendChild(textDiv);
            }

            df.appendChild(el);
        });

        gridEl.appendChild(df);
    }

    // ── Upload ────────────────────────────────────────────────────────────
    async function handleUpload(files, isFromGrid = false) {
        if (isUploading) return;
        isUploading = true;

        let successCount = 0;
        const total = files.length;

        if (!isFromGrid) {
            topProgBox.classList.remove('d-none');
            fileInput.disabled = true;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        for (let i = 0; i < total; i++) {
            const file = files[i];
            
            if (!isFromGrid) {
                topProgName.textContent = `Đang tải lên (${i+1}/${total}): ${file.name}`;
                topProgBar.style.width = '0%';
                topProgPct.textContent = '0%';
            } else {
                showGridStatus(`Đang tải lên (${i+1}/${total})...`, 'loading', 0);
            }

            try {
                await new Promise((resolve, reject) => {
                    const formData = new FormData();
                    formData.append('file', file);
                    formData.append('collection_name', 'default');
                    if (currentFolderId) {
                        formData.append('folder_id', currentFolderId);
                    }
                    
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', '/admin/media', true);
                    xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
                    xhr.setRequestHeader('Accept', 'application/json');

                    xhr.upload.addEventListener('progress', function (e) {
                        if (e.lengthComputable) {
                            const pct = Math.round((e.loaded / e.total) * 100);
                            if (!isFromGrid) {
                                topProgBar.style.width = pct + '%';
                                topProgPct.textContent = pct + '%';
                            } else {
                                document.getElementById('media-grid-status-text').textContent = `Đang tải lên (${i+1}/${total}): ${pct}%`;
                            }
                        }
                    });

                    xhr.onload = function () {
                        if (xhr.status >= 200 && xhr.status < 300) {
                            successCount++;
                            resolve();
                        } else {
                            reject('Server error');
                        }
                    };
                    
                    xhr.onerror = () => reject('Network error');
                    xhr.send(formData);
                });
            } catch (e) {
                console.error(`Upload error for file ${file.name}:`, e);
            }
        }

        isUploading = false;
        
        if (!isFromGrid) {
            topProgBox.classList.add('d-none');
            fileInput.disabled = false;
            fileInput.value = '';
        }

        if (successCount > 0) {
            const msg = total > 1 ? `Đã tải lên thành công ${successCount}/${total} tệp!` : 'Tải lên thành công!';
            showGridStatus(msg, 'success', 3000);
            loadData(1);
        } else {
            showGridStatus('Tải lên thất bại!', 'error', 3000);
        }
    }

    // ── Drag & Drop Top Zone ──────────────────────────────────────────────
    btnOpenUpload.addEventListener('click', () => {
        uploadWrapper.classList.toggle('d-none');
    });
    btnCloseUpload.addEventListener('click', () => {
        uploadWrapper.classList.add('d-none');
    });
    dropZoneTop.addEventListener('click', () => fileInput.click());
    
    let topDragCounter = 0;
    dropZoneTop.addEventListener('dragenter', (e) => { e.preventDefault(); topDragCounter++; dropZoneTop.classList.add('media-dropzone-active'); });
    dropZoneTop.addEventListener('dragleave', (e) => { e.preventDefault(); topDragCounter--; if(topDragCounter===0) dropZoneTop.classList.remove('media-dropzone-active'); });
    dropZoneTop.addEventListener('dragover', (e) => e.preventDefault());
    dropZoneTop.addEventListener('drop', (e) => {
        e.preventDefault();
        topDragCounter = 0;
        dropZoneTop.classList.remove('media-dropzone-active');
        if (e.dataTransfer.files && e.dataTransfer.files.length > 0) handleUpload(e.dataTransfer.files, false);
    });
    fileInput.addEventListener('change', (e) => {
        if (e.target.files && e.target.files.length > 0) handleUpload(e.target.files, false);
    });

    // ── Drag & Drop Grid Zone ─────────────────────────────────────────────
    let gridDragCounter = 0;
    gridContainer.addEventListener('dragenter', (e) => { e.preventDefault(); gridDragCounter++; gridDropOverlay.classList.remove('d-none');
        gridDropOverlay.classList.add('d-flex'); });
    gridContainer.addEventListener('dragleave', (e) => { e.preventDefault(); gridDragCounter--; if(gridDragCounter===0) gridDropOverlay.classList.add('d-none');
        gridDropOverlay.classList.remove('d-flex'); });
    gridContainer.addEventListener('dragover', (e) => e.preventDefault());
    gridContainer.addEventListener('drop', (e) => {
        e.preventDefault();
        gridDragCounter = 0;
        gridDropOverlay.classList.add('d-none');
        gridDropOverlay.classList.remove('d-flex');
        if (e.dataTransfer.files && e.dataTransfer.files.length > 0) handleUpload(e.dataTransfer.files, true);
    });

    // ── View Modes & Filters ──────────────────────────────────────────────
    btnViewGrid.addEventListener('click', () => {
        isListView = false;
        btnViewGrid.classList.add('active');
        btnViewList.classList.remove('active');
        btnViewGrid.setAttribute('aria-pressed', 'true');
        btnViewList.setAttribute('aria-pressed', 'false');
        loadData(1);
    });
    
    btnViewList.addEventListener('click', () => {
        isListView = true;
        btnViewList.classList.add('active');
        btnViewGrid.classList.remove('active');
        btnViewList.setAttribute('aria-pressed', 'true');
        btnViewGrid.setAttribute('aria-pressed', 'false');
        loadData(1);
    });

    inputKeyword.addEventListener('input', () => { clearTimeout(searchTimeout); searchTimeout = setTimeout(() => loadData(1), 500); });
    selectType.addEventListener('change', () => loadData(1));
    selectSort.addEventListener('change', () => loadData(1));

    // ── Infinite Scroll / Load More ───────────────────────────────────────
    if (USE_INFINITE_SCROLL) {
        window.addEventListener('scroll', () => {
            if (isLoading || currentPage >= lastPage) return;
            const { scrollTop, scrollHeight, clientHeight } = document.documentElement;
            if (scrollTop + clientHeight >= scrollHeight - 150) {
                loadData(currentPage + 1);
            }
        });
    }

    btnLoadMore.addEventListener('click', () => {
        if (!isLoading && currentPage < lastPage) {
            loadData(currentPage + 1);
        }
    });

    // ── Bulk Actions ──────────────────────────────────────────────────────
    btnBulkUnselect.addEventListener('click', () => {
        selectedUuids.clear();
        updateBulkActionBar();
        renderGrid(); // Rerender to uncheck all visually (or could querySelector all checkboxes)
    });

    btnBulkDelete.addEventListener('click', async () => {
        if (selectedUuids.size === 0) return;
        if (!confirm(`Bạn có chắc chắn muốn xóa vĩnh viễn ${selectedUuids.size} tệp tin? Hành động này không thể hoàn tác.`)) return;

        showGridStatus(`Đang xóa ${selectedUuids.size} tệp...`, 'loading', 0);
        
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const res = await fetch('/admin/media/bulk', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ ids: Array.from(selectedUuids) })
            });

            if (res.ok) {
                showGridStatus('Đã xóa thành công!', 'success', 2500);
                
                // Remove from DOM without reloading
                selectedUuids.forEach(uuid => {
                    for (let [id, item] of mediaItemsMap.entries()) {
                        if (item.uuid === uuid) {
                            mediaItemsMap.delete(id);
                            const itemEl = document.querySelector(`.media-card[data-id="${id}"]`);
                            if (itemEl) itemEl.remove();
                            currentTotal--;
                            break;
                        }
                    }
                });
                selectedUuids.clear();
                updateBulkActionBar();
                updateTotalIndicator();
                
                // Show empty state if needed
                if (mediaItemsMap.size === 0) {
                    gridEl.className = 'media-grid';
                    gridEl.innerHTML = `<div class="media-empty-state">Thư viện trống. Hãy nhấp "Tải tệp tin lên" để thêm file mới!</div>`;
                }
            } else {
                throw new Error('Server error');
            }
        } catch(e) {
            showGridStatus('Lỗi khi xóa file!', 'error', 3000);
        }
    });

    // ── Modal Chi tiết ────────────────────────────────────────────────────
    function showDetailModal(item) {
        currentDetailMedia = item;
        detailName.textContent = item.file_name;
        detailSize.textContent = item.human_readable_size || 'N/A';
        detailMime.textContent = item.mime_type || 'N/A';
        detailDate.textContent = item.created_at || 'N/A';
        detailUrl.value = item.url || '';

        const isImg = item.mime_type && item.mime_type.startsWith('image/');
        detailPreview.innerHTML = '';
        if (isImg && item.url) {
            const previewImage = document.createElement('img');
            previewImage.src = item.url;
            previewImage.className = 'mw-100 mh-100 object-fit-contain pe-none';
            detailPreview.appendChild(previewImage);
        } else {
            const previewLabel = document.createElement('div');
            previewLabel.className = 'text-body-secondary fw-bold small';
            previewLabel.textContent = 'Tài liệu không hỗ trợ xem trước ảnh';
            detailPreview.appendChild(previewLabel);
        }

        if (typeof openModal === 'function') openModal('modal-media-detail');
    }

    btnCopyUrl.addEventListener('click', () => {
        detailUrl.select();
        document.execCommand('copy');
        const oldText = btnCopyUrl.textContent;
        btnCopyUrl.textContent = 'Đã chép!';
        btnCopyUrl.className = 'btn btn-sm btn-success flex-shrink-0';
        setTimeout(() => {
            btnCopyUrl.textContent = oldText;
            btnCopyUrl.className = 'btn btn-sm btn-primary flex-shrink-0';
        }, 1500);
    });

    btnDeleteDetail.addEventListener('click', async () => {
        if (!currentDetailMedia) return;
        if (!confirm('Xóa vĩnh viễn tệp tin này?')) return;

        if (typeof closeModal === 'function') closeModal('modal-media-detail');
        showGridStatus('Đang xóa tệp...', 'loading', 0);

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const res = await fetch(`/admin/media/${currentDetailMedia.uuid}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });

            if (res.ok) {
                showGridStatus('Đã xóa tệp thành công!', 'success', 2500);
                selectedUuids.delete(currentDetailMedia.uuid);
                updateBulkActionBar();
                
                // Remove from DOM without reloading
                mediaItemsMap.delete(currentDetailMedia.id);
                const itemEl = document.querySelector(`.media-card[data-id="${currentDetailMedia.id}"]`);
                if (itemEl) itemEl.remove();
                currentTotal--;
                updateTotalIndicator();
                
                // Show empty state if needed
                if (mediaItemsMap.size === 0) {
                    gridEl.className = 'media-grid';
                    gridEl.innerHTML = `<div class="media-empty-state">Thư viện trống. Hãy nhấp "Tải tệp tin lên" để thêm file mới!</div>`;
                }
            } else {
                throw new Error('Server error');
            }
        } catch(e) {
            showGridStatus('Lỗi khi xóa file!', 'error', 3000);
        }
    });

    // ── Tạo thư mục ────────────────────────────────────────────────────────
    const btnCreateFolder = document.getElementById('btn-create-folder');
    const formCreateFolder = document.getElementById('form-create-folder');
    
    if (btnCreateFolder) {
        btnCreateFolder.addEventListener('click', () => {
            document.getElementById('folder-name').value = '';
            if (typeof openModal === 'function') openModal('modal-create-folder');
        });
    }

    if (formCreateFolder) {
        formCreateFolder.addEventListener('submit', async (e) => {
            e.preventDefault();
            const name = document.getElementById('folder-name').value.trim();
            if (!name) return;

            const btnSubmit = document.getElementById('btn-submit-folder');
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = 'Đang tạo...';

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch('/admin/media-folders', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ name, parent_id: currentFolderId || null })
                });
                if (res.ok) {
                    showGridStatus('Tạo thư mục thành công!', 'success', 2500);
                    if (typeof closeModal === 'function') closeModal('modal-create-folder');
                    loadData(1);
                } else {
                    showGridStatus('Lỗi khi tạo thư mục', 'error', 3000);
                }
            } catch (e) {
                showGridStatus('Lỗi mạng', 'error', 3000);
            } finally {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = 'Tạo mới';
            }
        });
    }

    // ── Khởi tạo ──────────────────────────────────────────────────────────
    loadData(1);
});
</script>
@endpush
