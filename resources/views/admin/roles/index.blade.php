@extends('layouts.admin')
@section('title', __('Vai trò & Phân quyền (RBAC)'))

@section('content')
<div class="d-flex flex-column gap-4">
    <x-admin.page-header
        title="{{ __('Quản lý Vai trò & Quyền hạn') }}"
        subtitle="{{ __('Định nghĩa nhóm vai trò và gán chi tiết các quyền hạn truy cập trong hệ thống') }}"
        :breadcrumbs="[
            ['label' => __('Bảng điều khiển'), 'url' => route('admin.dashboard')],
            ['label' => __('Vai trò & Quyền hạn')],
        ]"
    >
        <x-slot:actions>
            <x-admin.button type="button" onclick="openModal('modal-add-role')" variant="primary">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                <span>{{ __('Tạo Vai trò mới') }}</span>
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="row g-4" id="roles-grid">
        @forelse($roles as $role)
            @php
                $isProtected = in_array($role->name, ['super-admin', 'admin', 'customer']);
                $permCount = $role->permissions_count ?? $role->permissions()->count();
                $userCount = $role->users_count ?? $role->users()->count();
            @endphp
            <div class="col-md-6 col-xl-4">
                <div class="card border shadow-sm h-100">
                    <div class="card-body d-flex flex-column gap-3">
                        <div class="d-flex align-items-start justify-content-between gap-3">
                            <div class="min-w-0">
                                <x-admin.badge :label="$role->name" color="blue" />
                                @if($isProtected)
                                    <x-admin.badge :label="__('Hệ thống')" color="amber" />
                                @endif
                                @if($role->name === 'super-admin')
                                    <x-admin.badge :label="__('Toàn quyền')" color="green" />
                                @endif
                            </div>
                            <span class="d-inline-flex align-items-center justify-content-center rounded bg-body-tertiary border text-body-secondary flex-shrink-0" style="width:2.5rem;height:2.5rem;">
                                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </span>
                        </div>

                        <div class="d-flex flex-column gap-2 small">
                            <div class="d-flex align-items-center justify-content-between border-bottom pb-2">
                                <span class="text-body-secondary">{{ __('Quyền hạn được gán:') }}</span>
                                <span class="fw-bold text-primary">{{ $permCount }} {{ __('quyền') }}</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="text-body-secondary">{{ __('Tài khoản đang dùng:') }}</span>
                                <span class="fw-bold">{{ $userCount }} {{ __('tài khoản') }}</span>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-2 mt-auto pt-2 border-top">
                            <x-admin.button href="{{ route('admin.roles.permissions', $role->id) }}" variant="outline-primary" size="sm" class="flex-grow-1">
                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                <span>{{ __('Phân quyền') }}</span>
                            </x-admin.button>

                            <button type="button" onclick="openModal('modal-edit-{{ $role->id }}')"
                                    class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center justify-content-center"
                                    style="width:2.25rem;height:2.25rem;" title="{{ __('Sửa') }}"
                                    aria-label="{{ __('Sửa') }}">
                                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>

                            @if($isProtected)
                                <button type="button" disabled
                                        class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center justify-content-center opacity-50"
                                        style="width:2.25rem;height:2.25rem;" title="{{ __('Vai trò hệ thống không thể xóa') }}"
                                        aria-label="{{ __('Xóa') }}">
                                    <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            @else
                                <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" class="d-inline-block form-confirm"
                                      data-confirm-title="{{ __('Xóa vai trò?') }}"
                                      data-confirm-text="{{ __('Thao tác này sẽ gỡ vai trò khỏi các tài khoản liên quan!') }}"
                                      data-confirm-btn="{{ __('Xóa ngay') }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm d-inline-flex align-items-center justify-content-center" style="width:2.25rem;height:2.25rem;" title="{{ __('Xóa') }}" aria-label="{{ __('Xóa') }}">
                                        <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Sửa Role -->
            <x-admin.modal id="modal-edit-{{ $role->id }}" title="{{ __('Sửa Vai trò') }}">
                <form action="{{ route('admin.roles.update', $role->id) }}" method="POST" id="form-edit-{{ $role->id }}">
                    @csrf
                    @method('PUT')
                    <x-admin.form-group label="{{ __('Tên Vai trò') }}" name="name" required
                                        description="{{ __('Ví dụ: editor, shop_manager. Sử dụng chữ thường và dấu gạch dưới.') }}">
                        <x-admin.input type="text" name="name" value="{{ old('name', $role->name) }}" required
                                       :readonly="$isProtected" />
                    </x-admin.form-group>
                    @if($isProtected)
                        <div class="alert alert-warning d-flex align-items-center py-2 small">
                            <svg class="me-2 flex-shrink-0" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>{{ __('Đây là vai trò hệ thống, không thể đổi tên.') }}</span>
                        </div>
                    @endif
                </form>
                <x-slot:footer>
                    <x-admin.button type="button" variant="outline-secondary" size="sm" onclick="closeModal('modal-edit-{{ $role->id }}')">{{ __('Hủy') }}</x-admin.button>
                    <x-admin.button type="submit" form="form-edit-{{ $role->id }}" variant="primary" size="sm">{{ __('Cập nhật') }}</x-admin.button>
                </x-slot:footer>
            </x-admin.modal>
        @empty
            <div class="col-12">
                <x-admin.card>
                    <div class="text-center py-5">
                        <div class="d-flex flex-column align-items-center gap-2 text-body-secondary">
                            <svg width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            <p class="mb-0 fw-medium">{{ __('Chưa có vai trò nào trong hệ thống') }}</p>
                            <p class="mb-0 small">{{ __('Bấm "Tạo Vai trò mới" để bắt đầu.') }}</p>
                        </div>
                    </div>
                </x-admin.card>
            </div>
        @endforelse
    </div>
</div>

<!-- Modal Thêm Role -->
<x-admin.modal id="modal-add-role" title="{{ __('Tạo Vai trò mới') }}">
    <form action="{{ route('admin.roles.store') }}" method="POST" id="form-add-role">
        @csrf
        <x-admin.form-group label="{{ __('Tên Vai trò') }}" name="name" required
                            description="{{ __('Ví dụ: editor, shop_manager. Sử dụng chữ thường và dấu gạch dưới.') }}">
            <x-admin.input type="text" name="name" value="{{ old('name') }}" required placeholder="editor" />
        </x-admin.form-group>
    </form>
    <x-slot:footer>
        <x-admin.button type="button" variant="outline-secondary" size="sm" onclick="closeModal('modal-add-role')">{{ __('Hủy') }}</x-admin.button>
        <x-admin.button type="submit" form="form-add-role" variant="primary" size="sm">{{ __('Tạo mới') }}</x-admin.button>
    </x-slot:footer>
</x-admin.modal>
@endsection
