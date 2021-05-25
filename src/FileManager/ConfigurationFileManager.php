<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\FileManager;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

final class ConfigurationFileManager
{
    private const MAINTENANCE_FILE = 'maintenance.yaml';

    private Filesystem $filesystem;

    private string $maintenanceDirectory;

    public function __construct(Filesystem $filesystem, KernelInterface $kernel, string $maintenanceDirectory)
    {
        $this->filesystem = $filesystem;
        $this->maintenanceDirectory = $kernel->getProjectDir() . '/' . $maintenanceDirectory;
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
            return Yaml::parseFile($this->getPathToFile(self::MAINTENANCE_FILE));
        } catch (ParseException $exception) {
            return null;
        }
    }

    private function getPathToFile(string $filename): string
    {
        return $this->maintenanceDirectory . '/' . $filename;
    }
}
