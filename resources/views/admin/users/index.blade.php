@extends('layouts.admin')
@section('title', 'Quản lý Người dùng')

@section('content')
<div class="d-flex flex-column gap-4">
    <x-admin.page-header title="Danh sách Người dùng" subtitle="Quản lý tài khoản, trạng thái và phân quyền vai trò (Role)" :breadcrumbs="[['label' => 'Bảng điều khiển', 'url' => '/admin/dashboard'], ['label' => 'Người dùng']]">
        <x-slot:actions>
            <x-admin.button href="{{ route('admin.users.create') }}" variant="primary" class="flex-shrink-0">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Thêm Người dùng mới</span>
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.filter-tabs :items="$tabs" :current="$tab" route="admin.users.index" />

    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-2">
            <x-admin.select id="bulk-action-select" class="w-auto fw-medium" size="sm">
                <option value="">Hành động hàng loạt...</option>
                @if($tab === 'trash')
                    <option value="restore">Khôi phục đã chọn</option>
                    <option value="force_delete">Xóa vĩnh viễn đã chọn</option>
                @else
                    <option value="delete">Xóa đã chọn</option>
                    @foreach($statuses as $status)
                        <option value="status_{{ $status->value }}">Chuyển trạng thái: {{ $status->label() }}</option>
                    @endforeach
                @endif
            </x-admin.select>
            <x-admin.button type="button" id="btn-apply-bulk" variant="secondary" class="flex-shrink-0" size="sm">Áp dụng</x-admin.button>
        </div>
        <form action="{{ route('admin.users.index') }}" method="GET" id="form-filter-users" class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2 flex-grow-1" style="max-width:36rem;justify-content:flex-end;">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div class="flex-grow-1" style="min-width:200px;">
                <x-admin.input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="Tìm tên, email, SĐT..." size="sm">
                    <x-slot:prefix><svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg></x-slot:prefix>
                </x-admin.input>
            </div>
            @if($tab !== 'trash')
                <x-admin.select name="status" class="w-auto" size="sm"><option value="">Tất cả trạng thái</option>@foreach($statuses as $status)<option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>@endforeach</x-admin.select>
                <x-admin.select name="role" class="w-auto" size="sm"><option value="">Tất cả vai trò</option>@foreach($roles as $r)<option value="{{ $r->name }}" {{ request('role') === $r->name ? 'selected' : '' }}>{{ $r->name }}</option>@endforeach</x-admin.select>
            @endif
            <x-admin.button type="submit" variant="outline" size="sm" class="flex-shrink-0">Lọc</x-admin.button>
        </form>
    </div>

    <form id="form-bulk-action" action="{{ route('admin.users.bulk') }}" method="POST">@csrf<input type="hidden" name="action" id="bulk-action-input" value=""></form>

    <x-admin.table :paginator="$users">
        <x-slot:head>
            <x-admin.table-th padding="px-3 text-center" align="center"><input type="checkbox" id="check-all" class="form-check-input m-0"></x-admin.table-th>
            <x-admin.table-th padding="px-3">Người dùng</x-admin.table-th>
            <x-admin.table-th padding="px-3">SĐT</x-admin.table-th>
            <x-admin.table-th padding="px-3">Vai trò</x-admin.table-th>
            <x-admin.table-th padding="px-3">Trạng thái</x-admin.table-th>
            <x-admin.table-th padding="px-3">Ngày tạo</x-admin.table-th>
        </x-slot:head>
        @forelse($users as $user)
            <tr class="group">
                <td class="py-3 px-3 text-center"><input type="checkbox" name="ids[]" value="{{ $user->id }}" class="user-checkbox form-check-input m-0"></td>
                <td class="py-3 px-3">
                    <div class="d-flex align-items-center gap-3">
                        @if($user->getAvatarUrl())
                            <img src="{{ $user->getAvatarUrl() }}" alt="{{ $user->name }}" class="rounded-circle object-fit-cover border" style="width:2.25rem;height:2.25rem;">
                        @else
                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold text-uppercase shadow-sm" style="width:2.25rem;height:2.25rem;background:linear-gradient(to top right,#2563eb,#06b6d4);font-size:0.75rem;">{{ substr($user->name, 0, 1) }}</div>
                        @endif
                        <div>
                            <p class="fw-semibold mb-0 small d-flex align-items-center gap-1">{{ $user->name }}@if($user->is_admin)<span class="badge bg-danger small text-uppercase" style="font-size:0.6rem;">Admin</span>@endif</p>
                            <p class="small text-body-secondary mb-0">{{ $user->email }}</p>
                            <x-admin.row-actions :actions="$user->availableActions" />
                        </div>
                    </div>
                </td>
                <td class="py-3 px-3 text-body-secondary small">{{ $user->phone ?? '---' }}</td>
                <td class="py-3 px-3"><div class="d-flex flex-wrap gap-1">@forelse($user->roles as $role)<x-admin.badge :label="$role->name" color="blue" />@empty<span class="small text-body-tertiary fst-italic">Chưa gán</span>@endforelse</div></td>
                <td class="py-3 px-3"><x-admin.badge :label="$user->status->label()" :color="$user->status->color()" /></td>
                <td class="py-3 px-3 text-body-secondary small">{{ $user->created_at->format('d/m/Y') }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center py-5 text-body-secondary">{{ $tab === 'trash' ? 'Thùng rác trống.' : 'Không tìm thấy người dùng nào phù hợp.' }}</td></tr>
        @endforelse
    </x-admin.table>
</div>
@endsection