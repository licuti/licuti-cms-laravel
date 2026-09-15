@extends('layouts.admin')
@section('title', 'Phân quyền: ' . $role->name)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <x-admin.page-header 
        title="Phân quyền chi tiết" 
        :subtitle="'Thiết lập quyền hạn truy cập cho vai trò: ' . $role->name"
        :breadcrumbs="[
            ['label' => 'Bảng điều khiển', 'url' => '/admin/dashboard'], 
            ['label' => 'Vai trò', 'url' => route('admin.roles.index')],
            ['label' => 'Phân quyền: ' . $role->name]
        ]"
    />

    <form action="{{ route('admin.roles.permissions.update', $role->id) }}" method="POST">
        @csrf
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">Danh sách Quyền hạn</h3>
                    <p class="text-sm text-slate-500 mt-1">Chọn các quyền bên dưới để cấp cho vai trò <strong class="text-blue-600 dark:text-blue-400">{{ $role->name }}</strong>.</p>
                </div>
                <div>
                    <label class="flex items-center gap-2 text-sm cursor-pointer hover:text-blue-500 font-medium text-slate-600 dark:text-slate-300">
                        <input type="checkbox" id="check-all" class="w-4 h-4 rounded text-blue-600 focus:ring-0">
                        <span>Chọn tất cả</span>
                    </label>
                </div>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 bg-slate-50/50 dark:bg-slate-900/50">
                @php
                    // Nhóm permissions theo tiền tố (VD: users.create -> users)
                    $groupedPermissions = $permissions->groupBy(function($perm) {
                        return explode('.', $perm->name)[0] ?? 'general';
                    });
                    
                    // Lấy danh sách tên quyền mà role này đang có
                    $rolePermissions = $role->permissions->pluck('name')->toArray();
                @endphp

                @forelse($groupedPermissions as $group => $perms)
                    <div class="bg-white dark:bg-slate-800 p-5 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
                        <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-100 dark:border-slate-700">
                            <h4 class="font-bold text-xs uppercase tracking-widest text-blue-600 dark:text-blue-400">
                                MODULE {{ strtoupper($group) }}
                            </h4>
                            <label class="text-[10px] uppercase font-bold text-slate-400 hover:text-blue-500 cursor-pointer flex items-center gap-1">
                                <input type="checkbox" class="check-group w-3 h-3 rounded text-blue-600" data-group="{{ $group }}">
                                All
                            </label>
                        </div>
                        <div class="space-y-2">
                            @foreach($perms as $p)
                                <label class="flex items-start gap-3 cursor-pointer group">
                                    <div class="pt-0.5">
                                        <input type="checkbox" name="permissions[]" value="{{ $p->name }}" 
                                            class="perm-checkbox group-{{ $group }} w-4 h-4 rounded text-blue-600 border-slate-300 focus:ring-blue-500 dark:border-slate-600 dark:bg-slate-700"
                                            @checked(in_array($p->name, $rolePermissions))>
                                    </div>
                                    <div class="flex-1">
                                        <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                            {{ $p->name }} 
                                            <span class="inline-block ml-1 px-1.5 py-0.5 text-[10px] uppercase font-bold rounded-full {{ $p->guard_name === 'api' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' : 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400' }}">
                                                {{ $p->guard_name }}
                                            </span>
                                        </span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-8 text-slate-500">
                        Chưa có quyền nào trong hệ thống.
                    </div>
                @endforelse
            </div>

            <div class="p-6 border-t border-slate-200 dark:border-slate-800 flex justify-end gap-3 bg-white dark:bg-slate-900">
                <a href="{{ route('admin.roles.index') }}" class="px-4 py-2 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 font-medium text-sm transition-colors">
                    Hủy bỏ
                </a>
                <x-admin.button type="submit" variant="primary">
                    Lưu Phân quyền
                </x-admin.button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Chọn tất cả
        $('#check-all').on('change', function() {
            const isChecked = $(this).prop('checked');
            $('.perm-checkbox').prop('checked', isChecked);
            $('.check-group').prop('checked', isChecked);
        });

        // Chọn theo group
        $('.check-group').on('change', function() {
            const isChecked = $(this).prop('checked');
            const group = $(this).data('group');
            $(`.group-${group}`).prop('checked', isChecked);
            updateCheckAllStatus();
        });

        // Chọn từng cái
        $('.perm-checkbox').on('change', function() {
            const groupClass = Array.from(this.classList).find(c => c.startsWith('group-'));
            if (groupClass) {
                const group = groupClass.replace('group-', '');
                const totalInGroup = $(`.${groupClass}`).length;
                const checkedInGroup = $(`.${groupClass}:checked`).length;
                $(`.check-group[data-group="${group}"]`).prop('checked', totalInGroup === checkedInGroup);
            }
            updateCheckAllStatus();
        });

        function updateCheckAllStatus() {
            const total = $('.perm-checkbox').length;
            const checked = $('.perm-checkbox:checked').length;
            $('#check-all').prop('checked', total > 0 && total === checked);
        }

        // Init check all status
        $('.check-group').each(function() {
            const group = $(this).data('group');
            const totalInGroup = $(`.group-${group}`).length;
            const checkedInGroup = $(`.group-${group}:checked`).length;
            if (totalInGroup > 0) {
                $(this).prop('checked', totalInGroup === checkedInGroup);
            }
        });
        updateCheckAllStatus();
    });
</script>
@endpush
