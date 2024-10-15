<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Synolia\SyliusMaintenancePlugin\Factory\MaintenanceConfigurationFactory;
use Synolia\SyliusMaintenancePlugin\FileManager\ConfigurationFileManager;
use Synolia\SyliusMaintenancePlugin\Model\MaintenanceConfiguration;
use Synolia\SyliusMaintenancePlugin\Voter\IsMaintenanceVoterInterface;
use Twig\Environment;

final class MaintenanceEventsubscriber implements EventSubscriberInterface
{
    public function __construct(
        private Environment $twig,
        private MaintenanceConfigurationFactory $configurationFactory,
        private IsMaintenanceVoterInterface $isMaintenanceVoter,
        private CacheInterface $synoliaMaintenanceCache,
        private int $maintenanceCache,
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
        $configuration = $this->getMaintenanceConfiguration();

        /** @phpstan-ignore-next-line */ /** Call to function method_exists() with RequestEvent and 'isMainRequest' will always evaluate to true. */
        if (method_exists($event, 'isMainRequest') && !$event->isMainRequest()) {
            return;
        }

        /** @TODO Drop after remove Symfony 4.4 compatibility */
        if (method_exists($event, 'isMasterRequest') && !$event->isMasterRequest()) {
            return;
        }

        if (!$this->isMaintenanceVoter->isMaintenance($configuration, $event->getRequest())) {
            return;
        }

        $responseContent = $this->twig->render('@SynoliaSyliusMaintenancePlugin/maintenance.html.twig', [
            'custom_message' => $configuration->getCustomMessage(),
        ]);

        if (!$event->getRequest()->isXmlHttpRequest()) {
            $event->setResponse(new Response($responseContent, Response::HTTP_SERVICE_UNAVAILABLE));

            return;
        }

        $response = [
            'key' => 'maintenance',
            'message' => $configuration->getCustomMessage(),
        ];
        $event->setResponse(new JsonResponse($response, Response::HTTP_SERVICE_UNAVAILABLE));
    }

    private function getMaintenanceConfiguration(): MaintenanceConfiguration
    {
        if (0 !== $this->maintenanceCache) {
            /** @var MaintenanceConfiguration $configuration */
            $configuration = $this->synoliaMaintenanceCache->get(ConfigurationFileManager::MAINTENANCE_CACHE_KEY, function (ItemInterface $item): MaintenanceConfiguration {
                $item->expiresAfter($this->maintenanceCache);

                return $this->configurationFactory->get();
            });

            return $configuration;
        }

        return $this->configurationFactory->get();
    }
}
