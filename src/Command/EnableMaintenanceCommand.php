<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Command;

use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Synolia\SyliusMaintenancePlugin\Exporter\MaintenanceConfigurationExporter;
use Synolia\SyliusMaintenancePlugin\Factory\MaintenanceConfigurationFactory;
use Synolia\SyliusMaintenancePlugin\FileManager\ConfigurationFileManager;
use Synolia\SyliusMaintenancePlugin\Model\MaintenanceConfiguration;

#[AsCommand(name: 'maintenance:enable', description: 'Turn your website under maintenance.')]
final class EnableMaintenanceCommand extends Command
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly MaintenanceConfigurationExporter $maintenanceExporter,
        private readonly MaintenanceConfigurationFactory $configurationFactory,
        private readonly CacheInterface $synoliaMaintenanceCache,
        private readonly ChannelRepositoryInterface $channelRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('ips_address', InputArgument::IS_ARRAY, 'Add ips addresses (separate multiple ips with a space)')
            ->addOption('disable-admin', null, InputOption::VALUE_NONE, 'disable admin access')
            ->setHelp('This command allows you to create the maintenance.yaml and also allows you to put the ips into this file.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $maintenanceConfiguration = $this->getMaintenanceConfiguration($input);
        $this->maintenanceExporter->export($maintenanceConfiguration);
        $this->synoliaMaintenanceCache->delete(ConfigurationFileManager::MAINTENANCE_CACHE_KEY);
        $output->writeln($this->translator->trans('maintenance.ui.message_enabled'));

        return 0;
    }

    private function getChannels(): array
    {
        $channels = $this->channelRepository->findAll();
        $channelToExport = [];

        /** @var ChannelInterface $channel */
        foreach ($channels as $channel) {
            $channelToExport[] = $channel->getCode();
        }

        return $channelToExport;
    }

    private function getMaintenanceConfiguration(InputInterface $input): MaintenanceConfiguration
    {
        $maintenanceConfiguration = $this->configurationFactory->get();
        $maintenanceConfiguration->setChannels($this->getChannels());
        $maintenanceConfiguration->setEnabled(true);

        $disableAdmin = (bool) $input->getOption('disable-admin');
        $maintenanceConfiguration->setAllowAdmins(!$disableAdmin);

        /** @var array $ipsAddress */
        $ipsAddress = $input->getArgument('ips_address');
        if ([] !== $ipsAddress) {
            $maintenanceConfiguration->setIpAddresses(implode(',', $ipsAddress));
        }

        return $maintenanceConfiguration;
    }
}
