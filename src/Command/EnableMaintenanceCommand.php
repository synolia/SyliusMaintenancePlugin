<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Command;

use App\Entity\Channel\Channel;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Synolia\SyliusMaintenancePlugin\Exporter\MaintenanceConfigurationExporter;
use Synolia\SyliusMaintenancePlugin\Factory\MaintenanceConfigurationFactory;
use Synolia\SyliusMaintenancePlugin\FileManager\ConfigurationFileManager;
use Synolia\SyliusMaintenancePlugin\Model\MaintenanceConfiguration;

final class EnableMaintenanceCommand extends Command
{
    protected static $defaultName = 'maintenance:enable';

    private MaintenanceConfiguration $maintenanceConfiguration;

    public function __construct(
        private TranslatorInterface $translator,
        private MaintenanceConfigurationExporter $maintenanceExporter,
        private MaintenanceConfigurationFactory $configurationFactory,
        private CacheInterface $synoliaMaintenanceCache,
        private ChannelRepositoryInterface $channelRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Turn your website under maintenance.')
            ->addArgument('ips_address', InputArgument::IS_ARRAY, 'Add ips addresses (separate multiple ips with a space)')
            ->setHelp('This command allows you to create the maintenance.yaml and also allows you to put the ips into this file.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->getMaintenanceConfiguration($input);
        $this->maintenanceExporter->export($this->maintenanceConfiguration);
        $this->synoliaMaintenanceCache->delete(ConfigurationFileManager::MAINTENANCE_CACHE_KEY);
        $this->defineOutputMessage($output);

        return 0;
    }

    private function getChannels():array
    {
        $channels = $this->channelRepository->findAll();
        $channelToExport = [];
        /** @var Channel $channel */
        foreach ($channels as $channel) {
            $channelToExport[] = $channel->getCode();
        }
        return $channelToExport;
    }

    private function getMaintenanceConfiguration(InputInterface $input):void
    {
        $this->maintenanceConfiguration = $this->configurationFactory->get();
        $this->maintenanceConfiguration->setChannels($this->getChannels());
        $this->maintenanceConfiguration->setEnabled($this->isMaintenanceConfigurationEnabled());

        /** @var array $ipsAddress */
        $ipsAddress = $input->getArgument('ips_address');
        if ([] !== $ipsAddress) {
            $this->maintenanceConfiguration->setIpAddresses(implode(',', $ipsAddress));
        }
    }

    private function isMaintenanceConfigurationEnabled():bool
    {
        if (count($this->maintenanceConfiguration->getChannels()) > 1){
            return false;
        }
        return true;
    }

    private function defineOutputMessage(OutputInterface $output):void
    {
        if ($this->maintenanceConfiguration->isEnabled()){
            $output->writeln($this->translator->trans('maintenance.ui.message_enabled'));
            return;
        }
        $output->writeln($this->translator->trans('maintenance.ui.cannot_enable_maintenance'));
    }
}
