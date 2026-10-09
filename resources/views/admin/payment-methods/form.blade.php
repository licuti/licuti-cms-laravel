@extends('layouts.admin')

@section('title', isset($paymentMethod) ? 'Sửa cổng thanh toán' : 'Thêm cổng thanh toán')

@section('content')
    <x-admin.page-header :title="isset($paymentMethod) ? 'Sửa phương thức: ' . $paymentMethod->name : 'Thêm phương thức thanh toán'">
        <x-slot name="actions">
            <a href="{{ route('admin.payment-methods.index') }}" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-left"></i> Quay lại
            </a>
        </x-slot>
    </x-admin.page-header>

    <div class="card">
        <div class="card-body">
            <form action="{{ isset($paymentMethod) ? route('admin.payment-methods.update', $paymentMethod->uuid) : route('admin.payment-methods.store') }}" method="POST">
                @csrf
                @if(isset($paymentMethod))
                    @method('PUT')
                @endif
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <x-admin.input 
                            name="code" 
                            label="Mã cổng" 
                            :value="old('code', $paymentMethod->code ?? '')"
                            placeholder="vd: vnpay, stripe, cod..."
                            required
                        />
                    </div>
                    <div class="col-md-6 mb-3">
                        <x-admin.input 
                            name="name" 
                            label="Tên hiển thị" 
                            :value="old('name', $paymentMethod->name ?? '')"
                            required
                        />
                    </div>
                </div>

                <div class="mb-3">
                    <x-admin.textarea 
                        name="description" 
                        label="Mô tả" 
                        :value="old('description', $paymentMethod->description ?? '')"
                    />
                </div>

                <div class="mb-3">
                    <x-admin.textarea 
                        name="config" 
                        label="Cấu hình (JSON)" 
                        :value="old('config', isset($paymentMethod) && $paymentMethod->config ? json_encode($paymentMethod->config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '')"
                        rows="5"
                        placeholder='{&quot;endpoint&quot;: &quot;...&quot;, &quot;secret_key&quot;: &quot;...&quot;}'
                    />
                    <small class="text-muted">Nhập cấu hình ở định dạng JSON chuẩn.</small>
                </div>

                <div class="mb-4">
                    <x-admin.toggle 
                        name="is_active" 
                        label="Kích hoạt" 
                        :checked="old('is_active', $paymentMethod->is_active ?? true)"
                    />
                </div>
                
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy"></i> Lưu thông tin
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection