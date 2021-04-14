<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Factory;

use Synolia\SyliusMaintenancePlugin\FileManager\ConfigurationFileManager;
use Synolia\SyliusMaintenancePlugin\Model\MaintenanceConfiguration;

final class MaintenanceConfigurationFactory
{
    private ConfigurationFileManager $configurationFileManager;

    public function __construct(ConfigurationFileManager $configurationFileManager)
    {
        $this->configurationFileManager = $configurationFileManager;
    }

    public function get(): MaintenanceConfiguration
    {
        $maintenanceConfiguration = new MaintenanceConfiguration();
        if (!$this->configurationFileManager->fileExists(ConfigurationFileManager::MAINTENANCE_FILE)) {
            return $maintenanceConfiguration;
        }

        $maintenanceConfiguration = $maintenanceConfiguration->map($this->configurationFileManager->parseMaintenanceYaml());

        if ('' === $maintenanceConfiguration->getCustomMessage()) {
            return $maintenanceConfiguration;
        }

        if ($this->configurationFileManager->fileExists(ConfigurationFileManager::MAINTENANCE_TEMPLATE)) {
            $content = file_get_contents($this->configurationFileManager->getPathtoFile(ConfigurationFileManager::MAINTENANCE_TEMPLATE));
            if (!is_string($content)) {
                $content = '';
            }
            $maintenanceConfiguration->setCustomMessage($content);
        }

        return $maintenanceConfiguration;
    }

    public function getIpAddressesArray(array $ipAddresses): array
    {
        $ipAddressesArray = array_map('trim', $ipAddresses);

        foreach ($ipAddressesArray as $key => $ipAddress) {
            if ($this->isValidIp($ipAddress)) {
                continue;
            }
            unset($ipAddressesArray[$key]);
        }

        if ([] === $ipAddressesArray) {
            return [];
        }

        return ['ips' => $ipAddressesArray];
    }

    private function isValidIp(string $ipAddress): bool
    {
        if (false === filter_var($ipAddress, \FILTER_VALIDATE_IP)) {
            return false;
        }

        return true;
    }
}
