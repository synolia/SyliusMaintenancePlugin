<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusMaintenancePlugin\PHPUnit;

use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Test\Services\DefaultChannelFactory;
use Symfony\Component\Yaml\Yaml;

final class MaintenanceByChannelTest extends AbstractWebTestCase
{
    public function testMaintenanceWithChannel(): void
    {
        /** @var ChannelRepositoryInterface $channelRepository */
        $channelRepository = $this->manager->getRepository(ChannelInterface::class);
        /** @var DefaultChannelFactory $channelFactory */
        $channelFactory = self::$container->get('sylius.behat.factory.default_channel');

        // set hostname for actuel channel
        $channel = $channelRepository->findOneByCode('FASHION_WEB');
        $channel->setHostname('fashion.localhost');

        // create a new channel for maintenance
        $maintenanceChannel = $channelFactory->create('maintenance', 'Maintenance channel')['channel'];
        $maintenanceChannel->setHostname('maintenance.localhost');
        $this->manager->persist($maintenanceChannel);

        $this->manager->flush();

        \file_put_contents(
            $this->file,
            Yaml::dump([
                'channels' => ['maintenance'],
            ])
        );

        self::$client->request('GET', 'http://fashion.localhost');
        $this->assertSiteIsUp();

        self::$client->request('GET', 'http://maintenance.localhost');
        $this->assertSiteIsInMaintenance();

        // remove maintenance channel
        $maintenanceChannel = $channelRepository->findOneByCode('maintenance');
        $this->manager->remove($maintenanceChannel);

        $this->manager->flush();
    }
}
