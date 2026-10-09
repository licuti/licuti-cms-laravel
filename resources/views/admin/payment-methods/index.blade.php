@extends('layouts.admin')

@section('title', 'Phương thức thanh toán')

@section('content')
    <x-admin.page-header title="Phương thức thanh toán">
        <x-slot name="actions">
            <a href="{{ route('admin.payment-methods.create') }}" class="btn btn-primary">
                <i class="ti ti-plus"></i> Thêm mới
            </a>
        </x-slot>
    </x-admin.page-header>

    <div class="card">
        <div class="card-body">
            <x-admin.table>
                <x-slot name="thead">
                    <tr>
                        <th>Mã cổng</th>
                        <th>Tên hiển thị</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </x-slot>
                
                @forelse($paymentMethods as $method)
                    <tr>
                        <td><span class="badge bg-secondary">{{ $method->code }}</span></td>
                        <td>{{ $method->name }}</td>
                        <td>
                            <form action="{{ route('admin.payment-methods.toggle-status', $method->uuid) }}" method="POST" class="d-inline m-0">
                                @csrf
                                <div class="form-check form-switch cursor-pointer" style="margin-bottom: 0" onclick="this.closest('form').submit()">
                                    <input class="form-check-input cursor-pointer" type="checkbox" {{ $method->is_active ? 'checked' : '' }} title="Click để đổi trạng thái">
                                </div>
                            </form>
                        </td>
                        <td>
                            <x-admin.row-actions 
                                :edit-url="route('admin.payment-methods.edit', $method->uuid)"
                                :delete-url="route('admin.payment-methods.destroy', $method->uuid)"
                            />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted">Chưa có phương thức thanh toán nào.</td>
                    </tr>
                @endforelse
            </x-admin.table>

            <div class="mt-3">
                {{ $paymentMethods->links() }}
            </div>
        </div>
    </div>
@endsection