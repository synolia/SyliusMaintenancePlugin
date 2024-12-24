<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Checker;

use Symfony\Component\HttpFoundation\Request;
use Synolia\SyliusMaintenancePlugin\Model\MaintenanceConfiguration;
use Synolia\SyliusMaintenancePlugin\Voter\IsMaintenanceVoterInterface;

class EnabledChecker implements IsMaintenanceCheckerInterface
{
    private const PRIORITY = 100;

    public static function getDefaultPriority(): int
    {
        return self::PRIORITY;
    }

    public function isMaintenance(MaintenanceConfiguration $configuration, Request $request): bool
    {
        if ($configuration->isEnabled()) {
            return IsMaintenanceVoterInterface::ACCESS_DENIED;
        }

        return IsMaintenanceVoterInterface::ACCESS_GRANTED;
    }
}
