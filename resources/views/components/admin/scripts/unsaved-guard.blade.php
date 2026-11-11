@props(['message' => null])

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('form[data-unsaved-guard]');
        if (!form) return;

        const message = @json($message ?? __('Bạn có thay đổi chưa lưu. Nếu chuyển trang, các thay đổi sẽ bị mất. Tiếp tục?'));
        let dirty = false;

        const markDirty = () => { dirty = true; };

        form.addEventListener('input', markDirty);
        form.addEventListener('change', markDirty);
        form.addEventListener('submit', () => { dirty = false; });
        document.addEventListener('form:dirty', markDirty);

        window.addEventListener('beforeunload', function (event) {
            if (!dirty) return;
            event.preventDefault();
            event.returnValue = '';
        });

        document.addEventListener('click', function (event) {
            const link = event.target.closest('a[data-lang-switch]');
            if (!link || !dirty) return;

            if (!window.confirm(message)) {
                event.preventDefault();
                return;
            }

            dirty = false;
        });
    });
</script>
@endpush
