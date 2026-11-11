@extends('layouts.admin')
@section('title', __('Thông báo của tôi'))

@section('content')
    <div id="notification-inbox">
        <x-admin.page-header :title="__('Thông báo của tôi')"
            :subtitle="__('Thông báo mới được cập nhật khi tải trang hoặc mở chuông.')"
            :breadcrumbs="[
                ['label' => __('Bảng điều khiển'), 'url' => route('admin.dashboard')],
                ['label' => __('Thông báo của tôi')],
            ]" />

        <x-admin.filter-tabs :items="$tabs" :current="$tab" route="admin.notifications.index" />

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-2">
            <form method="POST" action="{{ route('admin.notifications.read-all') }}" data-notification-form="read-all">
                @csrf
                <x-admin.button type="submit" variant="primary" data-notification-write>
                    {{ __('Đánh dấu tất cả đã đọc') }}
                </x-admin.button>
            </form>
            <form method="GET" action="{{ route('admin.notifications.index') }}" class="d-flex flex-wrap align-items-center gap-2">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <label for="notification-group" class="small">{{ __('Nhóm thông báo') }}</label>
                <x-admin.select id="notification-group" name="group" class="w-auto" size="sm">
                    <option value="">{{ __('Tất cả nhóm') }}</option>
                    @foreach($groups as $group)
                        <option value="{{ $group['value'] }}" @selected(($filters['group'] ?? '') === $group['value'])>{{ $group['label'] }}</option>
                    @endforeach
                </x-admin.select>
                <x-admin.button type="submit" variant="primary">{{ __('Lọc') }}</x-admin.button>
            </form>
        </div>
        <p class="small text-body-secondary">{{ __('Đánh dấu tất cả áp dụng cho toàn bộ thông báo bạn có quyền xem, không chỉ bộ lọc hiện tại.') }}</p>
        <div id="notification-page-status" role="status" aria-live="polite" tabindex="-1" class="small mb-3"></div>

        <x-admin.table :paginator="$notifications">
            <x-slot:head>
                <x-admin.table-th>{{ __('Nội dung') }}</x-admin.table-th>
                <x-admin.table-th>{{ __('Thời gian') }}</x-admin.table-th>
                <x-admin.table-th>{{ __('Trạng thái') }}</x-admin.table-th>
                <x-admin.table-th>{{ __('Hành động') }}</x-admin.table-th>
            </x-slot:head>
            @forelse($notifications as $notification)
                <tr>
                    <td class="py-3 px-3 text-break">
                        <div class="{{ $notification['read'] ? '' : 'fw-semibold' }}">{{ $notification['title'] }}</div>
                        <div class="small text-body-secondary">{{ $notification['message'] }}</div>
                        <span class="small text-body-secondary">{{ $notification['type_label'] }}</span>
                    </td>
                    <td class="py-3 px-3 small text-body-secondary">
                        <time datetime="{{ $notification['occurred_at'] }}" data-notification-time>{{ $notification['occurred_at'] }}</time>
                    </td>
                    <td class="py-3 px-3">
                        <x-admin.badge :label="$notification['read'] ? __('Đã đọc') : __('Chưa đọc')"
                            :color="$notification['read'] ? 'secondary' : 'primary'" />
                    </td>
                    <td class="py-3 px-3">
                        <div class="d-flex flex-wrap gap-2">
                            <form method="POST" action="{{ $notification['open_url'] }}" data-notification-form="open">
                                @csrf
                                <x-admin.button type="submit" variant="outline-primary" data-notification-write>{{ __('Mở') }}</x-admin.button>
                            </form>
                            @unless($notification['read'])
                                <form method="POST" action="{{ $notification['read_url'] }}" data-notification-form="read">
                                    @csrf
                                    <x-admin.button type="submit" variant="outline-secondary" data-notification-write>{{ __('Đánh dấu đã đọc') }}</x-admin.button>
                                </form>
                            @endunless
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center py-5 text-body-secondary">{{ __('Không có thông báo trong bộ lọc này.') }}</td></tr>
            @endforelse
        </x-admin.table>
    </div>
@endsection
