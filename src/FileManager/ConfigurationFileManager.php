<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\FileManager;

use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Exception\DumpException;
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

    public function getIpAddressesArray(array $ipAddresses): array
    {
        $ipAddressesArray = array_map('trim', $ipAddresses);

        foreach ($ipAddressesArray as $key => $ipAddress) {
            if ($this->isValidIp($ipAddress)) {
                continue;
            }
            unset($ipAddressesArray[$key]);
        }

        if (!$this->fileExists(self::MAINTENANCE_FILE) || [] === $ipAddressesArray) {
            return [];
        }

        return ['ips' => $ipAddressesArray];
    }

    public function saveYamlConfiguration(array $data): void
    {
        try {
            $yaml = Yaml::dump($data);
        } catch (DumpException $exception) {
            throw new DumpException('Unable to dump the YAML. ' . $exception->getMessage());
        }

        file_put_contents($this->getPathtoFile(self::MAINTENANCE_FILE), $yaml);
    }

    public function saveTemplate(?string $customMessage): void
    {
        if (null === $customMessage) {
            return;
        }

        if ($this->fileExists(self::MAINTENANCE_TEMPLATE)) {
            $this->filesystem->remove($this->getPathtoFile(self::MAINTENANCE_TEMPLATE));
        }

        $this->filesystem->appendToFile($this->getPathtoFile(self::MAINTENANCE_TEMPLATE), $customMessage);
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
        ];

        if ($this->fileExists(self::MAINTENANCE_FILE)) {
            $maintenanceYaml = $this->parseMaintenanceYaml();
            if (null !== $maintenanceYaml && array_key_exists('ips', $maintenanceYaml)) {
                $data['ipAddresses'] = implode(',', $maintenanceYaml['ips']);
            }
        }
        if ($this->fileExists(self::MAINTENANCE_TEMPLATE)) {
            $data['customMessage'] = file_get_contents($this->getPathtoFile(self::MAINTENANCE_TEMPLATE));
        }

        return $data;
    }

    private function isValidIp(string $ipAddress): bool
    {
        if (false === filter_var($ipAddress, \FILTER_VALIDATE_IP)) {
            return false;
        }

        return true;
    }
}
