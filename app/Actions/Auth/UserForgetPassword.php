<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Services\AuthService;

class UserForgetPassword
{
    public function __construct(
        private AuthService $authService
    ) {}

    public function handle(array $data): void
    {
        $this->authService->forgotPassword(
            $data['identifier']
        );
    }
}