<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\EventSubscriber;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class MaintenanceEventsubscriber implements EventSubscriberInterface
{
    private Filesystem $filesystem;

    private KernelInterface $kernel;

    private TranslatorInterface $translator;

    private ParameterBagInterface $params;

    public function __construct(
        Filesystem $filesystem,
        KernelInterface $kernel,
        TranslatorInterface $translator,
        ParameterBagInterface $params
    ) {
        $this->filesystem = $filesystem;
        $this->kernel = $kernel;
        $this->translator = $translator;
        $this->params = $params;
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
        $prefix = $this->params->get('sylius_admin.path_name');

        if ($this->filesystem->exists($this->kernel->getProjectDir() . '/maintenance.yaml')) {
            if (strpos($getRequestUri, $prefix, 1) === false) {
                $event->setResponse(new Response($this->translator->trans('maintenance.ui.message')));
            }
        }
    }
}
