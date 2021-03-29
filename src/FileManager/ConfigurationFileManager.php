<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\FileManager;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Exception\DumpException;
use Symfony\Component\Yaml\Yaml;

final class ConfigurationFileManager
{
    private const ADD_IP_SUCCESS = 'The ips were added to the file maintenance.yaml successfully.';

    private const ADD_IP_ERROR = 'An error occurred while adding ips addresses.';

    private const PLUGIN_ENABLED = 'The file maintenance.yaml was created successfully. The plugin was enabled.';

    private const PLUGIN_DISABLED = 'The file maintenance.yaml was deleted successfully. The plugin was disabled.';

    private const PLUGIN_DISABLED_FILE_NOT_FOUND = 'The file maintenance.yaml was not found. The plugin is disabled.';

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

        return self::PLUGIN_ENABLED;
    }

    public function deleteFile(string $filename): string
    {
        if (!$this->fileExists($filename)) {
            return self::PLUGIN_DISABLED_FILE_NOT_FOUND;
        }
        $this->filesystem->remove($this->getPathtoFile($filename));

        return self::PLUGIN_DISABLED;
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
        $ipAddressesArray = $this->trimSpaceOfArrayValues($ipAddresses);

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

            return self::ADD_IP_SUCCESS;
        }

        return self::ADD_IP_ERROR;
    }

    public function convertStringToArray(string $data): array
    {
        return explode(',', $data);
    }

    private function getPathtoFile(string $filename): string
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

    private function trimSpaceOfArrayValues(array $array): array
    {
        $result = [];
        foreach ($array as $value) {
            $result[] = trim($value);
        }

        return $result;
    }
}
