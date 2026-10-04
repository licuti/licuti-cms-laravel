@extends('layouts.admin')
@section('title', 'Quản lý Người dùng')

@section('content')
    <x-admin.page-header 
        title="Danh sách Người dùng" 
        subtitle="Quản lý tài khoản, trạng thái và phân quyền vai trò (Role)" 
        :breadcrumbs="[['label' => 'Bảng điều khiển', 'url' => '/admin/dashboard'], ['label' => 'Người dùng']]"
    >
        <x-slot:actions>
            <x-admin.button href="{{ route('admin.users.create') }}" variant="primary" class="flex-shrink-0">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="me-1"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Thêm Người dùng mới</span>
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.filter-tabs :items="$tabs" :current="$tab" route="admin.users.index" />

    {{-- Toolbar: Bulk Actions + Search & Filter Form --}}
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
        {{-- Bulk Actions --}}
        <div class="d-flex align-items-center gap-2">
            <x-admin.select id="bulk-action-select" size="sm" style="width: auto;">
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
            <x-admin.button type="button" id="btn-apply-bulk" variant="primary" class="flex-shrink-0" size="sm">
                Áp dụng
            </x-admin.button>
        </div>

        {{-- Filter & Search Form --}}
        <form action="{{ route('admin.users.index') }}" method="GET" id="form-filter-users" class="d-flex flex-wrap align-items-center gap-2 ms-md-auto">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div style="min-width: 220px;">
                <x-admin.input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="Tìm tên, email, SĐT..." size="sm" />
            </div>
            @if($tab !== 'trash')
                <x-admin.select name="status" size="sm" style="width: auto;">
                    <option value="">Tất cả trạng thái</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </x-admin.select>

                <x-admin.select name="role" size="sm" style="width: auto;">
                    <option value="">Tất cả vai trò</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->name }}" {{ request('role') === $r->name ? 'selected' : '' }}>
                            {{ $r->name }}
                        </option>
                    @endforeach
                </x-admin.select>
            @endif
            <x-admin.button type="submit" variant="primary" size="sm" class="flex-shrink-0">Lọc</x-admin.button>
            @if(request()->hasAny(['keyword', 'status', 'role']))
                <x-admin.button href="{{ route('admin.users.index', ['tab' => $tab]) }}" variant="outline" size="sm" class="flex-shrink-0">Xóa lọc</x-admin.button>
            @endif
        </form>
    </div>

    {{-- Hidden Bulk Action Form --}}
    <form id="form-bulk-action" action="{{ route('admin.users.bulk') }}" method="POST">
        @csrf
        <input type="hidden" name="action" id="bulk-action-input" value="">
    </form>

    {{-- Users Table --}}
    <x-admin.table :paginator="$users">
        <x-slot:head>
            <x-admin.table-th align="center" style="width: 40px;">
                <input type="checkbox" id="check-all" class="form-check-input m-0">
            </x-admin.table-th>
            <x-admin.table-th>Người dùng</x-admin.table-th>
            <x-admin.table-th>SĐT</x-admin.table-th>
            <x-admin.table-th>Vai trò</x-admin.table-th>
            <x-admin.table-th>Trạng thái</x-admin.table-th>
            <x-admin.table-th align="right">Ngày tạo</x-admin.table-th>
        </x-slot:head>

        @forelse($users as $user)
            <tr class="group">
                <td class="py-3 px-3 text-center">
                    <input type="checkbox" name="ids[]" value="{{ $user->id }}" class="row-checkbox form-check-input m-0">
                </td>
                <td class="py-3 px-3">
                    <x-admin.table-cell-primary 
                        :image="$user->getAvatarUrl()" 
                        :title="$user->name" 
                        :subtitle="$user->email" 
                        :actions="$user->availableActions" 
                        imageShape="circle"
                    >
                        @if($user->is_admin)
                            <x-slot:titleSuffix>
                                <x-admin.badge label="Admin" color="red" class="ms-1" />
                            </x-slot:titleSuffix>
                        @endif
                    </x-admin.table-cell-primary>
                </td>
                <td class="py-3 px-3 text-body-secondary small">{{ $user->phone ?: '---' }}</td>
                <td class="py-3 px-3">
                    <div class="d-flex flex-wrap gap-1">
                        @forelse($user->roles as $role)
                            <x-admin.badge :label="$role->name" color="blue" />
                        @empty
                            <span class="small text-body-tertiary fst-italic">Chưa gán</span>
                        @endforelse
                    </div>
                </td>
                <td class="py-3 px-3">
                    <x-admin.badge :label="$user->status->label()" :color="$user->status->color()" />
                </td>
                <td class="py-3 px-3 text-end text-body-secondary small">
                    {{ $user->created_at->format('d/m/Y') }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center py-5 text-body-secondary">
                    {{ $tab === 'trash' ? 'Thùng rác trống.' : 'Không tìm thấy người dùng nào phù hợp.' }}
                </td>
            </tr>
        @endforelse
    </x-admin.table>
@endsection