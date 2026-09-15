@extends('layouts.admin')

@section('title', isset($warehouse) ? 'Cập nhật Warehouses' : 'Thêm mới Warehouses')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
    <!-- Page header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-8">
        <!-- Left: Title -->
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-slate-800 dark:text-slate-100 font-bold">{{ isset($warehouse) ? 'Cập nhật' : 'Thêm mới' }} Warehouses ✨</h1>
        </div>
        <!-- Right: Actions -->
        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
            <a href="{{ route('admin.warehouses.index') }}" class="btn border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 text-slate-600 dark:text-slate-300">
                <span class="hidden xs:block ml-2">Quay lại</span>
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white dark:bg-slate-800 shadow-lg rounded-sm border border-slate-200 dark:border-slate-700 p-5">
        <form action="{{ isset($warehouse) ? route('admin.warehouses.update', $warehouse->uuid) : route('admin.warehouses.store') }}" method="POST">
            @csrf
            @if(isset($warehouse))
                @method('PUT')
            @endif
            
            <div class="space-y-4">
                <!-- Add form fields here -->
                <p class="text-sm text-slate-500 dark:text-slate-400">Các trường thông tin sẽ được cập nhật sau...</p>
            </div>
            
            <div class="mt-6">
                <button type="submit" class="btn bg-indigo-500 hover:bg-indigo-600 text-white">
                    Lưu thông tin
                </button>
            </div>
        </form>
    </div>
</div>
@endsection