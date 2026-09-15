import Swal from 'sweetalert2';

/**
 * Cầu nối giao tiếp UI (Notification Bridge)
 * Giúp tách biệt ứng dụng khỏi sự phụ thuộc vào 1 thư viện cụ thể (SweetAlert2, Toastr, Bootstrap Toast...)
 */
const AdminUI = {
    /**
     * Hiển thị thông báo dạng góc phải màn hình (Toast) hoặc pop-up
     * @param {string} type - 'success' | 'error' | 'warning' | 'info'
     * @param {string} message - Nội dung thông báo
     */
    notify(type = 'success', message = '') {
        return Swal.fire({
            toast: true,
            position: 'top-end',
            icon: type,
            title: message,
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });
    },

    /**
     * Hiển thị hộp thoại xác nhận (Confirm Dialog)
     * @param {Object} options
     * @param {string} options.title - Tiêu đề
     * @param {string} options.text - Nội dung mô tả
     * @param {string} options.icon - 'warning' | 'question' | 'error' | 'info'
     * @param {string} options.confirmText - Nút xác nhận
     * @param {string} options.cancelText - Nút hủy
     * @param {Function} callback - Hàm thực thi khi người dùng bấm xác nhận
     */
    confirm({ title = 'Bạn có chắc chắn?', text = '', icon = 'warning', confirmText = 'Đồng ý', cancelText = 'Hủy' }, callback) {
        Swal.fire({
            title: title,
            text: text,
            icon: icon,
            showCancelButton: true,
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#ef4444',
            confirmButtonText: confirmText,
            cancelButtonText: cancelText,
        }).then((result) => {
            if (result.isConfirmed && typeof callback === 'function') {
                callback();
            }
        });
    },

    /**
     * Hiển thị cảnh báo hoặc lỗi chi tiết (Alert Dialog)
     * @param {string} title 
     * @param {string} text 
     * @param {string} type - 'error' | 'success' | 'warning' | 'info'
     */
    alert(title = '', text = '', type = 'info') {
        return Swal.fire({
            title: title,
            text: text,
            icon: type,
            confirmButtonText: 'Đóng',
            confirmButtonColor: '#2563eb'
        });
    }
};

window.AdminUI = AdminUI;
export default AdminUI;
