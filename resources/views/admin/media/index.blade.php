@extends('layouts.admin')
@section('title', 'Thư viện Media')

@section('content')
<div class="space-y-6 pb-20 relative">
    <!-- Header & Actions -->
    <x-admin.page-header 
        title="Thư viện Media & Tệp tin"
        subtitle="Quản lý hình ảnh, tài liệu và tự động tạo thumbnail"
        :breadcrumbs="[
            ['label' => 'Bảng điều khiển', 'url' => '/admin/dashboard'], 
            ['label' => 'Thư viện Media']
        ]"
    >
        <x-admin.button id="btn-open-upload" variant="primary" class="shrink-0">
            <svg class="w-5 h-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            <span>Tải tệp tin lên</span>
        </x-admin.button>
    </x-admin.page-header>

    <!-- Drag & Drop Upload Zone (Mặc định ẩn) -->
    <div id="upload-zone-wrapper" class="hidden transition-all duration-300">
        <x-admin.card class="p-8 border-2 border-dashed border-blue-500/40 bg-blue-50/50 dark:bg-blue-950/20 text-center relative">
            <input type="file" id="file-input" class="hidden" multiple>
            <div id="drop-zone" class="cursor-pointer py-8 space-y-4 rounded-xl border border-transparent transition-all">
                <div class="w-16 h-16 mx-auto rounded-full bg-blue-500/10 text-blue-500 flex items-center justify-center pointer-events-none">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                </div>
                <div class="pointer-events-none">
                    <p class="text-base font-bold text-slate-800 dark:text-slate-200">Kéo và thả tệp tin vào đây, hoặc <span class="text-blue-500 underline">nhấp để chọn</span></p>
                    <p class="mt-1 text-xs text-slate-400">Hỗ trợ tải lên nhiều file cùng lúc (Tối đa 10MB/tệp)</p>
                </div>
            </div>

            <!-- Progress Box -->
            <div id="upload-progress-box" class="hidden max-w-md mx-auto mt-4 space-y-2">
                <div class="flex justify-between text-xs font-semibold text-blue-500">
                    <span id="progress-filename" class="truncate max-w-[80%]">Đang tải lên...</span>
                    <span id="progress-percent">0%</span>
                </div>
                <div class="w-full bg-slate-200 dark:bg-slate-800 rounded-full h-2 overflow-hidden">
                    <div id="progress-bar" class="bg-blue-600 h-2 rounded-full transition-all duration-150" style="width: 0%"></div>
                </div>
            </div>

            <button type="button" id="btn-close-upload" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </x-admin.card>
    </div>

    <!-- Filter Bar -->
    <x-admin.card class="p-4 space-y-4">
        <!-- Row 1: Breadcrumb & Stats -->
        <div class="flex flex-col sm:flex-row justify-between items-center text-sm">
            <div class="flex items-center gap-2 text-slate-600 dark:text-slate-400 font-medium" id="folder-breadcrumb">
                <a href="#" class="hover:text-blue-600 transition-colors" data-folder-id="">Tất cả ảnh</a>
                <!-- Sub-folders will be appended here via JS -->
            </div>
            <div class="flex items-center gap-4 text-slate-500 font-medium">
                <div id="folder-total-indicator">0 danh mục</div>
                <div class="w-1 h-1 rounded-full bg-slate-300 dark:bg-slate-700"></div>
                <div id="media-total-indicator">0 tệp tin</div>
            </div>
        </div>

        <!-- Row 2: Controls -->
        <div class="flex flex-col lg:flex-row gap-4 items-center justify-between border-t border-slate-100 dark:border-slate-800 pt-4">
            <div class="flex flex-nowrap items-center gap-2 w-full lg:w-auto overflow-x-auto pb-2 lg:pb-0 hide-scrollbar">
                <x-admin.button id="btn-create-folder" variant="outline" class="shrink-0 px-3 py-2">
                    <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 11h14a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2z"/></svg>
                    Tạo thư mục
                </x-admin.button>
                
                <div class="w-[180px] shrink-0">
                    <x-admin.input type="text" id="media-keyword" placeholder="Tìm kiếm tên...">
                        <x-slot:prefix>
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </x-slot:prefix>
                    </x-admin.input>
                </div>

                <div class="w-[120px] shrink-0">
                    <x-admin.select id="media-type">
                        <option value="">Tất cả loại</option>
                        <option value="image">Hình ảnh</option>
                        <option value="document">Tài liệu</option>
                    </x-admin.select>
                </div>

                <div class="w-[110px] shrink-0">
                    <x-admin.select id="media-sort">
                        <option value="desc">Mới nhất</option>
                        <option value="asc">Cũ nhất</option>
                    </x-admin.select>
                </div>
            </div>

            <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                <div class="flex p-1 bg-slate-100 dark:bg-slate-800 rounded-lg">
                    <button id="btn-view-grid" class="p-1.5 rounded bg-white shadow-sm text-blue-600 dark:bg-slate-700 dark:text-blue-400 transition-colors" title="Dạng lưới">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    </button>
                    <button id="btn-view-list" class="p-1.5 rounded text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 transition-colors" title="Dạng danh sách">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </x-admin.card>

    <!-- Grid Container -->
    <div id="media-grid-container" class="relative bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4 min-h-[400px] flex flex-col gap-6">
        {{-- Dropzone Overlay for Grid (Kéo thả vào lưới) --}}
        <div id="media-grid-drop-overlay" class="absolute inset-0 bg-blue-50/90 dark:bg-blue-900/90 backdrop-blur-[2px] z-20 rounded-xl flex flex-col items-center justify-center border-2 border-dashed border-blue-400 hidden pointer-events-none">
            <svg class="w-16 h-16 text-blue-500 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
            <p class="font-bold text-blue-600 dark:text-blue-400 text-lg">Thả file vào đây để tải lên</p>
        </div>

        <!-- Folders Section -->
        <div id="folder-section" class="hidden flex-col gap-3">
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                Thư mục
            </h3>
            <div id="folder-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                <!-- Folders will be rendered here -->
            </div>
            <hr class="border-slate-100 dark:border-slate-800 mt-2">
        </div>

        <!-- Media Section -->
        <div id="media-section" class="flex-col gap-3 flex">
            <div id="media-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                <!-- Render via JS -->
                <div class="col-span-full text-center py-16 text-slate-500">
                    <svg class="w-8 h-8 animate-spin mx-auto text-blue-500 mb-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    Đang tải dữ liệu thư viện...
                </div>
            </div>
        </div>
        
        <!-- Load More / Loading Indicator -->
        <div id="media-load-more-container" class="py-8 text-center hidden mt-auto">
            <x-admin.button id="btn-load-more" variant="outline" class="rounded-full px-6 py-2">
                Tải thêm tệp tin
            </x-admin.button>
            <div id="media-loading-indicator" class="hidden text-sm font-medium text-slate-500">
                <svg class="w-5 h-5 animate-spin mx-auto text-slate-400 mb-1" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                Đang tải thêm...
            </div>
        </div>
    </div>
