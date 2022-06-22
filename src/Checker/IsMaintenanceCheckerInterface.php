<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Checker;

use Symfony\Component\HttpFoundation\Request;
use Synolia\SyliusMaintenancePlugin\Model\MaintenanceConfiguration;

interface IsMaintenanceCheckerInterface
{
    public const TAG_ID = 'synolia_maintenance.checker.is_maintenance';

    public static function getDefaultPriority(): int;

    public function isMaintenance(MaintenanceConfiguration $configuration, Request $request): bool;
}
