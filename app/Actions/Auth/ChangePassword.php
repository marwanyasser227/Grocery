<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;

class ChangePassword
{
    public function handle(User $user, array $data): void
    {
        $user->update([
            'password' => $data['password'],
        ]);
    }
}
