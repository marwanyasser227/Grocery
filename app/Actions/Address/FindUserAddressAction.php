<?php

namespace App\Actions\Address;

use App\Models\Address;
use App\Models\User;

class FindUserAddressAction
{
    public function handle(User $user, string $addressId): Address
    {
        return $user->addresses()->findOrFail($addressId);
    }
}
