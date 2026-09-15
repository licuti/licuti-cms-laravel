<?php

namespace App\Core\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

trait ApiResponse
{
    /**
     * Trả về response thành công (200 OK)
     */
    protected function successResponse(mixed $data = null, string $message = 'Thao tác thành công', int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $statusCode);
    }

    /**
     * Trả về response tạo mới thành công (201 Created)
     */
    protected function createdResponse(mixed $data = null, string $message = 'Tạo mới thành công'): JsonResponse
    {
        return $this->successResponse($data, $message, 201);
    }

    /**
     * Trả về response không có nội dung (204 No Content)
     */
    protected function noContentResponse(): JsonResponse
    {
        return response()->json(null, 204);
    }

    /**
     * Trả về response danh sách có phân trang
     */
    protected function paginatedResponse(LengthAwarePaginator $paginator, string $message = 'Lấy danh sách thành công'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $paginator->items(),
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'from'         => $paginator->firstItem(),
                'to'           => $paginator->lastItem(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last'  => $paginator->url($paginator->lastPage()),
                'prev'  => $paginator->previousPageUrl(),
                'next'  => $paginator->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Trả về response lỗi
     */
    protected function errorResponse(string $message = 'Đã có lỗi xảy ra', int $statusCode = 400, mixed $errors = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
        ], $statusCode);
    }

    /**
     * Trả về response lỗi 404 Not Found
     */
    protected function notFoundResponse(string $message = 'Không tìm thấy dữ liệu'): JsonResponse
    {
        return $this->errorResponse($message, 404);
    }

    /**
     * Trả về response lỗi 403 Forbidden
     */
    protected function forbiddenResponse(string $message = 'Bạn không có quyền thực hiện thao tác này'): JsonResponse
    {
        return $this->errorResponse($message, 403);
    }

    /**
     * Trả về response lỗi 422 Unprocessable Entity
     */
    protected function validationErrorResponse(mixed $errors, string $message = 'Dữ liệu không hợp lệ'): JsonResponse
    {
        return $this->errorResponse($message, 422, $errors);
    }

    /**
     * Trả về response lỗi 500 Internal Server Error
     */
    protected function serverErrorResponse(string $message = 'Lỗi máy chủ nội bộ'): JsonResponse
    {
        return $this->errorResponse($message, 500);
    }
}