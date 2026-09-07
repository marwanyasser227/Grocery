<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Services\AuthService;

class RegisterUser
{
    public function __construct(
        private AuthService $authService
    ) {}

    public function handle(array $data): array
    {
        return $this->authService->register($data);
    }
}
