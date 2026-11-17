import './bootstrap';
import 'flatpickr/dist/flatpickr.min.css';
import '../css/flatpickr-theme.css';
import jQuery from 'jquery';
import Swal from 'sweetalert2';
import TomSelect from 'tom-select';
import AdminUI from './admin/ui-bridge';
import AdminTable from './admin/table-utils';
import './admin/notifications';
import initDatetimePickers from './admin/datetime';
import initSeoMeta from './admin/seo-meta';

// Đưa ra scope window để sử dụng global trong các file Blade
window.$ = window.jQuery = jQuery;
window.Swal = Swal;
window.TomSelect = TomSelect;
window.AdminUI = AdminUI;
window.AdminTable = AdminTable;

// Kích hoạt tooltip Bootstrap cho phần tử opt-in [data-bs-toggle="tooltip"]
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (element) {
        bootstrap.Tooltip.getOrCreateInstance(element);
    });
});

// Date/time picker dùng Flatpickr (UI thống nhất light/dark)
// SEO meta box: tab ⇄ preview, đếm ký tự, snippet trực tiếp
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
        initDatetimePickers();
        initSeoMeta();
    });
} else {
    initDatetimePickers();
    initSeoMeta();
}

// Cấu hình mặc định cho toàn bộ các request AJAX
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        'Accept': 'application/json',
    },
    beforeSend: function (xhr) {
        // Tự động đính kèm Bearer Token nếu Admin đã đăng nhập (vẫn hỗ trợ cho các API tĩnh nếu cần)
        const token = localStorage.getItem('admin_token');
        if (token) {
            xhr.setRequestHeader('Authorization', 'Bearer ' + token);
        }
    },
    error: function (xhr) {
        if (xhr.status === 401 && !window.location.pathname.includes('/admin/login')) {
            localStorage.removeItem('admin_token');
            localStorage.removeItem('admin_user');
            window.location.href = '/admin/login';
        }
    }
});
