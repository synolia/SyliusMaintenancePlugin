<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\Factory;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Synolia\SyliusMaintenancePlugin\FileManager\ConfigurationFileManager;
use Synolia\SyliusMaintenancePlugin\Model\MaintenanceConfiguration;

final class MaintenanceConfigurationFactory
{
    public function __construct(private ConfigurationFileManager $configurationFileManager)
    {
    }

    public function get(): MaintenanceConfiguration
    {
        $maintenanceConfiguration = new MaintenanceConfiguration();

        if (!$this->configurationFileManager->hasMaintenanceFile()) {
            return $maintenanceConfiguration;
        }

        $maintenanceConfiguration->setEnabled(true);

        $this::map($maintenanceConfiguration, $this->configurationFileManager->parseMaintenanceYaml());

        return $maintenanceConfiguration;
    }

    public static function map(MaintenanceConfiguration $maintenanceConfiguration, ?array $options): void
    {
        if (null === $options) {
            return;
        }

        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'ips' => [],
            'channels' => [],
            'scheduler' => [
                'start_date' => '',
                'end_date' => '',
            ],
            'custom_message' => '',
            'token' => '',
            'allow_bots' => false,
        ]);
        $options = $resolver->resolve($options);

        $ips = implode(',', $options['ips']);
        $startDate = \DateTime::createFromFormat(
            'Y-m-d H:i:s',
            \array_key_exists('start_date', $options['scheduler']) ? $options['scheduler']['start_date'] : '',
        );
        $endDate = \DateTime::createFromFormat(
            'Y-m-d H:i:s',
            \array_key_exists('end_date', $options['scheduler']) ? $options['scheduler']['end_date'] : '',
        );

        $maintenanceConfiguration
            ->setIpAddresses($ips)
            ->setChannels($options['channels'])
            ->setStartDate(false === $startDate ? null : $startDate)
            ->setEndDate(false === $endDate ? null : $endDate)
            ->setCustomMessage($options['custom_message'])
            ->setToken($options['token'])
            ->setAllowBots($options['allow_bots'])
        ;
    }
}
