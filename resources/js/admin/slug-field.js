// Permalink kiểu WordPress cho component x-admin.slug-field.
// Chỉ có MỘT input: hiển thị như text, bấm "Chỉnh sửa" thì bật .is-editing để nhập.
// Không xử lý sinh slug — việc đó vẫn do scripts.auto-slug đảm nhiệm.

let measureContext = null;

const measureText = (element, text) => {
    if (!measureContext) measureContext = document.createElement('canvas').getContext('2d');
    const style = getComputedStyle(element);
    measureContext.font = `${style.fontWeight} ${style.fontSize} ${style.fontFamily}`;
    return measureContext.measureText(text).width;
};

export default function initSlugFields() {
    document.querySelectorAll('[data-slug-field]').forEach(initSlugField);
}

function initSlugField(root) {
    const row = root.querySelector('.slug-permalink');
    const input = root.querySelector('[data-slug-input]');
    const editButton = root.querySelector('[data-slug-edit]');
    const okButton = root.querySelector('[data-slug-ok]');

    if (!row || !input) return;

    // Ở chế độ hiển thị, input rộng đúng bằng nội dung để trông như text.
    const autosize = () => {
        if (row.classList.contains('is-editing')) {
            input.style.width = '';
            return;
        }
        const text = input.value || input.placeholder || '';
        input.style.width = Math.ceil(measureText(input, text) + 4) + 'px';
    };

    const open = () => {
        row.classList.add('is-editing');
        input.readOnly = false;
        if (editButton) editButton.classList.add('d-none');
        if (okButton) okButton.classList.remove('d-none');
        autosize();
        input.focus();
        input.select();
    };

    const close = () => {
        row.classList.remove('is-editing');
        input.readOnly = true;
        if (editButton) editButton.classList.remove('d-none');
        if (okButton) okButton.classList.add('d-none');
        autosize();
    };

    if (editButton) editButton.addEventListener('click', open);
    if (okButton) okButton.addEventListener('click', close);

    row.addEventListener('click', (event) => {
        // Bấm vào vùng hiển thị (không phải nút/link) khi đang readonly -> mở sửa
        if (input.readOnly && !event.target.closest('button, a')) open();
    });

    input.addEventListener('input', autosize);

    input.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            close();
        }
        if (event.key === 'Escape') {
            close();
        }
    });

    // Đóng khi blur ra ngoài field (setTimeout để không chặn cú click nút OK)
    input.addEventListener('blur', () => {
        setTimeout(() => {
            if (!root.contains(document.activeElement)) close();
        }, 0);
    });

    // Có lỗi validation từ server -> mở sẵn
    if (input.classList.contains('is-invalid')) open();

    autosize();
}
