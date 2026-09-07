<?php

namespace App\Actions\Address;

use App\Models\Address;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateAddressAction
{
    public function handle(User $user, array $data): Address
    {
        return DB::transaction(function () use ($user, $data) {
            $isFirstAddress = $user->addresses()->count() === 0;
            $data['is_default'] = ($data['is_default'] ?? false) || $isFirstAddress;
            $data = $this->stripDuplicatePhoneCode($data);

            return $user->addresses()->create($data);
        });
    }

    private function stripDuplicatePhoneCode(array $data): array
    {
        $phone = trim($data['phone'] ?? '');
        $code = trim($data['country_code'] ?? '');

        if ($code !== '' && str_starts_with($phone, $code)) {
            $data['phone'] = substr($phone, strlen($code));
        }

        return $data;
    }
}
