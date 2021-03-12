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
use Twig\Environment;

final class MaintenanceEventsubscriber implements EventSubscriberInterface
{
    private Filesystem $filesystem;

    private KernelInterface $kernel;

    private TranslatorInterface $translator;

    private ParameterBagInterface $params;

    private Environment $twig;

    /** @var Environment */
    private $twig;

    public function __construct(
        Filesystem $filesystem,
        KernelInterface $kernel,
        TranslatorInterface $translator,
        ParameterBagInterface $params,
        Environment $twig
    ) {
        $this->filesystem = $filesystem;
        $this->kernel = $kernel;
        $this->translator = $translator;
        $this->params = $params;
        $this->twig = $twig;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => 'handle',
        ];
    }

    public function handle(RequestEvent $event): void
    {
        $getRequestUri = $event->getRequest()->getRequestUri();
        $projectRootPath = $this->kernel->getProjectDir();
        $prefix = $this->params->get('sylius_admin.path_name');

        if (!$this->filesystem->exists($projectRootPath . '/maintenance.yaml')) {
            return;
        }

        if (false !== strpos($getRequestUri, $prefix, 1)) {
            return;
        }

        $event->setResponse(new Response($this->translator->trans('maintenance.ui.message')));

        if ($this->filesystem->exists($projectRootPath . '/templates/maintenance.html.twig')) {
            $event->setResponse(new Response($this->twig->render('/maintenance.html.twig')));
        }
    }
}
