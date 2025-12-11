<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class HandleApiErrors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            return $next($request);
        } catch (Throwable $exception) {
            return $this->handleException($request, $exception);
        }
    }

    /**
     * Handle exceptions and return proper API response
     */
    private function handleException(Request $request, Throwable $exception): JsonResponse
    {
        $statusCode = 500;
        $message = 'An unexpected error occurred';
        $error = 'Internal Server Error';

        // Handle specific exception types
        if ($exception instanceof \Illuminate\Validation\ValidationException) {
            $statusCode = 422;
            $error = 'Validation Error';
            $message = 'The provided data is invalid';

            return response()->json([
                'error' => $error,
                'message' => $message,
                'errors' => $exception->errors(),
            ], $statusCode);
        }

        if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
            $statusCode = 401;
            $error = 'Unauthorized';
            $message = 'Authentication failed. Please login first.';
        }

        if ($exception instanceof \Illuminate\Auth\Access\AuthorizationException) {
            $statusCode = 403;
            $error = 'Forbidden';
            $message = 'You do not have permission to perform this action.';
        }

        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
            $statusCode = 404;
            $error = 'Not Found';
            $message = 'The requested resource was not found.';
        }

        if ($exception instanceof \Illuminate\Database\QueryException) {
            $statusCode = 500;
            $error = 'Database Error';
            $message = 'A database error occurred. Please try again later.';

            // Don't expose raw database errors in production
            if (!app()->isProduction()) {
                $message = $exception->getMessage();
            }
        }

        if ($exception instanceof \Illuminate\Http\Exceptions\ThrottleRequestsException) {
            $statusCode = 429;
            $error = 'Too Many Requests';
            $message = 'You have made too many requests. Please try again later.';
        }

        // Log the exception
        \Illuminate\Support\Facades\Log::error('API Exception', [
            'exception' => class_basename($exception),
            'message' => $exception->getMessage(),
            'url' => $request->url(),
            'method' => $request->method(),
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'error' => $error,
            'message' => $message,
            'status' => $statusCode,
        ], $statusCode);
    }
}
