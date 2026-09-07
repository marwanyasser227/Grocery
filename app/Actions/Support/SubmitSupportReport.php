<?php

namespace App\Actions\Support;

use App\Models\SupportReport;
use App\Models\User;
use App\Services\SupportService;

class SubmitSupportReport
{
    public function __construct(
        private readonly SupportService $supportService
    ) {}

    public function handle(
        User $user,
        array $data,
        ?string $ipAddress,
        ?string $userAgent
    ): SupportReport {
        return $this->supportService->submitReport(
            $user,
            $data,
            $ipAddress,
            $userAgent
        );
    }
}