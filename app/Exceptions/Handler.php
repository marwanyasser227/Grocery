<?php

namespace App\Exceptions;

use DomainException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Stripe\Exception\SignatureVerificationException;
use UnexpectedValueException;

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
        $this->renderable(function (SignatureVerificationException|UnexpectedValueException $e, Request $request) {
            if ($request->is('api/*') || $request->is('stripe/*') || $request->wantsJson()) {
                return response('Invalid payload or signature.', 400);
            }
        });

        $this->renderable(function (ModelNotFoundException $e, Request $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Resource not found.',
                    'data' => null,
                ], 404);
            }
        });

        $this->renderable(function (InvalidArgumentException $e, Request $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                return response()->json([
                    'status' => 400,
                    'message' => $e->getMessage(),
                    'data' => null,
                ], 400);
            }
        });

        $this->renderable(function (DomainException $e, Request $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                return response()->json([
                    'status' => 402,
                    'message' => $e->getMessage(),
                    'data' => null,
                ], 402);
            }
        });
    }
}
