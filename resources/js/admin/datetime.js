import flatpickr from 'flatpickr';
import { Vietnamese } from 'flatpickr/dist/l10n/vn.js';

let initialized = false;

export default function initDatetimePickers() {
    document.querySelectorAll('input[data-datetime-picker]').forEach((input) => {
        if (input._flatpickr) return;

        const picker = flatpickr(input, {
            enableTime: true,
            time_24hr: true,
            dateFormat: 'Y-m-d H:i',
            altInput: true,
            altFormat: 'd/m/Y H:i',
            allowInput: true,
            disableMobile: true,
            locale: Vietnamese,
        });

        // Label `for` trỏ tới input gốc (bị ẩn) — chuyển id sang input hiển thị.
        if (picker.altInput) {
            const name = input.getAttribute('name');

            if (input.id) {
                picker.altInput.id = input.id;
                input.removeAttribute('id');
            } else if (name && /^[A-Za-z][\w-]*$/.test(name)) {
                picker.altInput.id = name;
            }
        }
    });

    if (initialized) return;
    initialized = true;

    document.addEventListener('click', (event) => {
        const group = event.target.closest('.input-group');
        if (!group) return;

        const input = group.querySelector('input[data-datetime-picker]');
        if (!input) return;

        if (event.target.closest('.js-datetime-toggle')) {
            input._flatpickr?.open();
        }

        if (event.target.closest('.js-datetime-clear')) {
            if (input._flatpickr) {
                input._flatpickr.clear();
            } else {
                input.value = '';
            }
        }
    });
}
