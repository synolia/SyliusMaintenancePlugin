<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Checker;

use Symfony\Component\HttpFoundation\Request;
use Synolia\SyliusMaintenancePlugin\Model\MaintenanceConfiguration;
use Synolia\SyliusMaintenancePlugin\Voter\IsMaintenanceVoterInterface;

class ProfilerChecker implements IsMaintenanceCheckerInterface
{
    public static function getDefaultPriority(): int
    {
        return 30;
    }

    public function isMaintenance(MaintenanceConfiguration $configuration, Request $request): bool
    {
        $getRequestUri = $request->getRequestUri();

        if (str_starts_with($getRequestUri, '/_profiler') || str_starts_with($getRequestUri, '/_wdt')) {
            return IsMaintenanceVoterInterface::ACCESS_GRANTED;
        }

        return IsMaintenanceVoterInterface::ACCESS_DENIED;
    }
}
