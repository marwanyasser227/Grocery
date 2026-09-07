<?php

namespace App\Actions\Address;

use App\Models\Address;
use Illuminate\Support\Facades\DB;

class UpdateAddressAction
{
    public function handle(Address $address, array $data): Address
    {
        return DB::transaction(function () use ($address, $data) {
            $data = $this->stripDuplicatePhoneCode($data);
            $address->fill($data)->save();

            return $address->fresh();
        });
    }

    private function stripDuplicatePhoneCode(array $data): array
    {
        if (isset($data['phone'], $data['country_code']) && $data['country_code'] !== '' && str_starts_with(trim($data['phone']), $data['country_code'])) {
            $data['phone'] = substr(trim($data['phone']), strlen($data['country_code']));
        }

        return $data;
    }
}
