<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusMaintenancePlugin\PHPUnit;

use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Test\Services\DefaultChannelFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Synolia\SyliusMaintenancePlugin\Factory\MaintenanceConfigurationFactory;
use Synolia\SyliusMaintenancePlugin\Voter\IsMaintenanceVoterInterface;

final class MultiChannelMaintenanceTest extends AbstractWebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        /** @var ChannelRepositoryInterface $channelRepository */
        $channelRepository = $this->manager->getRepository(ChannelInterface::class);
        /** @var DefaultChannelFactory $channelFactory */
        $channelFactory = self::$kernel->getContainer()->get('sylius.behat.factory.default_channel');

        // set hostname for actual channel
        $channel = $channelRepository->findOneByCode('FASHION_WEB');
        $channel->setHostname('fashion.localhost');

        // create a new channel for maintenance
        $maintenanceChannel = $channelFactory->create('test', 'Test channel')['channel'];
        $maintenanceChannel->setHostname('test.localhost');
        $this->manager->persist($maintenanceChannel);

        $this->manager->flush();
    }

    protected function tearDown(): void
    {
        /** @var ChannelRepositoryInterface $channelRepository */
        $channelRepository = $this->manager->getRepository(ChannelInterface::class);

        // remove test channel
        $maintenanceChannel = $channelRepository->findOneByCode('test');
        $this->manager->remove($maintenanceChannel);

        $this->manager->flush();

        parent::tearDown();
    }

    public function testMaintenanceIsNotEnabledWhenFileIsNotEnabled(): void
    {
        $this->configurationFileManager->createMaintenanceFile([
            'channels' => [
                'FASHION_WEB',
                'test',
            ],
            'enabled' => true,
        ]);
        self::$client->request('GET', 'http://fashion.localhost/en_US/');
        $this->assertSiteIsInMaintenance();

        self::$client->request('GET', 'http://test.localhost/en_US/');
        $this->assertSiteIsInMaintenance();
    }

    public function testMaintenanceIsDisplayedOnAHostWithoutChannel(): void
    {
        $this->configurationFileManager->createMaintenanceFile([
            'channels' => ['FASHION_WEB', 'test'],
        ]);

        self::$client->request('GET', 'http://unknown.localhost/en_US/');

        $this->assertSiteIsInMaintenance();
    }

    public function testProfilerIsReachableOnAHostWithoutChannel(): void
    {
        $this->configurationFileManager->createMaintenanceFile([
            'channels' => ['FASHION_WEB', 'test'],
        ]);

        // The profiler is disabled in the test environment, so the voter is asked directly
        self::assertFalse($this->isMaintenance(Request::create('http://unknown.localhost/_profiler/123456')));
    }

    public function testMaintenanceTokenIsAcceptedOnAHostWithoutChannel(): void
    {
        $this->configurationFileManager->createMaintenanceFile([
            'channels' => ['FASHION_WEB', 'test'],
            'token' => 'secret-token',
        ]);

        // The shop itself cannot be displayed without a channel, so the voter is asked directly
        self::assertFalse($this->isMaintenance(Request::create('http://unknown.localhost/en_US/?synolia_maintenance_token=secret-token')));
    }

    private function isMaintenance(Request $request): bool
    {
        $requestStack = self::getContainer()->get(RequestStack::class);
        $requestStack->push($request);

        try {
            return self::getContainer()->get(IsMaintenanceVoterInterface::class)->isMaintenance(
                self::getContainer()->get(MaintenanceConfigurationFactory::class)->get(),
                $request,
            );
        } finally {
            $requestStack->pop();
        }
    }
}
