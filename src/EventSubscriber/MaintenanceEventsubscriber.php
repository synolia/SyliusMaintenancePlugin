<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\EventSubscriber;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

final class MaintenanceEventsubscriber implements EventSubscriberInterface
{
    private const MAINTENANCE_FILE = 'maintenance.yaml';

    private const MAINTENANCE_TEMPLATE = 'templates/maintenance.html.twig';

    private Filesystem $filesystem;

    private KernelInterface $kernel;

    private TranslatorInterface $translator;

    private ParameterBagInterface $params;

    private Environment $twig;

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
        $projectRootPath = $this->kernel->getProjectDir();
        $getRequestUri = $event->getRequest()->getRequestUri();
        $prefix = $this->params->get('sylius_admin.path_name');
        $ipUser = $event->getRequest()->getClientIp();

        if (!$this->filesystem->exists($projectRootPath . '/maintenance.yaml')) {
            return;
        }

        try {
            $maintenanceYaml = Yaml::parseFile($projectRootPath . '/' . self::MAINTENANCE_FILE);
        } catch (ParseException $exception) {
            throw new ParseException('Unable to parse the YAML. ' . $exception->getMessage());
        }

        if ($maintenanceYaml !== null && in_array($ipUser, $maintenanceYaml['ips'], true)) {
            return;
        }

        if (false !== strpos($getRequestUri, $prefix, 1)) {
            return;
        }

        $event->setResponse(new Response($this->translator->trans('maintenance.ui.message')));

        if ($this->filesystem->exists($projectRootPath . '/' . self::MAINTENANCE_TEMPLATE)) {
            $event->setResponse(new Response($this->twig->render('/maintenance.html.twig')));
        }
    }
}
