@extends('layouts.admin')

@php
    $paymentMethod = $paymentMethod ?? null;
    $isEdit = !is_null($paymentMethod);
    $currentCode = old('code', $paymentMethod?->code ?? 'vnpay');
    $currentConfig = old('config', $paymentMethod?->config ?? []);
    if (is_string($currentConfig)) {
        $decoded = json_decode($currentConfig, true);
        $currentConfig = is_array($decoded) ? $decoded : [];
    }
    $gatewayOptions = $gatewayOptions ?? [];
    $gatewaySchemas = $gatewaySchemas ?? [];
@endphp

@section('title', $isEdit ? __('Sửa Phương thức: :name', ['name' => $paymentMethod->name]) : __('Thêm Phương thức Thanh toán mới'))

@section('content')
    {{-- 1. Page Header --}}
    <x-admin.page-header 
        :title="$isEdit ? __('Sửa Phương thức: :name', ['name' => $paymentMethod->name]) : __('Thêm Phương thức Thanh toán mới')" 
        :breadcrumbs="[
            ['label' => __('Bảng điều khiển'), 'url' => route('admin.dashboard')], 
            ['label' => __('Phương thức thanh toán'), 'url' => route('admin.payment-methods.index')],
            ['label' => $isEdit ? __('Sửa') : __('Thêm mới')]
        ]" 
    />

    {{-- 2. Form soạn thảo chính --}}
    <form 
        id="payment-method-form" 
        action="{{ $isEdit ? route('admin.payment-methods.update', $paymentMethod->uuid) : route('admin.payment-methods.store') }}" 
        method="POST"
    >
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif
        
        <div class="row align-items-start g-4">

            {{-- ======================================================== --}}
            {{-- CỘT TRÁI: THÔNG TIN PHƯƠNG THỨC & CẤU HÌNH API          --}}
            {{-- ======================================================== --}}
            <div class="col-md-8 col-lg-9 d-flex flex-column gap-4">

                {{-- CARD 1: THÔNG TIN CƠ BẢN --}}
                <x-admin.card :title="__('Thông tin phương thức')">
                    <div class="d-flex flex-column gap-3">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <x-admin.form-group 
                                    :label="__('Mã cổng (Code / Driver)')" 
                                    name="code" 
                                    required
                                    :description="__('Chọn cổng có sẵn từ Registry hoặc nhập mã kỹ thuật riêng.')"
                                    class="mb-0"
                                >
                                    @if($isEdit)
                                        <x-admin.input 
                                            type="text" 
                                            name="code" 
                                            size="sm"
                                            :value="$paymentMethod->code" 
                                            readonly
                                            required
                                        />
                                    @else
                                        <div class="input-group input-group-sm">
                                            <input 
                                                type="text" 
                                                id="code-input"
                                                name="code" 
                                                value="{{ $currentCode }}" 
                                                class="form-control form-control-sm font-monospace" 
                                                placeholder="vnpay"
                                                required
                                            >
                                            @if(!empty($gatewayOptions))
                                                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    {{ __('Chọn mẫu') }}
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                    @foreach($gatewayOptions as $gCode => $gName)
                                                        <li>
                                                            <a class="dropdown-item select-gateway-preset" href="#" data-code="{{ $gCode }}" data-name="{{ $gName }}">
                                                                <span class="fw-semibold">{{ $gName }}</span>
                                                                <span class="text-body-secondary small ms-1">({{ $gCode }})</span>
                                                            </a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </div>
                                    @endif
                                </x-admin.form-group>
                            </div>

                            <div class="col-md-6">
                                <x-admin.form-group 
                                    :label="__('Tên hiển thị')" 
                                    name="name" 
                                    required
                                    :description="__('Tên hiển thị với khách hàng tại bước thanh toán.')"
                                    class="mb-0"
                                >
                                    <x-admin.input 
                                        type="text" 
                                        name="name" 
                                        id="name-input"
                                        size="sm" 
                                        :value="old('name', $paymentMethod?->name)" 
                                        placeholder="{{ __('Thanh toán VNPAY (QR / ATM / Visa)') }}"
                                        required
                                    />
                                </x-admin.form-group>
                            </div>
                        </div>

                        <x-admin.form-group 
                            :label="__('Mô tả ngắn')" 
                            name="description" 
                            :description="__('Mô tả tóm tắt tính năng phương thức.')"
                        >
                            <x-admin.textarea 
                                name="description" 
                                id="description-input"
                                size="sm" 
                                rows="2" 
                                placeholder="{{ __('Ví dụ: Thanh toán trực tuyến an toàn qua cổng VNPAY...') }}"
                            >{{ old('description', $paymentMethod?->description) }}</x-admin.textarea>
                        </x-admin.form-group>

                        <x-admin.form-group 
                            :label="__('Hướng dẫn thanh toán cho khách hàng')" 
                            name="instructions" 
                            :description="__('Hiển thị thông tin ghi chú hoặc hướng dẫn chi tiết (STK ngân hàng, lưu ý nhận hàng...) tại trang checkout và hóa đơn.')"
                            class="mb-0"
                        >
                            <x-admin.textarea 
                                name="instructions" 
                                size="sm" 
                                rows="3" 
                                placeholder="{{ __('Ví dụ: Vui lòng chuyển khoản đến STK 123456789 - Vietcombank. Nội dung: [MÃ ĐƠN HÀNG]...') }}"
                            >{{ old('instructions', $paymentMethod?->instructions) }}</x-admin.textarea>
                        </x-admin.form-group>
                    </div>
                </x-admin.card>

                {{-- CARD 2: CẤU HÌNH KẾT NỐI API & SECRET --}}
                <x-admin.card :title="__('Cấu hình kết nối Cổng (API Credentials)')">
                    <x-slot:headerActions>
                        <span class="badge bg-success-subtle text-success small d-flex align-items-center gap-1">
                            <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            <span>{{ __('Mã hóa AES-256') }}</span>
                        </span>
                    </x-slot:headerActions>

                    <div class="d-flex flex-column gap-3">
                        {{-- Container render form động theo từng Gateway Driver --}}
                        <div id="dynamic-gateway-fields">
                            @foreach($gatewaySchemas as $gCode => $schema)
                                <div class="gateway-schema-panel {{ $currentCode === $gCode ? '' : 'd-none' }}" id="schema-panel-{{ $gCode }}">
                                    <div class="alert alert-light border small text-body-secondary py-2 px-3 mb-3">
                                        <strong>{{ $schema['name'] }}:</strong> {{ $schema['description'] }}
                                    </div>
                                    <div class="row g-3">
                                        @foreach($schema['fields'] as $fName => $field)
                                            <div class="col-md-{{ in_array($field['type'], ['textarea', 'switch']) ? '12' : '6' }}">
                                                @if($field['type'] === 'switch')
                                                    <x-admin.toggle
                                                        name="config[{{ $fName }}]"
                                                        :label="$field['label']"
                                                        :description="$field['description'] ?? null"
                                                        :checked="old('config.'.$fName, $currentConfig[$fName] ?? ($field['default'] ?? false))"
                                                    />
                                                @elseif($field['type'] === 'select')
                                                    <x-admin.form-group :label="$field['label']" name="config[{{ $fName }}]" class="mb-0">
                                                        <x-admin.select name="config[{{ $fName }}]" size="sm">
                                                            @foreach($field['options'] as $optVal => $optLabel)
                                                                <option value="{{ $optVal }}" @selected((string)old('config.'.$fName, $currentConfig[$fName] ?? ($field['default'] ?? '')) === (string)$optVal)>
                                                                    {{ $optLabel }}
                                                                </option>
                                                            @endforeach
                                                        </x-admin.select>
                                                    </x-admin.form-group>
                                                @elseif($field['type'] === 'password')
                                                    <x-admin.form-group :label="$field['label']" name="config[{{ $fName }}]" class="mb-0">
                                                        <x-admin.input 
                                                            type="password" 
                                                            name="config[{{ $fName }}]" 
                                                            size="sm"
                                                            :value="old('config.'.$fName, $currentConfig[$fName] ?? '')"
                                                            :placeholder="$field['placeholder'] ?? '••••••••••••••••'"
                                                        />
                                                    </x-admin.form-group>
                                                @else
                                                    <x-admin.form-group :label="$field['label']" name="config[{{ $fName }}]" class="mb-0">
                                                        <x-admin.input 
                                                            type="text" 
                                                            name="config[{{ $fName }}]" 
                                                            size="sm"
                                                            :value="old('config.'.$fName, $currentConfig[$fName] ?? ($field['default'] ?? ''))"
                                                            :placeholder="$field['placeholder'] ?? ''"
                                                        />
                                                    </x-admin.form-group>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Fallback: Editor JSON raw cho cổng tùy biến hoặc nâng cao --}}
                        <div class="border-top pt-3 mt-2">
                            <a class="text-decoration-none small text-body-secondary d-inline-flex align-items-center gap-1" data-bs-toggle="collapse" href="#collapseRawJson" role="button" aria-expanded="false">
                                <span>{{ __('Chỉnh sửa trực tiếp JSON thô (Tùy chọn nâng cao)') }}</span>
                                <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </a>
                            <div class="collapse mt-2" id="collapseRawJson">
                                <x-admin.form-group 
                                    :label="__('Dữ liệu JSON tùy biến')" 
                                    name="raw_config" 
                                    :description="__('Dữ liệu nhập tại đây sẽ được tự động mã hóa AES-256 an toàn trong CSDL.')"
                                    class="mb-0"
                                >
                                    <x-admin.textarea 
                                        name="config" 
                                        id="raw-config-textarea"
                                        size="sm" 
                                        rows="4" 
                                        class="font-monospace"
                                        placeholder='{&quot;key&quot;: &quot;value&quot;}'
                                    >{{ !empty($currentConfig) ? json_encode($currentConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '' }}</x-admin.textarea>
                                </x-admin.form-group>
                            </div>
                        </div>
                    </div>
                </x-admin.card>

                {{-- CARD 3: ĐIỀU KIỆN ÁP DỤNG & GIỚI HẠN --}}
                <x-admin.card :title="__('Điều kiện & Giới hạn áp dụng')">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <x-admin.form-group 
                                :label="__('Đơn hàng tối thiểu (₫)')" 
                                name="min_order_amount" 
                                :description="__('Đơn hàng phải đạt mức này mới được dùng phương thức.')"
                                class="mb-0"
                            >
                                <x-admin.input 
                                    type="number" 
                                    step="1000" 
                                    name="min_order_amount" 
                                    size="sm" 
                                    :value="old('min_order_amount', $paymentMethod?->min_order_amount)" 
                                    placeholder="0"
                                />
                            </x-admin.form-group>
                        </div>

                        <div class="col-md-6">
                            <x-admin.form-group 
                                :label="__('Đơn hàng tối đa (₫)')" 
                                name="max_order_amount" 
                                :description="__('Hạn mức tối đa được phép dùng (ví dụ hạn mức COD).')"
                                class="mb-0"
                            >
                                <x-admin.input 
                                    type="number" 
                                    step="1000" 
                                    name="max_order_amount" 
                                    size="sm" 
                                    :value="old('max_order_amount', $paymentMethod?->max_order_amount)" 
                                    placeholder="{{ __('Không giới hạn') }}"
                                />
                            </x-admin.form-group>
                        </div>
                    </div>
                </x-admin.card>

            </div>

            {{-- ======================================================== --}}
            {{-- CỘT PHẢI: CÀI ĐẶT SIDEBAR                                --}}
            {{-- ======================================================== --}}
            <div class="col-md-4 col-lg-3 d-flex flex-column gap-4">

                {{-- HỘP 1: XUẤT BẢN & TRẠNG THÁI --}}
                <x-admin.publish-box
                    :title="__('Xuất bản')"
                    status-name="is_active"
                    :status="$paymentMethod ? (int) $paymentMethod->is_active : 1"
                    :statuses="[1 => __('Hoạt động'), 0 => __('Tạm tắt')]"
                    :show-published-at="false"
                    :index-route="route('admin.payment-methods.index')"
                    :save-label="$isEdit ? __('Cập nhật') : __('Lưu phương thức')"
                />

                {{-- HỘP 2: THỨ TỰ HIỂN THỊ --}}
                <x-admin.card :title="__('Thứ tự hiển thị')">
                    <x-admin.form-group 
                        :label="__('Chỉ số sắp xếp')" 
                        name="display_order" 
                        :description="__('Số nhỏ hơn sẽ hiển thị trước tại trang thanh toán (0, 1, 2...).')"
                        class="mb-0"
                    >
                        <x-admin.input 
                            type="number" 
                            name="display_order" 
                            size="sm" 
                            :value="old('display_order', $paymentMethod?->display_order ?? 0)" 
                            min="0"
                        />
                    </x-admin.form-group>
                </x-admin.card>

            </div>
        </div>
    </form>

    {{-- Script hỗ trợ chọn Preset Gateway và chuyển đổi panel schema động --}}
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const codeInput = document.getElementById('code-input');
            const nameInput = document.getElementById('name-input');
            const descInput = document.getElementById('description-input');
            const panels = document.querySelectorAll('.gateway-schema-panel');

            function switchGatewayPanel(code) {
                const targetCode = (code || '').trim().toLowerCase();
                let matched = false;
                panels.forEach(p => {
                    if (p.id === 'schema-panel-' + targetCode) {
                        p.classList.remove('d-none');
                        matched = true;
                    } else {
                        p.classList.add('d-none');
                    }
                });

                // Nếu không khớp schema nào, tự động mở rộng ô JSON raw
                const collapseRaw = document.getElementById('collapseRawJson');
                if (!matched && collapseRaw) {
                    const bsCollapse = bootstrap.Collapse.getOrCreateInstance(collapseRaw);
                    bsCollapse.show();
                }
            }

            if (codeInput) {
                codeInput.addEventListener('input', function () {
                    switchGatewayPanel(this.value);
                });
            }

            document.querySelectorAll('.select-gateway-preset').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    const code = this.dataset.code;
                    const name = this.dataset.name;
                    if (codeInput) {
                        codeInput.value = code;
                        codeInput.dispatchEvent(new Event('input'));
                    }
                    if (nameInput && (!nameInput.value || nameInput.value === '')) {
                        nameInput.value = name;
                    }
                });
            });
        });
    </script>
    @endpush
@endsection