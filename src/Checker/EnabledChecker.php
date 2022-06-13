<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Checker;

use Symfony\Component\HttpFoundation\Request;
use Synolia\SyliusMaintenancePlugin\Model\MaintenanceConfiguration;
use Synolia\SyliusMaintenancePlugin\Voter\IsMaintenanceVoterInterface;

class EnabledChecker implements IsMaintenanceCheckerInterface
{
    public static function getDefaultPriority(): int
    {
        return 100;
    }

    public function isMaintenance(MaintenanceConfiguration $configuration, Request $request): bool
    {
        if ($configuration->isEnabled()) {
            return IsMaintenanceVoterInterface::ACCESS_DENIED;
        }

        return IsMaintenanceVoterInterface::ACCESS_GRANTED;
    }
}
