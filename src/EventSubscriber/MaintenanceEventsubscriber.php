<?php

declare(strict_types=1);

namespace Synolia\SyliusMaintenancePlugin\EventSubscriber;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Contracts\Translation\TranslatorInterface;
use Synolia\SyliusMaintenancePlugin\FileManager\ConfigurationFileManager;
use Twig\Environment;

final class MaintenanceEventsubscriber implements EventSubscriberInterface
{
    private const START_DATE = 'start_date';

    private const END_DATE = 'end_date';

    private TranslatorInterface $translator;

    private ParameterBagInterface $params;

    private Environment $twig;

    private ConfigurationFileManager $configurationFileManager;

    public function __construct(
        TranslatorInterface $translator,
        ParameterBagInterface $params,
        Environment $twig,
        ConfigurationFileManager $fileManager
    ) {
        $this->translator = $translator;
        $this->params = $params;
        $this->twig = $twig;
        $this->configurationFileManager = $fileManager;
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
        $prefix = $this->params->get('sylius_admin.path_name');
        $ipUser = $event->getRequest()->getClientIp();

        if (!$this->configurationFileManager->fileExists(ConfigurationFileManager::MAINTENANCE_FILE)) {
            return;
        }

        $maintenanceYaml = $this->configurationFileManager->parseMaintenanceYaml();

        if (null !== $maintenanceYaml && isset($maintenanceYaml['ips']) &&
            in_array($ipUser, $maintenanceYaml['ips'], true)
        ) {
            return;
        }

        if (isset($maintenanceYaml['scheduler']) &&
            false === $this->checkScheduledDates($maintenanceYaml['scheduler'])
        ) {
            return;
        }

        if (false !== strpos($getRequestUri, $prefix, 1)) {
            return;
        }

        $event->setResponse(new Response($this->translator->trans('maintenance.ui.message')));

        if ($this->configurationFileManager->fileExists(ConfigurationFileManager::MAINTENANCE_TEMPLATE)) {
            $event->setResponse(new Response($this->twig->render('/maintenance.html.twig')));
        }
    }

    private function checkScheduledDates(array $scheduler): bool
    {
        $now = (new \DateTime())->format('Y-m-d H:i:s');

        if (array_key_exists(self::START_DATE, $scheduler) &&
            array_key_exists(self::END_DATE, $scheduler) &&
            ($now >= $scheduler[self::START_DATE]) &&
            ($now <= $scheduler[self::END_DATE])
        ) {
            return true;
        }

        if (array_key_exists(self::START_DATE, $scheduler) &&
            !array_key_exists(self::END_DATE, $scheduler) &&
            ($now >= $scheduler[self::START_DATE])
        ) {
            return true;
        }

        if (array_key_exists(self::END_DATE, $scheduler) &&
            !array_key_exists(self::START_DATE, $scheduler) &&
            ($now <= $scheduler[self::END_DATE])
        ) {
            return true;
        }

        return false;
    }
}
