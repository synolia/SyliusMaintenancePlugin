<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Synolia\SyliusMaintenancePlugin\FileManager\ConfigurationFileManager;

final class DisableMaintenanceCommand extends Command
{
    protected static $defaultName = 'maintenance:disable';

    public function __construct(
        private ConfigurationFileManager $configurationFileManager,
        private TranslatorInterface $translator,
        private CacheInterface $synoliaMaintenanceCache,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Deactivate maintenance plugin')
            ->setHelp('This command allows you to delete the maintenance.yaml')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->configurationFileManager->deleteMaintenanceFile();
        $this->synoliaMaintenanceCache->delete(ConfigurationFileManager::MAINTENANCE_CACHE_KEY);

        $output->writeln($this->translator->trans('maintenance.ui.message_disabled'));

        return 0;
    }
}
