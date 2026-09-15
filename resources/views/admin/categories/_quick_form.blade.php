<x-admin.card title="{{ __('Thêm Danh mục mới') }}" class="p-6">
    <form action="{{ route('admin.categories.store', ['lang' => $currentLocale]) }}" method="POST" class="space-y-4">
        @csrf
        
        @php
            $lang = $languages->firstWhere('code', $currentLocale) ?? $languages->first();
            $nameKey = 'translations.' . $lang->code . '.name';
            $slugKey = 'translations.' . $lang->code . '.slug';
            $descKey = 'translations.' . $lang->code . '.description';
        @endphp

        <x-admin.form-group label="{{ __('Tên danh mục') }}" :name="$nameKey" required :error="$errors->first($nameKey)">
            <x-admin.input type="text" name="translations[{{ $lang->code }}][name]" value="{{ old('translations.'.$lang->code.'.name') }}" placeholder="{{ __('Nhập tên danh mục...') }}" required class="seo-source-name" />
        </x-admin.form-group>

        <x-admin.form-group label="{{ __('Đường dẫn (Slug)') }}" :name="$slugKey" description="{{ __('Để trống tự tạo từ tên.') }}" :error="$errors->first($slugKey)">
            <x-admin.input type="text" name="translations[{{ $lang->code }}][slug]" value="{{ old('translations.'.$lang->code.'.slug') }}" placeholder="vi-du-danh-muc" class="seo-source-slug" />
        </x-admin.form-group>

        <x-admin.form-group label="{{ __('Danh mục cha') }}" name="parent_id" :error="$errors->first('parent_id')">
            <x-admin.select name="parent_id">
                <option value="">{{ __('— Không có —') }}</option>
                @foreach($parents as $parent)
                    <option value="{{ $parent->id }}" {{ (string) old('parent_id') === (string) $parent->id ? 'selected' : '' }}>
                        {{ $parent->tree_name ?? $parent->translated_name }}
                    </option>
                @endforeach
            </x-admin.select>
        </x-admin.form-group>
        
        <div>
            <p class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">{{ __('Hình ảnh') }}</p>
            <x-admin.image-upload name="image" :current="null" :current-uuid="old('image')" shape="square" />
        </div>

        <x-admin.form-group label="{{ __('Mô tả') }}" :name="$descKey" :error="$errors->first($descKey)">
            <x-admin.textarea name="translations[{{ $lang->code }}][description]" rows="3" placeholder="{{ __('Nhập mô tả danh mục...') }}">{{ old('translations.'.$lang->code.'.description') }}</x-admin.textarea>
        </x-admin.form-group>
        
        <x-admin.form-group label="{{ __('Thứ tự hiển thị') }}" name="display_order" :error="$errors->first('display_order')">
            <x-admin.input type="number" name="display_order" value="{{ old('display_order', 0) }}" min="0" />
        </x-admin.form-group>

        <x-admin.button type="submit" variant="primary" class="w-full justify-center">
            {{ __('Thêm mới') }}
        </x-admin.button>
        <p class="text-[11px] text-slate-400 mt-2 text-center">{{ __('Các cấu hình Nâng cao, SEO, Ngôn ngữ có thể thao tác khi Chỉnh sửa') }}</p>
    </form>
</x-admin.card>
