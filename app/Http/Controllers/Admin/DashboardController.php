<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\PostRepositoryInterface;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly PostRepositoryInterface $postRepository,
        private readonly CategoryRepositoryInterface $categoryRepository,
    ) {}

    public function index(): View
    {
        $stats = [
            'users_count'      => $this->userRepository->count(),
            'products_count'   => $this->productRepository->count(),
            'posts_count'      => $this->postRepository->count(),
            'categories_count' => $this->categoryRepository->count(),
        ];

        $recentProducts = $this->productRepository->getActivePaginated([], 5);
        $recentPosts    = $this->postRepository->getFiltered(['per_page' => 5]);
        $recentUsers    = $this->userRepository->getPaginatedUsers([], 5);

        return view('admin.dashboard', compact('stats', 'recentProducts', 'recentPosts', 'recentUsers'));
    }
}
