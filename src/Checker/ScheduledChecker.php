<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Checker;

use Symfony\Component\HttpFoundation\Request;
use Synolia\SyliusMaintenancePlugin\Model\MaintenanceConfiguration;
use Synolia\SyliusMaintenancePlugin\Voter\IsMaintenanceVoterInterface;

class ScheduledChecker implements IsMaintenanceCheckerInterface
{
    public static function getDefaultPriority(): int
    {
        return 80;
    }

    public function isMaintenance(MaintenanceConfiguration $configuration, Request $request): bool
    {
        if (false === $this->isActuallyScheduledMaintenance($configuration) &&
            (null !== $configuration->getStartDate() ||
                null !== $configuration->getEndDate())
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
        if ($startDate !== null && $endDate !== null && ($now >= $startDate) && ($now <= $endDate)) {
            return true;
        }
        // No enddate provided, now is greater than startDate
        if ($startDate !== null && $endDate === null && ($now >= $startDate)) {
            return true;
        }
        // No startdate provided, now is before than enddate
        if ($endDate !== null && $startDate === null && ($now <= $endDate)) {
            return true;
        }
        // No schedule date
        return false;
    }
}