</div>

<!-- Floating Status Toast -->
<div id="media-grid-status" class="fixed top-10 left-1/2 -translate-x-1/2 bg-slate-800 dark:bg-slate-700 text-white px-6 py-3 rounded-full shadow-2xl flex items-center gap-3 z-50 transition-all duration-300 -translate-y-20 opacity-0 pointer-events-none">
    <svg id="media-grid-icon-spinner" class="w-5 h-5 animate-spin text-blue-400" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
    <svg id="media-grid-icon-success" class="w-5 h-5 text-green-400 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
    </svg>
    <span id="media-grid-status-text" class="text-sm font-semibold tracking-wide">Đang xử lý...</span>
</div>

<!-- Floating Bulk Action Bar -->
<div id="bulk-action-bar" class="fixed bottom-6 left-1/2 -translate-x-1/2 bg-white dark:bg-slate-800 shadow-2xl border border-slate-200 dark:border-slate-700 rounded-2xl px-4 py-3 flex items-center gap-6 z-40 transition-all duration-300 translate-y-20 opacity-0 pointer-events-none">
    <div class="flex items-center gap-3 border-r border-slate-200 dark:border-slate-700 pr-6">
        <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center text-blue-600 dark:text-blue-400 font-bold text-sm" id="bulk-count">0</div>
        <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Tệp đang chọn</span>
    </div>
    <div class="flex items-center gap-2">
        <button id="btn-bulk-unselect" class="px-3 py-1.5 text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors">
            Hủy chọn
        </button>
        <button id="btn-bulk-delete" class="px-4 py-1.5 text-sm font-medium text-white bg-red-500 hover:bg-red-600 rounded-lg shadow-sm transition-colors flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            Xóa hàng loạt
        </button>
    </div>
