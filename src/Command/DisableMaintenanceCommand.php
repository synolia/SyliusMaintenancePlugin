<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Synolia\SyliusMaintenancePlugin\FileManager\ConfigurationFileManager;

final class DisableMaintenanceCommand extends Command
{
    private const MAINTENANCE_FILE = 'maintenance.yaml';

    protected static $defaultName = 'maintenance:disable';

    private ConfigurationFileManager $fileManager;

    public function __construct(ConfigurationFileManager $fileManager)
    {
        $this->fileManager = $fileManager;

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
        $output->writeln($this->fileManager->deleteFile(self::MAINTENANCE_FILE));

        return 0;
    }
}
