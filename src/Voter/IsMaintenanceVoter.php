<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Voter;

use Symfony\Component\HttpFoundation\Request;
use Synolia\SyliusMaintenancePlugin\Model\MaintenanceConfiguration;

class IsMaintenanceVoter implements IsMaintenanceVoterInterface
{
    /** @var array<\Synolia\SyliusMaintenancePlugin\Checker\IsMaintenanceCheckerInterface> */
    private array $isMaintenanceCheckers;

    public function __construct(\Traversable $checkers)
    {
        $this->isMaintenanceCheckers = iterator_to_array($checkers);
    }

    public function isMaintenance(MaintenanceConfiguration $configuration, Request $request): bool
    {
        foreach ($this->isMaintenanceCheckers as $checker) {
            $result = $checker->isMaintenance($configuration, $request);

            // As soon as a voter says that the site is accessible then we deactivate the maintenance
            if (IsMaintenanceVoterInterface::ACCESS_GRANTED === $result) {
                return false;
            }
        }

        return true;
    }
}
