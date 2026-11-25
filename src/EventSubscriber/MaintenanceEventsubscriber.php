<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\EventSubscriber;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Synolia\SyliusMaintenancePlugin\Factory\MaintenanceConfigurationFactory;
use Synolia\SyliusMaintenancePlugin\FileManager\ConfigurationFileManager;
use Synolia\SyliusMaintenancePlugin\Model\MaintenanceConfiguration;
use Synolia\SyliusMaintenancePlugin\Voter\IsMaintenanceVoterInterface;
use Twig\Environment;

final readonly class MaintenanceEventsubscriber implements EventSubscriberInterface
{
    public function __construct(
        private Environment $twig,
        private MaintenanceConfigurationFactory $configurationFactory,
        private IsMaintenanceVoterInterface $isMaintenanceVoter,
        private CacheInterface $synoliaMaintenanceCache,
        #[Autowire(param: 'synolia_maintenance_cache')]
        private int $maintenanceCache,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => 'handle',
            ExceptionEvent::class => 'handleNotFound',
        ];
    }

    public function handle(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $response = $this->getMaintenanceResponse($event->getRequest());

        if ($response instanceof Response) {
            $event->setResponse($response);
        }
    }

    /**
     * The router throws a 404 before the request listener runs when no route matches the URL.
     */
    public function handleNotFound(ExceptionEvent $event): void
    {
        if (!$event->isMainRequest() || !$event->getThrowable() instanceof NotFoundHttpException) {
            return;
        }

        $response = $this->getMaintenanceResponse($event->getRequest());

        if ($response instanceof Response) {
            $event->setResponse($response);
        }
    }

    private function getMaintenanceResponse(Request $request): ?Response
    {
        $configuration = $this->getMaintenanceConfiguration();

        if (!$this->isMaintenanceVoter->isMaintenance($configuration, $request)) {
            return null;
        }

        if ('json' === $request->getContentTypeFormat()) {
            return new JsonResponse([
                'key' => 'maintenance',
                'message' => $configuration->getCustomMessage(),
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $responseContent = $this->twig->render('@SynoliaSyliusMaintenancePlugin/maintenance.html.twig', [
            'custom_message' => $configuration->getCustomMessage(),
        ]);

        return new Response($responseContent, Response::HTTP_SERVICE_UNAVAILABLE);
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
