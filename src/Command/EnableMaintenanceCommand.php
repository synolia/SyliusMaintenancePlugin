<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Synolia\SyliusMaintenancePlugin\Exporter\MaintenanceConfigurationExporter;
use Synolia\SyliusMaintenancePlugin\Factory\MaintenanceConfigurationFactory;

final class EnableMaintenanceCommand extends Command
{
    protected static $defaultName = 'maintenance:enable';

    private TranslatorInterface $translator;

    private MaintenanceConfigurationExporter $maintenanceExporter;

    private MaintenanceConfigurationFactory $configurationFactory;

    public function __construct(
        TranslatorInterface $translator,
        MaintenanceConfigurationExporter $maintenanceExporter,
        MaintenanceConfigurationFactory $configurationFactory
    ) {
        $this->translator = $translator;
        $this->maintenanceExporter = $maintenanceExporter;
        $this->configurationFactory = $configurationFactory;

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
        $maintenanceConfiguration = $this->configurationFactory->get();
        $maintenanceConfiguration->setEnabled(true);
        /** @var array $ipsAddress */
        $ipsAddress = $input->getArgument('ips_address');
        if ([] !== $ipsAddress) {
            $maintenanceConfiguration->setIpAddresses(implode(',', $ipsAddress));
        }
        $this->maintenanceExporter->export($maintenanceConfiguration);
        $output->writeln($this->translator->trans('maintenance.ui.message_enabled'));

        return 0;
    }
}
