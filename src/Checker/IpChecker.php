<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Checker;

use Symfony\Component\HttpFoundation\Request;
use Synolia\SyliusMaintenancePlugin\Model\MaintenanceConfiguration;
use Synolia\SyliusMaintenancePlugin\Voter\IsMaintenanceVoterInterface;

class IpChecker implements IsMaintenanceCheckerInterface
{
    private const PRIORITY = 90;

    public static function getDefaultPriority(): int
    {
        return self::PRIORITY;
    }

    public function isMaintenance(MaintenanceConfiguration $configuration, Request $request): bool
    {
        $authorizedIps = $configuration->getArrayIpsAddresses();

        if (in_array($request->getClientIp(), $authorizedIps, true)) {
            return IsMaintenanceVoterInterface::ACCESS_GRANTED;
        }

        return IsMaintenanceVoterInterface::ACCESS_DENIED;
    }
}
