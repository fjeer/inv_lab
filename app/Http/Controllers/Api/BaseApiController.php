<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BaseApiController extends Controller
{
    protected function applyTrashedFilter($query, Request $request): void
    {
        match ($request->input('trash_status')) {
            'with' => $query->withTrashed(),
            'only' => $query->onlyTrashed(),
            default => null,
        };
    }

    protected function getSearch(Request $request): ?string
    {
        if ($request->filled('search.value')) {
            return $request->input('search.value');
        }

        $search = $request->input('search');

        if (is_string($search) && strlen($search) > 0) {
            return $search;
        }

        return null;
    }

    protected function getPerPage(Request $request): int
    {
        if ($request->has('length')) {
            return max((int) $request->input('length', 10), 1);
        }

        if ($request->has('per_page')) {
            return max((int) $request->input('per_page', 10), 1);
        }

        return 10;
    }

    protected function getPageFromRequest(Request $request): int
    {
        if ($request->has('start') && $request->has('length')) {
            $length = max((int) $request->input('length', 10), 1);
            $start = max((int) $request->input('start', 0), 0);

            return (int) ($start / $length) + 1;
        }

        return (int) $request->input('page', 1);
    }

    /**
     * Send success response.
     */
    protected function sendSuccess($data, string $message = 'Success', int $code = 200): JsonResponse
    {
        $response = [
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ];

        return response()->json($response, $code);
    }

    /**
     * Send error response.
     */
    protected function sendError(string $error, array $errorMessages = [], int $code = 404): JsonResponse
    {
        $response = [
            'status' => 'error',
            'message' => $error,
        ];

        if (!empty($errorMessages)) {
            $response['errors'] = $errorMessages;
        }

        return response()->json($response, $code);
    }

    /**
     * Send paginated response.
     */
    protected function sendPaginated($paginator, string $message = 'Success'): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ]
        ]);
    }
}
