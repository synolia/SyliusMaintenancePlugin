<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Checker;

use Symfony\Component\HttpFoundation\Request;
use Synolia\SyliusMaintenancePlugin\Model\MaintenanceConfiguration;
use Synolia\SyliusMaintenancePlugin\Voter\IsMaintenanceVoterInterface;

class ProfilerChecker implements IsMaintenanceCheckerInterface
{
    private const PRIORITY = 30;

    public static function getDefaultPriority(): int
    {
        return self::PRIORITY;
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
