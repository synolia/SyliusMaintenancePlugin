<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class SynoliaSyliusMaintenanceExtension extends Extension
{
    /**
     * @inheritdoc
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(\dirname(__DIR__, 2) . '/config'));

        $loader->load('services.yaml');
    }

    public function prepend(ContainerBuilder $container): void
    {
        $this->addCachePool($container);
    }

    public function getAlias(): string
    {
        return 'synolia_sylius_maintenance';
    }

    private function addCachePool(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('framework', [
            'cache' => [
                'pools' => [
                    'synolia_maintenance.cache' => [
                        'adapter' => 'cache.adapter.filesystem',
                        'public' => false,
                        'tags' => false,
                    ],
                ],
            ],
        ]);
    }
}
