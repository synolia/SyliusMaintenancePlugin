<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Voter;

use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\HttpFoundation\Request;
use Synolia\SyliusMaintenancePlugin\Checker\IsMaintenanceCheckerInterface;
use Synolia\SyliusMaintenancePlugin\Model\MaintenanceConfiguration;

class IsMaintenanceVoter implements IsMaintenanceVoterInterface
{
    /** @param iterable<IsMaintenanceCheckerInterface> $checkers */
    public function __construct(
        #[TaggedIterator(IsMaintenanceCheckerInterface::class)]
        private readonly iterable $checkers,
    ) {
    }

    public function isMaintenance(MaintenanceConfiguration $configuration, Request $request): bool
    {
        foreach ($this->checkers as $checker) {
            $result = $checker->isMaintenance($configuration, $request);

            // As soon as a voter says that the site is accessible then we deactivate the maintenance
            if (IsMaintenanceVoterInterface::ACCESS_GRANTED === $result) {
                return false;
            }
        }

        return true;
    }
}
