import $ from 'jquery';
import AdminUI from './ui-bridge';

/**
 * Tiện ích Bảng dữ liệu Admin (Table Utilities)
 * Dùng chung cho check-all và áp dụng hành động hàng loạt (bulk action) trên toàn bộ Admin Panel
 */
const AdminTable = {
    init() {
        this.initCheckAll();
        this.initBulkActions();
        this.initFormConfirm();
    },

    /**
     * Khởi tạo sự kiện Xác nhận Form chung (.form-confirm)
     */
    initFormConfirm() {
        $(document).on('submit', '.form-confirm', function (e) {
            e.preventDefault();
            const form = this;
            const $form = $(this);
            
            AdminUI.confirm({
                title: $form.data('confirm-title') || 'Xác nhận thao tác?',
                text: $form.data('confirm-text') || 'Bạn có chắc chắn muốn thực hiện hành động này?',
                icon: $form.data('confirm-icon') || 'warning',
                confirmText: $form.data('confirm-btn') || 'Đồng ý'
            }, function () {
                form.submit();
            });
        });
    },

    /**
     * Khởi tạo sự kiện Chọn tất cả (#check-all)
     */
    initCheckAll() {
        $(document).on('change', '#check-all', function () {
            const isChecked = $(this).is(':checked');
            $('.row-checkbox, .user-checkbox').prop('checked', isChecked);
        });

        $(document).on('change', '.row-checkbox, .user-checkbox', function () {
            const total = $('.row-checkbox, .user-checkbox').length;
            const checked = $('.row-checkbox:checked, .user-checkbox:checked').length;
            $('#check-all').prop('checked', total > 0 && total === checked);
        });
    },

    /**
     * Khởi tạo sự kiện Nút áp dụng hành động hàng loạt (#btn-apply-bulk)
     */
    initBulkActions() {
        $(document).on('click', '#btn-apply-bulk', function () {
            const action = $('#bulk-action-select').val();
            if (!action) {
                AdminUI.notify('warning', 'Vui lòng chọn một hành động cần thực hiện!');
                return;
            }

            const selectedIds = [];
            $('.row-checkbox:checked, .user-checkbox:checked').each(function () {
                selectedIds.push($(this).val());
            });

            if (selectedIds.length === 0) {
                AdminUI.notify('warning', 'Vui lòng chọn ít nhất một bản ghi để thực hiện!');
                return;
            }

            const actionNames = {
                'delete': 'Xóa các bản ghi đã chọn',
                'status_active': 'Chuyển trạng thái sang Active (Hoạt động)',
                'status_inactive': 'Chuyển trạng thái sang Inactive (Tạm khóa)',
                'status_banned': 'Chuyển trạng thái sang Banned (Cấm)',
                'restore': 'Khôi phục các bản ghi đã chọn',
                'force_delete': 'Xóa VĨNH VIỄN các bản ghi đã chọn',
            };
            const actionText = actionNames[action] || action;

            AdminUI.confirm({
                title: 'Xác nhận hành động',
                text: `Bạn có chắc muốn thực hiện: "${actionText}" cho ${selectedIds.length} bản ghi đã chọn?`,
                icon: action === 'delete' ? 'warning' : 'question',
                confirmText: 'Đồng ý thực hiện'
            }, () => {
                const form = $('#form-bulk-action');
                if (form.length > 0) {
                    // Xóa các input ids[] cũ nếu có
                    form.find('input[name="ids[]"]').remove();
                    // Tạo các input ẩn ids[] mới đại diện cho các bản ghi được chọn
                    selectedIds.forEach(id => {
                        form.append(`<input type="hidden" name="ids[]" value="${id}">`);
                    });
                    $('#bulk-action-input').val(action);
                    form.submit();
                } else {
                    // Tự động tạo HTML form POST tới URL bulk-action hiện tại nếu không có form tĩnh
                    const bulkUrl = window.location.pathname.replace(/\/+$/, '') + '/bulk-action';
                    const hiddenForm = $(`<form action="${bulkUrl}" method="POST" style="display:none;"></form>`);
                    hiddenForm.append(`<input type="hidden" name="_token" value="${$('meta[name="csrf-token"]').attr('content')}">`);
                    hiddenForm.append(`<input type="hidden" name="action" value="${action}">`);
                    selectedIds.forEach(id => {
                        hiddenForm.append(`<input type="hidden" name="ids[]" value="${id}">`);
                    });
                    $('body').append(hiddenForm);
                    hiddenForm.submit();
                }
            });
        });
    }
};

window.AdminTable = AdminTable;

// Tự động khởi tạo khi DOM sẵn sàng
$(document).ready(function () {
    AdminTable.init();
});

export default AdminTable;
