@extends('layouts.admin')
@section('title', 'Cấu hình hệ thống')

@php
    $groupLabels = [
        'general' => ['icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z', 'label' => 'Cơ bản'],
        'seo' => ['icon' => 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z', 'label' => 'SEO'],
        'mail' => ['icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'label' => 'Email SMTP'],
        'social' => ['icon' => 'M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z', 'label' => 'Mạng xã hội'],
        'appearance' => ['icon' => 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01', 'label' => 'Giao diện'],
    ];
@endphp

@section('content')
<div class="space-y-4">
    <!-- Header -->
    <x-admin.page-header 
        title="Cấu hình hệ thống" 
        subtitle="Quản lý các thông số chung, SEO, giao diện và kết nối của nền tảng"
        :breadcrumbs="[
            ['label' => 'Bảng điều khiển', 'url' => '/admin/dashboard'], 
            ['label' => 'Cài đặt hệ thống']
        ]"
    />

    <div class="flex flex-col gap-6">
        <!-- Horizontal Tabs -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-sm">
            <nav class="flex overflow-x-auto custom-scrollbar" aria-label="Tabs">
                @foreach($groups as $g)
                    <a href="{{ route('admin.settings.edit', $g) }}" 
                       class="flex items-center gap-3 px-6 py-4 text-sm font-medium border-b-2 whitespace-nowrap transition-all {{ $group === $g ? 'border-blue-600 bg-blue-50/50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400' : 'border-transparent text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-slate-200' }}">
                        <svg class="w-5 h-5 {{ $group === $g ? 'text-blue-600 dark:text-blue-400' : 'text-slate-400 dark:text-slate-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $groupLabels[$g]['icon'] }}"></path>
                        </svg>
                        {{ $groupLabels[$g]['label'] }}
                    </a>
                @endforeach
            </nav>
        </div>

        <!-- Cấu hình Content -->
        <div class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm">
            <div class="p-6 border-b border-slate-200 dark:border-slate-800">
                <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">
                    {{ $groupLabels[$group]['label'] }}
                </h3>
            </div>

            <form action="{{ route('admin.settings.update', $group) }}" method="POST" class="p-6 space-y-8">
                @csrf
                
                @forelse($settings as $setting)
                    @php
                        // Value parse JSON nếu là field translatable
                        $value = $setting->value;
                        if ($setting->is_translatable) {
                            $decoded = json_decode($value, true);
                            $value = is_array($decoded) ? $decoded : ['vi' => $value];
                        }
                    @endphp

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
                        <!-- Cột Trái: Tiêu đề & Mô tả -->
                        <div class="lg:col-span-1">
                            <label class="block text-sm font-bold text-slate-900 dark:text-slate-100">
                                {{ $setting->label ?: $setting->key }}
                            </label>
                            @if($setting->description)
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">{{ $setting->description }}</p>
                            @endif
                            @if($setting->is_translatable)
                                <span class="inline-block mt-3 px-2 py-0.5 text-[10px] uppercase font-bold bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 rounded tracking-wider">Đa ngôn ngữ</span>
                            @endif
                        </div>

                        <!-- Cột Phải: Giá trị Input -->
                        <div class="lg:col-span-2">
                            @if($setting->is_translatable)
                                <!-- Đa ngôn ngữ: Render động theo danh sách ngôn ngữ -->
                                <div class="space-y-4 bg-slate-50 dark:bg-slate-800/50 p-4 rounded-xl border border-slate-100 dark:border-slate-700/50">
                                    @foreach($activeLanguages as $lang)
                                        <div>
                                            <label class="flex items-center gap-2 text-xs font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                                @if($lang->flag_url)
                                                    <img src="{{ $lang->flag_url }}" class="w-5 h-5 rounded-full object-cover shadow-sm border border-slate-200">
                                                @else
                                                    <span class="flex items-center justify-center w-5 h-5 bg-slate-600 text-white font-bold text-[8px] rounded-full shadow-sm border border-slate-700 uppercase">{{ substr($lang->code, 0, 2) }}</span>
                                                @endif
                                                {{ $lang->name }}
                                                @if($lang->is_default) <span class="text-[9px] text-blue-500">(Mặc định)</span> @endif
                                            </label>
                                            @if($setting->type === 'textarea')
                                                <textarea name="{{ $setting->key }}[{{ $lang->code }}]" rows="3" class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20 text-slate-900 dark:text-slate-100 placeholder-slate-400 transition-all">{{ $value[$lang->code] ?? '' }}</textarea>
                                            @else
                                                <x-admin.input type="{{ $setting->type === 'color' ? 'color' : 'text' }}" name="{{ $setting->key }}[{{ $lang->code }}]" value="{{ $value[$lang->code] ?? '' }}" class="bg-white dark:bg-slate-900" />
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <!-- Field bình thường -->
                                @if($setting->type === 'textarea')
                                    <textarea name="{{ $setting->key }}" rows="3" class="w-full px-3.5 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/20 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 transition-all">{{ $value }}</textarea>
                                @elseif($setting->type === 'image')
                                    <div class="max-w-xs">
                                        <x-admin.image-upload 
                                            name="{{ $setting->key }}" 
                                            :current="$value ? Storage::url($value) : ''" 
                                            shape="wide"
                                            description="Chọn ảnh từ Thư viện Media"
                                        />
                                    </div>
                                @elseif($setting->type === 'color')
                                    <div class="flex items-center gap-3">
                                        <input type="color" name="{{ $setting->key }}" value="{{ $value ?: '#000000' }}" class="w-12 h-12 p-1 border border-slate-200 dark:border-slate-700 rounded cursor-pointer bg-white dark:bg-slate-800">
                                        <x-admin.input type="text" value="{{ $value }}" readonly class="w-32 font-mono text-center" />
                                    </div>
                                @else
                                    <x-admin.input type="{{ $setting->type === 'number' ? 'number' : 'text' }}" name="{{ $setting->key }}" value="{{ $value }}" />
                                @endif
                            @endif
                        </div>
                    </div>

                    @if(!$loop->last)
                        <hr class="border-slate-100 dark:border-slate-800">
                    @endif
                @empty
                    <div class="py-12 text-center text-slate-500">
                        Chưa có cài đặt nào trong nhóm này.
                    </div>
                @endforelse

                <div class="pt-6 border-t border-slate-200 dark:border-slate-800 flex justify-end">
                    <x-admin.button type="submit" variant="primary">
                        <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Lưu Cấu hình
                    </x-admin.button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
