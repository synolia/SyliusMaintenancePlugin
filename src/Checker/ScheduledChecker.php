<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Checker;

use Symfony\Component\HttpFoundation\Request;
use Synolia\SyliusMaintenancePlugin\Model\MaintenanceConfiguration;
use Synolia\SyliusMaintenancePlugin\Voter\IsMaintenanceVoterInterface;

class ScheduledChecker implements IsMaintenanceCheckerInterface
{
    private const PRIORITY = 80;

    public static function getDefaultPriority(): int
    {
        return self::PRIORITY;
    }

    public function isMaintenance(MaintenanceConfiguration $configuration, Request $request): bool
    {
        if (
            false === $this->isActuallyScheduledMaintenance($configuration) &&
            ($configuration->getStartDate() instanceof \DateTime ||
                $configuration->getEndDate() instanceof \DateTime)
        ) {
            return IsMaintenanceVoterInterface::ACCESS_GRANTED;
        }

        return IsMaintenanceVoterInterface::ACCESS_DENIED;
    }

    private function isActuallyScheduledMaintenance(MaintenanceConfiguration $maintenanceConfiguration): bool
    {
        $now = new \DateTime();
        $startDate = $maintenanceConfiguration->getStartDate();
        $endDate = $maintenanceConfiguration->getEndDate();
        // Now is between startDate and endDate
        if ($startDate instanceof \DateTime && $endDate instanceof \DateTime && ($now >= $startDate) && ($now <= $endDate)) {
            return true;
        }
        // No enddate provided, now is greater than startDate
        if ($startDate instanceof \DateTime && !$endDate instanceof \DateTime && ($now >= $startDate)) {
            return true;
        }

        // No startdate provided, now is before than enddate
        // No schedule date
        return $endDate instanceof \DateTime && !$startDate instanceof \DateTime && $now <= $endDate;
    }
}
