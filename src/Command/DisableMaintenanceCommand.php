<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Synolia\SyliusMaintenancePlugin\FileManager\ConfigurationFileManager;

final class DisableMaintenanceCommand extends Command
{
    protected static $defaultName = 'maintenance:disable';

    private ConfigurationFileManager $fileManager;

    private TranslatorInterface $translator;

    public function __construct(ConfigurationFileManager $fileManager, TranslatorInterface $translator)
    {
        $this->fileManager = $fileManager;
        $this->translator = $translator;

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
        $output->writeln($this->translator->trans($this->fileManager->deleteFile(ConfigurationFileManager::MAINTENANCE_FILE)));

        return 0;
    }
}