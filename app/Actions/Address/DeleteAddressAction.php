<?php

namespace App\Actions\Address;

use App\Models\Address;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DeleteAddressAction
{
    public function handle(User $user, Address $address): void
    {
        DB::transaction(function () use ($user, $address) {
            $wasDefault = $address->is_default;
            $address->delete();

            if ($wasDefault) {
                $newDefault = $user->addresses()->first();
                $newDefault?->update(['is_default' => true]);
            }
        });
    }
}
