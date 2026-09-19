@extends('layouts.admin')
@section('title', __('Phân quyền: :name', ['name' => $role->name]))

@section('content')
<div class="d-flex flex-column gap-4">
    <x-admin.page-header
        title="{{ __('Phân quyền chi tiết') }}"
        :subtitle="__('Thiết lập quyền hạn truy cập cho vai trò: :name', ['name' => $role->name])"
        :breadcrumbs="[
            ['label' => __('Bảng điều khiển'), 'url' => route('admin.dashboard')],
            ['label' => __('Vai trò'), 'url' => route('admin.roles.index')],
            ['label' => __('Phân quyền: :name', ['name' => $role->name])],
        ]"
    >
        <x-slot:actions>
            <x-admin.button href="{{ route('admin.roles.index') }}" variant="outline-secondary" size="sm">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>{{ __('Quay lại') }}</span>
            </x-admin.button>
        </x-slot:actions>
    </x-admin.page-header>

    @if($role->name === 'super-admin')
        <div class="alert alert-info d-flex align-items-start gap-2 mb-0">
            <svg class="flex-shrink-0 mt-1" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="small">
                {{ __('Vai trò này luôn giữ toàn bộ quyền hạn. Mọi thay đổi dưới đây sẽ không được áp dụng.') }}
            </span>
        </div>
    @endif

    @php
        // Nhóm permissions theo tiền tố (VD: users.create -> users)
        $groupedPermissions = $permissions->groupBy(function ($perm) {
            return explode('.', $perm->name)[0] ?? 'general';
        });

        // Lấy danh sách tên quyền mà role này đang có
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        $totalPermissions = $permissions->count();
        $selectedPermissions = count(array_intersect($rolePermissions, $permissions->pluck('name')->toArray()));
    @endphp

    <form action="{{ route('admin.roles.permissions.update', $role->id) }}" method="POST" id="form-permissions">
        @csrf
        <div class="row g-4">
            <!-- Cột chính: Accordion quyền hạn -->
            <div class="col-lg-9 col-md-8">
                <x-admin.card title="{{ __('Danh sách Quyền hạn') }}">
                    @if($groupedPermissions->isEmpty())
                        <div class="text-center py-5 text-body-secondary">
                            <div class="d-flex flex-column align-items-center gap-2">
                                <svg width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                <p class="mb-0 small">{{ __('Chưa có quyền nào trong hệ thống.') }}</p>
                            </div>
                        </div>
                    @else
                        <div class="accordion" id="permAccordion">
                            @foreach($groupedPermissions as $group => $perms)
                                @php
                                    $groupKey = \Illuminate\Support\Str::slug($group);
                                    $groupSelected = count(array_intersect($perms->pluck('name')->toArray(), $rolePermissions));
                                    $isOpen = $loop->first;
                                @endphp
                                <div class="accordion-item border-0 border-bottom">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button @if(!$isOpen) collapsed @endif d-flex align-items-center gap-2 py-3" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#collapse-{{ $groupKey }}"
                                                aria-expanded="{{ $isOpen ? 'true' : 'false' }}" aria-controls="collapse-{{ $groupKey }}">
                                            <svg class="text-body-secondary flex-shrink-0" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                                            <span class="fw-bold small text-uppercase text-primary" style="letter-spacing:0.06em;">{{ $group }}</span>
                                            <span class="badge text-bg-secondary bg-opacity-10 border perm-group-badge"
                                                  data-group-badge="{{ $group }}">{{ $groupSelected }}/{{ count($perms) }}</span>
                                        </button>
                                    </h2>
                                    <div id="collapse-{{ $groupKey }}" class="accordion-collapse collapse @if($isOpen) show @endif"
                                         data-bs-parent="#permAccordion">
                                        <div class="accordion-body">
                                            <div class="d-flex align-items-center justify-content-end pb-2 border-bottom">
                                                <div class="form-check form-switch mb-0 d-flex align-items-center gap-2">
                                                    <input class="form-check-input check-group" type="checkbox" role="switch"
                                                           id="group-{{ $groupKey }}" data-group="{{ $group }}">
                                                    <label class="form-check-label small text-body-secondary" for="group-{{ $groupKey }}">{{ __('Chọn cả nhóm') }}</label>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-column gap-1 mt-2">
                                                @foreach($perms as $p)
                                                    <label class="form-check d-flex align-items-start gap-2 py-1 px-2 rounded" for="perm-{{ $p->id }}">
                                                        <input type="checkbox" name="permissions[]" value="{{ $p->name }}" id="perm-{{ $p->id }}"
                                                               class="form-check-input perm-checkbox group-{{ $group }} mt-1"
                                                               @checked(in_array($p->name, $rolePermissions))>
                                                        <span class="d-flex flex-wrap align-items-center gap-1">
                                                            <span class="small fw-medium">{{ $p->name }}</span>
                                                            @if($p->guard_name === 'api')
                                                                <x-admin.badge :label="$p->guard_name" color="amber" />
                                                            @else
                                                                <x-admin.badge :label="$p->guard_name" color="default" />
                                                            @endif
                                                        </span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </x-admin.card>
            </div>

            <!-- Cột phụ: Tổng quan & Lưu -->
            <div class="col-lg-3 col-md-4">
                <div class="d-flex flex-column gap-4 perm-sidebar">
                    <x-admin.card title="{{ __('Tổng quan') }}">
                        <div class="d-flex flex-column gap-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="text-body-secondary small">{{ __('Vai trò:') }}</span>
                                <x-admin.badge :label="$role->name" color="blue" />
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="text-body-secondary small">{{ __('Tổng số quyền:') }}</span>
                                <span class="fw-semibold small">{{ $totalPermissions }}</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="text-body-secondary small">{{ __('Đã chọn:') }}</span>
                                <span class="badge text-bg-primary bg-opacity-10" id="perm-count-badge">{{ $selectedPermissions }} / {{ $totalPermissions }}</span>
                            </div>
                            <hr class="my-1 text-body-tertiary">
                            <div class="form-check form-switch mb-0 d-flex align-items-center gap-2">
                                <input class="form-check-input" type="checkbox" role="switch" id="check-all">
                                <label class="form-check-label small fw-medium" for="check-all">{{ __('Chọn tất cả quyền') }}</label>
                            </div>
                            <p class="text-body-secondary mb-0" style="font-size:0.7rem;">
                                {{ __('Bật/tắt nhanh toàn bộ quyền hạn của hệ thống.') }}
                            </p>
                        </div>
                    </x-admin.card>

                    <x-admin.card>
                        <div class="d-flex flex-column gap-2">
                            <x-admin.button type="submit" variant="primary" class="w-100 justify-content-center">
                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>{{ __('Lưu Phân quyền') }}</span>
                            </x-admin.button>
                            <x-admin.button href="{{ route('admin.roles.index') }}" variant="outline-secondary" class="w-100 justify-content-center">
                                {{ __('Hủy bỏ') }}
                            </x-admin.button>
                        </div>
                    </x-admin.card>

                    <p class="text-body-secondary text-center mb-0" style="font-size:0.7rem;">
                        {{ __('Số quyền đã chọn:') }} <span id="perm-summary-text" class="fw-semibold">{{ $selectedPermissions }}</span>
                    </p>
                </div>
            </div>
        </div>
    </form>
