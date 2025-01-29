<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Checker;

use Jaybizzle\CrawlerDetect\CrawlerDetect;
use Symfony\Component\HttpFoundation\Request;
use Synolia\SyliusMaintenancePlugin\Model\MaintenanceConfiguration;
use Synolia\SyliusMaintenancePlugin\Voter\IsMaintenanceVoterInterface;

class BotChecker implements IsMaintenanceCheckerInterface
{
    private const PRIORITY = 90;

    public static function getDefaultPriority(): int
    {
        return self::PRIORITY;
    }

    public function isMaintenance(MaintenanceConfiguration $configuration, Request $request): bool
    {
        $crawlerDetect = new CrawlerDetect();

        if ($configuration->allowBots() && $crawlerDetect->isCrawler($request->headers->get('User-Agent'))) {
            return IsMaintenanceVoterInterface::ACCESS_GRANTED;
        }

        return IsMaintenanceVoterInterface::ACCESS_DENIED;
    }
}
