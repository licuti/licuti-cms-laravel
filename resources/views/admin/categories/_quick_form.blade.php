<x-admin.card title="{{ __('Thêm Danh mục mới') }}">
    <form action="{{ route('admin.categories.store', ['lang' => $currentLocale]) }}" method="POST" class="d-flex flex-column gap-3">
        @csrf
        
        @php
            $lang = $languages->firstWhere('code', $currentLocale) ?? $languages->first();
            $nameKey = 'translations.' . $lang->code . '.name';
            $slugKey = 'translations.' . $lang->code . '.slug';
            $descKey = 'translations.' . $lang->code . '.description';
        @endphp

        <x-admin.form-group label="{{ __('Tên danh mục') }}" :name="$nameKey" required>
            <x-admin.input 
                type="text" 
                name="translations[{{ $lang->code }}][name]" 
                value="{{ old('translations.'.$lang->code.'.name') }}" 
                placeholder="{{ __('Nhập tên danh mục...') }}" 
                size="sm"
                required 
                class="seo-source-name" 
            />
        </x-admin.form-group>

        <x-admin.form-group label="{{ __('Đường dẫn (Slug)') }}" :name="$slugKey" description="{{ __('Để trống tự tạo từ tên.') }}">
            <x-admin.input 
                type="text" 
                name="translations[{{ $lang->code }}][slug]" 
                value="{{ old('translations.'.$lang->code.'.slug') }}" 
                placeholder="vi-du-danh-muc" 
                size="sm"
                class="seo-source-slug" 
            />
        </x-admin.form-group>

        <x-admin.form-group label="{{ __('Danh mục cha') }}" name="parent_id">
            <x-admin.select name="parent_id" size="sm">
                <option value="">{{ __('— Không có —') }}</option>
                @foreach($parents as $parent)
                    <option value="{{ $parent->id }}" {{ (string) old('parent_id') === (string) $parent->id ? 'selected' : '' }}>
                        {{ $parent->tree_name ?? $parent->translated_name }}
                    </option>
                @endforeach
            </x-admin.select>
        </x-admin.form-group>
        
        <div>
            <label class="form-label small fw-semibold text-body-secondary mb-1">{{ __('Hình ảnh đại diện') }}</label>
            <x-admin.image-upload name="image" :current="null" :current-uuid="old('image')" shape="square" />
        </div>

        <x-admin.form-group label="{{ __('Mô tả') }}" :name="$descKey">
            <x-admin.textarea 
                name="translations[{{ $lang->code }}][description]" 
                size="sm"
                rows="3" 
                placeholder="{{ __('Nhập mô tả danh mục...') }}"
            >{{ old('translations.'.$lang->code.'.description') }}</x-admin.textarea>
        </x-admin.form-group>
        
        <x-admin.form-group label="{{ __('Thứ tự hiển thị') }}" name="display_order">
            <x-admin.input 
                type="number" 
                name="display_order" 
                size="sm"
                value="{{ old('display_order', 0) }}" 
                min="0" 
            />
        </x-admin.form-group>

        <div class="pt-2">
            <x-admin.button type="submit" variant="primary" class="w-100 justify-content-center" size="sm">
                {{ __('Thêm mới') }}
            </x-admin.button>
            <p class="small text-body-secondary text-center mt-2 mb-0">
                {{ __('Các cấu hình Nâng cao, SEO, Ngôn ngữ có thể thao tác khi Chỉnh sửa.') }}
            </p>
        </div>
    </form>
</x-admin.card>
