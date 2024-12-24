<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\FileManager;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;
use Webmozart\Assert\Assert;

final readonly class ConfigurationFileManager
{
    public const MAINTENANCE_CACHE_KEY = 'synolia_maintenance_configuration';

    private const MAINTENANCE_FILE = 'maintenance.yaml';

    public function __construct(
        private Filesystem $filesystem,
        #[Autowire(value: '%kernel.project_dir%/%synolia_maintenance_dir%')]
        private string $maintenanceDirectory,
    ) {
    }

    public function hasMaintenanceFile(): bool
    {
        return $this->filesystem->exists($this->getPathToFile(self::MAINTENANCE_FILE));
    }

    public function createMaintenanceFile(array $data): void
    {
        $maintenanceFilePath = $this->getPathToFile(self::MAINTENANCE_FILE);
        if (!$this->filesystem->exists($maintenanceFilePath)) {
            $this->filesystem->touch($maintenanceFilePath);
        }

        if ([] === $data) {
            return;
        }

        $yaml = Yaml::dump($data);
        $this->filesystem->dumpFile($maintenanceFilePath, $yaml);
    }

    public function deleteMaintenanceFile(): void
    {
        $this->filesystem->remove($this->getPathToFile(self::MAINTENANCE_FILE));
    }

    public function parseMaintenanceYaml(): ?array
    {
        try {
            $yaml = Yaml::parseFile($this->getPathToFile(self::MAINTENANCE_FILE));
            Assert::nullOrIsArray($yaml);

            return $yaml;
        } catch (ParseException) {
            return null;
        }
    }

    private function getPathToFile(string $filename): string
    {
        return $this->maintenanceDirectory . '/' . $filename;
    }
}