</div>

<!-- Modal: Xem chi tiết tệp -->
<x-admin.modal id="modal-media-detail" title="Chi tiết tệp tin">
    <div class="space-y-4">
        <div id="detail-preview" class="w-full h-64 bg-slate-100 dark:bg-slate-800 rounded-lg flex items-center justify-center overflow-hidden relative group">
            <!-- Render via JS -->
        </div>
        <div class="space-y-2 text-sm text-slate-600 dark:text-slate-400">
            <p><strong>Tên tệp:</strong> <span id="detail-name" class="text-slate-900 dark:text-slate-100"></span></p>
            <p><strong>Kích thước:</strong> <span id="detail-size"></span></p>
            <p><strong>Loại tệp (MIME):</strong> <span id="detail-mime"></span></p>
            <p><strong>Ngày tải lên:</strong> <span id="detail-date"></span></p>
            <p><strong>Đường dẫn gốc:</strong></p>
            <div class="flex items-center gap-2">
                <input type="text" id="detail-url" readonly class="flex-1 px-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs select-all focus:outline-none focus:border-blue-500">
                <button type="button" id="btn-copy-url" class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-semibold hover:bg-blue-700 transition-colors">Sao chép</button>
            </div>
        </div>
        <div class="pt-4 flex justify-between items-center border-t border-slate-200 dark:border-slate-800 mt-4">
            <x-admin.button type="button" variant="danger" id="btn-delete-from-detail">
                <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Xóa file này
            </x-admin.button>
            <x-admin.button type="button" variant="secondary" onclick="closeModal('modal-media-detail')">Đóng</x-admin.button>
        </div>
    </div>
</x-admin.modal>

