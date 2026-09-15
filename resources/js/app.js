import './bootstrap';
import jQuery from 'jquery';
import Swal from 'sweetalert2';
import AdminUI from './admin/ui-bridge';
import AdminTable from './admin/table-utils';

// Đưa ra scope window để sử dụng global trong các file Blade
window.$ = window.jQuery = jQuery;
window.Swal = Swal;
window.AdminUI = AdminUI;
window.AdminTable = AdminTable;

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
