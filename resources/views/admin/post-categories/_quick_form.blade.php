<x-admin.card title="{{ __('Thêm nhanh Danh mục') }}" subtitle="{{ __('Tạo danh mục với các thông tin cơ bản. Có thể chỉnh sửa chi tiết sau.') }}">
    <form action="{{ route('admin.post-categories.store', ['lang' => $currentLocale]) }}" method="POST">
        @csrf
        <input type="hidden" name="is_active" value="1">

        @php
            $lang = $activeLanguages->firstWhere('code', $currentLocale) ?? $activeLanguages->first();
            $nameKey = 'translations.' . $lang->code . '.name';
            $slugKey = 'translations.' . $lang->code . '.slug';
            $descKey = 'translations.' . $lang->code . '.description';
        @endphp

        <x-admin.form-group label="{{ __('Tên danh mục') }}" :name="$nameKey" required :error="$errors->first($nameKey)">
            <x-admin.input
                type="text"
                name="translations[{{ $lang->code }}][name]"
                value="{{ old('translations.'.$lang->code.'.name') }}"
                placeholder="{{ __('Nhập tên danh mục...') }}"
                class="seo-source-name"
                required
            />
        </x-admin.form-group>

        <x-admin.form-group
            label="{{ __('Đường dẫn (Slug)') }}"
            :name="$slugKey"
            description="{{ __('Để trống sẽ tự tạo từ tên.') }}"
            :error="$errors->first($slugKey)"
        >
            <x-admin.input
                type="text"
                name="translations[{{ $lang->code }}][slug]"
                value="{{ old('translations.'.$lang->code.'.slug') }}"
                placeholder="vi-du-danh-muc"
                class="seo-source-slug"
            />
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

        <x-admin.form-group label="{{ __('Mô tả') }}" :name="$descKey" :error="$errors->first($descKey)">
            <x-admin.textarea
                name="translations[{{ $lang->code }}][description]"
                rows="3"
                placeholder="{{ __('Nhập mô tả ngắn...') }}"
            >{{ old('translations.'.$lang->code.'.description') }}</x-admin.textarea>
        </x-admin.form-group>

        <x-admin.form-group label="{{ __('Hình đại diện') }}" name="image">
            <x-admin.image-upload
                name="image"
                :current="null"
                :current-uuid="old('image')"
                shape="square"
            />
        </x-admin.form-group>

        <x-admin.form-group
            label="{{ __('Thứ tự hiển thị') }}"
            name="display_order"
            description="{{ __('Số nhỏ hơn sẽ hiển thị trước.') }}"
            :error="$errors->first('display_order')"
        >
            <x-admin.input
                type="number"
                name="display_order"
                value="{{ old('display_order', 0) }}"
                min="0"
            />
        </x-admin.form-group>

        <div class="d-flex justify-content-end pt-2 mt-2 border-top">
            <x-admin.button type="submit" variant="primary">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                <span>{{ __('Thêm mới') }}</span>
            </x-admin.button>
        </div>

        <p class="text-body-secondary text-center mb-0 mt-2" style="font-size:0.7rem;">
            {{ __('Các tùy chọn nâng cao, SEO & đa ngôn ngữ có thể chỉnh khi mở danh mục.') }}
        </p>
    </form>
</x-admin.card>
