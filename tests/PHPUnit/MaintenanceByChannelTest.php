<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusMaintenancePlugin\PHPUnit;

use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Test\Services\DefaultChannelFactory;
use Symfony\Component\Yaml\Yaml;

final class MaintenanceByChannelTest extends AbstractWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

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
    }

    protected function tearDown(): void
    {
        /** @var ChannelRepositoryInterface $channelRepository */
        $channelRepository = $this->manager->getRepository(ChannelInterface::class);

        // remove maintenance channel
        $maintenanceChannel = $channelRepository->findOneByCode('maintenance');
        $this->manager->remove($maintenanceChannel);

        $this->manager->flush();

        parent::tearDown();
    }

    public function testMaintenanceWithChannel(): void
    {
        \file_put_contents(
            $this->file,
            Yaml::dump([
                'channels' => ['maintenance'],
            ])
        );

        self::$client->request('GET', 'http://fashion.localhost/en_US/');
        $this->assertSiteIsUp();

        self::$client->request('GET', 'http://maintenance.localhost/en_US/');
        $this->assertSiteIsInMaintenance();
    }
}
