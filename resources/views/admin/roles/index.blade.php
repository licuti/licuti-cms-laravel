@extends('layouts.admin')
@section('title', 'Vai trò & Phân quyền (RBAC)')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <x-admin.page-header 
        title="Quản lý Vai trò (Roles) & Quyền hạn" 
        subtitle="Định nghĩa nhóm vai trò và gán chi tiết các quyền hạn truy cập trong hệ thống"
        :breadcrumbs="[
            ['label' => 'Bảng điều khiển', 'url' => '/admin/dashboard'], 
            ['label' => 'Vai trò & Quyền hạn']
        ]"
    >
        <x-admin.button type="button" onclick="openModal('modal-add-role')" variant="primary" class="shrink-0">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Tạo Vai trò mới</span>
        </x-admin.button>
    </x-admin.page-header>

    <!-- Roles Grid -->
    <div id="roles-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($roles as $role)
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg p-6 shadow-sm flex flex-col justify-between hover:border-blue-500/50 transition-all">
                <div>
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <span class="px-2.5 py-1 rounded-md text-xs font-bold uppercase tracking-wider bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20">
                                {{ $role->name }}
                            </span>
                            <h3 class="mt-3 text-lg font-bold text-slate-900 dark:text-slate-100">{{ $role->name }}</h3>
                            <p class="mt-1 text-xs text-slate-500 min-h-[32px]">{{ $role->description ?? 'Không có mô tả' }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-lg bg-slate-50 dark:bg-slate-800 text-slate-400 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                    </div>

                    <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs text-slate-500">
                        <span>Quyền hạn được gán:</span>
                        <span class="font-bold text-blue-600 dark:text-blue-400">{{ $role->permissions_count ?? $role->permissions()->count() }} quyền</span>
                    </div>
                </div>

                <div class="mt-6 flex items-center gap-2">
                    <a href="{{ route('admin.roles.permissions', $role->id) }}" class="flex-1 py-2 px-3 rounded-lg bg-blue-50 dark:bg-blue-950/40 hover:bg-blue-100 dark:hover:bg-blue-900/60 text-blue-600 dark:text-blue-400 font-semibold text-xs transition-colors flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <span>Phân quyền</span>
                    </a>
                    <button type="button" onclick="openModal('modal-edit-{{ $role->id }}')" class="p-2 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:text-amber-500 transition-colors" title="Sửa">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                    @if(in_array($role->name, ['super-admin', 'admin', 'customer']))
                        <button type="button" disabled class="opacity-30 cursor-not-allowed p-2 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    @else
                        <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" class="inline-block form-confirm" data-confirm-title="Xóa vai trò?" data-confirm-text="Thao tác này sẽ gỡ vai trò khỏi các tài khoản liên quan!" data-confirm-btn="Xóa ngay">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:text-red-500 transition-colors" title="Xóa">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Modal Sửa Role -->
            <x-admin.modal id="modal-edit-{{ $role->id }}" title="Sửa Vai trò">
                <form action="{{ route('admin.roles.update', $role->id) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <x-admin.form-group label="Tên Vai trò (Ví dụ: editor, shop_manager)" name="name" required>
                        <x-admin.input type="text" name="name" value="{{ old('name', $role->name) }}" required :readonly="in_array($role->name, ['super-admin', 'admin', 'customer'])" />
                    </x-admin.form-group>
                    <x-admin.form-group label="Mô tả chi tiết" name="description">
                        <textarea name="description" rows="3" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:border-blue-500">{{ old('description', $role->description) }}</textarea>
                    </x-admin.form-group>
                    <div class="pt-4 flex justify-end gap-2 border-t border-slate-200 dark:border-slate-800">
                        <x-admin.button type="button" variant="secondary" onclick="closeModal('modal-edit-{{ $role->id }}')">Hủy</x-admin.button>
                        <x-admin.button type="submit" variant="primary">Cập nhật Vai trò</x-admin.button>
                    </div>
                </form>
            </x-admin.modal>
        @empty
            <div class="col-span-full text-center py-12 text-slate-500 bg-white dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-800">
                Chưa có vai trò nào trong hệ thống.
            </div>
        @endforelse
    </div>
</div>

<!-- Modal Thêm Role -->
<x-admin.modal id="modal-add-role" title="Tạo Vai trò mới">
    <form action="{{ route('admin.roles.store') }}" method="POST" class="space-y-4">
        @csrf
        <x-admin.form-group label="Tên Vai trò (Ví dụ: editor, shop_manager)" name="name" required>
            <x-admin.input type="text" name="name" value="{{ old('name') }}" required />
        </x-admin.form-group>
        <x-admin.form-group label="Mô tả chi tiết" name="description">
            <textarea name="description" rows="3" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:border-blue-500">{{ old('description') }}</textarea>
        </x-admin.form-group>
        <div class="pt-4 flex justify-end gap-2 border-t border-slate-200 dark:border-slate-800">
            <x-admin.button type="button" variant="secondary" onclick="closeModal('modal-add-role')">Hủy</x-admin.button>
            <x-admin.button type="submit" variant="primary">Lưu Vai trò</x-admin.button>
        </div>
    </form>
</x-admin.modal>
@endsection
