<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\User\CreateUserDTO;
use App\DTOs\User\UpdateUserDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\CreateUserRequest;
use App\Http\Requests\Admin\User\UpdateUserRequest;
use App\Services\Admin\User\UserService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
    ) {}

    public function index(Request $request): View
    {
        $filters  = $request->only(['keyword', 'status', 'role', 'is_admin']);
        $tab      = $request->input('tab', 'all'); // 'all' | 'trash'
        $perPage  = (int) $request->input('per_page', 15);

        // Xác định filter theo tab đang chọn
        if ($tab === 'trash') {
            $filters['only_trashed'] = true;
        }

        $users        = $this->userService->getPaginatedUsers($filters, $perPage);
        
        $users->getCollection()->transform(function ($user) use ($tab, $request) {
            $user->availableActions = $this->userService->resolveActions($user, $tab, $request->user());
            return $user;
        });

        $roles        = Role::all();
        $statuses     = \App\Core\Enums\UserStatus::cases();
        $totalCount   = \App\Models\User::count();
        $trashedCount = \App\Models\User::onlyTrashed()->count();

        $tabs = [
            ['key' => 'all',   'label' => 'Tất cả',    'count' => $totalCount,   'color' => 'blue'],
            ['key' => 'trash', 'label' => 'Thùng rác', 'count' => $trashedCount, 'color' => 'red'],
        ];

        return view('admin.users.index', compact('users', 'filters', 'roles', 'statuses', 'tab', 'tabs', 'totalCount', 'trashedCount'));
    }

    public function create(): View
    {
        $roles = Role::all();
        $statuses = \App\Core\Enums\UserStatus::cases();
        return view('admin.users.form', compact('roles', 'statuses'));
    }

    public function store(CreateUserRequest $request): RedirectResponse
    {
        $user = $this->userService->create(CreateUserDTO::fromRequest($request));

        if ($request->input('submit_action') === 'save_and_edit') {
            return redirect()->route('admin.users.edit', $user->uuid)->with('success', 'Tạo tài khoản người dùng mới thành công!');
        }

        return redirect()->route('admin.users.index')->with('success', 'Tạo tài khoản người dùng mới thành công!');
    }

    public function edit(string $uuid): View
    {
        $user = $this->userService->getByUuid($uuid);
        $roles = Role::all();
        $statuses = \App\Core\Enums\UserStatus::cases();
        return view('admin.users.form', compact('user', 'roles', 'statuses'));
    }

    public function update(UpdateUserRequest $request, string $uuid): RedirectResponse
    {
        $user = $this->userService->update(
            $uuid,
            UpdateUserDTO::fromRequest($request),
            $request->user()
        );

        if ($request->input('submit_action') === 'save_and_edit') {
            return redirect()->route('admin.users.edit', $user->uuid)->with('success', 'Cập nhật thông tin người dùng thành công!');
        }

        return redirect()->route('admin.users.index')->with('success', 'Cập nhật thông tin người dùng thành công!');
    }

    public function destroy(Request $request, string $uuid): RedirectResponse
    {
        $this->userService->delete($uuid, $request->user());

        return redirect()->route('admin.users.index')->with('success', 'Xóa người dùng thành công! Tài khoản đã được chuyển vào Thùng rác.');
    }

    public function restore(string $uuid): RedirectResponse
    {
        $this->userService->restore($uuid);

        return redirect()->route('admin.users.index', ['tab' => 'trash'])->with('success', 'Khôi phục tài khoản người dùng thành công!');
    }

    public function forceDelete(string $uuid): RedirectResponse
    {
        $user = \App\Models\User::withTrashed()->where('uuid', $uuid)->firstOrFail();

        if ($user->hasRole('super-admin')) {
            return redirect()->back()->with('error', 'Không thể xóa vĩnh viễn tài khoản Super Admin.');
        }

        $user->forceDelete();

        return redirect()->route('admin.users.index', ['tab' => 'trash'])->with('success', 'Đã xóa vĩnh viễn tài khoản người dùng.');
    }

    public function assignRoles(Request $request, string $uuid): RedirectResponse
    {
        $roles = $request->input('roles', []);
        $this->userService->syncRoles($uuid, $roles, $request->user());

        return redirect()->route('admin.users.index')->with('success', 'Phân quyền vai trò (Role) thành công!');
    }

    public function bulkAction(Request $request): RedirectResponse
    {
        $action = $request->input('action');
        $ids = $request->input('ids', []);

        if (empty($ids) || !is_array($ids)) {
            return redirect()->back()->with('warning', 'Vui lòng chọn ít nhất một bản ghi để thực hiện.');
        }

        $count = match ($action) {
            'delete' => $this->userService->bulkDelete($ids, $request->user()),
            'status_active' => $this->userService->bulkUpdateStatus($ids, 'active', $request->user()),
            'status_inactive' => $this->userService->bulkUpdateStatus($ids, 'inactive', $request->user()),
            'status_banned' => $this->userService->bulkUpdateStatus($ids, 'banned', $request->user()),
            'restore' => $this->userService->bulkRestore($ids),
            'force_delete' => $this->userService->bulkForceDelete($ids, $request->user()),
            default => 0,
        };

        return redirect()->back()->with('success', "Đã thực hiện hành động hàng loạt cho {$count} bản ghi thành công!");
    }
}
