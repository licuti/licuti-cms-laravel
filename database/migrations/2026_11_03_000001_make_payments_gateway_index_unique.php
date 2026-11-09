<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Siết idempotency webhook xuống tầng DB.
 *
 * Bối cảnh: index (gateway_code, gateway_transaction_id) hiện là NON-unique
 * (payments_gateway_code_gateway_transaction_id_index). Việc chống trùng chỉ nằm ở
 * tầng ứng dụng (PaymentWebhookController tìm bản ghi trước khi tạo), nên 2 webhook
 * đến đồng thời vẫn có thể tạo 2 bản ghi payment cho cùng 1 giao dịch.
 *
 * gateway_transaction_id nullable → MySQL/MariaDB cho phép nhiều NULL trong unique index,
 * các bản ghi chưa có mã giao dịch (pending nội bộ) vẫn insert bình thường.
 */
return new class extends Migration
{
    private const OLD_INDEX = 'payments_gateway_code_gateway_transaction_id_index';
    private const NEW_INDEX = 'payments_gateway_code_gateway_transaction_id_unique';

    public function up(): void
    {
        // Chặn deploy khi dữ liệu hiện hữu đã trùng, để lỗi là thông báo rõ ràng chứ không phải exception khó đọc.
        $duplicates = DB::table('payments')
            ->select('gateway_code', 'gateway_transaction_id')
            ->whereNotNull('gateway_transaction_id')
            ->groupBy('gateway_code', 'gateway_transaction_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($duplicates->isNotEmpty()) {
            throw new RuntimeException(
                'Không thể tạo unique index: phát hiện ' . $duplicates->count()
                . ' nhóm (gateway_code, gateway_transaction_id) trùng nhau trong bảng payments. '
                . 'Hãy dọn bản ghi trùng (giữ lại bản ghi paid_at sớm nhất) rồi chạy lại migration.'
            );
        }

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(self::OLD_INDEX);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->unique(['gateway_code', 'gateway_transaction_id'], self::NEW_INDEX);
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(self::NEW_INDEX);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->index(['gateway_code', 'gateway_transaction_id'], self::OLD_INDEX);
        });
    }
};
