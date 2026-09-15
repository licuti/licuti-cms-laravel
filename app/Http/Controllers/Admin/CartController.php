<?php

namespace App\Http\Controllers\Admin;

use App\Core\Base\BaseController;
use App\DTOs\Cart\CartDTO;
use App\Http\Requests\Admin\Cart\StoreCartRequest;
use App\Http\Requests\Admin\Cart\UpdateCartRequest;
use App\Services\Admin\Cart\CartService;
use App\Repositories\Interfaces\CartRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class CartController extends BaseController
{
    public function __construct(
        private readonly CartService $service,
        private readonly CartRepositoryInterface $repository
    ) {
    }

    public function index(): View
    {
        $Carts = $this->service->getList(request()->all());
        return view('admin.carts.index', compact('Carts'));
    }

    public function create(): View
    {
        return view('admin.carts.form');
    }

    public function store(StoreCartRequest $request)
    {
        $dto = CartDTO::fromRequest($request);
        $this->service->create($dto);
        
        return redirect()->route('admin.carts.index')
            ->with('success', __('Thêm mới thành công.'));
    }

    public function edit(string $uuid): View
    {
        $cart = $this->repository->findByUuidOrFail($uuid);
        return view('admin.carts.form', compact('cart'));
    }

    public function update(UpdateCartRequest $request, string $uuid)
    {
        $dto = CartDTO::fromRequest($request);
        $this->service->update($uuid, $dto);
        
        return redirect()->route('admin.carts.index')
            ->with('success', __('Cập nhật thành công.'));
    }

    public function destroy(string $uuid)
    {
        try {
            $this->service->delete($uuid);
            return $this->successResponse(message: __('Xóa thành công.'));
        } catch (\Exception $e) {
            return $this->errorResponse(message: $e->getMessage(), code: 400);
        }
    }
}
