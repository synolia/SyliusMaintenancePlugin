<?php

declare(strict_types=1);

namespace Tests\Synolia\SyliusMaintenancePlugin\PHPUnit\Checker;

use PHPUnit\Framework\TestCase;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Channel\Context\ChannelNotFoundException;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Synolia\SyliusMaintenancePlugin\Checker\ChannelChecker;
use Synolia\SyliusMaintenancePlugin\Model\MaintenanceConfiguration;
use Synolia\SyliusMaintenancePlugin\Voter\IsMaintenanceVoterInterface;

final class ChannelCheckerTest extends TestCase
{
    public function testAccessIsGrantedOnAChannelNotInMaintenance(): void
    {
        $channel = $this->createMock(ChannelInterface::class);
        $channel->method('getCode')->willReturn('FASHION_WEB');

        $channelContext = $this->createMock(ChannelContextInterface::class);
        $channelContext->method('getChannel')->willReturn($channel);

        self::assertSame(
            IsMaintenanceVoterInterface::ACCESS_GRANTED,
            $this->createChecker($channelContext)->isMaintenance($this->createConfiguration(), new Request()),
        );
    }

    public function testAccessIsDeniedOnAChannelInMaintenance(): void
    {
        $channel = $this->createMock(ChannelInterface::class);
        $channel->method('getCode')->willReturn('test');

        $channelContext = $this->createMock(ChannelContextInterface::class);
        $channelContext->method('getChannel')->willReturn($channel);

        self::assertSame(
            IsMaintenanceVoterInterface::ACCESS_DENIED,
            $this->createChecker($channelContext)->isMaintenance($this->createConfiguration(), new Request()),
        );
    }

    public function testDecisionIsLeftToOtherCheckersWhenNoChannelMatchesTheRequest(): void
    {
        $channelContext = $this->createMock(ChannelContextInterface::class);
        $channelContext->method('getChannel')->willThrowException(new ChannelNotFoundException());

        self::assertSame(
            IsMaintenanceVoterInterface::ACCESS_DENIED,
            $this->createChecker($channelContext)->isMaintenance($this->createConfiguration(), new Request()),
        );
    }

    private function createChecker(ChannelContextInterface $channelContext): ChannelChecker
    {
        // count() is provided by the Doctrine repository implementation, not by the interface
        $channelRepository = $this->getMockBuilder(ChannelRepositoryInterface::class)
            ->addMethods(['count'])
            ->getMockForAbstractClass();
        $channelRepository->method('count')->willReturn(2);

        return new ChannelChecker($channelRepository, $channelContext);
    }

    private function createConfiguration(): MaintenanceConfiguration
    {
        $configuration = new MaintenanceConfiguration();
        $configuration->setChannels(['test']);

        return $configuration;
    }
}
