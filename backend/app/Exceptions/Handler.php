<?php

namespace App\Exceptions;

use App\Http\ApiResponse;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\{UnauthorizedException, ValidationException};
use Symfony\Component\HttpKernel\Exception\{
    AccessDeniedHttpException,
    BadRequestHttpException,
    NotFoundHttpException,
    UnauthorizedHttpException
};
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

        // Custom exception handling for not found (404).
        $this->renderable(function (NotFoundHttpException $e, $request) {
            if ($request->is('api/*')) {
                return ApiResponse::notFound('Record not found.');
            }
        });

        // Custom exception handling for access denied (403).
        $this->renderable(function (AccessDeniedHttpException $e, $request) {
            return ApiResponse::unauthorized('You are not authorized.');
        });

        // Custom exception handling for access denied (403) (illuminate).
        $this->renderable(function (UnauthorizedException $e, $request) {
            return ApiResponse::unauthorized('You are not authorized for this action.');
        });
        $this->renderable(function (UnauthorizedHttpException $e, $request) {
            return ApiResponse::unauthorized('You are not authorized for this action.');
        });

        // Custom exception handling for validation errors.
        $this->renderable(function (ValidationException $e, $request) {
            return ApiResponse::validationError($e);
        });

        // Custom exception handling for validation errors.
        $this->renderable(function (BadRequestHttpException $e, $request) {
            return ApiResponse::badRequest($e->getMessage());
        });
    }
}
