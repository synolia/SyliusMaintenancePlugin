<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Exporter;

use Synolia\SyliusMaintenancePlugin\FileManager\ConfigurationFileManager;
use Synolia\SyliusMaintenancePlugin\Model\MaintenanceConfiguration;

final class MaintenanceConfigurationExporter
{
    public function __construct(private ConfigurationFileManager $configurationFileManager)
    {
    }

    public function export(MaintenanceConfiguration $configuration): void
    {
        $this->configurationFileManager->deleteMaintenanceFile();
        if (!$configuration->isEnabled()) {
            return;
        }
        $dataToExport = [];

        $ipAddresses = $configuration->getArrayIpsAddresses();
        if ([] !== $ipAddresses) {
            $dataToExport['ips'] = $ipAddresses;
        }
        $customMessage = $configuration->getCustomMessage();
        if ('' !== $customMessage) {
            $dataToExport['custom_message'] = $customMessage;
        }
        $channels = $configuration->getChannels();
        if (0 !== count($channels)) {
            $dataToExport['channels'] = $channels;
        }
        $token = $configuration->getToken();
        if ('' !== $token) {
            $dataToExport['token'] = $token;
        }
        if ($configuration->allowBots()) {
            $dataToExport['allow_bots'] = true;
        }
        $scheduler = $this->getSchedulerArray($configuration->getStartDate(), $configuration->getEndDate());

        $this->configurationFileManager->createMaintenanceFile(array_merge(
            $scheduler,
            $dataToExport,
        ));
    }

    private function getSchedulerArray(?\DateTimeInterface $startDate, ?\DateTimeInterface $endDate): array
    {
        $scheduler = ['scheduler' => []];

        if (null !== $startDate) {
            $scheduler['scheduler']['start_date'] = $startDate->format('Y-m-d H:i:s');
        }
        if (null !== $endDate) {
            $scheduler['scheduler']['end_date'] = $endDate->format('Y-m-d H:i:s');
        }
        if ([] === $scheduler['scheduler']) {
            return [];
        }

        return $scheduler;
    }
}
