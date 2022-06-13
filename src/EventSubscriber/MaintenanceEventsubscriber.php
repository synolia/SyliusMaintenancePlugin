<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Contracts\Translation\TranslatorInterface;
use Synolia\SyliusMaintenancePlugin\Factory\MaintenanceConfigurationFactory;
use Synolia\SyliusMaintenancePlugin\Voter\IsMaintenanceVoterInterface;
use Twig\Environment;

final class MaintenanceEventsubscriber implements EventSubscriberInterface
{
    private TranslatorInterface $translator;

    private Environment $twig;

    private MaintenanceConfigurationFactory $configurationFactory;

    private IsMaintenanceVoterInterface $isMaintenanceVoter;

    public function __construct(
        TranslatorInterface $translator,
        Environment $twig,
        MaintenanceConfigurationFactory $configurationFactory,
        IsMaintenanceVoterInterface $isMaintenanceVoter
    ) {
        $this->translator = $translator;
        $this->twig = $twig;
        $this->configurationFactory = $configurationFactory;
        $this->isMaintenanceVoter = $isMaintenanceVoter;
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

        $responseContent = $this->translator->trans('maintenance.ui.message');

        if ('' !== $configuration->getCustomMessage()) {
            $responseContent = $this->twig->render('@SynoliaSyliusMaintenancePlugin/maintenance.html.twig', [
                'custom_message' => $configuration->getCustomMessage(),
            ]);
        }

        $event->setResponse(new Response($responseContent, Response::HTTP_SERVICE_UNAVAILABLE));
    }
}
