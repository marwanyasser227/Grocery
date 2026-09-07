<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use App\Services\AuthService;


class DeleteAccount
{
    public function __construct(
        protected AuthService $authService
    ) {}

    public function handle(User $user): void
    {
        $this->authService->deleteAccount($user);
    }
}

