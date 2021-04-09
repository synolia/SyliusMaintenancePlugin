<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\FileManager;

use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

final class ConfigurationFileManager
{
    public const MAINTENANCE_FILE = 'maintenance.yaml';

    public const MAINTENANCE_TEMPLATE = 'templates/maintenance.html.twig';

    private const PLUGIN_ENABLED_MESSAGE = 'maintenance.ui.message_enabled';

    private const PLUGIN_DISABLED_MESSAGE = 'maintenance.ui.message_disabled';

    private Filesystem $filesystem;

    private KernelInterface $kernel;

    public function __construct(Filesystem $filesystem, KernelInterface $kernel)
    {
        $this->filesystem = $filesystem;
        $this->kernel = $kernel;
    }

    public function createFile(): string
    {
        $this->deleteFile();
        $this->filesystem->touch($this->getPathtoFile(self::MAINTENANCE_FILE));

        return self::PLUGIN_ENABLED_MESSAGE;
    }

    public function deleteFile(): string
    {
        try {
            $this->filesystem->remove($this->getPathtoFile(self::MAINTENANCE_FILE));
            $this->filesystem->remove($this->getPathtoFile(self::MAINTENANCE_TEMPLATE));

            return self::PLUGIN_DISABLED_MESSAGE;
        } catch (IOException $exception) {
            throw new IOException($exception->getMessage());
        }
    }

    public function fileExists(string $filename): bool
    {
        return $this->filesystem->exists($this->getPathtoFile($filename));
    }

    public function parseMaintenanceYaml(): ?array
    {
        try {
            return Yaml::parseFile($this->getPathtoFile(self::MAINTENANCE_FILE));
        } catch (ParseException $exception) {
            return null;
        }
    }

    public function getPathtoFile(string $filename): string
    {
        $projectRootPath = $this->kernel->getProjectDir();

        return $projectRootPath . '/' . $filename;
    }

    public function getDataFromYaml(): array
    {
        $data = [
            'enabled' => true,
            'ipAddresses' => null,
            'customMessage' => null,
            'startDate' => null,
            'endDate' => null,
        ];

        if (!$this->fileExists(self::MAINTENANCE_FILE)) {
            return $data;
        }

        if ($this->fileExists(self::MAINTENANCE_TEMPLATE)) {
            $data['customMessage'] = file_get_contents($this->getPathtoFile(self::MAINTENANCE_TEMPLATE));
        }

        $maintenanceYaml = $this->parseMaintenanceYaml();

        if (null === $maintenanceYaml) {
            return $data;
        }

        if (array_key_exists('ips', $maintenanceYaml)) {
            $data['ipAddresses'] = implode(',', $maintenanceYaml['ips']);
        }

        if (array_key_exists('scheduler', $maintenanceYaml)) {
            $data['startDate'] = \DateTime::createFromFormat('Y-m-d H:i:s', $maintenanceYaml['scheduler']['start_date'] ?? '');
            $data['endDate'] = \DateTime::createFromFormat('Y-m-d H:i:s', $maintenanceYaml['scheduler']['end_date'] ?? '');
        }

        return $data;
    }

    public function getSchedulerArray(?\DateTime $startDate, ?\DateTime $endDate): array
    {
        if (null === $startDate && null === $endDate) {
            return [];
        }

        $scheduler = ['scheduler' => []];

        if (null !== $startDate) {
            $scheduler['scheduler'] += ['start_date' => $startDate->format('Y-m-d H:i:s')];
        }
        if (null !== $endDate) {
            $scheduler['scheduler'] += ['end_date' => $endDate->format('Y-m-d H:i:s')];
        }
        if ([] === $scheduler['scheduler']) {
            return [];
        }

        return $scheduler;
    }
}
