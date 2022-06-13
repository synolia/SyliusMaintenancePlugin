<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Checker;

use Symfony\Component\HttpFoundation\Request;
use Synolia\SyliusMaintenancePlugin\Model\MaintenanceConfiguration;
use Synolia\SyliusMaintenancePlugin\Voter\IsMaintenanceVoterInterface;

class IpChecker implements IsMaintenanceCheckerInterface
{
    public static function getDefaultPriority(): int
    {
        return 90;
    }

    public function isMaintenance(MaintenanceConfiguration $configuration, Request $request): bool
    {
        $ipUser = $request->getClientIp();
        $authorizedIps = $configuration->getArrayIpsAddresses();

        if (in_array($ipUser, $authorizedIps, true)) {
            return IsMaintenanceVoterInterface::ACCESS_GRANTED;
        }

        return IsMaintenanceVoterInterface::ACCESS_DENIED;
    }
}
