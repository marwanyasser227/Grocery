<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Services\AuthService;

class LoginUser
{
    public function __construct(
        private AuthService $authService
    ) {}


    public function handle(array $data): array
    {
        return $this->authService->login(
            $data['login'],
            $data['password']
        );
    }
}
