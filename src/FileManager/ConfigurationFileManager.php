<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\FileManager;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Exception\DumpException;
use Symfony\Component\Yaml\Yaml;

final class ConfigurationFileManager
{
    public const ADD_IP_SUCCESS = 'maintenance.ui.message_success_ips';

    public const MAINTENANCE_FILE = 'maintenance.yaml';

    public const MAINTENANCE_TEMPLATE = 'templates/maintenance.html.twig';

    private const ADD_IP_SUCCESS_MESSAGE = 'maintenance.ui.message_success_ips';

    private const ADD_IP_ERROR_MESSAGE = 'maintenance.ui.message_error_ips';

    private const PLUGIN_ENABLED_MESSAGE = 'maintenance.ui.message_enabled';

    private const PLUGIN_DISABLED_MESSAGE = 'maintenance.ui.message_disabled';

    private const PLUGIN_DISABLED_404_MESSAGE = 'maintenance.ui.message_disabled_404';

    private Filesystem $filesystem;

    private KernelInterface $kernel;

    public function __construct(Filesystem $filesystem, KernelInterface $kernel)
    {
        $this->filesystem = $filesystem;
        $this->kernel = $kernel;
    }

    public function createFile(string $filename): string
    {
        $this->deleteFile($filename);
        $this->filesystem->touch($this->getPathtoFile($filename));

        return self::PLUGIN_ENABLED_MESSAGE;
    }

    public function deleteFile(string $filename): string
    {
        if (!$this->fileExists($filename)) {
            return self::PLUGIN_DISABLED_404_MESSAGE;
        }
        $this->filesystem->remove($this->getPathtoFile($filename));

        return self::PLUGIN_DISABLED_MESSAGE;
    }

    public function fileExists(string $filename): bool
    {
        if (!$this->filesystem->exists($this->getPathtoFile($filename))) {
            return false;
        }

        return true;
    }

    public function putIpsIntoFile(array $ipAddresses, string $filename): string
    {
        $ipAddressesArray = array_map('trim', $ipAddresses);

        foreach ($ipAddressesArray as $key => $ipAddress) {
            if ($this->isValidIp($ipAddress)) {
                continue;
            }
            unset($ipAddressesArray[$key]);
        }

        if ($this->fileExists($filename) && \count($ipAddressesArray) > 0) {
            $ipsArray = ['ips' => $ipAddressesArray];

            try {
                $yaml = Yaml::dump($ipsArray);
            } catch (DumpException $exception) {
                throw new DumpException('Unable to dump the YAML. ' . $exception->getMessage());
            }

            file_put_contents($this->getPathtoFile($filename), $yaml);

            return self::ADD_IP_SUCCESS_MESSAGE;
        }

        return self::ADD_IP_ERROR_MESSAGE;
    }

    public function convertStringToArray(string $data): array
    {
        return explode(',', $data);
    }

    public function addCustomMessage(string $content): void
    {
        $this->deleteFile(self::MAINTENANCE_TEMPLATE);
        $this->filesystem->appendToFile($this->getPathtoFile(self::MAINTENANCE_TEMPLATE), $content);
    }

    public function getPathtoFile(string $filename): string
    {
        $projectRootPath = $this->kernel->getProjectDir();

        return $projectRootPath . '/' . $filename;
    }

    private function isValidIp(string $ipAddress): bool
    {
        if (false === filter_var($ipAddress, \FILTER_VALIDATE_IP)) {
            return false;
        }

        return true;
    }
}
