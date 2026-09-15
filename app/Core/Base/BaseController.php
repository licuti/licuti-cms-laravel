<?php

namespace App\Core\Base;

use App\Http\Controllers\Controller;
use App\Core\Traits\ApiResponse;

abstract class BaseController extends Controller
{
    // Nhúng Trait ApiResponse vào đây để mọi controller con đều dùng được hàm successResponse()
    use ApiResponse; 
}