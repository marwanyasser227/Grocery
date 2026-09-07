<?php
declare(strict_types =1);
namespace App\Http\Controllers\Api;
//! actions 

use App\Actions\Auth\ChangePassword;
use App\Actions\Auth\DeleteAccount;
use App\Actions\Auth\LoginUser;
use App\Actions\Auth\LogoutUser;
use App\Actions\Auth\RegisterUser;
use App\Actions\Auth\ResetPassword;
use App\Actions\Auth\UserForgetPassword;
use App\Actions\Auth\VerifyOtp;
use App\Http\Controllers\Controller;

//! requests classes
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\DeleteAccountRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\VerifyOtpRequest;

//! resource of user
use App\Http\Resources\UserResource;

//! facades 
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use \App\Traits\ApiResponse;

    /**
     * Register a new user
     */
    public function register(RegisterRequest $request, RegisterUser $action): JsonResponse
    {
        try {

            $result = $action->handle($request->validated());
            return static::success(
                'Registration successful',
                [
                    'user' => new UserResource($result['user']),
                    'token' => $result['token']
                ],
                201
            );
        } catch (\Exception $e) {
            return static::error("Registration failed.",  $e, 500);
        }
    }

    /**
     * Login user
     */
    public function login(LoginRequest $request, LoginUser $action): JsonResponse
    {
        $result = $action->handle($request->validated()); //! return user and token

        return static::success(
            'Login successful',
            [
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
            ]
        );
    }
    /**
     * Logout user
     */
    public function logout(Request $request, LogoutUser $action): JsonResponse
    {
        try {
            $action->handle($request->user());

            return self::success("Logout successfull", null, 200);
        } catch (\Exception $e) {

            return self::error("Logout Failed", null, 500);
        }
    }

    /**
     * Forgot password - send OTP
     */
    public function forgotPassword(ForgotPasswordRequest $request, UserForgetPassword $action): JsonResponse
    {

        try {
            $action->handle($request->validated());
            return self::success("OTP sent successfully . Please check your email", null, 200);
        } catch (\Exception $e) {
            return self::error("Failed to send OTP", null, 500);
        }
    }

    /**
     * Verify OTP
     */
    public function verifyOtp(VerifyOtpRequest $request, VerifyOtp $action): JsonResponse
    {
        try {
            $isValid = $action->handle(
                $request->validated()
            );

            if (! $isValid) {

                return self::error("Invalid or expired OTP", null, 400);
            }

            return self::success("OTP verified successfully", null, 200);
        } catch (\Exception $e) {

            return self::error("OTP verification failed", null, 500);
        }
    }

    /**
     * Reset password
     */
    public function resetPassword(ResetPasswordRequest $request, ResetPassword $action): JsonResponse
    {
        try {
            $action->handle($request->validated());

            return self::success("Password reset successfully", null, 200);
        } catch (\Illuminate\Validation\ValidationException $e) {

            return self::error("Password reset failed", $e, 400);
        } catch (\Exception $e) {

            // ], 500);
            return self::error("Password reset failed", $e, 500);
        }
    }

    /**
     * Get authenticated user
     */
    public function me(Request $request): JsonResponse
    {
        return self::success(
            "User retrieved successfully",
            [
                'user' => new UserResource($request->user()),
            ],
            200
        );
    }

    public function deleteAccount(
        DeleteAccountRequest $request,
        DeleteAccount $action
    ): JsonResponse {
        try {
            $action->handle($request->user());

            return self::success(
                "Account deleted successfully"
            );
        } catch (\Exception $e) {
            return self::error(
                "Failed to delete account",
                null,
                500
            );
        }
    }

    /**
     * Change password for authenticated user
     */
    public function changePassword(
        ChangePasswordRequest $request,
        ChangePassword $action
    ): JsonResponse {
        try {
            $action->handle(
                $request->user(),
                $request->validated()
            );

            return self::success(
                "Password changed successfully"
            );
        } catch (\Exception $e) {
            return self::error(
                "Failed to change password",
                $e,
                500
            );
        }
    }
}
