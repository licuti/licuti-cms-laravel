<?php

namespace App\Services\Admin\Order;

use App\Core\Base\BaseService;
use App\DTOs\Order\OrderDTO;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\Interfaces\PaymentMethodRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class OrderService extends BaseService
{
    public function __construct(
        private readonly OrderRepositoryInterface $repository,
        private readonly PaymentMethodRepositoryInterface $paymentMethodRepository,
    ) {}

    public function getList(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getFilteredPaginated($filters, $perPage);
    }

    public function getDetail(string $uuid): Order
    {
        return $this->repository->findByUuidWithRelations($uuid);
    }

    public function getStatusCounts(): array
    {
        return Cache::remember('orders.status_counts', 60, fn () => $this->repository->getStatusCounts());
    }

    /**
     * Lấy danh sách cổng thanh toán đang active (cache 5 phút).
     */
    public function getActivePaymentMethods(): Collection
    {
        return Cache::remember('payment_methods.active', 300, fn () => $this->paymentMethodRepository->getActiveMethods());
    }

    /**
     * Xóa cache status counts (gọi sau mỗi thao tác thay đổi đơn hàng).
     */
    public function flushStatusCountsCache(): void
    {
        Cache::forget('orders.status_counts');
    }

    public function create(OrderDTO $dto): Order
    {
        return DB::transaction(function () use ($dto) {
            /** @var Order $order */
            $order = $this->repository->create([
                'user_id'           => $dto->userId,
                'payment_method_id' => $dto->paymentMethodId,
                'status'            => $dto->status,
                'subtotal'          => $dto->subtotal,
                'discount_amount'   => $dto->discountAmount,
                'shipping_fee'      => $dto->shippingFee,
                'tax_amount'        => $dto->taxAmount,
                'total_amount'      => $dto->totalAmount,
                'currency'          => $dto->currency,
                'notes'             => $dto->notes,
            ]);

            // Lưu địa chỉ giao hàng
            if (!empty($dto->shippingAddress['recipient_name'])) {
                $order->shippingAddress()->create($dto->shippingAddress);
            }

            // Lưu chi tiết sản phẩm đơn hàng (Snapshot)
            foreach ($dto->items as $item) {
                $unitPrice = (float) ($item['unit_price'] ?? 0);
                $qty = (int) ($item['quantity'] ?? 1);
                $discount = (float) ($item['discount_amount'] ?? 0);
                $subtotal = max(0, ($unitPrice * $qty) - $discount);

                $order->items()->create([
                    'product_id'         => $item['product_id'] ?? null,
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'product_name'       => $item['product_name'],
                    'product_sku'        => $item['product_sku'] ?? null,
                    'unit_price'         => $unitPrice,
                    'quantity'           => $qty,
                    'discount_amount'    => $discount,
                    'subtotal'           => $subtotal,
                    'meta'               => $item['meta'] ?? null,
                ]);
            }

            // Ghi nhận lịch sử trạng thái ban đầu
            $order->statusHistories()->create([
                'from_status'        => null,
                'to_status'          => $order->status,
                'note'               => __('Khởi tạo đơn hàng mới.'),
                'changed_by_user_id' => $dto->userId,
            ]);

            return $order->load(['shippingAddress', 'items', 'statusHistories']);
        });

        $this->flushStatusCountsCache();

        return $created;
    }

    public function updateStatus(string $uuid, OrderStatus $newStatus, ?string $note = null, ?int $changedBy = null): Order
    {
        $updated = DB::transaction(function () use ($uuid, $newStatus, $note, $changedBy) {
            $order = $this->repository->findByUuidWithRelations($uuid);
            $currentStatus = $order->status;

            if (!$currentStatus->canTransitionTo($newStatus)) {
                throw new InvalidArgumentException(
                    __('Không thể chuyển trạng thái từ :from sang :to.', [
                        'from' => $currentStatus->label(),
                        'to'   => $newStatus->label(),
                    ])
                );
            }

            $updateData = ['status' => $newStatus];

            if ($newStatus === OrderStatus::SHIPPING && is_null($order->shipped_at)) {
                $updateData['shipped_at'] = now();
            }

            if ($newStatus === OrderStatus::COMPLETED && is_null($order->completed_at)) {
                $updateData['completed_at'] = now();
                if (is_null($order->paid_at)) {
                    $updateData['paid_at'] = now();
                }
            }

            if ($newStatus === OrderStatus::CANCELLED) {
                $updateData['cancelled_at'] = now();
                $updateData['cancel_reason'] = $note ?? __('Người quản trị hủy đơn.');
            }

            $order->update($updateData);

            $order->statusHistories()->create([
                'from_status'        => $currentStatus,
                'to_status'          => $newStatus,
                'note'               => $note ?? __('Cập nhật trạng thái sang: :status', ['status' => $newStatus->label()]),
                'changed_by_user_id' => $changedBy ?? auth()->id(),
            ]);

            return $order->fresh(['statusHistories.changedBy', 'shippingAddress', 'items']);
        });

        $this->flushStatusCountsCache();

        return $updated;
    }

    public function cancel(string $uuid, string $reason, ?int $changedBy = null): Order
    {
        return $this->updateStatus($uuid, OrderStatus::CANCELLED, $reason, $changedBy);
    }

    public function delete(string $uuid): bool
    {
        $order = $this->repository->findByUuid($uuid);
        $result = (bool) $this->repository->delete($order->id);

        if ($result) {
            $this->flushStatusCountsCache();
        }

        return $result;
    }
}
