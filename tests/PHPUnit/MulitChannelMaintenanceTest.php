<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusMaintenancePlugin\PHPUnit;

use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Test\Services\DefaultChannelFactory;
use Symfony\Component\Yaml\Yaml;

final class MulitChannelMaintenanceTest extends AbstractWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        /** @var ChannelRepositoryInterface $channelRepository */
        $channelRepository = $this->manager->getRepository(ChannelInterface::class);
        /** @var DefaultChannelFactory $channelFactory */
        $channelFactory = self::$kernel->getContainer()->get('sylius.behat.factory.default_channel');

        // set hostname for actuel channel
        $channel = $channelRepository->findOneByCode('FASHION_WEB');
        $channel->setHostname('fashion.localhost');

        // create a new channel for maintenance
        $maintenanceChannel = $channelFactory->create('test', 'Test channel')['channel'];
        $maintenanceChannel->setHostname('test.localhost');
        $this->manager->persist($maintenanceChannel);

        $this->manager->flush();
    }

    public function testMaintenanceIsNotEnabledWhenFileIsNotEnabled(): void
    {
        \file_put_contents(
            $this->file,
            Yaml::dump([
                'channels' => [
                    'FASHION_WEB',
                    'test',
                ],
                'enabled' => true,
            ]),
        );
        self::$client->request('GET', 'http://fashion.localhost/en_US/');
        $this->assertSiteIsInMaintenance();

        self::$client->request('GET', 'http://test.localhost/en_US/');
        $this->assertSiteIsInMaintenance();
    }
}
