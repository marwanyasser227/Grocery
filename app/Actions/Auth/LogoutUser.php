<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use App\Services\AuthService;

class LogoutUser
{
    public function __construct(
        private AuthService $authService
    ) {}


       public function handle(User $user): void
    {
        $this->authService->logout($user);
    }
}
