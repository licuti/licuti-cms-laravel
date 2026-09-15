<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Role\CreateRoleRequest;
use App\Http\Requests\Admin\Role\UpdateRoleRequest;
use App\Services\Admin\Role\RoleService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    public function __construct(
        private readonly RoleService $roleService
    ) {}

    public function index(): View
    {
        $roles = $this->roleService->getAllWithCount();
        return view('admin.roles.index', compact('roles'));
    }

    public function store(CreateRoleRequest $request): RedirectResponse
    {
        $this->roleService->create(\App\DTOs\Role\CreateRoleDTO::fromRequest($request));
        return redirect()->route('admin.roles.index')->with('success', 'Tạo vai trò thành công!');
    }

    public function update(UpdateRoleRequest $request, string $id): RedirectResponse
    {
        $this->roleService->update($id, \App\DTOs\Role\UpdateRoleDTO::fromRequest($request));
        return redirect()->route('admin.roles.index')->with('success', 'Cập nhật vai trò thành công!');
    }

    public function destroy(string $id): RedirectResponse
    {
        $this->roleService->delete($id);
        return redirect()->route('admin.roles.index')->with('success', 'Xóa vai trò thành công!');
    }

    public function editPermissions(string $id): View
    {
        $role = $this->roleService->getByIdOrName($id);
        $permissions = $this->roleService->getAllPermissions();
        return view('admin.roles.permissions', compact('role', 'permissions'));
    }

    public function updatePermissions(Request $request, string $id): RedirectResponse
    {
        // Require specific permission or reuse roles.update. Using simple authorize since it's standard Request
        $this->authorize('roles.update');
        
        $role = $this->roleService->getByIdOrName($id);
        
        // Build DTO-like array for permissions update
        // Using existing UpdateRoleDTO, assuming it accepts permissions array
        $dto = new \App\DTOs\Role\UpdateRoleDTO(
            name: null,
            permissions: $request->input('permissions', [])
        );
        
        $this->roleService->update($id, $dto);
        return redirect()->route('admin.roles.index')->with('success', 'Cập nhật phân quyền thành công!');
    }
}
