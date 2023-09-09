<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Synolia\SyliusMaintenancePlugin\Factory\MaintenanceConfigurationFactory;
use Synolia\SyliusMaintenancePlugin\Voter\IsMaintenanceVoterInterface;
use Twig\Environment;

final class MaintenanceEventsubscriber implements EventSubscriberInterface
{
    public function __construct(
        private Environment $twig,
        private MaintenanceConfigurationFactory $configurationFactory,
        private IsMaintenanceVoterInterface $isMaintenanceVoter,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => 'handle',
        ];
    }

    public function handle(RequestEvent $event): void
    {
        $configuration = $this->configurationFactory->get();

        if (!$this->isMaintenanceVoter->isMaintenance($configuration, $event->getRequest())) {
            return;
        }

        $responseContent = $this->twig->render('@SynoliaSyliusMaintenancePlugin/maintenance.html.twig', [
            'custom_message' => $configuration->getCustomMessage(),
        ]);

        $event->setResponse(new Response($responseContent, Response::HTTP_SERVICE_UNAVAILABLE));
    }
}