<!-- Modal: Tạo thư mục -->
<x-admin.modal id="modal-create-folder" title="Tạo thư mục mới">
    <form id="form-create-folder" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Tên thư mục</label>
            <x-admin.input type="text" id="folder-name" required placeholder="Nhập tên thư mục..." />
        </div>
        <div class="pt-4 flex justify-end gap-3 border-t border-slate-200 dark:border-slate-800 mt-4">
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
            iconSpinner.classList.remove('hidden');
            iconSuccess.classList.add('hidden');
        } else {
            iconSpinner.classList.add('hidden');
            iconSuccess.classList.remove('hidden');
            if (type === 'error') iconSuccess.classList.replace('text-green-400', 'text-red-400');
            else iconSuccess.classList.replace('text-red-400', 'text-green-400');
        }
        
        statusText.textContent = text;
        statusBox.classList.remove('-translate-y-20', 'opacity-0');
        statusBox.classList.add('translate-y-0', 'opacity-100');

        if (duration > 0) {
            gridStatusTimer = setTimeout(hideGridStatus, duration);
        }
    }

    function hideGridStatus() {
        const statusBox = document.getElementById('media-grid-status');
        statusBox.classList.remove('translate-y-0', 'opacity-100');
        statusBox.classList.add('-translate-y-20', 'opacity-0');
    }

    function updateBulkActionBar() {
        const count = selectedUuids.size;
        const bar = document.getElementById('bulk-action-bar');
        document.getElementById('bulk-count').textContent = count;

        if (count > 0) {
            bar.classList.remove('translate-y-20', 'opacity-0', 'pointer-events-none');
            bar.classList.add('translate-y-0', 'opacity-100', 'pointer-events-auto');
        } else {
            bar.classList.add('translate-y-20', 'opacity-0', 'pointer-events-none');
            bar.classList.remove('translate-y-0', 'opacity-100', 'pointer-events-auto');
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
            gridEl.innerHTML = `<div class="col-span-full text-center py-16 text-slate-500"><svg class="w-8 h-8 animate-spin mx-auto text-blue-500 mb-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Đang tải dữ liệu...</div>`;
            selectedUuids.clear();
            updateBulkActionBar();
            loadMoreContainer.classList.add('hidden');
            
            // Cập nhật URL parameter
            const newUrl = new URL(window.location);
            if (currentFolderId) newUrl.searchParams.set('folder', currentFolderId);
            else newUrl.searchParams.delete('folder');
            window.history.pushState({}, '', newUrl);
        } else {
            loadMoreContainer.classList.remove('hidden');
            btnLoadMore.classList.add('hidden');
            loadMoreSpinner.classList.remove('hidden');
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
                loadMoreContainer.classList.remove('hidden');
                if (USE_INFINITE_SCROLL) {
                    btnLoadMore.classList.add('hidden');
                } else {
                    btnLoadMore.classList.remove('hidden');
                }
                loadMoreSpinner.classList.add('hidden');
            } else {
                loadMoreContainer.classList.add('hidden');
            }

        } catch (error) {
            console.error('Lỗi tải media:', error);
            if (page === 1) {
                gridEl.innerHTML = `<div class="col-span-full text-center py-16 text-red-500 font-semibold">Có lỗi xảy ra khi tải dữ liệu!</div>`;
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
        rootLink.className = 'hover:text-blue-600 transition-colors';
        rootLink.textContent = 'Tất cả ảnh';
        rootLink.addEventListener('click', () => { currentFolderId = ''; loadData(1); });
        bcContainer.appendChild(rootLink);

        breadcrumbsArray.forEach(bc => {
            const sep = document.createElement('span');
            sep.className = 'text-slate-400 mx-1';
            sep.textContent = '/';
            
            const link = document.createElement('a');
            link.href = 'javascript:void(0)';
            link.className = 'hover:text-blue-600 transition-colors';
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
            folderSection.classList.add('hidden');
            gridEl.className = 'grid grid-cols-1';
            gridEl.innerHTML = `<div class="col-span-full text-center py-16 text-slate-500 bg-white dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-800">Thư viện trống. Hãy nhấp "Tải tệp tin lên" hoặc "Tạo thư mục".</div>`;
            return;
        }

        // Apply grid/list layout
        if (isListView) {
            gridEl.className = 'grid grid-cols-1 gap-2';
            folderGrid.className = 'grid grid-cols-1 gap-2';
        } else {
            gridEl.className = 'grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4';
            folderGrid.className = 'grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4';
        }

        if (currentPage === 1) gridEl.innerHTML = '';

        const df = document.createDocumentFragment();

        // Render Folders first (only on page 1)
        if (currentPage === 1) {
            folderGrid.innerHTML = '';
            if (foldersArray.length > 0) {
                folderSection.classList.remove('hidden');
                foldersArray.forEach(folder => {
                    const el = document.createElement('div');
                    el.className = `media-item folder-item group relative bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg overflow-hidden shadow-sm transition-all flex flex-col justify-between hover:border-blue-500 hover:shadow-md cursor-pointer select-none`;
                    
                    if (isListView) {
                        el.classList.add('flex-row', 'items-center', 'h-16');
                    } else {
                        el.classList.add('aspect-square');
                    }
                    
                    const iconContainer = document.createElement('div');
                    iconContainer.className = isListView 
                        ? 'w-16 h-16 flex-none bg-blue-50 dark:bg-slate-800 flex items-center justify-center text-blue-500' 
                        : 'w-full h-full flex-1 bg-blue-50 dark:bg-slate-800 flex flex-col items-center justify-center text-blue-500';
                    
                    iconContainer.innerHTML = `<svg class="${isListView ? 'w-8 h-8' : 'w-12 h-12 mb-2'} drop-shadow-sm transition-transform group-hover:scale-110" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/></svg>`;
                    
                    if (!isListView) {
                        const text = document.createElement('div');
                        text.className = 'w-full px-2 text-center text-sm font-semibold text-slate-700 dark:text-slate-200 truncate absolute bottom-3';
                        text.textContent = folder.name;
                        iconContainer.appendChild(text);
                    }
                    el.appendChild(iconContainer);

                    if (isListView) {
                        const textDiv = document.createElement('div');
                        textDiv.className = 'flex-1 px-4 min-w-0';
                        textDiv.innerHTML = `<p class="font-bold text-sm truncate text-slate-800 dark:text-slate-200">${folder.name}</p>`;
                        el.appendChild(textDiv);
                    }

                    // Delete button for folder
                    const delBtn = document.createElement('button');
                    delBtn.className = 'absolute top-2 right-2 p-1.5 bg-white/80 dark:bg-slate-800/80 rounded-md text-slate-400 hover:text-red-500 hover:bg-white transition-colors opacity-0 group-hover:opacity-100 shadow-sm z-10';
                    delBtn.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>`;
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
                folderSection.classList.add('hidden');
            }
        }

        mediaItemsMap.forEach(item => {
            // Check if element already exists (in case of append)
            if (document.querySelector(`.media-item[data-id="${item.id}"]`)) return;

            const isImg = item.mime_type && item.mime_type.startsWith('image/');
            const thumbUrl = item.url || item.original_url || '';

            const el = document.createElement('div');
            el.className = `media-item group relative bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg overflow-hidden shadow-sm transition-all flex flex-col justify-between hover:border-blue-500 hover:shadow-md`;
            el.dataset.id = item.id;
            el.dataset.uuid = item.uuid;
            
            if (isListView) {
                el.classList.remove('flex-col', 'hover:-translate-y-1');
                el.classList.add('flex-row', 'items-center', 'h-24');
            } else {
                el.classList.add('aspect-square');
            }

            // Image Container
            const imgContainer = document.createElement('div');
            imgContainer.className = isListView 
                ? 'w-24 h-24 flex-none bg-slate-100 dark:bg-slate-800 overflow-hidden relative' 
                : 'w-full h-full bg-slate-100 dark:bg-slate-800 relative overflow-hidden';

            if (isImg && thumbUrl) {
                const img = document.createElement('img');
                img.src = thumbUrl;
                img.draggable = false;
                img.className = 'absolute inset-0 w-full h-full object-cover transition-transform duration-300 group-hover:scale-105';
                imgContainer.appendChild(img);
            } else {
                const docLabel = document.createElement('div');
                docLabel.className = 'absolute inset-0 flex items-center justify-center text-slate-400 font-bold text-xs';
                docLabel.textContent = 'FILE';
                imgContainer.appendChild(docLabel);
            }

            // Checkbox
            const checkDiv = document.createElement('div');
            const isChecked = selectedUuids.has(item.uuid);
            checkDiv.className = `absolute top-2 right-2 w-5 h-5 rounded border-2 z-10 flex items-center justify-center cursor-pointer transition-colors shadow-sm
                ${isChecked ? 'bg-blue-600 border-blue-600 hover:bg-blue-700 hover:border-blue-700' : 'bg-white/80 border-slate-300 dark:bg-slate-800/80 dark:border-slate-600 hover:bg-white dark:hover:bg-slate-700'}`;
            
            const checkSvg = document.createElement('div');
            checkSvg.innerHTML = `<svg class="w-3.5 h-3.5 text-white ${isChecked ? '' : 'hidden'}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>`;
            checkDiv.appendChild(checkSvg.firstChild);

            checkDiv.addEventListener('mousedown', (e) => {
                if (e.shiftKey) e.preventDefault();
            });

            checkDiv.addEventListener('click', (e) => {
                e.stopPropagation();
                
                const items = Array.from(document.querySelectorAll('.media-item'));
                const currentIndex = items.findIndex(el => el.dataset.id == item.id);
                
                if (e.shiftKey && lastCheckedItemIndex !== -1) {
                    const start = Math.min(lastCheckedItemIndex, currentIndex);
                    const end = Math.max(lastCheckedItemIndex, currentIndex);
                    
                    for (let i = start; i <= end; i++) {
                        const itUuid = items[i].dataset.uuid;
                        if (!selectedUuids.has(itUuid)) {
                            selectedUuids.add(itUuid);
                            const check = items[i].querySelector('.absolute.top-2.right-2');
                            check.classList.remove('bg-white/80', 'border-slate-300', 'dark:bg-slate-800/80', 'dark:border-slate-600', 'hover:bg-white', 'dark:hover:bg-slate-700');
                            check.classList.add('bg-blue-600', 'border-blue-600', 'hover:bg-blue-700', 'hover:border-blue-700');
                            check.querySelector('svg').classList.remove('hidden');
                        }
                    }
                } else {
                    const svg = checkDiv.querySelector('svg');
                    if (selectedUuids.has(item.uuid)) {
                        selectedUuids.delete(item.uuid);
                        checkDiv.classList.remove('bg-blue-600', 'border-blue-600', 'hover:bg-blue-700', 'hover:border-blue-700');
                        checkDiv.classList.add('bg-white/80', 'border-slate-300', 'dark:bg-slate-800/80', 'dark:border-slate-600', 'hover:bg-white', 'dark:hover:bg-slate-700');
                        svg.classList.add('hidden');
                    } else {
                        selectedUuids.add(item.uuid);
                        checkDiv.classList.remove('bg-white/80', 'border-slate-300', 'dark:bg-slate-800/80', 'dark:border-slate-600', 'hover:bg-white', 'dark:hover:bg-slate-700');
                        checkDiv.classList.add('bg-blue-600', 'border-blue-600', 'hover:bg-blue-700', 'hover:border-blue-700');
                        svg.classList.remove('hidden');
                    }
                }
                
                lastCheckedItemIndex = currentIndex;
                updateBulkActionBar();
            });

            // Hover Overlay -> Open detail
            const overlayDiv = document.createElement('div');
            overlayDiv.className = 'absolute inset-0 bg-slate-950/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center cursor-pointer';
            overlayDiv.innerHTML = `<span class="px-3 py-1 rounded-lg bg-white/20 backdrop-blur-md text-white font-semibold text-xs shadow-sm">Xem chi tiết</span>`;
            
            overlayDiv.addEventListener('click', (e) => {
                if (e.target.closest('.absolute.top-2.right-2')) return; // Ignore if clicked on checkbox area
                showDetailModal(item);
            });

            imgContainer.appendChild(checkDiv);
            imgContainer.appendChild(overlayDiv);
            el.appendChild(imgContainer);

            // Text Info (List view only)
            if (isListView) {
                const textDiv = document.createElement('div');
                textDiv.className = 'flex-1 px-4 min-w-0 pointer-events-none';
                textDiv.innerHTML = `
                    <p class="font-bold text-sm truncate text-slate-800 dark:text-slate-200">${item.file_name}</p>
                    <div class="flex items-center gap-4 mt-1 text-xs text-slate-500">
                        <span>${item.human_readable_size || ''}</span>
                        <span>${item.created_at || ''}</span>
                    </div>
                `;
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
            topProgBox.classList.remove('hidden');
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
            topProgBox.classList.add('hidden');
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
        uploadWrapper.classList.toggle('hidden');
    });
    btnCloseUpload.addEventListener('click', () => {
        uploadWrapper.classList.add('hidden');
    });
    dropZoneTop.addEventListener('click', () => fileInput.click());
    
    let topDragCounter = 0;
    dropZoneTop.addEventListener('dragenter', (e) => { e.preventDefault(); topDragCounter++; dropZoneTop.classList.add('border-blue-500', 'bg-blue-500/10'); });
    dropZoneTop.addEventListener('dragleave', (e) => { e.preventDefault(); topDragCounter--; if(topDragCounter===0) dropZoneTop.classList.remove('border-blue-500', 'bg-blue-500/10'); });
    dropZoneTop.addEventListener('dragover', (e) => e.preventDefault());
    dropZoneTop.addEventListener('drop', (e) => {
        e.preventDefault();
        topDragCounter = 0;
        dropZoneTop.classList.remove('border-blue-500', 'bg-blue-500/10');
        if (e.dataTransfer.files && e.dataTransfer.files.length > 0) handleUpload(e.dataTransfer.files, false);
    });
    fileInput.addEventListener('change', (e) => {
        if (e.target.files && e.target.files.length > 0) handleUpload(e.target.files, false);
    });

    // ── Drag & Drop Grid Zone ─────────────────────────────────────────────
    let gridDragCounter = 0;
    gridContainer.addEventListener('dragenter', (e) => { e.preventDefault(); gridDragCounter++; gridDropOverlay.classList.remove('hidden'); });
    gridContainer.addEventListener('dragleave', (e) => { e.preventDefault(); gridDragCounter--; if(gridDragCounter===0) gridDropOverlay.classList.add('hidden'); });
    gridContainer.addEventListener('dragover', (e) => e.preventDefault());
    gridContainer.addEventListener('drop', (e) => {
        e.preventDefault();
        gridDragCounter = 0;
        gridDropOverlay.classList.add('hidden');
        if (e.dataTransfer.files && e.dataTransfer.files.length > 0) handleUpload(e.dataTransfer.files, true);
    });

    // ── View Modes & Filters ──────────────────────────────────────────────
    btnViewGrid.addEventListener('click', () => {
        isListView = false;
        btnViewGrid.className = 'p-1.5 rounded bg-white shadow-sm text-blue-600 dark:bg-slate-700 dark:text-blue-400 transition-colors';
        btnViewList.className = 'p-1.5 rounded text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 transition-colors';
        loadData(1);
    });
    
    btnViewList.addEventListener('click', () => {
        isListView = true;
        btnViewList.className = 'p-1.5 rounded bg-white shadow-sm text-blue-600 dark:bg-slate-700 dark:text-blue-400 transition-colors';
        btnViewGrid.className = 'p-1.5 rounded text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 transition-colors';
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
                            const itemEl = document.querySelector(`.media-item[data-id="${id}"]`);
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
                    gridEl.className = 'grid grid-cols-1';
                    gridEl.innerHTML = `<div class="col-span-full text-center py-16 text-slate-500 bg-white dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-800">Thư viện trống. Hãy nhấp "Tải tệp tin lên" để thêm file mới!</div>`;
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
        if (isImg && item.url) {
            detailPreview.innerHTML = `<img src="${item.url}" class="max-h-60 max-w-full object-contain pointer-events-none">`;
        } else {
            detailPreview.innerHTML = `<div class="text-slate-400 font-bold text-sm">Tài liệu không hỗ trợ xem trước ảnh</div>`;
        }

        if (typeof openModal === 'function') openModal('modal-media-detail');
    }

    btnCopyUrl.addEventListener('click', () => {
        detailUrl.select();
        document.execCommand('copy');
        const oldText = btnCopyUrl.textContent;
        btnCopyUrl.textContent = 'Đã chép!';
        btnCopyUrl.className = 'px-3 py-1.5 bg-emerald-500 text-white rounded-lg text-xs font-semibold transition-colors';
        setTimeout(() => {
            btnCopyUrl.textContent = oldText;
            btnCopyUrl.className = 'px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-semibold hover:bg-blue-700 transition-colors';
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
                const itemEl = document.querySelector(`.media-item[data-id="${currentDetailMedia.id}"]`);
                if (itemEl) itemEl.remove();
                currentTotal--;
                updateTotalIndicator();
                
                // Show empty state if needed
                if (mediaItemsMap.size === 0) {
                    gridEl.className = 'grid grid-cols-1';
                    gridEl.innerHTML = `<div class="col-span-full text-center py-16 text-slate-500 bg-white dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-800">Thư viện trống. Hãy nhấp "Tải tệp tin lên" để thêm file mới!</div>`;
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
