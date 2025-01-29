<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Checker;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\Request;
use Synolia\SyliusMaintenancePlugin\Model\MaintenanceConfiguration;

#[AutoconfigureTag]
interface IsMaintenanceCheckerInterface
{
    public static function getDefaultPriority(): int;

    public function isMaintenance(MaintenanceConfiguration $configuration, Request $request): bool;
}
