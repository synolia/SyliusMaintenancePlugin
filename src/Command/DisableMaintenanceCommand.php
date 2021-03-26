<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

final class DisableMaintenanceCommand extends Command
{
    private const MAINTENANCE_FILE = 'maintenance.yaml';

    protected static $defaultName = 'maintenance:disable';

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
            ->setDescription('Deactivate maintenance plugin')
            ->setHelp('This command allows you to delete the maintenance.yaml');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->filesystem->exists($this->getPathtoFile())) {
            $this->filesystem->remove($this->getPathtoFile());
            $output->writeln('The file maintenance.yaml was deleted successfully');

            return 0;
        }
        $output->writeln('The file maintenance.yaml was not found. No need to delete it');

        return 0;
    }

    private function getPathtoFile(): string
    {
        $projectRootPath = $this->kernel->getProjectDir();

        return  $projectRootPath . '/' . self::MAINTENANCE_FILE;
    }
}
