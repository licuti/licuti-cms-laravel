@extends('layouts.admin')
@section('title', isset($user) ? 'Sửa Người dùng: ' . $user->name : 'Thêm Người dùng mới')

@section('content')
<div class="d-flex flex-column gap-4">
    <x-admin.page-header :title="isset($user) ? 'Sửa thông tin Người dùng' : 'Thêm Người dùng mới'" :subtitle="isset($user) ? 'Cập nhật tài khoản cho: ' . $user->name : 'Tạo tài khoản người dùng mới'" :breadcrumbs="[['label' => 'Bảng điều khiển', 'url' => '/admin/dashboard'], ['label' => 'Người dùng', 'url' => route('admin.users.index')], ['label' => isset($user) ? 'Sửa: ' . $user->name : 'Thêm mới']]" />

    <form action="{{ isset($user) ? route('admin.users.update', $user->uuid) : route('admin.users.store') }}" method="POST" class="d-flex flex-column gap-4">
        @csrf
        @if(isset($user)) @method('PUT') @endif

        <div class="row g-4 align-items-start">
            {{-- Cột Trái (9 phần) --}}
            <div class="col-md-8 col-lg-9 d-flex flex-column gap-4">
                <x-admin.card title="Thông tin Tài khoản">
                    <div class="card-body d-flex flex-column gap-4">
                        {{-- Avatar --}}
                        <div class="d-flex align-items-start gap-4 pb-4 border-bottom">
                            <div style="width:8rem;flex-shrink:0;">
                                @php $avatarMedia = isset($user) ? $user->avatarMedia : null; @endphp
                                <x-admin.image-upload name="avatar" label="Ảnh đại diện" :current="isset($user) ? $user->getAvatarUrl() : null" :current-uuid="$avatarMedia?->uuid" shape="circle" />
                            </div>
                            <div class="pt-4">
                                <h6 class="fw-semibold small mb-1">Ảnh đại diện người dùng</h6>
                                <p class="small text-body-secondary mb-0">Định dạng: JPEG, PNG, WEBP, GIF.<br>Dung lượng tối đa: 5MB. Khuyên dùng ảnh vuông 1:1.</p>
                            </div>
                        </div>

                        <x-admin.form-group label="Họ và tên" name="name" description="Nhập đầy đủ cả họ và tên đệm." required>
                            <x-admin.input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" placeholder="Nhập họ và tên..." required />
                        </x-admin.form-group>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <x-admin.form-group label="Email" name="email" description="Địa chỉ email đăng nhập." required>
                                    <x-admin.input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" placeholder="email@example.com" required>
                                        <x-slot:prefix><svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg></x-slot:prefix>
                                    </x-admin.input>
                                </x-admin.form-group>
                            </div>
                            <div class="col-md-6">
                                <x-admin.form-group label="Số điện thoại" name="phone">
                                    <x-admin.input type="tel" name="phone" value="{{ old('phone', $user->phone ?? '') }}" placeholder="09xx xxx xxx">
                                        <x-slot:prefix><svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg></x-slot:prefix>
                                    </x-admin.input>
                                </x-admin.form-group>
                            </div>
                        </div>

                        @if(!isset($user))
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <x-admin.form-group label="Mật khẩu" name="password" required>
                                        <x-admin.input type="password" name="password" placeholder="Tối thiểu 8 ký tự" required />
                                    </x-admin.form-group>
                                </div>
                                <div class="col-md-6">
                                    <x-admin.form-group label="Xác nhận mật khẩu" name="password_confirmation" required>
                                        <x-admin.input type="password" name="password_confirmation" placeholder="Nhập lại mật khẩu" required />
                                    </x-admin.form-group>
                                </div>
                            </div>
                        @endif
                    </div>
                </x-admin.card>
            </div>

            {{-- Cột Phải (3 phần) --}}
            <div class="col-md-4 col-lg-3 d-flex flex-column gap-4">
                <x-admin.card title="Trạng thái">
                    <div class="card-body">
                        <x-admin.form-group label="Trạng thái tài khoản" name="status">
                            <x-admin.select name="status">
                                @foreach($statuses as $status)
                                    <option value="{{ $status->value }}" {{ old('status', $user->status?->value ?? 'active') === $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
                                @endforeach
                            </x-admin.select>
                        </x-admin.form-group>
                    </div>
                </x-admin.card>

                <x-admin.card title="Phân quyền Vai trò">
                    <div class="card-body d-flex flex-column gap-3">
                        @php
                            $canEditRoles = auth()->user()->hasRole('super_admin') || !isset($user) || (auth()->id() !== $user->id);
                        @endphp
                        @if(!$canEditRoles)
                            <div class="alert alert-warning small py-2 mb-0">Bạn không thể tự sửa vai trò của chính mình.</div>
                        @endif
                        @php $currentUserRoles = old('roles', isset($user) ? $user->roles->pluck('name')->toArray() : []); @endphp
                        <div class="d-flex flex-column gap-2 overflow-y-auto custom-scrollbar" style="max-height:17.5rem;">
                            @forelse($roles as $role)
                                <label class="d-flex align-items-start justify-content-between p-3 rounded border {{ !$canEditRoles ? 'opacity-75' : '' }}" style="cursor:{{ $canEditRoles ? 'pointer' : 'not-allowed' }};background:var(--bs-tertiary-bg);">
                                    <div class="pe-2">
                                        <p class="fw-bold small mb-0">{{ $role->name }}</p>
                                        @if($role->description)<p class="small text-body-secondary mb-0 mt-1" style="font-size:0.75rem;">{{ $role->description }}</p>@endif
                                    </div>
                                    <input type="checkbox" name="roles[]" value="{{ $role->name }}" class="form-check-input mt-1 flex-shrink-0" {{ in_array($role->name, $currentUserRoles) || (!isset($user) && empty($currentUserRoles) && $role->name === 'customer') ? 'checked' : '' }} {{ !$canEditRoles ? 'disabled' : '' }}>
                                    @if(!$canEditRoles && in_array($role->name, $currentUserRoles))<input type="hidden" name="roles[]" value="{{ $role->name }}">@endif
                                </label>
                            @empty
                                <p class="small text-body-tertiary fst-italic text-center py-3 mb-0">Chưa có vai trò nào.</p>
                            @endforelse
                        </div>
                        @error('roles') <div class="invalid-feedback d-block small">{{ $message }}</div> @enderror
                    </div>
                </x-admin.card>

                <div class="d-flex flex-wrap justify-content-end gap-2 pt-3 border-top">
                    <x-admin.button href="{{ route('admin.users.index') }}" variant="secondary"><span>Quay lại</span></x-admin.button>
                    <x-admin.button type="submit" name="submit_action" value="save" variant="primary"><span>Lưu</span></x-admin.button>
                    <x-admin.button type="submit" name="submit_action" value="save_and_edit" variant="outline"><span>Lưu & Sửa</span></x-admin.button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection