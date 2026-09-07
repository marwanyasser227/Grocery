<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Services\AuthService;

class VerifyOtp
{
    public function __construct(
        private AuthService $authService
    ) {}

    public function handle(array $data){
        return $this->authService->verifyOtp($data['identifier'] , $data['otp']);
    }
}
