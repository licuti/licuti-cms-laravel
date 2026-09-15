<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\MediaRepositoryInterface;
use App\Repositories\Interfaces\RoleRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Contracts\View\View;
use Spatie\Permission\Models\Permission;

class DashboardController extends Controller
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly RoleRepositoryInterface $roleRepository,
        private readonly MediaRepositoryInterface $mediaRepository,
    ) {}

    public function index(): View
    {
        $stats = [
            'users_count'       => $this->userRepository->count(),
            'roles_count'       => $this->roleRepository->count(),
            'permissions_count' => Permission::count(),
            'media_count'       => $this->mediaRepository->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
