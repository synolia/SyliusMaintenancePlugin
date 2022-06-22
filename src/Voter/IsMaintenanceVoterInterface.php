<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Voter;

use Symfony\Component\HttpFoundation\Request;
use Synolia\SyliusMaintenancePlugin\Model\MaintenanceConfiguration;

interface IsMaintenanceVoterInterface
{
    public const ACCESS_GRANTED = true;

    public const ACCESS_DENIED = false;

    public function isMaintenance(MaintenanceConfiguration $configuration, Request $request): bool;
}
