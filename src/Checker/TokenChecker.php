<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Checker;

use Symfony\Component\HttpFoundation\Request;
use Synolia\SyliusMaintenancePlugin\Model\MaintenanceConfiguration;
use Synolia\SyliusMaintenancePlugin\Storage\TokenStorage;
use Synolia\SyliusMaintenancePlugin\Voter\IsMaintenanceVoterInterface;

class TokenChecker implements IsMaintenanceCheckerInterface
{
    private const PRIORITY = 20;

    public static function getDefaultPriority(): int
    {
        return self::PRIORITY;
    }

    public function isMaintenance(MaintenanceConfiguration $configuration, Request $request): bool
    {
        if ($request->get('maintenanceToken') === $configuration->getToken()) {
            return IsMaintenanceVoterInterface::ACCESS_GRANTED;
        } elseif ($request->getSession()->get(TokenStorage::MAINTENANCE_TOKEN_NAME) === $configuration->getToken()) {
            return IsMaintenanceVoterInterface::ACCESS_GRANTED;
        }

        return IsMaintenanceVoterInterface::ACCESS_DENIED;
    }
}
