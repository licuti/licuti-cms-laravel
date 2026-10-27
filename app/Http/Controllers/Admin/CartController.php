<?php

namespace App\Http\Controllers\Admin;

use App\Core\Base\BaseController;
use App\Core\BulkAction\BulkActionRegistry;
use App\Http\Requests\Admin\BulkActionRequest;
use App\Services\Admin\Cart\CartAdminService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends BaseController
{
    public function __construct(
        private readonly CartAdminService $adminService
    ) {}

    public function index(Request $request, BulkActionRegistry $bulkRegistry): View
    {
        $filters = $request->only(['status', 'search']);
        $perPage = max(1, min(100, (int) $request->input('per_page', 15)));
        $carts = $this->adminService->getList($filters, $perPage);
        $statusCounts = $this->adminService->getStatusCounts();
        $bulkActions = $bulkRegistry->getActionOptions('carts');

        return view('admin.carts.index', compact('carts', 'statusCounts', 'filters', 'bulkActions'));
    }

    public function show(string $uuid): View
    {
        $data = $this->adminService->getDetail($uuid);

        return view('admin.carts.show', [
            'cart'    => $data['cart'],
            'summary' => $data['summary'],
        ]);
    }

    public function destroy(string $uuid): JsonResponse
    {
        try {
            $success = $this->adminService->delete($uuid);
            if (!$success) {
                return $this->notFoundResponse(__('Không tìm thấy giỏ hàng.'));
            }

            return $this->successResponse(message: __('Xóa giỏ hàng thành công.'));
        } catch (\Throwable $e) {
            return $this->errorResponse(message: $e->getMessage(), statusCode: 400);
        }
    }

    public function bulk(BulkActionRequest $request, BulkActionRegistry $registry): JsonResponse|RedirectResponse
    {
        $action = $request->input('action');
        $uuids = (array) $request->input('ids', []);

        $registry->dispatch('carts', $action, $uuids);
        $count = count($uuids);

        if ($request->expectsJson() || $request->ajax()) {
            return $this->successResponse(message: __('Đã xóa thành công :count giỏ hàng.', ['count' => $count]));
        }

        return redirect()->back()->with('success', __('Đã xóa thành công :count giỏ hàng.', ['count' => $count]));
    }
}
