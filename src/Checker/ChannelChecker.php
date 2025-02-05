<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Checker;

use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Synolia\SyliusMaintenancePlugin\Model\MaintenanceConfiguration;
use Synolia\SyliusMaintenancePlugin\Voter\IsMaintenanceVoterInterface;

class ChannelChecker implements IsMaintenanceCheckerInterface
{
    private const PRIORITY = 60;

    public function __construct(
        protected ChannelRepositoryInterface $channelRepository,
        protected ChannelContextInterface $channelContext,
    ) {
    }

    public static function getDefaultPriority(): int
    {
        return self::PRIORITY;
    }

    public function isMaintenance(MaintenanceConfiguration $configuration, Request $request): bool
    {
        if ($this->channelRepository->count([]) > 1 && !\in_array($this->channelContext->getChannel()->getCode(), $configuration->getChannels(), true)) {
            return IsMaintenanceVoterInterface::ACCESS_GRANTED;
        }

        return IsMaintenanceVoterInterface::ACCESS_DENIED;
    }
}
