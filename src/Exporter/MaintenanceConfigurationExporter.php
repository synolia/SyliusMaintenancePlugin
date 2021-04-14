<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Exporter;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Exception\DumpException;
use Symfony\Component\Yaml\Yaml;
use Synolia\SyliusMaintenancePlugin\Factory\MaintenanceConfigurationFactory;
use Synolia\SyliusMaintenancePlugin\FileManager\ConfigurationFileManager;
use Synolia\SyliusMaintenancePlugin\Model\MaintenanceConfiguration;

final class MaintenanceConfigurationExporter
{
    private ConfigurationFileManager $configurationFileManager;

    private Filesystem $filesystem;

    private MaintenanceConfigurationFactory $configurationFactory;

    public function __construct(
        ConfigurationFileManager $configurationFileManager,
        Filesystem $filesystem,
        MaintenanceConfigurationFactory $configurationFactory
    ) {
        $this->configurationFileManager = $configurationFileManager;
        $this->filesystem = $filesystem;
        $this->configurationFactory = $configurationFactory;
    }

    public function export(MaintenanceConfiguration $configuration): void
    {
        $this->saveTemplate($configuration->getCustomMessage());

        if ('' === $configuration->getIpAddresses()) {
            return;
        }

        $ipAddresses = $this->configurationFactory->getIpAddressesArray(explode(',', $configuration->getIpAddresses()));
        $this->saveYamlConfiguration($ipAddresses);
    }

    public function saveYamlConfiguration(array $yamlData): void
    {
        if ([] === $yamlData) {
            return;
        }

        try {
            $yaml = Yaml::dump($yamlData);
        } catch (DumpException $exception) {
            throw new DumpException('Unable to dump the YAML. ' . $exception->getMessage());
        }

        file_put_contents($this->configurationFileManager->getPathtoFile(ConfigurationFileManager::MAINTENANCE_FILE), $yaml);
    }

    private function saveTemplate(string $templateContent): void
    {
        $this->filesystem->remove($this->configurationFileManager->getPathtoFile(ConfigurationFileManager::MAINTENANCE_TEMPLATE));

        if ('' !== $templateContent) {
            $this->filesystem->appendToFile($this->configurationFileManager->getPathtoFile(ConfigurationFileManager::MAINTENANCE_TEMPLATE), $templateContent);
        }
    }
}
