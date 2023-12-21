<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e): JsonResponse | bool
    {
        if ($request->is('api/*')) {
            try {
                $statusCode = (Response::$statusTexts[$e->getCode()]) ? $e->getCode() : 500;
            } catch (\Exception $ex) {
                $statusCode = (Str::contains($ex->getMessage(), "Undefined array key"))
                    ? 404
                    : 500;
            }

            return response()->json([
                'message' => $e->getMessage()
            ], $statusCode);
        }

        return false;
    }
}