</div>

@pushOnce('styles')
<style>
    @media (min-width: 992px) {
        .perm-sidebar { position: sticky; top: 1rem; }
    }
    .perm-group-badge { font-size: 0.65rem; }
</style>
@endPushOnce

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const checkAll = document.getElementById('check-all');
        const countBadge = document.getElementById('perm-count-badge');
        const summaryText = document.getElementById('perm-summary-text');
        const permCheckboxes = document.querySelectorAll('.perm-checkbox');
        const groupChecks = document.querySelectorAll('.check-group');
        const groupBadges = document.querySelectorAll('[data-group-badge]');
        const total = permCheckboxes.length;
        const labelSelected = '{{ __("quyền được chọn") }}';

        function countInGroup(group) {
            const items = document.querySelectorAll('.perm-checkbox.group-' + CSS.escape(group));
            const checked = document.querySelectorAll('.perm-checkbox.group-' + CSS.escape(group) + ':checked');
            return { total: items.length, checked: checked.length };
        }

        function updateCounters() {
            const checked = document.querySelectorAll('.perm-checkbox:checked').length;
            if (countBadge) countBadge.textContent = checked + ' / ' + total;
            if (summaryText) summaryText.textContent = checked;
            if (checkAll) checkAll.checked = total > 0 && checked === total;

            // Cập nhật badge từng nhóm + màu theo tỉ lệ chọn
            groupBadges.forEach(badge => {
                const g = badge.dataset.groupBadge;
                const c = countInGroup(g);
                badge.textContent = c.checked + '/' + c.total;
                badge.classList.remove('text-bg-secondary', 'text-bg-primary', 'text-bg-success');
                if (c.total > 0 && c.total === c.checked) {
                    badge.classList.add('text-bg-success');
                } else if (c.checked > 0) {
                    badge.classList.add('text-bg-primary');
                } else {
                    badge.classList.add('text-bg-secondary');
                }
            });
        }

        function updateGroupState(group) {
            const groupCheck = document.querySelector('.check-group[data-group="' + group + '"]');
            if (!groupCheck) return;
            const c = countInGroup(group);
            groupCheck.checked = c.total > 0 && c.total === c.checked;
        }

        // Chọn tất cả
        if (checkAll) {
            checkAll.addEventListener('change', function () {
                const isChecked = this.checked;
                permCheckboxes.forEach(cb => cb.checked = isChecked);
                groupChecks.forEach(g => g.checked = isChecked);
                updateCounters();
            });
        }

        // Chọn theo nhóm
        groupChecks.forEach(groupCheck => {
            groupCheck.addEventListener('change', function () {
                const group = this.dataset.group;
                const isChecked = this.checked;
                document.querySelectorAll('.perm-checkbox.group-' + CSS.escape(group)).forEach(cb => cb.checked = isChecked);
                updateCounters();
            });
        }

        // Chọn từng quyền
        permCheckboxes.forEach(cb => {
            cb.addEventListener('change', function () {
                const groupClass = Array.from(this.classList).find(c => c.startsWith('group-'));
                if (groupClass) updateGroupState(groupClass.replace('group-', ''));
                updateCounters();
            });
        });

        // Init trạng thái nhóm + counter khi load
        groupChecks.forEach(groupCheck => updateGroupState(groupCheck.dataset.group));
        updateCounters();
    });
</script>
@endpush
