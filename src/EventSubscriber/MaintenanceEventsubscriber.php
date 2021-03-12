<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final class MaintenanceEventsubscriber implements EventSubscriberInterface
{
    private const PREFIX = 'admin';

    /** @var Filesystem */
    private $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public static function getSubscribedEvents()
    {
        return [
            RequestEvent::class => 'handle',
        ];
    }

    public function handle(RequestEvent $event): void
    {
        $getRequestUri = $event->getRequest()->getRequestUri();
        if ($this->filesystem->exists(\dirname(__DIR__) . '/Resources/config/maintenance.yaml')) {
            if (strpos($getRequestUri, self::PREFIX, 1) === false) {
                $response = new JsonResponse('site en maintenance !!!');
                $event->setResponse($response);
            }
        }
    }
}
