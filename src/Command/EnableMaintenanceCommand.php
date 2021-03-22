<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

final class EnableMaintenanceCommand extends Command
{
    private const MAINTENANCE_FILE = 'maintenance.yaml';

    protected static $defaultName = 'maintenance:enable';

    private Filesystem $filesystem;

    private KernelInterface $kernel;

    public function __construct(
        Filesystem $filesystem,
        KernelInterface $kernel
    ) {
        $this->filesystem = $filesystem;
        $this->kernel = $kernel;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Turn your website under maintenance.')
            ->addArgument('ips_address', InputArgument::IS_ARRAY, 'Add ips addresses (separate multiple ips with a space)')
            ->setHelp('This command allows you to create the maintenance.yaml and also allows you to put the ips into this file.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln($this->createFile());
        /** @var array $ipsAddress */
        $ipsAddress = $input->getArgument('ips_address');

        if (0 < \count($ipsAddress)) {
            $output->writeln($this->putIpsIntoFile($ipsAddress));
        }

        return 0;
    }

    private function createFile(): string
    {
        if ($this->filesystem->exists($this->getPathtoFile())) {
            $this->filesystem->remove($this->getPathtoFile());
        }
        $this->filesystem->touch($this->getPathtoFile());

        return 'The file maintenance.yaml was created successfully';
    }

    private function putIpsIntoFile(array $ipAddresses): string
    {
        foreach ($ipAddresses as $key => $ipAddress) {
            if ($this->isValidIp($ipAddress)) {
                continue;
            }
            unset($ipAddresses[$key]);
        }

        if ($this->filesystem->exists($this->getPathtoFile()) && \count($ipAddresses) > 0) {
            $ipsArray = [
                'ips' => $ipAddresses,
            ];
            $yaml = Yaml::dump($ipsArray);
            file_put_contents($this->getPathtoFile(), $yaml);

            return 'The ips were added to the file maintenance.yaml successfully.';
        }

        return 'The file maintenance.yaml was not found. Please create it.';
    }

    private function getPathtoFile(): string
    {
        $projectRootPath = $this->kernel->getProjectDir();

        return  $projectRootPath . '/' . self::MAINTENANCE_FILE;
    }

    private function isValidIp(string $ipAddress): bool
    {
        if (false !== filter_var($ipAddress, \FILTER_VALIDATE_IP)) {
            return true;
        }

        return false;
    }
}
