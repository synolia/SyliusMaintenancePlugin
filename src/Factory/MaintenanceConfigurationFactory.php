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

        if (!$this->configurationFileManager->hasMaintenanceFile()) {
            return $maintenanceConfiguration;
        }

        $maintenanceConfiguration->setEnabled(true);
        $maintenanceConfiguration = $maintenanceConfiguration->map($this->configurationFileManager->parseMaintenanceYaml());

        return $maintenanceConfiguration;
    }
}
